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

#set page size
$unitMult    = $unit == 'in' ? 1 : 2.54;
$pageWidth   = 8.5 * $unitMult;
$pageHeight  = 11 * $unitMult;
$pageOverlap = 0.5 * $unitMult;
$printableHeight = $pageHeight - ( 2 * $pageOverlap );
$printableWidth = $pageWidth - ( 2 * $pageOverlap );
$yPages = ceil( $h / $printableHeight );
$xPages = ceil( $w / $printableWidth );

$pdf=new FPDF('P',$unit,array($pageWidth,$pageHeight));

$it=processGuitar4svg();
for ($i=0;$i<$yPages;$i++)
{
	for ($j=0;$j<$xPages;$j++)
	{
		$yOffset = ($pageHeight * $i) - ($pageOverlap * (1 + (2 * $i)));
		$xOffset = ($pageWidth * $j) - ($pageOverlap * (1 + (2 * $j)));
		$pdf->AddPage();
		$pdf->SetDrawColor(192);
		$pdf->Rect($pageOverlap,$pageOverlap,$printableWidth,$printableHeight);		
		$pdf->SetDrawColor(0);
		
		//output a line for each string
		foreach($guitar['strings'] as $string)
		{
			$pdf->Line($string->end1->x-$xOffset,$string->end1->y-$yOffset,$string->end2->x-$xOffset,$string->end2->y-$yOffset);
		}
	
		//output a line for each fretboard edge
		$pdf->Line($guitar['meta'][0]->end1->x-$xOffset,$guitar['meta'][0]->end1->y-$yOffset,$guitar['meta'][0]->end2->x-$xOffset,$guitar['meta'][0]->end2->y-$yOffset);
		$pdf->Line($guitar['meta'][sizeof($guitar['meta'])-1]->end1->x-$xOffset,$guitar['meta'][sizeof($guitar['meta'])-1]->end1->y-$yOffset,$guitar['meta'][sizeof($guitar['meta'])-1]->end2->x-$xOffset,$guitar['meta'][sizeof($guitar['meta'])-1]->end2->y-$yOffset);
	
		//output a line for each fret on each string
		foreach($it['strings'] as $string)
		{
			foreach($string as $fret)
			{
				$pdf->Line($fret['fret']->end1->x()-$xOffset,$fret['fret']->end1->y()-$yOffset,$fret['fret']->end2->x()-$xOffset,$fret['fret']->end2->y()-$yOffset);
			}
		}
	}
}
$pdf->Output('fretboard.pdf','D');


?>
