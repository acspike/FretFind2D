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
<meta name="description" lang="en" content="The standard interface for FretFind 2-D can create a typical fretboard design.">
<meta name="resource-type" content="document">
<meta http-equiv="pragma" content="no-cache">
<meta name="revisit-after" content="7 days">
<meta name="robots" content="index,follow">
<meta name="rating" content="Safe For Kids">
<meta name="author" content="Aaron Spike">
<style>
<!--
<?php include('styles.php'); ?>
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
	chooseOverhang('enterOverhangE');
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
function chooseOverhang(choice)
{
	if (currentOverhang)document.getElementById(currentOverhang).style.display='none';
	if (choice) document.getElementById(choice).style.display='block';
	currentOverhang=choice;		
}
function chooseCalc(choice)
{
	if (currentCalc)document.getElementById(currentCalc).style.display='none';
	if (choice) document.getElementById(choice).style.display='block';
	currentCalc=choice;		
}
function updateStrings()
{
	outBuffer='';
	for (i=1;i<=parseInt(document.getElementById('inputStrings').value);i++)
	{
		outBuffer+='string '+i+' <input type="text" name="inputTuning" value="0" /><br />';
	}
	document.getElementById('enterTuning').innerHTML=outBuffer;
}
function submitThis()
{
	outBuffer='';
	scaleLength=parseFloat(document.getElementById('inputScale').value);
	nutWidth=parseFloat(document.getElementById('inputNutWidth').value);
	bridgeWidth=parseFloat(document.getElementById('inputBridgeWidth').value);
	strings=parseInt(document.getElementById('inputStrings').value);
	frets=parseInt(document.getElementById('inputFrets').value);
	tuning=document.getElementsByName('inputTuning');
	switch(currentOverhang)
	{
		case 'enterOverhangE':
			oNF=oNL=oBF=oBL=parseFloat(document.getElementById('inputOverhangE').value);
			break;
		case 'enterOverhangNB':
			oNF=oNL=parseFloat(document.getElementById('inputOverhangN').value);
			oBF=oBL=parseFloat(document.getElementById('inputOverhangB').value);
			break;
		case 'enterOverhangFL':
			oNF=oBF=parseFloat(document.getElementById('inputOverhangF').value);
			oBL=oNL=parseFloat(document.getElementById('inputOverhangL').value);
			break;
		case 'enterOverhangA':
			oNF=parseFloat(document.getElementById('inputOverhangNF').value);
			oBF=parseFloat(document.getElementById('inputOverhangBF').value);
			oNL=parseFloat(document.getElementById('inputOverhangNL').value);
			oBL=parseFloat(document.getElementById('inputOverhangBL').value);
			break;
	}
	nutHalf=nutWidth/2;
	bridgeHalf=bridgeWidth/2;
	nutCandidateCenter=(nutHalf)+oNL;
	bridgeCandidateCenter=(bridgeHalf)+oBL;
	center=bridgeCandidateCenter>=nutCandidateCenter?bridgeCandidateCenter:nutCandidateCenter;
	nutStringSpacing=nutWidth/(strings-1);
	bridgeStringSpacing=bridgeWidth/(strings-1);
	outBuffer+='<input type="hidden" name="nx[]" value="'+(center+nutHalf+oNF)+'" />'+
		'<input type="hidden" name="bx[]" value="'+(center+bridgeHalf+oBF)+'" />'+
		'<input type="hidden" name="nx[]" value="'+(center+nutHalf)+'" />'+
		'<input type="hidden" name="bx[]" value="'+(center+bridgeHalf)+'" />';
	for (i=1;i<=(strings-2);i++)
	{
		outBuffer+='<input type="hidden" name="nx[]" value="'+(center+nutHalf-(i*nutStringSpacing))+'" />'+
			'<input type="hidden" name="bx[]" value="'+(center+bridgeHalf-(i*bridgeStringSpacing))+'" />';
	}
	outBuffer+='<input type="hidden" name="nx[]" value="'+(center-nutHalf)+'" />'+
		'<input type="hidden" name="bx[]" value="'+(center-bridgeHalf)+'" />'+
		'<input type="hidden" name="nx[]" value="'+(center-(nutHalf+oNL))+'" />'+
		'<input type="hidden" name="bx[]" value="'+(center-(bridgeHalf+oBL))+'" />';
	
	for (i=0;i<(strings+2);i++)
	{
		outBuffer+='<input type="hidden" name="ny[]" value="0" />'+
			'<input type="hidden" name="by[]" value="'+scaleLength+'" />';
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
<a href="nonparallel.php">non-parallel interface</a> | 
<a href="advanced.php">advanced interface</a> |
<a href="examples.php">examples</a>
</div>
<table><tr><td>

<div id="worksheet">
<table>
<tr><td>
fundamental scale length 
[<a href="javascript:toggleDiv('helpScale');">?</a>]
</td><td>
<input type="text" id="inputScale" name="inputScale" value="25" />
</td><tr><td colspan="2">
<div id="helpScale" name="helpScale" class="help">
The fundamental scale length is the length of a line drawn from 
the middle of the nut to the middle of the bridge. 
In this FretFind frontend that line is the perpendicular bisector of both the nut and the bridge.
I call this length "fundamental" because the on a standard instrument 
with a narrow nut and a wide bridge the outer strings
actaually have a slightly longer scale length.
</div>
</td></tr>
<tr><td>
string width at the nut
[<a href="javascript:toggleDiv('helpNutWidth');">?</a>]
</td><td>
<input type="text" id="inputNutWidth" name="inputNutWidth" value="1.375" />
</td><tr><td colspan="2">
<div id="helpNutWidth" name="helpNutWidth" class="help">
The string width at the nut is the distance along the nut from the center 
of the first string to the center of the last string. (Please note, FretFind will space 
the remaining strings equally between these two points. Custom string spacings 
can be achieved with FretFind using the advanced frontend.)
</div>
</td></tr>
<tr><td>
string width at the bridge 
[<a href="javascript:toggleDiv('helpBridgeWidth');">?</a>]
</td><td>
<input type="text" id="inputBridgeWidth" name="inputBridgeWidth" value="2.125" />
</td><tr><td colspan="2">
<div id="helpBridgeWidth" name="helpBridgeWidth" class="help">
The string width at the bridge is the distance along the bridge from the center 
of the first string to the center of the last string. (Please note, FretFind will space 
the remaining strings equally between these two points. Custom string spacings 
can be achieved with FretFind using the advanced frontend.)
</div>
</td></tr>
<tr><td>
fretboard overhang 
[<a href="javascript:toggleDiv('helpOverhang');">?</a>]
<br />
<div class="choices">
<a href="javascript:chooseOverhang('enterOverhangE');">equal</a> | 
<a href="javascript:chooseOverhang('enterOverhangNB');">nut &amp; bridge</a> | 
<a href="javascript:chooseOverhang('enterOverhangFL');">first &amp; last</a> | 
<a href="javascript:chooseOverhang('enterOverhangA');">all</a> 
</div>
</td><td>
<div id="enterOverhangE" name="enterOverhangE" class="choice">
<input type="text" id="inputOverhangE" name="inputOverhangE" value="0.09375" />
</div>
<div id="enterOverhangNB" name="enterOverhangNB" class="choice">
<table>
<tr><td>nut</td><td>
<input type="text" id="inputOverhangN" name="inputOverhangN" value="0.09375" />
</td></tr>
<tr><td>bridge</td><td>
<input type="text" id="inputOverhangB" name="inputOverhangB" value="0.09375" />
</td></tr>
</table>
</div>
<div id="enterOverhangFL" name="enterOverhangFL" class="choice">
<table>
<tr><td>last</td><td>first</td></tr>
<tr><td>
<input type="text" id="inputOverhangL" name="inputOverhangL" value="0.09375" />
</td><td>
<input type="text" id="inputOverhangF" name="inputOverhangF" value="0.09375" />
</td></tr>
</table>
</div>
<div id="enterOverhangA" name="enterOverhangA" class="choice">
<table>
<tr><td>&nbsp;</td><td>last</td><td>first</td></tr>
<tr><td>nut</td><td>
<input type="text" id="inputOverhangNL" name="inputOverhangNL" value="0.09375" />
</td><td>
<input type="text" id="inputOverhangNF" name="inputOverhangNF" value="0.09375" />
</td></tr>
<tr><td>bridge</td><td>
<input type="text" id="inputOverhangBL" name="inputOverhangBL" value="0.09375" />
</td><td>
<input type="text" id="inputOverhangBF" name="inputOverhangBF" value="0.09375" />
</td></tr>
</table>
</div>
</td><tr><td colspan="2">
<div id="helpOverhang" name="helpOverhang" class="help">
The fretboard overhang is the distance from the center of outer strings to edge of nut or bridge. 
There are four input modes for overhang. Equal: you enter a single value and the overhang will be constant.
Nut &amp; Bridge: allows you to specify one overhang at the nut and another overhang at the bridge.
First &amp; Last: allows you to specify one overhang for the first string and another for the last string.
All: you specify an overhang for all four locations seperately.
(Please note, in FretFind the first string is shown on the far right 
where the high E string would be on a typical right-handed guitar. 
The last string is on the far left, where the low E would be found.)
</div>
</td></tr>
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
 2/1
</textarea>
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
If you change the number of strings click "Update" to update the tuning section below.
</div>
</td></tr>
<tr><td>
tuning
[<a href="javascript:toggleDiv('helpTuning');">?</a>]
</td><td>
<div id="enterTuning" name="enterTuning">
</div>
</td><tr><td colspan="2">
<div id="helpTuning" name="helpTuning" class="help">
Enter the scale step (of the scale defined above) to which each string will be tuned.
For example a standard guitar in the key of E would be tuned 0, 7, 3, 10, 5, 0.
The first string is the string to the far right on the fretboard.
This step is not important for the Equal calculation method.
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

