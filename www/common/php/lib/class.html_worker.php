<?php
  /**
   * @author Tobias Nolte <tobias@abygr.com>
   * @link http://www.mndcntrl.com/ Developer Blog
   */  

  class HTML_Worker {

    /**
     * Automatically makes links clickable, new lines visible etc.
     *
     * @param string $text
     * @return string
     */
    public function enhanceContent($text){
          $text = preg_replace('!(((f|ht)tp(s)?://)[-a-zA-Zа-яА-Я()0-9@:%_+.~#?&;//=]+)!i', '<a href="$1" target="_blank">$1</a>', $text);
          $text = preg_replace('#&lt;(/?(?:i|b|u|ul|li|ol|del))&gt;#', '<\1>', $text);
          $text = $this -> closeTags ($text);
          $text = nl2br ($text);
          return $text;
    }

    /**
     * Closes HTML tags at the end of the content that were left open
     *
     * @param string $html
     * @return string
     */
    private function closeTags ($html) {
      preg_match_all ('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
      $opened_tags = $result[1];
      preg_match_all ('#</([a-z]+)>#iU', $html, $result);
      $closed_tags = $result[1];
      $len_opened = count($opened_tags);
      if (count ($closed_tags) == $len_opened) {
        return $html;
      }
      $opened_tags = array_reverse ($opened_tags);
      for ($i = 0; $i < $len_opened; $i++) {
        if (!in_array ($opened_tags[$i], $closed_tags)){
          $html .= '</'.$opened_tags[$i].'>';
        } else {
          unset ($closed_tags[array_search ($opened_tags[$i], $closed_tags)]);
        }
      }
      return $html;
    }
  }    
?>
