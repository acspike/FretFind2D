//ffgeom.js
/*
    Copyright (C) 2004, 2010 Aaron Cyril Spike

    This file is part of FretFind 2-D.

    FretFind 2-D is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    FretFind 2-D is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with FretFind 2-D; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
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
	}
	Point.prototype.translate = function(x, y) {
		this.x += x;
		this.y += y;
		return this;
	}
	Point.prototype.copy = function() {
		return new Point(this.x, this.y);
	}
	Point.prototype.equals = function(point) {
		return this.x === point.x && this.y === point.y;
	}


    function Segment(one, two) {
	    this.end1 = one;
	    this.end2 = two;
    }	
	Segment.prototype.deltaX = function() {
		return this.end2.x - this.end1.x;
	}
	Segment.prototype.run = Segment.prototype.deltaX;
	Segment.prototype.deltaY = function() {
		return this.end2.y - this.end1.y;
	}
	Segment.prototype.rise = Segment.prototype.deltaY;
	Segment.prototype.slope = function() {
		if (this.deltaX() !== 0) {
			return this.deltaY() / this.deltaX();
		}
		return Number.NaN;
	}
	Segment.prototype.intercept = function() {
		if (this.deltaX() !== 0) {
			return this.end2.y - (this.end2.x * this.slope());
		}
		return Number.NaN;
	}
	Segment.prototype.distanceToPoint = function(point) {
		var len = this.length();
		if (len === 0) {return Number.NaN;}
		return Math.abs(((this.end2.x - this.end1.x) * (this.end1.y - point.y)) -
			((this.end1.x - point.x) * (this.end2.y - this.end1.y))) / len;
	}
	Segment.prototype.angle = function() {
		return (180/Math.PI) * Math.atan2(this.deltaY(), this.deltaX()); 	
	}
	Segment.prototype.length = function() {
		return Math.sqrt(
			((this.end2.x - this.end1.x) * (this.end2.x - this.end1.x)) +
			((this.end2.y - this.end1.y) * (this.end2.y - this.end1.y))
			);
	}
	Segment.prototype.toString = function() {
		return "(" + this.end1.toString() + ":" + this.end2.toString() + ")";
	}
	Segment.prototype.pointAtRatio = function(ratio) {
		var x = this.end1.x + (ratio * this.deltaX());
		var y = this.end1.y + (ratio * this.deltaY());
		return new Point(x, y);
	}
	Segment.prototype.pointAtLength = function(len)	{
		if (this.length() === 0) {return new Point(Number.NaN, Number.NaN);}
		var ratio = len / this.length();
		return this.pointAtRatio(ratio);
	}
    Segment.prototype.pointAt = Segment.prototype.pointAtLength;
	Segment.prototype.createParallel = function(point) {
		return new Segment(new Point(point.x + this.deltaX(), point.y + this.deltaY()), point.copy());
	}
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
	}
	Segment.prototype.translate = function(x, y) {
		this.end1.translate(x, y);
		this.end2.translate(x, y);
		return this;
	}
	// equality test allows for flipped swapped endpoints
	// a number of other equality test might be appropriate 
	// we'll have to see how it gets used
	Segment.prototype.equals = function(line) {
	    return (this.end1.equals(line.end1) && this.end2.equals(line.end2)) || 
	        (this.end1.equals(line.end2) && this.end2.equals(line.end1));
	}
	Segment.prototype.copy = function() {
	    return new Segment(this.end1.copy(), this.end2.copy());
	}


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
    }
    Scale.prototype.addStep = function(num,denom) {
        this.steps.push([num,denom]);
        return this;
    }
    
    function etScale(tones, octave) {
        if (typeof octave === 'undefined' ) octave = 2;
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
        var expected = parseInt(alllines.shift());
        
        // discard blank lines and anything following whitespace
        var lines = [];
        for (var i in alllines) {
            var line = alllines[i];
            if (line.length() > 0) {
                lines.push(line.split(/\s+/)[0])
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
                    num = parseInt(l[0]);
                    denom = parseInt(l[1]);
                } else {
                    num = parseInt(l);
                }
                scale.addStep(num, denom);
                
                if (num < 0 || denom <= 0) {
                    scale.addError('Error at "' + l + '": Negative and undefined ratios are not allowed!');
                }   
            }
        }
        return scale;
    }
	
    return {
        getPrecision: function() {return precision;},
        setPrecision: function(x) {precision = x;},
        Point: Point,
        Segment: Segment,
        Scale: Scale,
        etScale: etScale,
        scalaScale: scalaScale
    };
}())
