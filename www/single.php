<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>Forum</title>
  </head>
  <body>

<?php
  error_reporting (-1);

  require_once ("class.forum.php");
  $forum = new Forum ();

  if(!isset($_GET["id"])) {
    $_GET["id"] = 0;
  }

  if(!isset($_GET["topic"])) {
    $_GET["topic"] = "";
  }

  echo $forum -> getSingle ($_GET["id"], $_POST);
?>

  </body>
</html>
