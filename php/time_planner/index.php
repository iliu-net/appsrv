<?php
  /*
   *   syslogview
   *   Copyright (C) 2010 Alejandro Liu Ly
   *
   *   syslogview is free software; you can redistribute it and/or modify
   *   it under the terms of the GNU General Public License as
   *   published by the Free Software Foundation; either version 2 of 
   *   the License, or (at your option) any later version.
   *
   *   syslogview is distributed in the hope that it will be useful,
   *   but WITHOUT ANY WARRANTY; without even the implied warranty of
   *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   *   GNU General Public License for more details.
   *
   *   You should have received a copy of the GNU General Public
   *   License along with this program.  If not, see 
   *   <http://www.gnu.org/licenses/>
   *
   */
define('NL',"\n");
define('OFFICE_HOURS','bgcolor="#daeaf7"');
define('AWAKE_HOURS','bgcolor="#b4cfdd"');

$date = gmdate('Y-m-d');
$tzlist = array('America/Lima',
		'US/Central', 'US/Pacific',
		'Europe/Amsterdam', 'Europe/London',
		'Asia/Kuala_Lumpur');
//, 'Asia/Calcutta');
$tzoffs = array();
foreach ($tzlist as $tz) {
  $o = new DateTime($date,new DateTimeZone($tz));
  $tzoffs[] = $o->getOffset();
}

array_multisort($tzoffs,SORT_DESC,SORT_NUMERIC,$tzlist);
?>
<html>
 <head>
  <title>Time Planner</title>
  <style type="text/css">
  h1 { color: red; font-family: sans-serif; font-size: x-large; }
  p { font-family: sans-serif; font-size: medium; }
  caption { font-family: sans-serif; font-size: large; color: magenta; font-weight: bolder;}
th {  align: left; font-family: sans-serif; font-size: small; color: blue; }
th.hours { align: right; }
td { font-family: sans-serif; font-size: small; }
p.note { font-size: x-small; align: right; }
  </style>
 </head>
 <body>
   <h1>Time Planner</h1>
<pre>
   Select a date [CALENDAR]
   Add a city [CITY SELECTOR]

</pre>
   
<p>Read up for times that are earlier, and read down for times that are later.
  Note that change of date may occur.</p>
<?php
  echo '<table border="1">'.NL;
  echo '<caption>'.$date.'</caption>'.$NL;
  echo '<tr><th align="left">UTC</th>';
  for ($i =0 ; $i < 24; $i++) {
    echo '<th class="hours">&nbsp;&nbsp;'.($i == 0 ? 24 : $i).'</th>';
  }
  echo '</tr>'.NL;
  for ($i =0; $i < count($tzlist); $i++) {
    echo '<tr><th align="left">'.$tzlist[$i].'</th>';
    for ($j =0 ; $j < 24; $j++) {
      $h = ($j*3600 + $tzoffs[$i])/3600;
      if ($h < 1) {
	$h += 24;
      } elseif ($h > 24) {
	$h -= 24;
      }
      if ($h < 10) {
	$h = $h;
      }
      $attr = '';
      if (8 <= $h && $h <= 17) {
	$attr = ' '.OFFICE_HOURS;
      } else if (7 <= $h && $h <= 22) {
	$attr = ' '.AWAKE_HOURS;
      }
      echo '<td align="right"'.$attr.'>'.$h.'</td>';
    }
    echo '<tr>'.NL;
  }
  echo '</table>'.NL;
?>
  <div align="right"><p class="note">12 = noon; 24 = midnight</p></div>
  <p>The highlighted areas of the table show where working hours and awake hours line up across the different locations.</p>

  <div align="center"><table>
   <tr>
    <td><table border="1">
     <tr><td <?=OFFICE_HOURS?>>&nbsp;&nbsp;&nbsp;</td></tr>
    </table></td>
    <td>Normal working hours (8am-5pm)</td>
    <td><table border="1">
     <tr><td <?=AWAKE_HOURS?>>&nbsp;&nbsp;&nbsp;</td></tr>
    </table></td>
    <td>Hours when people are usually awake but not at work (7-8am, 5-10pm)
   </tr>
  </table></div>

</pre>
 </body>
</html>