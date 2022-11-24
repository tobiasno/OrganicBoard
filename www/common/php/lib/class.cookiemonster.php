<?php
  /**
   * @author Tobias Nolte <tobias@abygr.com>
   * @link http://www.mndcntrl.com/ Developer Blog
   */  

  require_once ("common/php/path.php");
  require_once (PATH_PHP . "class.clock.php");

  class CookieMonster {
    private $clock;
    private $cookie_name = "OrganicBoard";
    private $cookie_life = "30"; // in days
    private $domain = "tobiasnolte.com";

    /**
     * Constructor
     */
    public function __construct () {
      $this -> clock = new Clock ();
    }

    /**
     * Sets cookie.
     */
    public function setMyCookie () {
      setcookie ($this -> cookie_name, $this -> clock -> getTimestamp (), strtotime ('+'.$this -> cookie_life.' days'), "/", $this -> domain, false, true);
    }

    /**
     * Returns time since last visit
     *
     * @param mixed[] $cookie Array
     * @return integer in seconds
     */
    public function getTimeSinceLastVisit ($cookie) {
      if (empty ($cookie)) {
        return 2592000;
      } else {
        return $this -> clock -> getDifference ($cookie[$this -> cookie_name], $this -> clock -> getTimestamp ());
      }
    }
  }
?>
