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
//The parseScala() function explodes a Scala Scale File
//into an array of ratios useful for calculating frequencies 
//and string lengths.
if (!function_exists('parseScala'))
{
	function parseScala($scala)
	{
		$lines=explode("\n",trim($scala));
//		echo '<pre>';print_r($lines);echo '</pre>';
		
		$scale=array('steps'=>array(array(1,1)),
				'title'=>'',
				'errors'=>0,
				'errorstring'=>'');
		$first=true;
		$numlines=sizeof($lines);
		for ($i=0;$i<$numlines;$i++)
		{
			if ($first)
			{
				$lines[$i]=trim($lines[$i]);
			}
			else
			{
				list($lines[$i])=preg_split('/[\s]+/', trim($lines[$i]), -1, PREG_SPLIT_NO_EMPTY);
			}
	
			if (strpos($lines[$i],'!')===0 || (!$first && $lines[$i]=='')) 
			{	
				unset($lines[$i]);
			}
			else
			{
				$first=false;
			}
		}
		$lines=array_values($lines);
		$scale['title']=$lines[0];
		$expected = (int)$lines[1];
		$actual = sizeof($lines)-2;
		if ($actual==$expected)
		{
			for($i=2;$i<sizeof($lines);$i++)
			{

				if (strpos($lines[$i],'.')!==false)
				{
					$num=pow(2,(((float)$lines[$i])/1200));
					$denom=1;
				}
				elseif (strpos($lines[$i],'/')!==false)
				{
					$line=explode('/',$lines[$i]);
					$num=(int)trim($line[0]);
					$denom=(int)trim($line[1]);

				}
				else
				{
					$num=(int)$lines[$i];
					$denom=1;
				}
				$scale['steps'][]=array($num,$denom);
				
				if ($num<0 xor $denom<0)
				{
					//die('Scala file read error! (2)');
					//return array();
					$scale['errors']++;
					$scale['errorstring'].='Error at ("'.$lines[$i]."\"): Negitive ratios are not allowed!\n<br />";
				}
				
				
			}
		}
		else
		{
			//die('Scala file read error! (1)');
			//return array();
			$scale['errors']++;
			$scale['errorstring'].="Error: expected $expected tones but found $actual!\n<br />";
		}
		//echo '<pre>';print_r($scale);echo '</pre>';
		return $scale;
	}
}
?>
