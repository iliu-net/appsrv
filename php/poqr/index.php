<?php
echo '<PRE>';
foreach ($_ENV as $i=>$j) {
  echo "$i=$j\n";
}
echo '</PRE><HR/></PRE>';
foreach ($_SERVER as $i=>$j) {
  echo "$i=$j\n";
}
echo '</PRE>';

