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

//ini_set('display_errors', '0');
define('FPDF_FONTPATH','fpdf/font/');
require_once('fpdf/fpdf.php');

require_once('ffgeom.php');
require_once('guitar.php');
$unit=isset($_GET['unit']) && in_array($_GET['unit'],array('in','cm'))?$_GET['unit']:'in';

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

$pdf=new FPDF('P',$unit,array($w,$h));
$pdf->AddPage();

//output a line for each string
foreach($guitar['strings'] as $string)
{
	$pdf->Line($string->end1->x,$string->end1->y,$string->end2->x,$string->end2->y);
}

//output a line for each fretboard edge
	$pdf->Line($guitar['meta'][0]->end1->x,$guitar['meta'][0]->end1->y,$guitar['meta'][0]->end2->x,$guitar['meta'][0]->end2->y);
	$pdf->Line($guitar['meta'][sizeof($guitar['meta'])-1]->end1->x,$guitar['meta'][sizeof($guitar['meta'])-1]->end1->y,$guitar['meta'][sizeof($guitar['meta'])-1]->end2->x,$guitar['meta'][sizeof($guitar['meta'])-1]->end2->y);

//output a line for each fret on each string
$it=processGuitar4svg();
foreach($it['strings'] as $string)
{
	foreach($string as $fret)
	{
		$pdf->Line($fret['fret']->end1->x(),$fret['fret']->end1->y(),$fret['fret']->end2->x(),$fret['fret']->end2->y());
	}
}

$pdf->Output('fretboard.pdf','D');


?>
