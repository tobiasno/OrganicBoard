<?php
  /**
   * @author Tobias Nolte <tobias@abygr.com>
   * @link http://www.mndcntrl.com/ Developer Blog
   */  

  class Clock {

    /**
     * Returns timestamp.
     *
     * @return string $timestamp
     */
    public function getTimestamp () {
      return date('Y-m-d H:i:s');
    }

    /**
     * Calculates difference between timestamps in seconds.
     *
     * @param string $date1
     * @param string $date2
     * @return integer difference in seconds
     */
    public function getDifference ($date1, $date2) {
      return abs (strtotime ($date2) - strtotime ($date1));
    }
  }
?>
