//ffgeom.js
/*

    Copyright (C) 2004, 2005, 2010 Aaron C Spike
    
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

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

        var extendedFretEnds = [];
        if(parallelFrets) {

            var lastFretIndex = strings.length - 1;
            var endX = Math.abs(guitar.edge2.end2.x - guitar.edge1.end2.x);
            for(var j=0; j<=guitar.fret_count; j++) {

                var leftStart = new Point(0, strings[0][j].fret.end1.y);
                var leftEnd = new Point(strings[0][j].fret.end1.x, strings[0][j].fret.end1.y);
                extendedFretEnds.push(new Segment(leftStart, leftEnd));
                
                var rightStart = new Point(strings[lastFretIndex][j].fret.end2.x, strings[lastFretIndex][j].fret.end1.y);
                var rightEnd = new Point(endX, strings[lastFretIndex][j].fret.end1.y);
                extendedFretEnds.push(new Segment(rightStart, rightEnd));

            }

        }
        else {
            
            var endX = Math.abs(guitar.edge2.end2.x - guitar.edge1.end2.x);
            for(var j=0; j<=guitar.fret_count; j++) {

                var firstPoint = strings[strings.length-1][j].fret.end2;
                var lastPoint = strings[0][j].fret.end1;

                var slope = (lastPoint.y - firstPoint.y) / (lastPoint.x - firstPoint.x);
                if(Math.abs(slope) < threshold) {
                    slope = 0;
                }
                var b = firstPoint.y - slope * firstPoint.x;

                var leftPoint = new Point(0, b);
                var rightPoint = new Point(endX, slope * endX + b);

                if(doPartials) {
                    // multiple scale
                    extendedFretEnds.push(new Segment(leftPoint, firstPoint));
                    extendedFretEnds.push(new Segment(lastPoint, rightPoint));
                }
                else {
                    // individual scale
                    extendedFretEnds.push(new Segment(leftPoint, leftPoint));
                    extendedFretEnds.push(new Segment(rightPoint, rightPoint));
                }

            }
            
        }

        guitar.frets = strings;
        guitar.fretWidths = totalWidth;
        guitar.midline = midline;
        guitar.nut = nut;
        guitar.bridge = bridge;
        guitar.meta = meta;
        guitar.doPartials = doPartials;
        guitar.extendedFretEnds = extendedFretEnds;
        return guitar;
    }
    
    var getTable = function(guitar) {
        var i = 0;
        var output = ['<table class="foundfrets">'+
            '<tr><td colspan="4">Neck</td></tr>'+
            '<tr><td> </td><td>endpoints</td><td>length</td><td>angle</td></tr>'+
            '<tr><td>Nut</td><td>'+guitar.nut.toString()+'</td><td>'+
            guitar.nut.length()+'</td><td>'+guitar.nut.angle()+'</td></tr>'+
            '<tr><td>Edge 1</td><td>'+guitar.meta[0].toString()+'</td><td>'+
            guitar.meta[0].length()+'</td><td>'+guitar.meta[0].angle()+'</td></tr>'+
            '<tr><td>Midline</td><td>'+guitar.midline.toString()+'</td><td>'+
            guitar.midline.length()+'</td><td>'+guitar.midline.angle()+'</td></tr>'+
            '<tr><td>Edge 2</td><td>'+guitar.meta[guitar.meta.length-1].toString()+'</td><td>'+
            guitar.meta[guitar.meta.length-1].length()+'</td><td>'+guitar.meta[guitar.meta.length-1].angle()+'</td></tr>'+
            '<tr><td>Bridge</td><td>'+guitar.bridge.toString()+'</td><td>'+
            guitar.bridge.length()+'</td><td>'+guitar.bridge.angle()+'</td></tr>'+
            '</table><br /><br />\n'];
        output.push('<table class="foundfrets">'+
            '<tr><td colspan="4">Strings</td></tr>'+
            '<tr><td> </td><td>endpoints</td><td>length</td><td>angle</td></tr>');
        for (i=0; i<guitar.strings.length; i++) {
            output.push('<tr><td>String ' +(i+1)+'</td><td>'+guitar.strings[i].toString()+'</td><td>'+
            guitar.strings[i].length()+'</td><td>'+guitar.strings[i].angle()+'</td></tr>');
        }
        output.push('</table><br /><br />\n');
        output.push('<table class="foundfrets">');
        for (i=0; i<guitar.frets.length; i++) {
            output.push('<tr><td colspan="11">String ' +(i+1)+' Frets</td></tr>'+
                '<tr><td>#</td><td>to nut</td><td>to fret</td><td>to bridge</td>'+
                '<td>intersection point</td>');
            if (guitar.doPartials) {
                output.push('<td>partial width</td><td>angle</td>'+
                    '<td>mid to nut</td><td>mid to fret</td><td>mid to bridge</td><td>mid intersection</td>');
            }
            output.push('</tr>\n');
            for(var j=0; j<guitar.frets[i].length; j++) {
                output.push('<tr><td>'+(j===0?'n':j)+'</td><td>');
                output.push(roundFloat(guitar.frets[i][j].nutDist, precision));
                output.push('</td><td>');
                output.push(roundFloat(guitar.frets[i][j].pFretDist, precision));
                output.push('</td><td>');
                output.push(roundFloat(guitar.frets[i][j].bridgeDist, precision));
                output.push('</td><td>');
                output.push(guitar.frets[i][j].intersection.toString());
                output.push('</td>');
                if (guitar.doPartials) {
                  output.push('<td>');
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
                  output.push('</td>');
                }
                output.push('</tr>\n');
            }
        }
        output.push('</table>');
        return output.join('');
    };
    
    var drawGuitar = function(paper, guitar, displayOptions) {
        var stringstyle = {stroke:'rgb(0,0,0)','stroke-width':'1px'};
        var edgestyle = {stroke:'rgb(0,0,255)','stroke-width':'1px'};
        var metastyle = {stroke:'rgb(221,221,221)','stroke-width':'1px'};
        var pfretstyle = {stroke:'rgb(255,0,0)','stroke-linecap':'round','stroke-width':'1px'};
        var ifretstyle = {stroke:'rgb(255,0,0)','stroke-linecap':'round','stroke-width':'3px'};
        var fretstyle = guitar.doPartials ? pfretstyle : ifretstyle;

        paper.clear();
        
        var all = paper.set();
        
        if(displayOptions.showStrings) {
            var stringpath = '';
            for (var i=0; i<guitar.strings.length; i++) {
                stringpath += guitar.strings[i].toSVGD();
            }
            var strings = paper.path(stringpath).attr(stringstyle);
            all.push(strings);
        }
        
        if(displayOptions.showMetas) {
            var metapath = '';
            for (var i=0; i<guitar.meta.length; i++) {
                metapath += guitar.meta[i].toSVGD();
            }
            var metas = paper.path(metapath).attr(metastyle);
            all.push(metas);
        }
        
        if(displayOptions.showFretboardEdges) {
            var edges = paper.path(guitar.edge1.toSVGD() + guitar.edge2.toSVGD()).attr(edgestyle);
            all.push(edges);
        }
        
        var ends = paper.path(guitar.nut.toSVGD() + guitar.bridge.toSVGD()).attr(pfretstyle);
        all.push(ends);
        
        var fretpath = [];
        for (var i=0; i<guitar.frets.length; i++) {
            for (var j=0; j<guitar.frets[i].length; j++) {
                fretpath.push(guitar.frets[i][j].fret.toSVGD());
            }
        }
        var frets = paper.path(fretpath.join('')).attr(fretstyle);
        all.push(frets);

        if(displayOptions.extendFrets) {
            var extendedFretsPath = [];
            for(var j=0; j<guitar.extendedFretEnds.length; j++) {
                extendedFretsPath.push(guitar.extendedFretEnds[j].toSVGD());
            }
            var extendedFrets = paper.path(extendedFretsPath.join('')).attr(fretstyle);
            all.push(extendedFrets);
        }

        if(displayOptions.showBoundingBox) {
            var bbox = getExtents(guitar);
            all.push(paper.rect(bbox.minx, bbox.miny, bbox.width, bbox.height).attr(stringstyle));
        }

        // calculate scale
        var gw = getExtents(guitar).width;
        var gh = getExtents(guitar).height;
        var pw = parseInt(paper.canvas.style.width) || paper.width;
        var ph = parseInt(paper.canvas.style.height) || paper.height;
        var scale = Math.min(pw/gw,ph/gh);
        all.scale(scale,scale,0,0);
    };
    
    var getExtents = function(guitar) {
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
        return {
            minx: minx,
            maxx: maxx,
            miny: miny,
            maxy: maxy,
            height: maxy - miny,
            width: maxx - minx
        };
    };
    
    var getSVG = function(guitar, displayOptions) {
        var x = getExtents(guitar);
        var fret_class = guitar.doPartials ? 'pfret': 'ifret';
        output = ['<svg xmlns="http://www.w3.org/2000/svg" viewBox="'+x.minx+' '+x.miny+' '+x.maxx+' '+x.maxy+
                        '" height="'+x.height+guitar.units+'" width="'+x.width+guitar.units+'" >\n'];
        output.push('<defs><style type="text/css"><![CDATA[\n'+
                    '\t.string{stroke:rgb(0,0,0);stroke-width:0.2%;}\n'+
                    '\t.meta{stroke:rgb(221,221,221);stroke-width:0.2%;}\n'+
                    '\t.edge{stroke:rgb(0,0,255);stroke-width:0.2%;}\n'+
                    '\t.pfret{stroke:rgb(255,0,0);stroke-linecap:round;stroke-width:0.2%;}\n'+
                    '\t.ifret{stroke:rgb(255,0,0);stroke-linecap:round;stroke-width:0.8%;}\n'+
                    '\t.bbox{stroke:rgb(0,0,0);stroke-width:0.2%;fill:rgba(0,0,0,0)}\n'+
                    ']'+']></style></defs>\n');
        //Output SVG line elements for each string.

        if(displayOptions.showStrings) {
            for (var i=0; i<guitar.strings.length; i++) {
                var string = guitar.strings[i];
                output.push('<line x1="'+string.end1.x+'" x2="'+string.end2.x+
                    '" y1="'+string.end1.y+'" y2="'+string.end2.y+'"'+
                    ' class="string" />\n');
            }
        }

        if(displayOptions.showMetas) {
            for (var i=0; i<guitar.meta.length; i++) {
                var meta = guitar.meta[i];
                output.push('<line x1="'+meta.end1.x+'" x2="'+meta.end2.x+
                    '" y1="'+meta.end1.y+'" y2="'+meta.end2.y+'"'+
                    ' class="meta" />\n');
            }
        }

        if(displayOptions.showFretboardEdges) {
            //Output SVG line elements for each fretboard edge
            output.push('<line x1="'+guitar.edge1.end1.x+'" x2="'+guitar.edge1.end2.x+
                '" y1="'+guitar.edge1.end1.y+'" y2="'+guitar.edge1.end2.y,'"'+
                ' class="edge" />\n');
            output.push('<line x1="'+guitar.edge2.end1.x+'" x2="'+guitar.edge2.end2.x+
                '" y1="'+guitar.edge2.end1.y+'" y2="'+guitar.edge2.end2.y,'"'+
                ' class="edge" />\n');
        }

        if(displayOptions.showBoundingBox) {
            var bbox = getExtents(guitar);
            output.push('<rect x="' + bbox.minx + '" y="' + bbox.miny + '"' +
                ' width="' + bbox.width + '" height="' + bbox.height + '"' +
                ' class="bbox" />\n');
        }

        //output as SVG path for each fretlet. 
        //using paths because they allow for the linecap style 
        //which gives nice rounded ends
        for (var i=0; i<guitar.frets.length; i++) {
            for (var j=0; j<guitar.frets[i].length; j++) {
                output.push('<path d="'+guitar.frets[i][j].fret.toSVGD()+'" class="'+fret_class+'" />\n');
            }
        }

        if(displayOptions.extendFrets) {
            for (var i=0; i<guitar.extendedFretEnds.length; i++) {
                output.push('<path d="'+guitar.extendedFretEnds[i].toSVGD()+'" class="'+fret_class+'" />\n');
            }
        }


        output.push('</svg>');
        return output.join('');
    };
    
    var getHTML = function(guitar) {
        var output = '<html><head><title>FretFind</title><style type="text/css">\n'+
            'table.foundfrets {border-collapse: collapse;}\n'+
            'table.foundfrets td {border:1px solid black;padding: 0px 5px 0px 5px;}\n'+
            '</style></head><body>\n'+
            getTable(guitar)+
            '</body></html>';
        return output;
    };
    
    var getDelimited = function(guitar, sep, wrap) {
        if (typeof wrap === 'undefined') {
            wrap = function(x){return x;};
        }
        var output = [wrap('Midline')+'\n'+wrap('endpoints')+sep+wrap('length')+sep+wrap('angle')+'\n'+
            wrap(guitar.midline.toString())+sep+guitar.midline.length()+sep+guitar.midline.angle()+'\n\n'];
        for (var i=0; i<guitar.frets.length; i++) {
            output.push(wrap('String ' +(i+1))+'\n'+
                wrap('#')+sep+wrap('to nut')+sep+wrap('to fret')+sep+wrap('to bridge')+sep+
                wrap('intersection point')+sep+wrap('partial width')+sep+wrap('angle')+sep+
                wrap('mid to nut')+sep+wrap('mid to fret')+sep+wrap('mid to bridge')+sep+wrap('mid intersection')+
                '\n');
            for(var j=0; j<guitar.frets[i].length; j++) {
                output.push(wrap(j===0?'n':j)+sep);
                output.push(roundFloat(guitar.frets[i][j].nutDist, precision));
                output.push(sep);
                output.push(roundFloat(guitar.frets[i][j].pFretDist, precision));
                output.push(sep);
                output.push(roundFloat(guitar.frets[i][j].bridgeDist, precision));
                output.push(sep);
                output.push(wrap(guitar.frets[i][j].intersection.toString()));
                output.push(sep);
                output.push(roundFloat(guitar.frets[i][j].width, precision));
                output.push(sep);
                output.push(roundFloat(guitar.frets[i][j].angle, precision));
                output.push(sep);
                output.push(roundFloat(guitar.frets[i][j].midline_nutDist, precision));
                output.push(sep);
                output.push(roundFloat(guitar.frets[i][j].midline_pFretDist, precision));
                output.push(sep);
                output.push(roundFloat(guitar.frets[i][j].midline_bridgeDist, precision));
                output.push(sep);
                output.push(wrap(guitar.frets[i][j].midline_intersection.toString()));
                output.push('\n');
            }
        }
        return output.join('');
    };
    
    var getCSV = function(guitar) {
        return getDelimited(guitar, ',', function(x){return '"'+x+'"';});
    };
    
    var getTAB = function(guitar) {
        return getDelimited(guitar, '\t', function(x){return x;});
    };
    
    var getPDF = function(guitar, displayOptions) {
        var x = getExtents(guitar);
        
        var unitMult = 1;
        if (guitar.units === 'cm') {
            unitMult = 2.54;
        } else if (guitar.units === 'mm') {
            unitMult = 25.4;
        }
        var margin = 0.5 * unitMult;
        var doc = jsPDF('P', guitar.units, [x.maxx + (2 * margin), x.maxy + (2 * margin)]);
        var lineWidth = (1/72) * unitMult;

        var intersect = guitar.doPartials ? 0 : .02;

        doc.setLineWidth(lineWidth);

        if(displayOptions.showMetas) {
            //Output center line
            doc.line(guitar.center + margin, 0, guitar.center + margin, x.maxy + (2 * margin));
        }

        if(displayOptions.showStrings) {
            //Output line for each string.
            for (var i=0; i<guitar.strings.length; i++) {
                var string = guitar.strings[i];
                doc.line(
                    string.end1.x + margin, 
                    string.end1.y + margin, 
                    string.end2.x + margin, 
                    string.end2.y + margin
                    );
            }
        }
        
        if(displayOptions.showFretboardEdges) {
            //Output line for each fretboard edge
            doc.line(
                guitar.edge1.end1.x + margin, 
                guitar.edge1.end1.y + margin, 
                guitar.edge1.end2.x + margin, 
                guitar.edge1.end2.y + margin
                );
            doc.line(
                guitar.edge2.end1.x + margin, 
                guitar.edge2.end1.y + margin, 
                guitar.edge2.end2.x + margin, 
                guitar.edge2.end2.y + margin
                );
        }


        if(displayOptions.showBoundingBox) {
            // draw a bounding box
            var bbox = getExtents(guitar); //new BoundingBox(guitar.edge1, guitar.edge2);
            doc.rect(bbox.minx + margin, bbox.miny + margin, bbox.width, bbox.height);
        }

        //Output a line for each fretlet. 
        for (var i=0; i<guitar.frets.length; i++) {
            for (var j=0; j<guitar.frets[i].length; j++) {
                doc.line(
                    guitar.frets[i][j].fret.end1.x + intersect + margin,
                    guitar.frets[i][j].fret.end1.y + margin,
                    guitar.frets[i][j].fret.end2.x - intersect + margin,
                    guitar.frets[i][j].fret.end2.y + margin
                    );
            }
        }

        
        if(displayOptions.extendFrets) {
            for (var i=0; i<guitar.extendedFretEnds.length; i++) {
                doc.line(
                    guitar.extendedFretEnds[i].end1.x + intersect + margin,
                    guitar.extendedFretEnds[i].end1.y + margin,
                    guitar.extendedFretEnds[i].end2.x - intersect + margin,
                    guitar.extendedFretEnds[i].end2.y + margin);
            }
        }

        return doc.output();
    };
    
    var getPDFMultipage = function(guitar, displayOptions, pagesize) {
        var x = getExtents(guitar);
        
        // pagesize is either a4 or letter
        if (pagesize === 'a4') {
            var rawPageWidth = 210 / 25.4;
            var rawPageHeight = 297 / 25.4;
        }
        else if(pagesize === 'legal') {
            var rawPageWidth = 8.5;
            var rawPageHeight = 14;
        }
        else {
            pagesize = 'letter';
            var rawPageWidth = 8.5;
            var rawPageHeight = 11;
        }
        
        var pdf = jsPDF('P', guitar.units, pagesize);
        
        var unitMult = 1;
        if (guitar.units === 'cm') {
            unitMult = 2.54;
        } else if (guitar.units === 'mm') {
            unitMult = 25.4;
        }
        var lineWidth = (1/72) * unitMult;
        var pageWidth = rawPageWidth * unitMult;
        var pageHeight = rawPageHeight * unitMult;
        var pageOverlap = 0.5 * unitMult;
        var printableHeight = pageHeight - ( 2 * pageOverlap );
        var printableWidth = pageWidth - ( 2 * pageOverlap );
        var yPages = Math.ceil( x.height / printableHeight );
        var xPages = Math.ceil( x.width / printableWidth );

        var intersect = guitar.doPartials ? 0 : .02;
        
        for (var i=0; i<yPages; i++) {
            for (var j=0; j<xPages; j++) {
                var yOffset = (pageHeight * i) - (pageOverlap * (1 + (2 * i)));
                var xOffset = (pageWidth * j) - (pageOverlap * (1 + (2 * j)));
                if (i>0 || j>0) {
                    pdf.addPage();
                }
                pdf.setLineWidth(lineWidth);
                pdf.setDrawColor(192);
                pdf.rect(pageOverlap, pageOverlap, printableWidth, printableHeight);        
                pdf.setDrawColor(0);
        
                if(displayOptions.showMetas) {
                    //Output center line
                    pdf.line(guitar.center - xOffset, 0, guitar.center - xOffset, pageHeight);
                }

                if(displayOptions.showStrings) {
                    //output a line for each string
                    for (var k=0; k<guitar.strings.length; k++) {
                        pdf.line(
                            guitar.strings[k].end1.x - xOffset,
                            guitar.strings[k].end1.y - yOffset,
                            guitar.strings[k].end2.x - xOffset,
                            guitar.strings[k].end2.y - yOffset
                            );
                    }
                }
    
                if(displayOptions.showFretboardEdges) {
                    //output a line for each fretboard edge
                    pdf.line(
                        guitar.edge1.end1.x - xOffset,
                        guitar.edge1.end1.y - yOffset,
                        guitar.edge1.end2.x - xOffset,
                        guitar.edge1.end2.y - yOffset
                        );
                    pdf.line(
                        guitar.edge2.end1.x - xOffset,
                        guitar.edge2.end1.y - yOffset,
                        guitar.edge2.end2.x - xOffset,
                        guitar.edge2.end2.y - yOffset
                        );
                }
    
                if(displayOptions.showBoundingBox) {
                    // draw a bounding box
                    var bbox = getExtents(guitar);
                    pdf.rect(bbox.minx - xOffset, bbox.miny - yOffset, bbox.width, bbox.height);
                }

                //output a line for each fret on each string
                for (var k=0; k<guitar.frets.length; k++) {
                    for (var l=0; l<guitar.frets[k].length; l++) {
                        pdf.line(
                            guitar.frets[k][l].fret.end1.x + intersect - xOffset,
                            guitar.frets[k][l].fret.end1.y - yOffset,
                            guitar.frets[k][l].fret.end2.x - intersect - xOffset,
                            guitar.frets[k][l].fret.end2.y - yOffset
                            );
                    }
                }
                
                if(displayOptions.extendFrets) {
                    for (var k=0; k<guitar.extendedFretEnds.length; k++) {
                        pdf.line(
                            guitar.extendedFretEnds[k].end1.x + intersect - xOffset,
                            guitar.extendedFretEnds[k].end1.y - yOffset,
                            guitar.extendedFretEnds[k].end2.x - intersect - xOffset,
                            guitar.extendedFretEnds[k].end2.y - yOffset);
                    }
                }

            }
        }
        return pdf.output();
    };
    
    // TODO: 
    // - more compatible DXF borrowing from inkscape?
    var getDXF = function(guitar, displayOptions) {
        //References: Minimum Requirements for Creating a DXF File of a 3D Model By Paul Bourke
        var seg2dxf = function(seg, dot) {
            if (typeof dot === 'undefined') {
                dot = false;
            }
            var intersect = 0;
            if (dot) {
                intersect = .02;
            }
            return '0\nLINE\n8\n2\n62\n4\n10\n'+
                (seg.end1.x+intersect)+'\n20\n'+
                seg.end1.y+'\n30\n0\n11\n'+
                (seg.end2.x-intersect)+'\n21\n'+
                seg.end2.y+'\n31\n0\n';
        };
        var x = getExtents(guitar);
        var output = [];
        output.push('999\nDXF created by FretFind2D\n');
        output.push('0\nSECTION\n2\nENTITIES\n');
        
        if(displayOptions.showStrings) {
            //Output line for each string.
            for (var i=0; i<guitar.strings.length; i++) {
                output.push(seg2dxf(guitar.strings[i]));
            }
        }
        
        if(displayOptions.showFretboardEdges) {
            //Output line for each fretboard edge
            output.push(seg2dxf(guitar.edge1));
            output.push(seg2dxf(guitar.edge2));
        }

        if(displayOptions.showBoundingBox) {
            //Output bounding box
            var bbox = getExtents(guitar); //new BoundingBox(guitar.edge1, guitar.edge2);
            var topLeft = new Point(bbox.minx, bbox.miny);
            var bottomLeft = new Point(bbox.minx, bbox.miny+bbox.height);
            var bottomRight = new Point(bbox.minx+bbox.width, bbox.miny+bbox.height);
            var topRight = new Point(bbox.minx+bbox.width, bbox.miny);
            var leftSeg = new Segment(topLeft, bottomLeft);
            var bottomSeg = new Segment(bottomLeft, bottomRight);
            var rightSeg = new Segment(bottomRight, topRight);
            var topSeg = new Segment(topRight, topLeft);

            output.push(seg2dxf(leftSeg));
            output.push(seg2dxf(bottomSeg));
            output.push(seg2dxf(rightSeg));
            output.push(seg2dxf(topSeg));
        }

        //Output a line for each fretlet. 
        for (var i=0; i<guitar.frets.length; i++) {
            for (var j=0; j<guitar.frets[i].length; j++) {
                output.push(seg2dxf(guitar.frets[i][j].fret,true));
            }
        }

        if(displayOptions.extendFrets) {
            for (var i=0; i<guitar.extendedFretEnds.length; i++) {
                output.push(seg2dxf(guitar.extendedFretEnds[i],true));
            }
        }

        output.push('0\nENDSEC\n0\nEOF\n');
        
        return output.join('');
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
    var setTuning = function(tuning_id, string_count_id, change_callback, tunings) {
        var strings = getInt(string_count_id);
        if (typeof tunings === 'undefined') {
            tunings = getTuning(tuning_id);
        }
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
        getPDF: getPDF,
        getPDFMultipage: getPDFMultipage,
        getDXF: getDXF,
        getSVG: getSVG,
        getHTML: getHTML,
        getDelimited: getDelimited,
        getCSV: getCSV,
        getTAB: getTAB,
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
