<?php
  require_once ("class.clock.php");

  class CookieMonster {
    private $clock;
    private $cookie_name = "OrganicBoard";
    private $cookie_life = "30"; // in days
    private $domain = ".mndcntrl.com";

    public function __construct () {
      $this -> clock = new Clock ();
    }

    /*
     * setMyCookie
     *
     * @param none
     * @return null
     */
    public function setMyCookie () {
      setcookie ($this -> cookie_name, $this -> clock -> getTimestamp (), strtotime ('+'.$this -> cookie_life.' days'), "/", $this -> domain);
    }

    /*
     * getTimeSinceLastVisit
     *
     * @param $cookie : Array
     * @return Int, in seconds
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
