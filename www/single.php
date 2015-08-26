<?php
  /**
   * @author Tobias Nolte <tobias@abygr.com>
   * @link http://www.mndcntrl.com/ Developer Blog
   */

  require_once ("common/php/path.php");
?>

<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" media="(orientation: landscape)" href="<?php echo PATH_CSS;?>style.css">
    <link rel="stylesheet" type="text/css" media="(orientation: portrait)" href="<?php echo PATH_CSS;?>mobile.css">
    <title>With a grain of salt</title>
  </head>
  <body>
  
    <!-- Powered by OrganicBoard -->
    <a href="http://www.mndcntrl.com/impressum.html" id="imprint_link">Impressum</a>

<?php
  error_reporting (-1);

  require_once (PATH_PHP . "class.forum.php");
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
