<?php
//ffgeom.php
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
$precision=5;

class Point 
{
	var $x;
	var $y;

	function Point($x=0,$y=0)
	{
		$this->x=(float)$x;
		$this->y=(float)$y;
		return $this;
	}
	function x()
	{
		if (func_num_args()>0)
		{
			$this->x=(float)func_get_arg(0);
		}
		return $this->x;
	}
	function y()
	{
		if (func_num_args()>0)
		{
			$this->y=(float)func_get_arg(0);
		}
		return $this->y;
	}
	function toString()
	{
		global $precision;
		return "(".round($this->x,$precision).",".round($this->y,$precision).")";
	}
	function translate($x,$y)
	{
		$this->x+=(float)$x;
		$this->y+=(float)$y;
		return $this;
	}
}
class Segment
{
	var $end1;
	var $end2;
	function Segment($end1,$end2)
	{
		$this->end1=$end1;	
		$this->end2=$end2;	
		return $this;
	}
	function end1()
	{
		if (func_num_args()==1 && get_class(func_get_arg(0))=='point')
		{
			$this->end1=func_get_arg(0);
		}
		return $this->end1;
	}
	function end2()
	{
		if (func_num_args()==1 && get_class(func_get_arg(0))=='point')
		{
			$this->end2=func_get_arg(0);
		}
		return $this->end2;
	}
	function delta_x()
	{
		return $this->end2->x()-$this->end1->x();
	}
	function run()
	{
		return $this->delta_x();
	}
	function delta_y()
	{
		return $this->end2->y()-$this->end1->y();
	}
	function rise()
	{
		return $this->delta_y();
	}
	function slope()
	{
		if ($this->delta_x()!=0)
		{
			return $this->delta_y()/$this->delta_x();
		}
		return acos(1.01);//NaN
	}
	function intercept()
	{
		if ($this->delta_x()!=0)
		{
			return $this->end2->y()-($this->end1->x()*$this->slope());
		}
		return acos(1.01);//NaN
		
	}
	function distanceToPoint($point)
	{
		$len=$this->length();
		if ($len==0) return acos(1.01);
		return abs((($this->end2->x-$this->end1->x)*($this->end1->y-$point->y))-
			(($this->end1->x-$point->x)*($this->end2->y-$this->end1->y)))/$len;
	}
	function angle()
	{
		return rad2deg(atan2($this->delta_y(),$this->delta_x())); 	
	}
	function length()
	{
		return sqrt(
			(($this->end2->x-$this->end1->x)*($this->end2->x-$this->end1->x)) +
			(($this->end2->y-$this->end1->y)*($this->end2->y-$this->end1->y))
			);
	}
	function toString()
	{
		return "(".$this->end1->toString().":".$this->end2->toString().")";
	}
	function pointAt($len)
	{
		if ($this->length()==0) return new Point(acos(1.01),acos(1.01));
		$ratio=$len/$this->length();
		$x=$this->end1->x+($ratio*$this->delta_x());
		$y=$this->end1->y+($ratio*$this->delta_y());
		return new Point($x,$y);
	}
	function createParallel($point)
	{
		return new Segment(new Point($point->x+$this->delta_x(),$point->y+$this->delta_y()),$point);
	}
	function intersect($line)
	{
		$retval=new Point(acos(1.01),acos(1.01));

		$x1=$this->end1->x;
		$x2=$this->end2->x;
		$x3=$line->end1->x;
		$x4=$line->end2->x;

		$y1=$this->end1->y;
		$y2=$this->end2->y;
		$y3=$line->end1->y;
		$y4=$line->end2->y;

		$denom=(($y4-$y3)*($x2-$x1))-(($x4-$x3)*($y2-$y1));
		$num1=(($x4-$x3)*($y1-$y3))-(($y4-$y3)*($x1-$x3));
		$num2=(($x2-$x1)*($y1-$y3))-(($y2-$y1)*($x1-$x3));

		$num=$num1;

		if($denom!=0)
		{	
			$x=$x1+(($num/$denom)*($x2-$x1));
			$y=$y1+(($num/$denom)*($y2-$y1));
			$retval=new Point($x,$y);

		}
		return $retval;
	}
	function translate($x,$y)
	{
		$this->end1->translate($x,$y);
		$this->end2->translate($x,$y);
	}
}
function intersect($one,$two)
{	
	$retval=new Point(acos(1.01),acos(1.01));
	
	$x1=$one->end1->x;
	$x2=$one->end2->x;
	$x3=$two->end1->x;
	$x4=$two->end2->x;
	
	$y1=$one->end1->y;
	$y2=$one->end2->y;
	$y3=$two->end1->y;
	$y4=$two->end2->y;

	$denom=(($y4-$y3)*($x2-$x1))-(($x4-$x3)*($y2-$y1));
	$num1=(($x4-$x3)*($y1-$y3))-(($y4-$y3)*($x1-$x3));
	$num2=(($x2-$x1)*($y1-$y3))-(($y2-$y1)*($x1-$x3));

	$num=$num1;

	if($denom!=0)
	{	
		$x=$x1+(($num/$denom)*($x2-$x1));
		$y=$y1+(($num/$denom)*($y2-$y1));
		$retval=new Point($x,$y);
		
	}
	return $retval;
}


?>
