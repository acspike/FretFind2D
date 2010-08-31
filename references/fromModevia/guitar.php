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
require_once('scala.php');
require_once('ffgeom.php');
$threshold=0.0000000001;

$guitar=array(
'strings'=>array(),
'meta'=>array(),
'scaletype'=>'',
'scale'=>array(),
'tuning'=>array(),
'frets'=>24
);

$guitar['frets']=isset($_GET['frets'])?(int)$_GET['frets']:24;
$precision=5;

if(!function_exists('array_fill'))
{
	function array_fill($iStart, $iLen, $vValue) {
	   $aResult = array();
	   for ($iCount = $iStart; $iCount < $iLen + $iStart; $iCount++) {
	       $aResult[$iCount] = $vValue;
	   }
	   return $aResult;
	}
}

if (
	isset($_GET['nx']) &&
	isset($_GET['ny']) &&
	isset($_GET['bx']) &&
	isset($_GET['by']) &&
	sizeof($_GET['nx'])==sizeof($_GET['ny']) &&
	sizeof($_GET['ny'])==sizeof($_GET['bx']) &&
	sizeof($_GET['bx'])==sizeof($_GET['by'])
	)
{
	$size=sizeof($_GET['nx']);
	$guitar['meta'][]=new Segment(new Point((float)$_GET['nx'][0],(float)$_GET['ny'][0]),
			new Point((float)$_GET['bx'][0],(float)$_GET['by'][0]));
	for ($i=1;$i<($size-1);$i++)
	{
		$guitar['strings'][]=new Segment(new Point((float)$_GET['nx'][$i],(float)$_GET['ny'][$i]),
				new Point((float)$_GET['bx'][$i],(float)$_GET['by'][$i]));
	}
	for ($i=0;$i<($size-3);$i++)
	{
		$s1=$guitar['strings'][$i];
		$s2=$guitar['strings'][$i+1];
		$guitar['meta'][]=new Segment(
					new Point(
						(($s1->end1->x()+$s2->end1->x())/2),
						(($s1->end1->y()+$s2->end1->y())/2)
						),
					new Point(
						(($s1->end2->x()+$s2->end2->x())/2),
						(($s1->end2->y()+$s2->end2->y())/2)
						)
					);
		
	}
	$guitar['meta'][]=new Segment(new Point((float)$_GET['nx'][$size-1],(float)$_GET['ny'][$size-1]),
			new Point((float)$_GET['bx'][$size-1],(float)$_GET['by'][$size-1]));
}
else
{
	die('Badly sized input arrays: nx[],ny[],bx[] and by[] must be equal in length.');
}

if (isset($_GET['scaletype']) && $_GET['scaletype']=='scala' && isset($_GET['scale']))
{
	$tempscale=parseScala($_GET['scale']);
	if ($tempscale['errors']==0)
	{
		$guitar['scaletype']='scala';
		$guitar['scale']=$tempscale;
	}
	else
	{
		die('Sorry I encountered the following error(s) while parsing your scale:'."\n\n<br />".$tempscale['errorstring']);
	}
}
else
{
	//create parseScala() like output for equal tempered scales	
	$step=isset($_GET['scale'])?(float)$_GET['scale']:12;
	$guitar['scaletype']='root2';
	$guitar['scale']=array('steps'=>array(array(1,1),array(pow(2,(1/$step)),1)),
		'title'=>$step.' tone equal temperament');
}

if (isset($_GET['tuning']) && sizeof($_GET['tuning'])==sizeof($guitar['strings']))
{
	foreach($_GET['tuning'] as $step)
	{
		$guitar['tuning'][]=abs((int)$step);
	}
}
else
{
	//default tuning
	foreach($guitar['strings'] as $string)
	{
		$guitar['tuning'][]=0;
	}
}

function processGuitar()
{
	global $guitar,$threshold;
	//test strings ends are on nut and bridge
	//if not don't do partials
	$numStrings=sizeof($guitar['strings']);
	$doPartials=true;
	$parallelFrets=true;
	
	$nut=new Segment($guitar['strings'][0]->end1(),$guitar['strings'][$numStrings-1]->end1());
	$bridge=new Segment($guitar['strings'][0]->end2(),$guitar['strings'][$numStrings-1]->end2());
	$midline=new Segment(
		new Point(($nut->end2->x+$nut->end1->x)/2,($nut->end2->y+$nut->end1->y)/2),
		new Point(($bridge->end2->x+$bridge->end1->x)/2,($bridge->end2->y+$bridge->end1->y)/2)
		);
	foreach($guitar['strings'] as $string)
	{
		if (	!($nut->distanceToPoint($string->end1())<$threshold) ||
			!($bridge->distanceToPoint($string->end2())<$threshold))
		{
			echo $string->toString(),'<br />';
			echo $nut->distanceToPoint($string->end1()),'<br />';
			echo $bridge->distanceToPoint($string->end2()),'<br />';
			$doPartials=false;
			break;
		}
	}
	$denom=(($bridge->end2->y-$bridge->end1->y)*($nut->end2->x-$nut->end1->x))-
	(($bridge->end2->x-$bridge->end1->x)*($nut->end2->y-$nut->end1->y));
	if ($denom!=0) $parallelFrets=false;
	$intersection=intersect($nut,$bridge);

	$strings=array();
	$tones=sizeof($guitar['scale']['steps'])-1;
	$totalWidth=array();
	$scale=$guitar['scale']['steps'];
	for ($i=0;$i<$numStrings;$i++)
	{
		$base=$guitar['tuning'][$i];
		$frets=array();
		$frets[0]['fret']=$doPartials?new Segment($guitar['meta'][$i]->end1,$guitar['meta'][$i+1]->end1):
				new Segment($guitar['strings'][$i]->end1,$guitar['strings'][$i]->end1);
		$frets[0]['bridgeDist']=$guitar['strings'][$i]->length();
		$frets[0]['nutDist']=0;
		$frets[0]['pFretDist']=0;
		$frets[0]['width']=$doPartials?$frets[0]['fret']->length():0;
		$frets[0]['angle']=$doPartials?$frets[0]['fret']->angle():acos(-1.01);
		$frets[0]['intersection']=$guitar['strings'][$i]->end1;
		$frets[0]['midline_intersection']=$doPartials?intersect($midline,$frets[0]['fret']):
						new Point(acos(-1.01),acos(-1.01));
		$temp=new Segment($midline->end2(),$frets[0]['midline_intersection']);
		$frets[0]['midline_bridgeDist']=$doPartials?$temp->length():acos(-1.01);
		$frets[0]['midline_nutDist']=$doPartials?0:acos(-1.01);
		$frets[0]['midline_pFretDist']=$doPartials?0:acos(-1.01);
		$frets[0]['totalRatio']=0;
		
		$totalWidth[0]+=$frets[0]['width'];

		for ($j=1;$j<=$guitar['frets'];$j++)
		{
			$step=(($base+$j-1)%($tones))+1;
	//		$step=(($base+$j)%($tones-1))+1;
	//		$step=$step==0?1:$step;
			$ratio=1-(
				($scale[$step][1]*$scale[$step-1][0])/
				($scale[$step][0]*$scale[$step-1][1])
				);
			$x=$frets[$j-1]['intersection']->x+
				($ratio*($guitar['strings'][$i]->end2->x-$frets[$j-1]['intersection']->x));
			$y=$frets[$j-1]['intersection']->y+
				($ratio*($guitar['strings'][$i]->end2->y-$frets[$j-1]['intersection']->y));
			$frets[$j]['intersection']= new Point($x,$y);	
			$temp=new Segment($guitar['strings'][$i]->end2(),$frets[$j]['intersection']);
			$frets[$j]['bridgeDist']=$temp->length();
			$temp=new Segment($guitar['strings'][$i]->end1(),$frets[$j]['intersection']);
			$frets[$j]['nutDist']=$temp->length();
			$temp=new Segment($frets[$j-1]['intersection'],$frets[$j]['intersection']);
			$frets[$j]['pFretDist']=$temp->length();
			$frets[$j]['totalRatio']=$frets[$j]['nutDist']/$guitar['strings'][$i]->length();
			
			if ($doPartials)
			{
	/*			//partials depending on nut bridge intersection (bad)
				$temp=$parallelFrets?
					$nut->createParallel($frets[$j]['intersection']):
					new Segment($intersection,$frets[$j]['intersection']);
				$frets[$j]['fret']=new Segment(intersect($temp,$guitar['meta'][$i]),
						intersect($temp,$guitar['meta'][$i+1]));
				//partials depending on meta lines (questionable)
				if ($parallelFrets)
				{
					$temp=$nut->createParallel($frets[$j]['intersection']);
					$frets[$j]['fret']=new Segment(intersect($temp,$guitar['meta'][$i]),
						intersect($temp,$guitar['meta'][$i+1]));
				}
				else
				{
					$frets[$j]['fret']=new Segment(
						$guitar['meta'][$i]->pointAt($guitar['meta'][$i]->length()*
							$frets[$j]['totalRatio']),
						$guitar['meta'][$i+1]->pointAt($guitar['meta'][$i+1]->length()*
							$frets[$j]['totalRatio'])
						);
				}
	*/
				//partials depending on outer strings (questionable)
				if ($parallelFrets)
				{
					$temp=$nut->createParallel($frets[$j]['intersection']);
				}
				else
				{
					$temp=new Segment(
						$guitar['strings'][0]->pointAt($guitar['strings'][0]->length()*
							$frets[$j]['totalRatio']),
						$guitar['strings'][$numStrings-1]->pointAt($guitar['strings'][$numStrings-1]->length()*
							$frets[$j]['totalRatio'])
						);
				}
				$frets[$j]['fret']=new Segment(intersect($temp,$guitar['meta'][$i]),
						intersect($temp,$guitar['meta'][$i+1]));
				
				
				$frets[$j]['width']=$frets[$j]['fret']->length();
				$frets[$j]['angle']=$frets[$j]['fret']->angle();
				$frets[$j]['midline_intersection']=intersect($midline,$frets[$j]['fret']);
				$temp=new Segment($midline->end2(),$frets[$j]['midline_intersection']);
				$frets[$j]['midline_bridgeDist']=$temp->length();
				$temp=new Segment($midline->end1(),$frets[$j]['midline_intersection']);
				$frets[$j]['midline_nutDist']=$temp->length();
				$temp=new Segment($frets[$j-1]['midline_intersection'],$frets[$j]['midline_intersection']);
				$frets[$j]['midline_pFretDist']=$temp->length();
			}
			else
			{
				$frets[$j]['fret']=new Segment($frets[$j]['intersection'],$frets[$j]['intersection']);
				$frets[$j]['width']=0;
				$frets[$j]['angle']=acos(-1.01);
				$frets[$j]['midline_intersection']=new Point(acos(-1.01),acos(-1.01));
				$frets[$j]['midline_bridgeDist']=acos(-1.01);
				$frets[$j]['midline_nutDist']=acos(-1.01);
				$frets[$j]['midline_pFretDist']=acos(-1.01);
			}
			$totalWidth[$j]+=$frets[$j]['width'];
		
		}
		$strings[]=$frets;
	}
	return array('strings'=>$strings,'frets'=>$totalWidth,'midline'=>$midline,'nut'=>$nut,'bridge'=>$bridge);	
}

function processGuitar4svg()
{
	global $guitar,$threshold;
	//test strings ends are on nut and bridge
	//if not dont to partials
	$numStrings=sizeof($guitar['strings']);
	$doPartials=true;
	$parallelFrets=true;
	
	$nut=new Segment($guitar['strings'][0]->end1(),$guitar['strings'][$numStrings-1]->end1());
	$bridge=new Segment($guitar['strings'][0]->end2(),$guitar['strings'][$numStrings-1]->end2());
	$midline=new Segment(
		new Point(($nut->end2->x+$nut->end1->x)/2,($nut->end2->y+$nut->end1->y)/2),
		new Point(($bridge->end2->x+$bridge->end1->x)/2,($bridge->end2->y+$bridge->end1->y)/2)
		);
	foreach($guitar['strings'] as $string)
	{
		if (	!($nut->distanceToPoint($string->end1())<$threshold) ||
			!($bridge->distanceToPoint($string->end2())<$threshold))
		{
			echo $string->toString(),'<br />';
			echo $nut->distanceToPoint($string->end1()),'<br />';
			echo $bridge->distanceToPoint($string->end2()),'<br />';
			$doPartials=false;
			break;
		}
	}
	$denom=(($bridge->end2->y-$bridge->end1->y)*($nut->end2->x-$nut->end1->x))-
	(($bridge->end2->x-$bridge->end1->x)*($nut->end2->y-$nut->end1->y));
	if ($denom!=0) $parallelFrets=false;
	$intersection=intersect($nut,$bridge);

	$strings=array();
	$tones=sizeof($guitar['scale']['steps'])-1;
	$totalWidth=array();
	$scale=$guitar['scale']['steps'];
	for ($i=0;$i<$numStrings;$i++)
	{
		$base=$guitar['tuning'][$i];
		$frets=array();
		$frets[0]['fret']=$doPartials?new Segment($guitar['meta'][$i]->end1,$guitar['meta'][$i+1]->end1):
				new Segment($guitar['strings'][$i]->end1,$guitar['strings'][$i]->end1);
		$frets[0]['intersection']=$guitar['strings'][$i]->end1;

		for ($j=1;$j<=$guitar['frets'];$j++)
		{
			$step=(($base+$j-1)%($tones))+1;
	//		$step=(($base+$j)%($tones-1))+1;
	//		$step=$step==0?1:$step;
			$ratio=1-(
				($scale[$step][1]*$scale[$step-1][0])/
				($scale[$step][0]*$scale[$step-1][1])
				);
			$x=$frets[$j-1]['intersection']->x+
				($ratio*($guitar['strings'][$i]->end2->x-$frets[$j-1]['intersection']->x));
			$y=$frets[$j-1]['intersection']->y+
				($ratio*($guitar['strings'][$i]->end2->y-$frets[$j-1]['intersection']->y));
			$frets[$j]['intersection']= new Point($x,$y);	
			$temp=new Segment($guitar['strings'][$i]->end1(),$frets[$j]['intersection']);
			$frets[$j]['nutDist']=$temp->length();
			$frets[$j]['totalRatio']=$frets[$j]['nutDist']/$guitar['strings'][$i]->length();
			
			if ($doPartials)
			{
	/*			//partials depending on nut bridge intersection (bad)
				$temp=$parallelFrets?
					$nut->createParallel($frets[$j]['intersection']):
					new Segment($intersection,$frets[$j]['intersection']);
				$frets[$j]['fret']=new Segment(intersect($temp,$guitar['meta'][$i]),
						intersect($temp,$guitar['meta'][$i+1]));
				//partials depending on meta lines (questionable)
				if ($parallelFrets)
				{
					$frets[$j]['fret']=$nut->createParallel($frets[$j]['intersection']);
				}
				else
				{
					$frets[$j]['fret']=new Segment(
						$guitar['meta'][$i]->pointAt($guitar['meta'][$i]->length()*
							$frets[$j]['totalRatio']),
						$guitar['meta'][$i+1]->pointAt($guitar['meta'][$i+1]->length()*
							$frets[$j]['totalRatio'])
						);
				}
	*/
				//partials depending on outer strings (questionable)
				if ($parallelFrets)
				{
					$temp=$nut->createParallel($frets[$j]['intersection']);
				}
				else
				{
					$temp=new Segment(
						$guitar['strings'][0]->pointAt($guitar['strings'][0]->length()*
							$frets[$j]['totalRatio']),
						$guitar['strings'][$numStrings-1]->pointAt($guitar['strings'][$numStrings-1]->length()*
							$frets[$j]['totalRatio'])
						);
				}
				$frets[$j]['fret']=new Segment(intersect($temp,$guitar['meta'][$i]),
						intersect($temp,$guitar['meta'][$i+1]));
			}
			else
			{
				$frets[$j]['fret']=new Segment($frets[$j]['intersection'],$frets[$j]['intersection']);
			}
		}
		$strings[]=$frets;
	}
	return array('strings'=>$strings,'midline'=>$midline,'nut'=>$nut,'bridge'=>$bridge);	
}

?>
