<?php
  /**
   * @author Tobias Nolte <tobias@abygr.com>
   * @link http://www.mndcntrl.com/ Developer Blog
   */

  require_once ("class.cookiemonster.php");
  $cookiemonster = new CookieMonster ();
  $cookiemonster -> setMyCookie ();
?>

<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>With a grain of salt</title>
  </head>
  <body>
  
    <!-- Powered by OrganicBoard -->
    <a href="http://www.mndcntrl.com/impressum.html" id="imprint_link">Impressum</a>

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

  echo $forum -> getBoard ($_GET["topic"], $_POST, $cookiemonster -> getTimeSinceLastVisit ($_COOKIE));
?>

  </body>
</html>
