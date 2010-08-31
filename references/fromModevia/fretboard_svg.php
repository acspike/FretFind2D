<?php
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

ini_set('display_errors', '0');
require_once('ffgeom.php');
require_once('guitar.php');
$unit=isset($_GET['unit']) && in_array($_GET['unit'],array('in','cm'))?$_GET['unit']:'in';
header('Content-type: image/svg+xml');
header('Content-Disposition: attachment; filename="fretboard.svg"');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.0//EN" "http://www.w3.org/TR/2001/REC-SVG-20010904/DTD/svg10.dtd">
<?php
//find extents of view box
$minx=$maxx=$guitar['strings'][0]->end2->x();
$miny=$maxy=$guitar['strings'][0]->end2->y();
$g=$guitar['strings'];
$g[]=$guitar['meta'][0];
$g[]=$guitar['meta'][sizeof($guitar['meta'])-1];
foreach($g as $string)
{
	$p=$string->end1;
	if($p->x<$minx)$minx=$p->x;
	if($p->x>$maxx)$maxx=$p->x;
	if($p->y<$miny)$miny=$p->y;
	if($p->y>$maxy)$maxy=$p->y;
	$p=$string->end2;
	if($p->x<$minx)$minx=$p->x;
	if($p->x>$maxx)$maxx=$p->x;
	if($p->y<$miny)$miny=$p->y;
	if($p->y>$maxy)$maxy=$p->y;
	
}
$w=$maxx - $minx;
$h=$maxy - $miny;

$width=$w;
$height=$h;

echo '<svg
xmlns="http://www.w3.org/2000/svg"
 viewBox="',$minx,' ',$miny,' ',$maxx,' ',$maxy,'" height="',$height,$unit,'" width="',$width,$unit,'" >';

?>
<defs>
<style type="text/css"><![CDATA[

	.string
	{
		stroke:rgb(0,0,0);
		stroke-width:0.2%;
	}
	.edge
	{
		stroke:rgb(0,0,255);
		stroke-width:0.2%;
	}
	.fret
	{
		stroke:rgb(255,0,0);
		stroke-linecap:round;
		stroke-width:0.2%;
	}
]]></style>
</defs>
<?


	//Output SVG line elements for each string.
	foreach($guitar['strings'] as $string)
	{
		echo '<line x1="',$string->end1->x,'" x2="',$string->end2->x,
			'" y1="',$string->end1->y,'" y2="',$string->end2->y,'"',
			' class="string" />',"\n";
	}
	//Output SVG line elements for each fretboard edge
	echo '<line x1="',$guitar['meta'][0]->end1->x,'" x2="',$guitar['meta'][0]->end2->x,
		'" y1="',$guitar['meta'][0]->end1->y,'" y2="',$guitar['meta'][0]->end2->y,'"',
		' class="edge" />',"\n";
	echo '<line x1="',$guitar['meta'][sizeof($guitar['meta'])-1]->end1->x,
		'" x2="',$guitar['meta'][sizeof($guitar['meta'])-1]->end2->x,
		'" y1="',$guitar['meta'][sizeof($guitar['meta'])-1]->end1->y,'" y2="',
		$guitar['meta'][sizeof($guitar['meta'])-1]->end2->y,'"',
		' class="edge" />',"\n";

	//output as SVG path for each fretlet. 
	//using paths because they allow for the linecap style 
	//which gives nice rounded ends
	$it=processGuitar4svg();
	foreach($it['strings'] as $string)
	{
		foreach($string as $fret)
		{
			echo '<path d="M',
			$fret['fret']->end1->x(),' ',$fret['fret']->end1->y(),
			'L',$fret['fret']->end2->x(),' ',$fret['fret']->end2->y(),
			'" class="fret" />',"\n";
		}
	}
/*
	//Continue the nut and bridge out to an intersection
	//this is to test the claim of the novax patent that 
	//all of the frets would converge to a point.
	//our Fretboards may infact not be covered by the patent
	//if its language is too specific.
	$point=intersect($it['nut'],$it['bridge']);
	if ($point->x().' '!='NAN ')
	{
		$nut=new Segment($point,$it['nut']->end1());
		$bridge=new Segment($point,$it['bridge']->end1());
		echo '<line x1="',$nut->end1->x,'" x2="',$nut->end2->x,
			'" y1="',$nut->end1->y,'" y2="',$nut->end2->y,'" class="string" />',"\n";
		echo '<line x1="',$bridge->end1->x,'" x2="',$bridge->end2->x,
			'" y1="',$bridge->end1->y,'" y2="',$bridge->end2->y,'" class="string" />',"\n";
	}
*/	
	echo '</svg>',"\n";

?>
