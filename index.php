<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>AppServer Menu</title>
</head>
<body>
<h1>Application Server Menu</h1>
<ul>
<?php
foreach (glob("*") as $d) {
  if (!is_dir($d)) continue;
  if (is_file("$d/index.php")) {
     echo "<ul><a href=\"$d\">$d</a></ul>";
  }
}
  ?>
</ul>
</body>
</html>
