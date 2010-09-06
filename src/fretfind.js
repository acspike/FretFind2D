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
    Point.prototype.midway = function(point) {
        return new Point((point.x + this.x) * 0.5, (point.y + this.y) * 0.5);
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
    Segment.prototype.pointAtLength = function(len) {
        if (this.length() === 0) {return new Point(Number.NaN, Number.NaN);}
        var ratio = len / this.length();
        return this.pointAtRatio(ratio);
    };
    Segment.prototype.pointAt = Segment.prototype.pointAtLength;
    Segment.prototype.midpoint = function() {
        return this.pointAtRatio(0.5);
    };
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
        var rawlines = strip(scala).split(/[\n\r]+/);
        // strip whitespace from all lines
        // discard comments, lines beginning with !
        var alllines = [];
        var comment = /^!/;
        for (var i=0; i<rawlines.length; i++) {
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
        for (var i=0; i<alllines.length; i++) {
            var line = alllines[i];
            if (line.length > 0) {
                lines.push(line.split(/\s+/)[0]);
            }
        }
        
        if (lines.length !== expected) {
            scale.addError('Error: expected ' + expected.toString() + ' more tones but found ' + lines.length.toString() + '!');
        } else {
            for (var i=0; i<lines.length; i++) {
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
    
    //extend guitar object with frets and other calculated information
    function fretGuitar(guitar) {
        var threshold = 0.0000000001;
        //test strings ends are on nut and bridge
        //if not don't do partials
        var numStrings = guitar.strings.length;
        var doPartials = true;
        var parallelFrets = true;
        
        var nut = new Segment(guitar.edge1.end1.copy(), guitar.edge2.end1.copy());
        var bridge = new Segment(guitar.edge1.end2.copy(), guitar.edge2.end2.copy());
        var midline = new Segment( nut.midpoint(), bridge.midpoint());
        
        //the meta array holds the edge lines and the lines between strings
        //will be used for calculating the extents of the fretlets
        var meta = [guitar.edge1.copy()];
        for (var i=0; i < guitar.strings.length - 1; i++) {
            meta.push(
                new Segment(
                    guitar.strings[i+1].end1.midway(guitar.strings[i].end1), 
                    guitar.strings[i+1].end2.midway(guitar.strings[i].end2)
                )
            );
        }
        meta.push(guitar.edge2.copy());
    
        for (var i=0; i<guitar.strings.length; i++) {
            if ((nut.distanceToPoint(guitar.strings[i].end1) > threshold) ||
                (bridge.distanceToPoint(guitar.strings[i].end2) > threshold)) {
                doPartials = false;
                break;
            }
        }
        
        var denom = ((bridge.end2.y - bridge.end1.y) * (nut.end2.x - nut.end1.x)) -
                           ((bridge.end2.x - bridge.end1.x) * (nut.end2.y - nut.end1.y));
        if (denom !== 0) {
            parallelFrets = false;
        }
        //var intersection = nut.intersect(bridge);
        
        // an array of fretlets for each string
        var strings = [];
        var tones = guitar.scale.steps.length - 1;
        var totalWidth = [];
        var scale = guitar.scale.steps;
        for (var i=0; i<numStrings; i++) {
            var base = guitar.tuning[i] || 0;
            var frets = [];
            frets[0] = {};
            frets[0].fret = doPartials ? new Segment(meta[i].end1.copy(), meta[i+1].end1.copy()) :
                                         new Segment(guitar.strings[i].end1.copy(), guitar.strings[i].end1.copy());
            frets[0].bridgeDist = guitar.strings[i].length();
            frets[0].nutDist = 0;
            frets[0].pFretDist = 0;
            frets[0].width = doPartials ? frets[0].fret.length() : 0;
            frets[0].angle = doPartials ? frets[0].fret.angle() : Number.NaN;
            frets[0].intersection = guitar.strings[i].end1;
            frets[0].midline_intersection = doPartials ? midline.intersect(frets[0].fret) :
                                                         new Point(Number.NaN, Number.NaN);
            var temp = new Segment(midline.end2, frets[0].midline_intersection);
            frets[0].midline_bridgeDist = doPartials ? temp.length() : Number.NaN;
            frets[0].midline_nutDist = doPartials ? 0 : Number.NaN;
            frets[0].midline_pFretDist = doPartials ? 0 : Number.NaN;
            frets[0].totalRatio = 0;
            
            totalWidth[0] += frets[0].width;

            for (j=1; j<=guitar.fret_count; j++) {
                frets[j] = {};
                var step = ((base + (j-1)) % (tones)) + 1;
                var ratio = 1 - (
                    (scale[step][1] * scale[step-1][0]) /
                    (scale[step][0] * scale[step-1][1])
                    );
                var x = frets[j-1].intersection.x +
                    (ratio * (guitar.strings[i].end2.x - frets[j-1].intersection.x));
                var y = frets[j-1].intersection.y+
                    (ratio * (guitar.strings[i].end2.y - frets[j-1].intersection.y));
                frets[j].intersection = new Point(x, y);	
                var temp = new Segment(guitar.strings[i].end2, frets[j].intersection);
                frets[j].bridgeDist = temp.length();
                temp = new Segment(guitar.strings[i].end1, frets[j].intersection);
                frets[j].nutDist = temp.length();
                temp = new Segment(frets[j-1].intersection, frets[j].intersection);
                frets[j].pFretDist = temp.length();
                frets[j].totalRatio = frets[j].nutDist / guitar.strings[i].length();
                
                if (doPartials) {
                    //partials depending on outer strings
                    if (parallelFrets) {
                        temp = nut.createParallel(frets[j].intersection);
                    } else {
                        temp = new Segment(
                            guitar.strings[0].pointAt(guitar.strings[0].length() *
                                frets[j].totalRatio),
                            guitar.strings[numStrings-1].pointAt(guitar.strings[numStrings-1].length() *
                                frets[j].totalRatio)
                            );
                    }
                    frets[j].fret = new Segment(temp.intersect(meta[i]),
                            temp.intersect(meta[i+1]));
                    
                    
                    frets[j].width = frets[j].fret.length();
                    frets[j].angle = frets[j].fret.angle();
                    frets[j].midline_intersection = midline.intersect(frets[j].fret);
                    temp = new Segment(midline.end2, frets[j].midline_intersection);
                    frets[j].midline_bridgeDist = temp.length();
                    temp = new Segment(midline.end1, frets[j].midline_intersection);
                    frets[j].midline_nutDist = temp.length();
                    temp = new Segment(frets[j-1].midline_intersection, frets[j].midline_intersection);
                    frets[j].midline_pFretDist = temp.length();
                } else {
                    frets[j].fret = new Segment(frets[j].intersection, frets[j].intersection);
                    frets[j].width = 0;
                    frets[j].angle = Number.NaN;
                    frets[j].midline_intersection = new Point(Number.NaN, Number.NaN);
                    frets[j].midline_bridgeDist = Number.NaN;
                    frets[j].midline_nutDist = Number.NaN;
                    frets[j].midline_pFretDist = Number.NaN;
                }
                totalWidth[j] += frets[j].width;
            
            }
            strings.push(frets);
        }
        guitar.frets = strings;
        guitar.fretWidths = totalWidth;
        guitar.midline = midline;
        guitar.nut = nut;
        guitar.bridge = bridge;
        guitar.meta = meta;
        return guitar;
    }
    
    var getTable = function(guitar) {
        var output = ['<table class="foundfrets">'+
            '<tr><td colspan="3">Midline</td></tr>'+
            '<tr><td>endpoints</td><td>length</td><td>angle</td></tr>'+
            '<tr><td>'+guitar.midline.toString()+'</td><td>'+
            guitar.midline.length()+'</td><td>'+guitar.midline.angle()+'</td></tr>'+
            '</table><br /><br />\n'];
        output.push('<table class="foundfrets">');
        for (var i=0; i<guitar.frets.length; i++) {
            output.push('<tr><td colspan="11">String ' +(i+1)+'</td></tr>'+
                '<tr><td>#</td><td>to nut</td><td>to fret</td><td>to bridge</td>'+
                '<td>intersection point</td><td>partial width</td><td>angle</td>'+
                '<td>mid to nut</td><td>mid to fret</td><td>mid to bridge</td><td>mid intersection</td>'+
                '</tr>\n');
            for(var j=0; j<guitar.frets[i].length; j++) {
                output.push('<tr><td>'+(j===0?'n':j)+'</td><td>');
                output.push(roundFloat(guitar.frets[i][j].nutDist, precision));
                output.push('</td><td>');
                output.push(roundFloat(guitar.frets[i][j].pFretDist, precision));
                output.push('</td><td>');
                output.push(roundFloat(guitar.frets[i][j].bridgeDist, precision));
                output.push('</td><td>');
                output.push(guitar.frets[i][j].intersection.toString());
                output.push('</td><td>');
                output.push(roundFloat(guitar.frets[i][j].width, precision));
                output.push('</td><td>');
                output.push(roundFloat(guitar.frets[i][j].angle, precision));
                output.push('</td><td>');
                output.push(roundFloat(guitar.frets[i][j].midline_nutDist, precision));
                output.push('</td><td>');
                output.push(roundFloat(guitar.frets[i][j].midline_pFretDist, precision));
                output.push('</td><td>');
                output.push(roundFloat(guitar.frets[i][j].midline_bridgeDist, precision));
                output.push('</td><td>');
                output.push(guitar.frets[i][j].midline_intersection.toString());
                output.push('</td></tr>\n');
            }
        }
        output.push('</table>');
        return output.join('');
    };
    
    var drawGuitar = function(paper, guitar) {
        var stringstyle = {stroke:'rgb(0,0,0)','stroke-width':'1px'};
        var edgestyle = {stroke:'rgb(0,0,255)','stroke-width':'1px'};
        var metastyle = {stroke:'rgb(221,221,221)','stroke-width':'1px'};
        var fretstyle = {stroke:'rgb(255,0,0)','stroke-linecap':'round','stroke-width':'1px'};
        
        paper.clear();
        
        var all = paper.set();
        
        var stringpath = '';
        for (var i=0; i<guitar.strings.length; i++) {
            stringpath += guitar.strings[i].toSVGD();
        }
        var strings = paper.path(stringpath).attr(stringstyle);
        all.push(strings);
        
        var metapath = '';
        for (var i=0; i<guitar.meta.length; i++) {
            metapath += guitar.meta[i].toSVGD();
        }
        var metas = paper.path(metapath).attr(metastyle);
        all.push(metas);
        
        var edges = paper.path(guitar.edge1.toSVGD() + guitar.edge2.toSVGD()).attr(edgestyle);
        all.push(edges);
        
        var ends = paper.path(guitar.nut.toSVGD() + guitar.bridge.toSVGD()).attr(fretstyle);
        all.push(ends);
        
        var fretpath = [];
        for (var i=0; i<guitar.frets.length; i++) {
            for (var j=0; j<guitar.frets[i].length; j++) {
                fretpath.push(guitar.frets[i][j].fret.toSVGD());
            }
        }
        var frets = paper.path(fretpath.join('')).attr(fretstyle);
        all.push(frets);
        
        // calculate scale
        var gw = edges.getBBox().width;
        var gh = edges.getBBox().height;
        var pw = paper.width;
        var ph = paper.height;
        var scale = Math.min(pw/gw,ph/gh);
        all.scale(scale,scale,0,0);
    };
    
    var getSVG = function(guitar) {
        var minx = guitar.edge1.end1.x;
        var maxx = guitar.edge1.end1.x;
        var miny = guitar.edge1.end1.y;
        var maxy = guitar.edge1.end1.y;
        for (var i=0; i<guitar.meta.length; i++) {
            minx = Math.min(minx, guitar.meta[i].end1.x);
            minx = Math.min(minx, guitar.meta[i].end2.x);
            maxx = Math.max(maxx, guitar.meta[i].end1.x);
            maxx = Math.max(maxx, guitar.meta[i].end2.x);
            miny = Math.min(miny, guitar.meta[i].end1.y);
            miny = Math.min(miny, guitar.meta[i].end2.y);
            maxy = Math.max(maxy, guitar.meta[i].end1.y);
            maxy = Math.max(maxy, guitar.meta[i].end2.y);
        }
        var height = maxy - miny;
        var width = maxx - minx;
        output = ['<svg xmlns="http://www.w3.org/2000/svg" viewBox="'+minx+' '+miny+' '+maxx+' '+maxy+
                        '" height="'+height+guitar.units+'" width="'+width+guitar.units+'" >\n'];
        output.push('<defs><style type="text/css"><![CDATA[\n'+
                    '\t.string{stroke:rgb(0,0,0);stroke-width:0.2%;}\n'+
                    '\t.meta{stroke:rgb(221,221,221);stroke-width:0.2%;}\n'+
                    '\t.edge{stroke:rgb(0,0,255);stroke-width:0.2%;}\n'+
                    '\t.fret{stroke:rgb(255,0,0);stroke-linecap:round;stroke-width:0.2%;}\n'+
                    ']'+']></style></defs>\n');
        //Output SVG line elements for each string.
        for (var i=0; i<guitar.strings.length; i++) {
            var string = guitar.strings[i];
            output.push('<line x1="'+string.end1.x+'" x2="'+string.end2.x+
                '" y1="'+string.end1.y+'" y2="'+string.end2.y+'"'+
                ' class="string" />\n');
        }
        for (var i=0; i<guitar.meta.length; i++) {
            var meta = guitar.meta[i];
            output.push('<line x1="'+meta.end1.x+'" x2="'+meta.end2.x+
                '" y1="'+meta.end1.y+'" y2="'+meta.end2.y+'"'+
                ' class="meta" />\n');
        }
        //Output SVG line elements for each fretboard edge
        output.push('<line x1="'+guitar.edge1.end1.x+'" x2="'+guitar.edge1.end2.x+
            '" y1="'+guitar.edge1.end1.y+'" y2="'+guitar.edge1.end2.y,'"'+
            ' class="edge" />\n');
        output.push('<line x1="'+guitar.edge2.end1.x+'" x2="'+guitar.edge2.end2.x+
            '" y1="'+guitar.edge2.end1.y+'" y2="'+guitar.edge2.end2.y,'"'+
            ' class="edge" />\n');

        //output as SVG path for each fretlet. 
        //using paths because they allow for the linecap style 
        //which gives nice rounded ends
        for (var i=0; i<guitar.frets.length; i++) {
            for (var j=0; j<guitar.frets[i].length; j++) {
                output.push('<path d="M'+guitar.frets[i][j].fret.toSVGD()+'" class="fret" />\n');
            }
        }
        return output.join('');
    }
    
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
        //calculate
        fretGuitar: fretGuitar,
        //output
        getTable: getTable,
        drawGuitar: drawGuitar,
        getSVG: getSVG,
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
