//ffgeom.js
/*
    Copyright (C) 2004 Aaron Cyril Spike

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
var precision=5;

function Point(first_x,first_y) {
	var x=first_x;
	var y=first_y;
	function roundFloat(fltValue, intDecimal) {
		return Math.round(fltValue * Math.pow(10, intDecimal)) / Math.pow(10, intDecimal);
	}

	this.x = function() {
		if (arguments.length>0)
		{
			x=arguments[0];
		}
		return x;
	}
	this.y = function() {
		if (arguments.length>0)
		{
			y=arguments[0];
		}
		return y;
	}
	this.toString = function() {
		return "("+roundFloat(x,precision)+","+roundFloat(y,precision)+")";
	}
	this.translate = function(new_x,new_y) {
		x+=new_x;
		y+=new_y;
		return this;
	}
	this.copy = function()
	{
		return new Point(x,y);
	}
}

function Segment(one,two) {
	this.end1=one;
	this.end2=two;
	
/*	this.end1 = function()
	{
		if (arguments.length>0)
		{
			this.end1=arguments[0];
		}
		return this.end1;
	}
	this.end2 = function()
	{
		if (arguments.length>0)
		{
			this.end2=arguments[0];
		}
		return this.end2;
	}
*/
	this.delta_x = function()
	{
		return this.end2.x()-this.end1.x();
	}
	this.run = function()
	{
		return this.delta_x();
	}
	this.delta_y = function()
	{
		return this.end2.y()-this.end1.y();
	}
	this.rise = function()
	{
		return this.delta_y();
	}
	this.slope = function()
	{
		if (this.delta_x()!=0)
		{
			return this.delta_y()/this.delta_x();
		}
		return Math.acos(1.01);//NaN
	}
	this.intercept = function()
	{
		if (this.delta_x()!=0)
		{
			return this.end2.y()-(this.end1.x()*this.slope());
		}
		return Math.acos(1.01);//NaN
		
	}
	this.distanceToPoint = function(point)
	{
		len=this.length();
		if (len==0) return Math.acos(1.01);
		return Math.abs(((this.end2.x()-this.end1.x())*(this.end1.y()-point.y()))-
			((this.end1.x()-point.x())*(this.end2.y()-this.end1.y())))/len;
	}
	this.angle = function()
	{
		return Math.pi*(Math.atan2(this.delta_y(),this.delta_x()))/180; 	
	}
	this.length = function()
	{
		return Math.sqrt(
			((this.end2.x()-this.end1.x())*(this.end2.x()-this.end1.x())) +
			((this.end2.y()-this.end1.y())*(this.end2.y()-this.end1.y()))
			);
	}
	this.toString = function()
	{
		return "("+this.end1.toString()+":"+this.end2.toString()+")";
	}
	this.pointAt = function(len)
	{
		if (this.length()==0) return new Point(Math.acos(1.01),Math.acos(1.01));
		ratio=len/this.length();
		x=this.end1.x()+(ratio*this.delta_x());
		y=this.end1.y()+(ratio*this.delta_y());
		return new Point(x,y);
	}
	this.pointAtLength = function(len)
	{
		if (this.length()==0) return new Point(Math.acos(1.01),Math.acos(1.01));
		ratio=len/this.length();
		x=this.end1.x()+(ratio*this.delta_x());
		y=this.end1.y()+(ratio*this.delta_y());
		return new Point(x,y);
	}
	this.pointAtRatio = function(ratio)
	{
		x=this.end1.x()+(ratio*this.delta_x());
		y=this.end1.y()+(ratio*this.delta_y());
		return new Point(x,y);
	}
	this.createParallel = function(point)
	{
		return new Segment(new Point(point.x()+this.delta_x(),point.y()+this.delta_y()),point);
	}
	this.intersect = function(line)
	{
		retval=new Point(Math.acos(1.01),Math.acos(1.01));

		x1=this.end1.x();
		x2=this.end2.x();
		x3=line.end1.x();
		x4=line.end2.x();

		y1=this.end1.y();
		y2=this.end2.y();
		y3=line.end1.y();
		y4=line.end2.y();

		denom=((y4-y3)*(x2-x1))-((x4-x3)*(y2-y1));
		num1=((x4-x3)*(y1-y3))-((y4-y3)*(x1-x3));
		num2=((x2-x1)*(y1-y3))-((y2-y1)*(x1-x3));

		num=num1;

		if(denom!=0)
		{	
			x=x1+((num/denom)*(x2-x1));
			y=y1+((num/denom)*(y2-y1));
			retval=new Point(x,y);

		}
		return retval;
	}
	this.translate = function(new_x,new_y) {
		this.end1.translate(new_x,new_y);
		this.end2.translate(new_x,new_y);
		return this;
	}
}
function intersect(one,two)
{	
	retval=new Point(Math.acos(1.01),Math.acos(1.01));
	
	x1=one.end1.x();
	x2=one.end2.x();
	x3=two.end1.x();
	x4=two.end2.x();
	
	y1=one.end1.y();
	y2=one.end2.y();
	y3=two.end1.y();
	y4=two.end2.y();

	denom=((y4-y3)*(x2-x1))-((x4-x3)*(y2-y1));
	num1=((x4-x3)*(y1-y3))-((y4-y3)*(x1-x3));
	num2=((x2-x1)*(y1-y3))-((y2-y1)*(x1-x3));

	num=num1;

	if(denom!=0)
	{	
		x=x1+((num/denom)*(x2-x1));
		y=y1+((num/denom)*(y2-y1));
		retval=new Point(x,y);
		
	}
	return retval;
}



