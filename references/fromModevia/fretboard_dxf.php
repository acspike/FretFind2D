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

require_once('ffgeom.php');
require_once('guitar.php');
header('Content-type: image/vnd.dxf');
header('Content-Disposition: attachment; filename="fretboard.dxf"');
//$unit=isset($_GET['unit']) && in_array($_GET['unit'],array('in','cm'))?$_GET['unit']:'in';

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

//References: Minimum Requirements for Creating a DXF File of a 3D Model By Paul Bourke
echo '999
DXF created by FretFind 2-D';

//well if they say I only need the ENTITIES SECTION 
//then that is all I'll give them!
/*
echo'
0
SECTION
2
HEADER
9
$ACADVER
1
AC1006
9
$INSBASE
10
0.0
20
0.0
30
0.0
9
$EXTMIN
10
',$minx,'
20
',$miny,'
30
0
9
$EXTMAX
10
',$maxx,'
20
',$maxy,'
30
0
0
ENDSEC
0
SECTION
2
TABLES
0
TABLE
2
LTYPE
70
1
0
LTYPE
2
CONTINUOUS
70
64
3
Solid line
72
65
73
0
40
0.000000
0
ENDTAB
0
TABLE
2
LAYER
70
6
0
LAYER
2
1
70
64
62
7
6
CONTINUOUS
0
LAYER
2
2
70
64
62
7
6
CONTINUOUS
0
ENDTAB
0
TABLE
2
STYLE
70
0
0
ENDTAB
0
ENDSEC
0
SECTION
2
BLOCKS
0
ENDSEC';
*/
echo '
0
SECTION
2
ENTITIES';

//output a line for each string
foreach($guitar['strings'] as $string)
{
echo '
0
LINE
8
2
62
4
10
',$string->end1->x,'
20
',$string->end1->y,'
30
0
11
',$string->end2->x,'
21
',$string->end2->y,'
31
0';
}

//output a line for each fretboard edge
echo '
0
LINE
8
2
62
4
10
',$guitar['meta'][0]->end1->x,'
20
',$guitar['meta'][0]->end1->y,'
30
0
11
',$guitar['meta'][0]->end2->x,'
21
',$guitar['meta'][0]->end2->y,'
31
0
0
LINE
8
2
62
4
10
',$guitar['meta'][sizeof($guitar['meta'])-1]->end1->x,'
20
',$guitar['meta'][sizeof($guitar['meta'])-1]->end1->y,'
30
0
11
',$guitar['meta'][sizeof($guitar['meta'])-1]->end2->x,'
21
',$guitar['meta'][sizeof($guitar['meta'])-1]->end2->y,'
31
0';

//output a line for each fret on each string
$it=processGuitar4svg();
foreach($it['strings'] as $string)
{
	foreach($string as $fret)
	{
echo '
0
LINE
8
2
62
4
10
',$fret['fret']->end1->x(),'
20
',$fret['fret']->end1->y(),'
30
0
11
',$fret['fret']->end2->x(),'
21
',$fret['fret']->end2->y(),'
31
0';
	}
}

//finish it off
echo '
0
ENDSEC
0
EOF
';


?>
