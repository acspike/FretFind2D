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
<title>FretFind Examples</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta name="keywords" lang="en" content="FretFind, Examples">
<meta name="description" lang="en" content="Example FretFind Designs: see what fretfind can do for you!">
<meta name="resource-type" content="document">
<meta http-equiv="pragma" content="no-cache">
<meta name="revisit-after" content="7 days">
<meta name="robots" content="index,follow">
<meta name="rating" content="Safe For Kids">
<meta name="author" content="Aaron Spike">
</head>
<body>
<?php
	include('header.php');
	echo '<h3>FretFind Examples</h3>';
	$type = isset($_GET['type']) && $_GET['type']=='svg' ? 'svg' : 'gif';
	$examples = array(
array(	'name' => '31tet',
	'description' => '31 tone equal temperament.
	You can find more information about 31 tone ET <a href="http://www.xs4all.nl/~huygensf/doc/rap31.html">here</a>.
	Click <a href="http://www.bikexprt.com/music/guitar31.htm">here</a> to see a 31 tone guitar.'),
array(	'name' => 'Dante',
	'description' => 'Dante Rosati retro-fretted an old classical into a great 21 tone guitar.
	The music he plays on this guitar is beautiful.
	You can read Dante\'s description of how he fretted this guitar
	<a href="http://users.rcn.com/dante.interport/justguitar.html">here</a>.
	From his description I have created an approximate design of his guitar with FretFind.'),
array(	'name' => 'Charlie',
	'description' => 'A very very approximate design of a <a href="http://www.charliehunter.com/gear/index.htm">
	Charlie Hunter 8-String</a>.'),
array(	'name' => 'CharliePyth',
	'description' => 'And just for kicks, that same very very approximate design using a 12 tone pythagorean scale in E.'),
);
	echo '<a href="examples.php?type=gif">*.GIF</a> | <a href="examples.php?type=svg">*.SVG</a>';
	echo '<table>';
	foreach ($examples as $example)
	{
		if ($type=='svg')
		{
			$img='<embed src="images/'.$example['name'].'.svgz" type="image/svg+xml" pluginspage="http://www.adobe.com/svg/viewer/install/main.html" style="width:200px;height:600px;" />';
		}
		elseif ($type=='gif')
		{
			$img='<img src="images/'.$example['name'].'.gif" />';
		}
		
		echo '<tr><td style="width: 200px;">', $example['description'], '</td><td>', $img, '</td></tr>';
	}
	echo '</table>';
?>
</body>
</html>
