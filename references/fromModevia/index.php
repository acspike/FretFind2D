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
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta name="keywords" lang="en" content="FretFind, stringed instrument, luthier, fret, guitar, fret placement calculator">
<meta name="description" lang="en" content="FretFind 2-D Home">
<meta name="resource-type" content="document">
<meta http-equiv="pragma" content="no-cache">
<meta name="revisit-after" content="7 days">
<meta name="robots" content="index,follow">
<meta name="rating" content="Safe For Kids">
<meta name="author" content="Aaron Spike">
<style type="text/css">
<!--
<?php include('styles.php'); ?>
-->
</style>
</head>
<body>
<?php include('header.php'); ?>
<p>
FretFind 2-D is a two dimensional fretboard design tool.
FretFind 2-D doesn't just calculate fret spacing. 
It models the entire fretboard, strings and frets, 
as a system of line segments on a two dimensional plane.
Because of this approach, it can design fretboards for instruments with 
multiple scale lengths and non-parallel frets 
as well as fretboards for instruments that play just or meantone scales.

</p>
<p>
FretFind 2-D can be accessed through three frontends.
<dl>
<dt><a href="standard.php">Standard Frontend</a>
<dd>User specifies a single scale length.
<dt><a href="nonparallel.php">Non-Parallel Frontend</a>
<dd>User specifies a scale length for the first and last strings.
<dt><a href="advanced.php">Advanced Frontend</a>
<dd>User specifies the endpoints for both fretboard edges and every string.
</dl>
</p>
<p>
Current features:
<ul>
<li>Uses the <a href="http://www.xs4all.nl/~huygensf/scala/scl_format.html">Scala Scale File Format</a> for defining just or meantone scales
<li>Generates graphical output of fretboard designs in:
	<ul>
	<li><a href="http://www.w3.org/TR/SVG/">SVG</a>
	<li><a href="http://www.libpng.org/pub/png/">PNG</a> rasterized from SVG (Requires <a href="http://www.imagemagick.org/">ImageMagick</a>)
	<li>PDF via <a href="http://www.fpdf.org/">FPDF</a>
	<li>DXF viewable online through the <a href="http://www.escape.de/users/quincunx/dxfviewer/index.html">DXF Viewer</a> applet
	</ul>
</ul>
</p>
<p>
Planned features:
<ul>
<li>Preview (prehear?) of scales via <a href="http://www.midi.org/">MIDI</a>
<li>Improved explanation of input and output
<li>Translation via gettext
<li>Any user suggested feaures (Email suggestions to <a href="mailto:website@ekips.org">Aaron Spike</a>)
</ul>
</p>
<p>
The latest release is available for download <a href="http://www.fretfind.ekips.org/download/ff2d_latest.tar.gz">here</a>.
You may view the <a href="ChangeLog">ChangeLog</a>.
</p>
<p>
Some portions of FretFind 2-D may be useful in other software projects.
<dl>
<dt><a href="useful/ffgeom.php.txt">ffgeom.php</a>
<dt><a href="useful/ffgeom.js.txt">ffgeom.js</a>
<dd>Simple point and segment classes with methods for some useful calculations in PHP and JavaScript
<dt><a href="useful/scala.php.txt">scala.php</a>
<dd>A function to convert <a href="http://www.xs4all.nl/~huygensf/scala/scl_format.html">Scala Scale Files</a> 
into an array of ratios useful for frequency and string length calculation
</dl>
FretFind 2-D is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
</p>
<p>
FretFind 2-D is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
<a href="COPYING">GNU General Public License</a> for more details.
</p>
</body>
</html>
<?php

?>
