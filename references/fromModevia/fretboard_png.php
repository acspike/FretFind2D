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
exit();
ini_set('display_errors', '0');
//we are using output buffering not only for speed 
//but also to control the data stream and use the 
//output locally for imediate rasterization.
ob_start();
require_once('ffgeom.php');
require_once('guitar.php');
$unit=isset($_GET['unit']) && in_array($_GET['unit'],array('in','cm'))?$_GET['unit']:'in';
$dpi=isset($_GET['dpi'])?(int)$_GET['dpi']:72;
$dpi=$unit=='in'?$dpi:$dpi/2.54;

header('Content-type: image/png');
header('Content-Disposition: attachment; filename="fretboard.png"');
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

$unit='px';
//$strokewidth='0.02%';
$strokewidth='0.03px';
echo '<svg viewBox="',$minx,' ',$miny,' ',$maxx,' ',$maxy,'" height="',$h*$dpi,$unit,'" width="',$w*$dpi,$unit,'" >';


//Output SVG line elements for each string.
//ImageMagick and convert wouldn't recognise the style sheet properly so 
//we use inline style in a the container element.
echo '<g style="stroke:rgb(0,0,0);stroke-width:',$strokewidth,';">';
foreach($guitar['strings'] as $string)
{
	echo '<line x1="',$string->end1->x,'" x2="',$string->end2->x,
	     '" y1="',$string->end1->y,'" y2="',$string->end2->y,'"',
	     ' />',"\n";
}
//Output SVG line elements for each fretboard edge
echo '</g><g style="stroke:rgb(0,0,255);stroke-width:',$strokewidth,';">';
echo '<line x1="',$guitar['meta'][0]->end1->x,'" x2="',$guitar['meta'][0]->end2->x,
     '" y1="',$guitar['meta'][0]->end1->y,'" y2="',$guitar['meta'][0]->end2->y,'"',
     ' />',"\n";
echo '<line x1="',$guitar['meta'][sizeof($guitar['meta'])-1]->end1->x,
     '" x2="',$guitar['meta'][sizeof($guitar['meta'])-1]->end2->x,
     '" y1="',$guitar['meta'][sizeof($guitar['meta'])-1]->end1->y,'" y2="',
     $guitar['meta'][sizeof($guitar['meta'])-1]->end2->y,'"',
     ' />',"\n";

//output as SVG path for each fretlet. 
//using paths because they allow for the linecap style 
//which gives nice rounded ends
echo '</g><g style="stroke:rgb(255,0,0);stroke-linecap:round;stroke-width:',$strokewidth,';">';
$it=processGuitar4svg();
foreach($it['strings'] as $string)
{
	foreach($string as $fret)
	{
		echo '<path d="M',
		     $fret['fret']->end1->x(),' ',$fret['fret']->end1->y(),
		     'L',$fret['fret']->end2->x(),' ',$fret['fret']->end2->y(),
		     '" />',"\n";
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
echo '</g>';
echo '</svg>',"\n";

//output svg to tempfile
//convert tempfile to png
//send tempfile to browser and clean up
function make_seed()
{
	list($usec, $sec) = explode(' ', microtime());
	return (float) $sec + ((float) $usec * 100000);
}
mt_srand(make_seed());
$randval = mt_rand();

$data = ob_get_contents(); 
ob_end_clean();
$fp = fopen('/tmp/ff'.$randval.'.svg','w'); 
fwrite($fp,$data,strlen($data)); 
fclose($fp); 
exec('/usr/bin/nice /usr/bin/convert /tmp/ff'.$randval.'.svg /tmp/ff'.$randval.'.png');
readfile('/tmp/ff'.$randval.'.png');
exec('rm -rf /tmp/ff'.$randval.'.svg /tmp/ff'.$randval.'.png');

?>
