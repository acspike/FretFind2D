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
<meta name="description" lang="en" content="A complex interface for FretFind 2-D that allows the design of arbitrary fretboards.">
<meta name="resource-type" content="document">
<meta http-equiv="pragma" content="no-cache">
<meta name="revisit-after" content="7 days">
<meta name="robots" content="index,follow">
<meta name="rating" content="Safe For Kids">
<meta name="author" content="Aaron Spike">
<style>
<!--
<?php include('styles.php'); ?>
.short
	{
		width: 5em;
	}
-->
</style>
<script>
<!--
//global vars
var currentOverhang;
var currentCalc;

//end gobal vars
function init()
{
	chooseCalc('enterCalcR');
	updateStrings();
}

function toggleDiv(id)
{
	div=document.getElementById(id);
	if(div.style.display=='block')
	{
		div.style.display='none';
	}
	else
	{
		div.style.display='block';
	}
}
function chooseCalc(choice)
{
	if (currentCalc)document.getElementById(currentCalc).style.display='none';
	if (choice) document.getElementById(choice).style.display='block';
	currentCalc=choice;		
}
function updateStrings()
{
	outBuffer='<table>';
	outBuffer+='<tr><td>&nbsp;</td><td>nut x</td><td>nut y</td><td>bridge x</td><td>bridge y</td><td>tuning</td></tr>';
		outBuffer+='<tr><td>right edge</td>'+
				'<td><input type="text" name="inputNutX" value="0" class="short" /></td>'+
				'<td><input type="text" name="inputNutY" value="0" class="short" /></td>'+
				'<td><input type="text" name="inputBridgeX" value="0" class="short" /></td>'+
				'<td><input type="text" name="inputBridgeY" value="0" class="short" /></td>'+
				'<td>&nbsp;</td></tr>';
	
	for (i=1;i<=parseInt(document.getElementById('inputStrings').value);i++)
	{
		
		outBuffer+='<tr><td>string '+i+'</td>'+
				'<td><input type="text" name="inputNutX" value="0" class="short" /></td>'+
				'<td><input type="text" name="inputNutY" value="0" class="short" /></td>'+
				'<td><input type="text" name="inputBridgeX" value="0" class="short" /></td>'+
				'<td><input type="text" name="inputBridgeY" value="0" class="short" /></td>'+
				'<td><input type="text" name="inputTuning" value="0" class="short" /></td></tr>';
	}
		outBuffer+='<tr><td>left edge</td>'+
				'<td><input type="text" name="inputNutX" value="0" class="short" /></td>'+
				'<td><input type="text" name="inputNutY" value="0" class="short" /></td>'+
				'<td><input type="text" name="inputBridgeX" value="0" class="short" /></td>'+
				'<td><input type="text" name="inputBridgeY" value="0" class="short" /></td>'+
				'<td>&nbsp;</td></tr>';
	document.getElementById('enterGuitar').innerHTML=outBuffer;
}
function submitThis()
{
	outBuffer='';
	strings=parseInt(document.getElementById('inputStrings').value);
	frets=parseInt(document.getElementById('inputFrets').value);
	nutx=document.getElementsByName('inputNutX');
	nuty=document.getElementsByName('inputNutY');
	bridgex=document.getElementsByName('inputBridgeX');
	bridgey=document.getElementsByName('inputBridgeY');
	tuning=document.getElementsByName('inputTuning');
	for (i=0;i<nutx.length;i++)
	{
		outBuffer+='<input type="hidden" name="nx[]" value="'+parseFloat(nutx[i].value)+'" />'+
			'<input type="hidden" name="ny[]" value="'+parseFloat(nuty[i].value)+'" />'+
			'<input type="hidden" name="bx[]" value="'+parseFloat(bridgex[i].value)+'" />'+
			'<input type="hidden" name="by[]" value="'+parseFloat(bridgey[i].value)+'" />';
	}
	switch(currentCalc)
	{	
		case 'enterCalcR':
			outBuffer+='<input type="hidden" name="scaletype" value="root2" />'+
				'<input type="hidden" name="scale" value="'+
				parseFloat(document.getElementById('inputCalcR').value)+'" />';
			break;
		case 'enterCalcS':
			outBuffer+='<input type="hidden" name="scaletype" value="scala" />'+
				'<textarea rows="0" cols="0" style="display:none;" type="hidden" name="scale">'+
				document.getElementById('inputCalcS').value+'</textarea>';
			break;
	}
	if (strings==tuning.length)
	{
		for (i=0;i<tuning.length;i++)
		{
			outBuffer+='<input type="hidden" name="tuning[]" value="'+parseInt(tuning[i].value)+'" />';
		}
	}
	else
	{
		for (i=0;i<strings;i++)
		{
			outBuffer+='<input type="hidden" name="tuning[]" value="0" />';
		}
	}
	outBuffer+='<input type="hidden" name="frets" value="'+frets+'" />';
	document.getElementById('submitContents').innerHTML=outBuffer;
	document.getElementById('submitForm').submit();
}
//-->
</script>
</head>
<body onload="init();">
<?php include('header.php');?>
<h3 width="100%">FretFind 2-D</h3>
<div class="linkstrip">
<a href="standard.php">standard interface</a> | 
<a href="nonparallel.php">non-parallel interface</a> | 
<a href="examples.php">examples</a>
</div>
<table><tr><td>

<div id="worksheet">
<table>
<tr><td>
calculation method
[<a href="javascript:toggleDiv('helpCalc');">?</a>]
<div class="choices">
<a href="javascript:chooseCalc('enterCalcR');">equal (root 2)</a> | 
<a href="javascript:chooseCalc('enterCalcS');">just (scala)</a>  
</div>
</td><td>
<div id="enterCalcR" name="enterCalcR" class="choice">
<input type="text" id="inputCalcR" name="inputCalcR" value="12" />
</div>
<div id="enterCalcS" name="enterCalcS" class="choice">
<textarea rows="17" id="inputCalcS" name="inputCalcS">
! 12tet.scl
!
12 tone equal temperament
 12
!
 100.0
 200.
 300.
 400.
 500.
 600.
 700.
 800.
 900.
 1000.
 1100.
 2/1</textarea>
</div>
</td><tr><td colspan="2">
<div id="helpCalc" name="helpCalc" class="help">
The calculation method determins how FretFind calculates fret placement.
There are two input modes. Equal: uses the X<sup>th</sup> root of two, a standard
method for calculating equal temperaments. You enter the number of tones per octave.
Scala: uses a Scala SCL file which allows you to specify 
each scale step exactly in either ratios or cents. 
If you are interested in creating your own scale, please read this description of the 
<a href="http://www.xs4all.nl/~huygensf/scala/scl_format.html">Scala scale file format</a>.
Otherwise try a scale from the Scala scale archive, found at the very bottom of the 
<a href="http://www.xs4all.nl/~huygensf/scala/downloads.html">Scala download page</a>.
You can learn more about Scala at the <a href="http://www.xs4all.nl/~huygensf/scala/index.html">Scala home page</a>.
</div>
</td></tr>
<tr><td>
number of frets
[<a href="javascript:toggleDiv('helpFrets');">?</a>]
</td><td>
<input type="text" id="inputFrets" name="inputFrets" value="24" />
</td><tr><td colspan="2">
<div id="helpFrets" name="helpFrets" class="help">
This is the number of frets you would like FretFind to calculate.
The number of frets must be an integer. 
</div>
</td></tr>
<tr><td>
number of strings
[<a href="javascript:toggleDiv('helpStrings');">?</a>]
</td><td>
<input type="text" id="inputStrings" name="inputStrings" value="6" />
<input type="button" value="Update" onclick="updateStrings();" />
</td><tr><td colspan="2">
<div id="helpStrings" name="helpStrings" class="help">
The number of strings must be an integer. 
If you change the number of strings click "Update" to update the section below.
Clicking "Update" will remove all values from the table below.
</div>
</td></tr>
<tr><td colspan="2">
guitar
[<a href="javascript:toggleDiv('helpGuitar');">?</a>]
</td></tr>
<tr><td colspan="2">
<div id="enterGuitar" name="enterGuitar">
</div>
</td><tr><td colspan="2">
<div id="helpGuitar" name="helpGuitar" class="help">
Define the extents of your guitar on a coordinate plane. 
You are asked to place the end points of each string and the end points of both fretboard edges. 
Because FretFind uses SVG for visualization you may want to use the SVG coordinate system. 
The point (0,0) is in the top left of your screen and values for x and y increase to the right and bottom respectively. 
A properly oriented standard guitar will have larger x values for the high E than for the low E 
and larger y values for the bridge than for the nut.
<br />
<br />
For tuning enter the scale step (of the scale defined above) to which each string will be tuned.
For example a standard guitar in the key of E would be tuned 0, 7, 3, 10, 5, 0.
The first string is the string to the far right on the fretboard.
Tuning is not important for the Equal calculation method.
Entering a tuning for the Scala calculation method will very likely result in partial frets.
</div>
</td></tr>
</table>

<form id="submitForm" name="submitForm" method="get" action="fretfind.php" target="results">
<br />
Units are
<input type="radio" name="unit" value="in" id="unit_in" checked />
<label for="unit_in">inches</label>
<input type="radio" name="unit" value="cm" id="unit_cm" />
<label for="unit_cm">centimeters</label>
<br />
Visualize in 
<input type="radio" name="format" value="png" id="format_png" checked />
<label for="format_png">PNG</label>
<input type="radio" name="format" value="svg" id="format_svg" />
<label for="format_svg">SVG</label>
<input type="radio" name="format" value="dxf" id="format_dxf" />
<label for="format_dxf">DXF</label>
<br />
<input type="button" value="Submit" onclick="submitThis();" />
<br />
(The results open in a new window.)
<div id="submitContents" name="submitContents">
</div>
</form>

</div>
</td><td>
<?php include('sidebarblurb.php'); ?>
</td></tr></table>
</body>
</html>

