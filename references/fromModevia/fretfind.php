<?php
ini_set('display_errors', '0');
$format=isset($_GET['format'])?$_GET['format']:'png';
$format=in_array($format,array('png','svg','dxf'))?$format:'png';

require_once('ffgeom.php');
require_once('guitar.php');
?>
<html>
<head>
<!--
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
-->
<title>FretFind 2-D</title>
</head>
<style>
<!--
#svgview {width:200px;height:600px;}
BODY 
	{
		background-color: white;
		font-family: Arial,sans-serif;
		font-size: 0.8em;
	}
table.results td{vertical-align:top;}
table.foundfrets {border-collapse:collapse;}
table.foundfrets td{border:1px solid black;padding: 0px 5px 0px 5px;}
-->
</style>
<script type="text/javascript">
<!--
window.focus();

function resizeSVG()
{
	svg=document.getElementById('svgview');
	svg.style.width=parseInt(document.getElementById('svgwidth').value)+'px';
	svg.style.height=parseInt(document.getElementById('svgheight').value)+'px';
}

//-->
</script>
<body>
<h3 width="100%">FretFind 2-D</h3>
Your feedback is essential!</p>
<table class="results">
	<tr>
		<td>
<?php
if ($format=='svg')
{
?>
<embed src="fretboard_svg.php?<?php echo $_SERVER['QUERY_STRING'];?>" type="image/svg+xml" 
pluginspage="http://www.adobe.com/svg/viewer/install/main.html" name="svgview" id="svgview" /> 

	<table>
	<tr><td>Width</td><td><input type="text" value="200" name="svgwidth" id="svgwidth" /></td></tr>
	<tr><td>Height</td><td><input type="text" value="600" name="svgheight" id="svgheight" /></td></tr>
	<tr><td></td><td><input type="button" onclick="javascript:resizeSVG();" value="Resize SVG View" /></td></tr>
	</table>
<?php
}
else if ($format=='png')
{
?>
<!--	<img src="fretboard_png.php?<?php echo $_SERVER['QUERY_STRING'];?>" id="pngview" />-->
<?php
}
else if ($format=='dxf')
{
?>
<applet codebase="." archive="dxfapplet/dxfapplet.jar"
code="de.escape.quincunx.dxf.DxfViewer" width="400" height="800" name="fretboard"> 
<param name="file"               value="fretboard_dxf.php?<?php echo $_SERVER['QUERY_STRING'];?>">
<param name="framed"             value="false">
<param name="frameWidth"         value="400">
<param name="frameHeight"        value="800">
</applet>
<?php
}
?>
	<br />
	<a href="fretboard_svg.php?<?php echo $_SERVER['QUERY_STRING'];?>">SVG File</a>
	<br /><!--
	<a href="fretboard_png.php?<?php echo $_SERVER['QUERY_STRING'];?>">PNG File</a>
	<br />-->
	<a href="fretboard_pdf_singlepage.php?<?php echo $_SERVER['QUERY_STRING'];?>">PDF File (Single Page)</a>
	<br />
	<a href="fretboard_pdf_multiplepages.php?<?php echo $_SERVER['QUERY_STRING'];?>">PDF File (Multiple Pages)</a>
	<br />
	<a href="fretboard_dxf.php?<?php echo $_SERVER['QUERY_STRING'];?>">DXF File</a>
	<br />
	<a href="fretboard_path.php?<?php echo $_SERVER['QUERY_STRING'];?>">SVG PathData</a>
	<br />
	<a href="fretboard_inkscape.php?<?php echo $_SERVER['QUERY_STRING'];?>">Inkscape SVG</a>
		</td>
		<td>
<?php
$it=processGuitar();
echo '<table class="foundfrets">',
	'<tr><td colspan="3">Midline</td></tr>',
	'<tr><td>endpoints</td><td>length</td><td>angle</td></tr>',
	'<tr><td>',$it['midline']->toString(),'</td><td>',
	$it['midline']->length(),'</td><td>',$it['midline']->angle(),'</td></tr>',
	'</table><br /><br />';

echo '<table class="foundfrets">';
for($i=0;$i<sizeof($it['strings']);$i++)
{
	echo '<tr><td colspan="11">String ',$i+1,'</td></tr>',
		'<tr><td>#</td><td>to nut</td><td>to fret</td><td>to bridge</td>',
		'<td>intersection point</td><td>partial width</td><td>angle</td>',
		'<td>mid to nut</td><td>mid to fret</td><td>mid to bridge</td><td>mid intersection</td>',
		'</tr>';
	for($j=0;$j<sizeof($it['strings'][$i]);$j++)
	{
		echo '<tr><td>',
			$j==0?'n':$j,
			'</td><td>',
			round($it['strings'][$i][$j]['nutDist'],$precision),
			'</td><td>',
			round($it['strings'][$i][$j]['pFretDist'],$precision),
			'</td><td>',
			round($it['strings'][$i][$j]['bridgeDist'],$precision),
			'</td><td>',
			$it['strings'][$i][$j]['intersection']->toString(),
			'</td><td>',
			round($it['strings'][$i][$j]['width'],$precision),
			'</td><td>',
			round($it['strings'][$i][$j]['angle'],$precision),
			'</td><td>',
			round($it['strings'][$i][$j]['midline_nutDist'],$precision),
			'</td><td>',
			round($it['strings'][$i][$j]['midline_pFretDist'],$precision),
			'</td><td>',
			round($it['strings'][$i][$j]['midline_bridgeDist'],$precision),
			'</td><td>',
			$it['strings'][$i][$j]['midline_intersection']->toString(),
			'</td></tr>';
	}
}
?>
			</table>
		</td>
	</tr>
</table>
</body>
</html>
