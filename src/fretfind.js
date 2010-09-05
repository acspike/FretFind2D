//ffgeom.js
/*
    Copyright (C) 2004, 2010 Aaron Cyril Spike
*/
var ff = (function(){

    var precision=5;

    // utility functions
    // we use but don't export
    function roundFloat(fltValue, intDecimal) {
        return Math.round(fltValue * Math.pow(10, intDecimal)) / Math.pow(10, intDecimal);
    }
    // remove whitespace from both ends of a string
    function strip(str) {
        return str.replace(/^\s+|\s+$/g,'');
    }


    function Point(x,y) {
        this.x = x;
        this.y = y;
    }
    Point.prototype.toString = function() {
        return "(" + roundFloat(this.x, precision) + "," + roundFloat(this.y, precision) + ")";
    };
    Point.prototype.translate = function(x, y) {
        this.x += x;
        this.y += y;
        return this;
    };
    Point.prototype.copy = function() {
        return new Point(this.x, this.y);
    };
    Point.prototype.equals = function(point) {
        return this.x === point.x && this.y === point.y;
    };


    function Segment(one, two) {
        this.end1 = one;
        this.end2 = two;
    }
    Segment.prototype.deltaX = function() {
        return this.end2.x - this.end1.x;
    };
    Segment.prototype.run = Segment.prototype.deltaX;
    Segment.prototype.deltaY = function() {
        return this.end2.y - this.end1.y;
    };
    Segment.prototype.rise = Segment.prototype.deltaY;
    Segment.prototype.slope = function() {
        if (this.deltaX() !== 0) {
            return this.deltaY() / this.deltaX();
        }
        return Number.NaN;
    };
    Segment.prototype.intercept = function() {
        if (this.deltaX() !== 0) {
            return this.end2.y - (this.end2.x * this.slope());
        }
        return Number.NaN;
    };
    Segment.prototype.distanceToPoint = function(point) {
        var len = this.length();
        if (len === 0) {return Number.NaN;}
        return Math.abs(((this.end2.x - this.end1.x) * (this.end1.y - point.y)) -
            ((this.end1.x - point.x) * (this.end2.y - this.end1.y))) / len;
    };
    Segment.prototype.angle = function() {
        return (180 / Math.PI) * Math.atan2(this.deltaY(), this.deltaX());     
    };
    Segment.prototype.length = function() {
        return Math.sqrt(
            ((this.end2.x - this.end1.x) * (this.end2.x - this.end1.x)) +
            ((this.end2.y - this.end1.y) * (this.end2.y - this.end1.y))
            );
    };
    Segment.prototype.toString = function() {
        return "(" + this.end1.toString() + ":" + this.end2.toString() + ")";
    };
    Segment.prototype.pointAtRatio = function(ratio) {
        var x = this.end1.x + (ratio * this.deltaX());
        var y = this.end1.y + (ratio * this.deltaY());
        return new Point(x, y);
    };
    Segment.prototype.pointAtLength = function(len)    {
        if (this.length() === 0) {return new Point(Number.NaN, Number.NaN);}
        var ratio = len / this.length();
        return this.pointAtRatio(ratio);
    };
    Segment.prototype.pointAt = Segment.prototype.pointAtLength;
    Segment.prototype.createParallel = function(point) {
        return new Segment(new Point(point.x + this.deltaX(), point.y + this.deltaY()), point.copy());
    };
    // intersection of projected ideal line; not constrained by segment endpoints
    Segment.prototype.intersect = function(line) {
        var retval = new Point(Number.NaN, Number.NaN);

        var x1 = this.end1.x;
        var x2 = this.end2.x;
        var x3 = line.end1.x;
        var x4 = line.end2.x;

        var y1 = this.end1.y;
        var y2 = this.end2.y;
        var y3 = line.end1.y;
        var y4 = line.end2.y;

        var denom = ((y4 - y3) * (x2 - x1)) - ((x4 - x3) * (y2 - y1));
        var num1 = ((x4 - x3) * (y1 - y3)) - ((y4 - y3) * (x1 - x3));
        var num2 = ((x2 - x1) * (y1 - y3)) - ((y2 - y1) * (x1 - x3));

        var num = num1;

        if (denom !== 0) {
            x = x1 + ((num / denom) * (x2 - x1));
            y = y1 + ((num / denom) * (y2 - y1));
            retval = new Point(x, y);
        }
        return retval;
    };
    Segment.prototype.translate = function(x, y) {
        this.end1.translate(x, y);
        this.end2.translate(x, y);
        return this;
    };
    // equality test allows for flipped swapped endpoints
    // a number of other equality test might be appropriate 
    // we'll have to see how it gets used
    Segment.prototype.equals = function(line) {
        return (this.end1.equals(line.end1) && this.end2.equals(line.end2)) || 
            (this.end1.equals(line.end2) && this.end2.equals(line.end1));
    };
    Segment.prototype.copy = function() {
        return new Segment(this.end1.copy(), this.end2.copy());
    };
    Segment.prototype.toSVGD = function() {
        return 'M' + this.end1.x + ' ' + this.end1.y + 'L' + this.end2.x + ' ' + this.end2.y;
    };
    


    function Scale() {
        // initial step 0 or 1/1 is implicit
        this.steps = [[1,1]];
        this.title = '';
        this.errors = 0;
        this.errorstrings = [];        
    }
    Scale.prototype.addError = function(str) {
        this.errors++;
        this.errorstrings.push(str);
        return this;
    };
    Scale.prototype.addStep = function(num,denom) {
        this.steps.push([num,denom]);
        return this;
    };
    
    function etScale(tones, octave) {
        if (typeof octave === 'undefined' ) {
            octave = 2;
        }
        var scale = new Scale();
        if (tones === 0) {
            scale.addError('Error: Number of tones must be non zero!');
        } else {
            var ratio = Math.pow(octave,1/tones);
            scale.addStep(ratio,1);
            scale.title = tones.toString() + ' root of ' + octave.toString() + ' Equal Temperament';
        }
        return scale;
    }
    function scalaScale(scala) {
        var scale = new Scale();
        
        // split lines
        var rawlines = strip(scala).split(/(\n|\r)+/);
        // strip whitespace from all lines
        // discard comments, lines beginning with !
        var alllines = [];
        var comment = /^!/;
        for (var i in rawlines) {
            var line = strip(rawlines[i]);
            if (!comment.test(line)) {
                alllines.push(line);
            }
        }
        
        // first line may be blank and contains the title
        scale.title = alllines.shift();
        
        // second line indicates the number of note lines that should follow
        var expected = parseInt(alllines.shift(), 10);
        
        // discard blank lines and anything following whitespace
        var lines = [];
        for (var i in alllines) {
            var line = alllines[i];
            if (line.length() > 0) {
                lines.push(line.split(/\s+/)[0]);
            }
        }
        
        if (lines.length() !== expected) {
            scale.addError('Error: expected ' + expected.toString() + ' more tones but found ' + lines.length().toString() + '!');
        } else {
            for (var i in lines) {
                var l = lines[i];
                // interpret any line containing a dot as cents
                // everything else is a ratio
                var num = 0;
                var denom = 1;
                if (/\./.test(l)) {
                    num = Math.pow(2,parseFloat(l)/1200);
                } else if (/\//.test(l)) {
                    l = l.split(/\//);
                    num = parseInt(l[0], 10);
                    denom = parseInt(l[1], 10);
                } else {
                    num = parseInt(l, 10);
                }
                scale.addStep(num, denom);
                
                if (num < 0 || denom <= 0) {
                    scale.addError('Error at "' + l + '": Negative and undefined ratios are not allowed!');
                }   
            }
        }
        return scale;
    }

    var drawGuitar = function(paper, guitar) {
        var stringstyle = {stroke:'rgb(0,0,0)','stroke-width':'1px'};
        var edgestyle = {stroke:'rgb(0,0,255)','stroke-width':'1px'};
        var fretstyle = {stroke:'rgb(255,0,0)','stroke-linecap':'round','stroke-width':'1px'};
        paper.clear();
        var stringpath = '';
        for (var i=1; i<guitar.strings.length-1; i++) {
            stringpath += guitar.strings[i].toSVGD();
        }
        var all = paper.set();
        var strings = paper.path(stringpath).attr(stringstyle);
        var edge1 = guitar.strings[0];
        var edge2 = guitar.strings[guitar.strings.length-1];
        var edges = paper.path(edge1.toSVGD() + edge2.toSVGD()).attr(edgestyle);
        
        var nut = new Segment(edge1.end1.copy(), edge2.end1.copy());
        var bridge = new Segment(edge1.end2.copy(), edge2.end2.copy());
        
        var ends = paper.path(nut.toSVGD() + bridge.toSVGD()).attr(fretstyle);
        
        all.push(strings, edges, ends);
        
        // calculate scale
        var gw = edges.getBBox().width;
        var gh = edges.getBBox().height;
        var pw = paper.width;
        var ph = paper.height;
        var scale = Math.min(pw/gw,ph/gh);
        all.scale(scale,scale,0,0);
    };
    
    var getAlt = function(id) {
        return $('#'+id).find('dt.selected-alt').attr('id');
    };
    var getStr = function(id) {
        return document.getElementById(id).value;
    };
    var getFlt = function(id) {
        return parseFloat(document.getElementById(id).value);
    };
    var getInt = function(id) {
        return parseInt(document.getElementById(id).value);
    };
    var getTuning = function(id) {
        var tunings = [];
        $('#'+id+' > input').each(function(_,item){tunings.push(parseInt(item.value, 10));});
        return tunings;
    };
    var setTuning = function(tuning_id, string_count_id, change_callback) {
        var strings = getInt(string_count_id);
        var tunings = getTuning(tuning_id);
        var output = '';
        for (var i=0; i<strings; i++) {
            output += 'string '+(i+1)+': <input type="text" value="'+(tunings[i] || 0)+'" /><br />';
        }
        $('#'+tuning_id).html(output);
        $('#'+tuning_id+' > input').change(change_callback);
    };
    var initHelp = function(form_id) {
        //create help links for each element in the help class 
        //append to previous sibling dt
        $('#'+form_id).find('dd.help').prev().prev().
            append(' [<a class="help" href="#">?</a>]').
            find('a.help').toggle(
                function(){$(this).parent().next().next().css('display','block');},
                function(){$(this).parent().next().next().css('display','none');}
            );
    };
    var initAlternatives = function(form_id, change_callback) {
        //create alternative switches
        $('#'+form_id).find('dl.alternative').each(function(_,item){
            $(item).children('dt').each(function(_,jtem){
                var alt = $(jtem).next();
                $(jtem).click(function(){
                    //visual que for selected
                    $(this).parent().children('dt').removeClass('selected-alt');
                    $(this).addClass('selected-alt');
                    //display selected dd
                    $(this).parent().children('dd').css('display','none');
                    alt.css('display','block');
                    change_callback();
                });
            });
            //reorder dt to top
            $(item).children('dt').prependTo($(item));
            //initialize first as selected
            $(item).children('dt').first().click();
        });
    };
    
    return {
        //geometry 
        getPrecision: function() {return precision;},
        setPrecision: function(x) {precision = x;},
        Point: Point,
        Segment: Segment,
        //scales
        Scale: Scale,
        etScale: etScale,
        scalaScale: scalaScale,
        //output
        drawGuitar: drawGuitar,
        //form helpers
        getAlt: getAlt,
        getStr: getStr,
        getFlt: getFlt,
        getInt: getInt,
        getTuning: getTuning,
        setTuning: setTuning,
        initHelp: initHelp,
        initAlternatives: initAlternatives
    };
}());
