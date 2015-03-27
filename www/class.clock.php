<?php
  class Clock {

    /*
     * getTimestamp
     *
     * @param none
     * @return String (timestamp)
     */
    public function getTimestamp () {
      return date('Y-m-d H:i:s');
    }

    /*
     * getDifference
     *
     * @param $date1 : String
     * @param $date2 : String
     * @return Int, difference in seconds
     */
    public function getDifference ($date1, $date2) {
      return abs (strtotime ($date2) - strtotime ($date1));
    }
  }
?>
