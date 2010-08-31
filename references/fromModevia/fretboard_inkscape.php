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
$mult=90;
ini_set('display_errors', '0');
require_once('ffgeom.php');
require_once('guitar.php');
$unit=isset($_GET['unit']) && in_array($_GET['unit'],array('in','cm'))?$_GET['unit']:'in';
header('Content-type: image/svg+xml');
header('Content-Disposition: attachment; filename="fretboard.svg"');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.0//EN" "http://www.w3.org/TR/2001/REC-SVG-20010904/DTD/svg10.dtd">
<!-- Created with FretFind 2-D (http://www.fretfind.ekips.org) -->
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
   xmlns:svg="http://www.w3.org/2000/svg"
   xmlns="http://www.w3.org/2000/svg"
   xmlns:sodipodi="http://inkscape.sourceforge.net/DTD/sodipodi-0.dtd"
   xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape"
   id="svgfretboard"
   height="',$height,$unit,'" 
   width="',$width,$unit,'">';
?>
  <g
     inkscape:label="Layer 1"
     inkscape:groupmode="layer"
     id="layer1">
	<g id="all">
<?
$style='style="fill:none;fill-opacity:1.0000000;stroke:#000000;stroke-width:1.2499996;stroke-linecap:round;stroke-linejoin:miter;stroke-miterlimit:4.0000000;stroke-opacity:1.0000000"';

	//Output SVG line elements for each string.
	echo '<path id="strings" ',$style,' d="';
	foreach($guitar['strings'] as $string)
	{
		echo 'M',$mult*$string->end1->x,',',$mult*$string->end1->y,'L',$mult*$string->end2->x,',',$mult*$string->end2->y;
	}
	echo '" />',"\n";
	//Output SVG line elements for each fretboard edge
	echo '<path id="edges" ',$style,' d="',
		'M',$mult*$guitar['meta'][0]->end1->x,',',$mult*$guitar['meta'][0]->end1->y,
		'L',$mult*$guitar['meta'][0]->end2->x,',',$mult*$guitar['meta'][0]->end2->y,
		'M',$mult*$guitar['meta'][sizeof($guitar['meta'])-1]->end1->x,
		',',$mult*$guitar['meta'][sizeof($guitar['meta'])-1]->end1->y,
		'L',$mult*$guitar['meta'][sizeof($guitar['meta'])-1]->end2->x,
		',',$mult*$guitar['meta'][sizeof($guitar['meta'])-1]->end2->y,'" />',"\n";

	//output as SVG path for each fretlet. 
	//using paths because they allow for the linecap style 
	//which gives nice rounded ends
	$it=processGuitar4svg();
	echo '<path id="frets" ',$style,' d="';
	foreach($it['strings'] as $string)
	{
		foreach($string as $fret)
		{
			echo 'M',$mult*$fret['fret']->end1->x(),',',$mult*$fret['fret']->end1->y(),
				'L',$mult*$fret['fret']->end2->x(),',',$mult*$fret['fret']->end2->y();
		}
	}
	echo '" />',"\n";
	echo '</g></g></svg>';

?>
