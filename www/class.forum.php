<?php
  /**
   * @author Tobias Nolte <tobias@abygr.com>
   * @link http://www.mndcntrl.com/ Developer Blog
   */  

  require_once ("class.database.php");
  require_once ("class.clock.php");

  class Forum {
    private $database;
    private $clock;
    private $time_since_last_visit;
    private $posts_on_frontpage = 100; // Number of posts shown on frontpage
    private $replies_on_frontpage = 5; // Number of replies shon in preview on frontpage
    private $purge_interval = 4320; // in minutes
    private $index_topics_interval = 120; // in minutes
    private $index_topics_number = 50; // Number of Topics shown on frontpage
    private $path = '';
    private $single_path = 'single.php?id=';
    private $topic_path = '?topic=';
    private $scoring_algorithm = '
        100.00000000 * POWER(number_of_replies + 1.00000000, 0.50000000)
        / (
          TIMESTAMPDIFF(HOUR, date, NOW()) + 0.00000000
          * (TIMESTAMPDIFF(HOUR, last_reply, NOW()) + 0.00000000)
          + 0.25000000
        )'; // Voodoo-Magic
  
    /**
     * Constructor.
     */
    public function __construct () {
      $this -> database = new Database ();
      $this -> clock = new Clock ();
    }

    // Taking requests

    /**
     * Returns complete HTML for the board.
     *
     * @param string $topic
     * @param integer $id
     * @param integer $time_since_last_visit
     * @return string
     */
    public function getBoard ($topic, $post, $time_since_last_visit) {
      $this -> time_since_last_visit = $time_since_last_visit;
      $this -> purge ();
      $this -> writePost ($post);
      $this -> indexTopics ();
      $output = $this -> composeBoard ($topic);
      return $output;
    }

    /**
     * Returns complete HTML for a single post.
     *
     * @param integer $id
     * @param string $post
     * @return string
     */
    public function getSingle ($id, $post) {
      $this -> writeReply ((int)$id, $post);
      $output = $this -> composeSingle ((int)$id);
      return $output;
    }

    // Composing of the frontpage

    /**
     * Returns HTML for the board
     *
     * @param string $topic
     * @return string
     */
    private function composeBoard ($topic = '') {
      $data = null;
      if ((string)$topic === '') {
        $data = $this -> database -> query ('
            SELECT *
            FROM posts
            ORDER BY '.$this -> scoring_algorithm.' DESC
            LIMIT '. $this -> posts_on_frontpage.';');
      }
      else {
        $data = $this -> database -> query ('
            SELECT *
            FROM posts
            WHERE topic = "'.$topic.'"
            ORDER BY '.$this -> scoring_algorithm.' DESC
            LIMIT '. $this -> posts_on_frontpage.';');
      }
      $data = $this -> database -> cleanData ($data);
      $output = '<a href="." id="home_link">Home</a>';
      $output .= $this -> composeTopicsList ();
      $output .= $this -> composeOpenPostForm ($topic);
      $output .= '<div id = board_box>';
      foreach ($data as $post_data) {
        $output .=  $this -> composeBoardPost ($post_data);
      }
      $output .= '</div>';
      return $output;
    }

    /**
     * Returns HTML for one post on the board
     *
     * @param mixed[] $post_data
     * @return string
     */
    private function composeBoardPost ($post_data) {
      $output = '
          <div class="board_post">
            <div class="board_post_area">
              <div class="board_headline">
                <a href="'.$this -> single_path . $post_data["id"] .'">'.($this -> time_since_last_visit < $this -> clock -> getDifference ($post_data["date"], $this -> clock -> getTimestamp ()) ? '' : '<span class="new">[Neu]</span> ').$post_data["headline"].'</a>
              </div>
              <div class="board_content">'.$this -> enhanceContent ($post_data["content"]).'</div>
              <div class="board_author">von <strong>'.$post_data["author"].'</strong></div>
              <div class="board_date">um <a href="'.$this -> single_path . $post_data["id"] .'">'.$post_data["date"].'</a></div>
              <div class="board_topic"><a href="'.$this -> topic_path . $post_data["topic"].'">'.$post_data["topic"].'</a></div>
            </div>
            <div class="board_replies_area">';
      $output .= $this -> composeBoardReplies ($post_data["id"]);
      $output .= '
            </div>
          </div>';
      return $output;
    }

    /**
     * Returns HTML for the replies on the board.
     *
     * @param integer $is
     * @return string
     */
    private function composeBoardReplies ($id) {
      $data = $this -> database -> query ('
          SELECT *
          FROM (
            SELECT *
            FROM replies
            WHERE post_ref = '.$id.'
            ORDER BY id DESC
            LIMIT '.$this -> replies_on_frontpage.'
            ) AS T
          ORDER BY id ASC;');
      $data = $this -> database -> cleanData ($data);
      if (! empty ($data)) {
        $output = '';
        foreach ($data as $reply_data) {
          $output .= $this -> composeBoardReply ($reply_data);
        }
        return $output;
      }
      return '';
    }

    /**
     * Returns HTML for one reply on the board.
     *
     * @param mixed[] $reply_data
     * @return string
     */
    private function composeBoardReply ($reply_data) {
      $output = '
          <div class="board_reply" id="'.$reply_data["id"].'">
            <div class="board_reply_content">'.$this -> enhanceContent ($reply_data["content"]).'</div>
            <div class="board_reply_author">'.($this -> time_since_last_visit < $this -> clock -> getDifference ($reply_data["date"], $this -> clock -> getTimestamp ()) ? '' : '<span class="new">[Neu]</span> ').'von <strong>'.$reply_data["author"].'</strong></div>
            <div class="board_reply_date">um <a href="'.$this -> single_path . $reply_data["post_ref"].'#'.$reply_data["id"].'">'.$reply_data["date"].'</a></div>
          </div>';
      return $output;
    }

    /**
     * Composes the form to write a post. Preinserts topic.
     *
     * @param string $topic
     * @return string
     */
    private function composeOpenPostForm ($topic) {
      $output = '
          <div id="open_topic_form">
            <div id="open_topic_form_headline">Neuen Beitrag verfassen</div>
            <form action="'.$this -> path.'" method="post" accept-charset="UTF-8">
              <input type="text" maxlength="16" id="name" name="name" placeholder="Name (optional)"><br>
              <input type="text" maxlength="64" id="headline" name="headline" placeholder="Überschrift"><br>
              <input type="text" maxlength="20" id="topic" name="topic" placeholder="Thema (optional)" value="'.$topic.'"><br>
              <textarea type="text" id="content" name="content" placeholder="Text"></textarea><br>
              <button type="submit">Los!</button>
            </form>
          </div>';
      return $output;
    }

    /**
     * Composes the list of topics.
     *
     * @return string
     */
    private function composeTopicsList () {
      $data = $this -> database -> query ('
          SELECT topic, number_of_occurrences
          FROM topics
          ORDER BY number_of_occurrences DESC
          LIMIT '.$this -> index_topics_number.';');
      $data = $this -> database -> cleanData ($data);
      $output = '
          <div id="topics_list_box">
            <div id="topics_list">
              <div id="topics_list_headline">Top 50 Themen</div>
              <ul>';
      foreach ($data as $item) {
        if (!($item["topic"] === '')) {
          $output .= '<li><a href="?topic='.$item["topic"].'">'.$item["topic"].'</a></li>';
        }
      }
      $output .= '
              </ul>
            </div>
          </div>';
      return $output;
    }

    // Compose single thread

    /**
     * Returns HTML for a single view of a post.
     *
     * @param integer $id
     * @return string
     */
    private function composeSingle ($id) {
      $data = $this -> database -> query ('
          SELECT *
          FROM posts
          WHERE id = '.$id.';');
      $data = $this -> database -> cleanData ($data);
      $output = '
          <a href="." id="single_back_link">Zurück</a>
          <div id="single_post">
            <div id="single_post_area">
              <div class="single_headline"><a href="'.$this -> single_path . $data[0]["id"] .'">'.$data[0]["headline"].'</a></div>
              <div class="single_content">'.$this -> enhanceContent ($data[0]["content"]).'</div>
              <div class="single_author">von <strong>'.$data[0]["author"].'</strong></div>
              <div class="single_date">um <a href="'.$this -> single_path . $data[0]["id"] .'">'.$data[0]["date"].'</a></div>
              <div class="single_topic"><a href="./'.$this -> topic_path . $data[0]["topic"].'">'.$data[0]["topic"].'</a></div>
            </div>
            <div id="single_replies_area">';
      $output .= $this -> composeSingleReplies ($id);
      $output .= '
            </div>
            <div id="answer_form">';
      $output .= $this -> composeAnswerForm ($id);
      $output .= '
        </div>
          </div>';
      return $output;
    }

    /**
     * Returns HTML for the replies in a single post.
     *
     * @param integer $id
     * @return string
     */
    private function composeSingleReplies ($id) {
      $data = $this -> database -> query ('
          SELECT *
          FROM replies
          WHERE post_ref = '.$id.'
          ORDER BY id ASC;');
      $data = $this -> database -> cleanData ($data);
      if (! empty ($data)) {
        $output = '';
        foreach ($data as $reply_data) {
          $output .= $this -> composeSingleReply ($reply_data);
        }
        return $output;
      }
      return '';
    }

    /**
     * Returns HTML for one reply in a single post.
     *
     * @param mixed[] $reply_data
     * @return string
     */
    private function composeSingleReply ($reply_data) {
      $output = '
          <div class="single_reply" id="'.$reply_data["id"].'">
            <div class="single_reply_content">'.$this -> enhanceContent ($reply_data["content"]).'</div>
            <div class="single_reply_author">von <strong>'.$reply_data["author"].'</strong></div>
            <div class="single_reply_date">um <a href="'.$this -> single_path . $reply_data["post_ref"].'#'.$reply_data["id"].'">'.$reply_data["date"].'</a></div>
          </div>';
      return $output;
    }

    /**
     * Composes the form to answer a post.
     *
     * @param integer $id
     * @return string
     */
    private function composeAnswerForm ($id) {
      $output = '
          <div id="answer_form_headline">Antworten</div>
          <form action="'.$this -> single_path . $id.'#answer_form" method="post" accept-charset="UTF-8">
            <input type="text" maxlength="16" id="name" name="name" placeholder="Name (optional)"><br>
            <textarea type="text" id="content" name="content" placeholder="Text"></textarea><br>
            <button type="submit">Los!</button>
          </form>';
      return $output;
    }

    // Additions for displaying content

    /**
     * Automatically makes links clickable, new lines visible etc.
     *
     * @param string $text
     * @return string
     */
    private function enhanceContent($text){
          $text = preg_replace('!(((f|ht)tp(s)?://)[-a-zA-Zа-яА-Я()0-9@:%_+.~#?&;//=]+)!i', '<a href="$1" target="_blanc">$1</a>', $text);
          $text = preg_replace('#&lt;(/?(?:i|b|u|ul|li|ol))&gt;#', '<\1>', $text);
          $text = nl2br ($text);
          return $text;
    }
    
    // Writing to the database

    /**
     * Writes post to the database.
     *
     * @param mixed[] $post
     * @return boolean
     */
    private function writePost ($post) {
      if (empty ($post) || $post["headline"] === '' || $post["content"] === '') {
        return false;
      } else {
        $post = $this -> cleanInput ($post);
        $this -> database -> query ('
            INSERT INTO posts
            (date, author, headline, content, topic, last_reply, number_of_replies)
            VALUES (
              "'.$this -> clock -> getTimestamp ().'", 
              "'.$post["name"].'", 
              "'.$post["headline"].'", 
              "'.$post["content"].'", 
              "'.$post["topic"].'", 
              "'.$this -> clock -> getTimestamp ().'",
              "0");');
        return true;
      }
    }

    /**
     * Writes a reply to the database.
     *
     * @param integer $id
     * @param mixed[] $post
     * @return boolean
     */
    private function writeReply ($id, $post) {
      if (empty ($post) || $post["content"] === '') {
        return false;
      } else {
        $post = $this -> cleanInput ($post);
        $this -> database -> query ('
            INSERT INTO replies
            (post_ref, date, author, content)
            VALUES (
              "'.$id.'", 
              "'.$this -> clock -> getTimestamp ().'", 
              "'.$post["name"].'", 
              "'.$post["content"].'");
            UPDATE posts
            SET last_reply = "'.$this -> clock -> getTimestamp ().'",
            number_of_replies = number_of_replies + 1
            WHERE id = "'.$id.'";');
        return true;
      }
    }

    /**
     * Cleans the input from dangerous chars.
     *
     * @param mixed[] $post
     * @return string
     */
    private function cleanInput ($post) {
      if ($post["name"] === '') {
        $post["name"] = "Anonymous";
      }
      foreach ($post as $key => $value) {
        $post[$key] = trim ($value);
        $post[$key] = htmlspecialchars ($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
      }
      return $post;
    }

    // Purge unwanted posts

    /**
     * Deletes unwanted posts from the database.
     * Contains purge rules!
     */
    private function purge () {
      $data = $this -> database -> query ('
          SELECT TIMESTAMPDIFF(
            MINUTE, last_purge, NOW()
          ) AS last_purge
          FROM cron_dates
          WHERE id = "1"
          LIMIT 1;');
      $data = $this -> database -> cleanData ($data);
      if ($data[0]["last_purge"] > $this -> purge_interval) {
        $data = $this -> database -> query ('
            SELECT id
            FROM posts
            WHERE '.$this -> scoring_algorithm.' <= 0.0002
            OR LENGTH(content) <= 140
            OR topic = "spam"
            ORDER BY id ASC;');
        $data = $this -> database -> cleanData ($data);
        foreach ($data as $post) {
          $this -> purgePost ($post["id"]);
        }
        $this -> database -> query ('
            UPDATE cron_dates
            SET last_purge = "'.$this -> clock -> getTimestamp ().'"
            WHERE id = "1";');
      }
    }

    /**
     * Deletes one post
     *
     * @param integer $post
     */
    private function purgePost ($id) {
      $this -> database -> query ('
          DELETE FROM posts
          WHERE id = "'.$id.'";
          DELETE FROM replies
          WHERE post_ref = "'.$id.'";');
    }

    // Index the topics
    
    /**
     * Gets most popular topics and creates ordered table.
     */
    public function indexTopics () {
      $data = $this -> database -> query ('
          SELECT TIMESTAMPDIFF(
            MINUTE, last_topic_index, NOW()
          ) AS last_topic_index
          FROM cron_dates
          WHERE id = "1"
          LIMIT 1;');
      $data = $this -> database -> cleanData ($data);
      if ($data[0]["last_topic_index"] > $this -> index_topics_interval) {
        $this -> database -> query ('
            TRUNCATE TABLE topics;
            INSERT INTO topics (
              topic, number_of_occurrences
            )            
            SELECT topic, count( * ) AS number_of_occurrences
            FROM posts
            GROUP BY topic
            LIMIT '.$this -> index_topics_number.';
            UPDATE cron_dates
            SET last_topic_index = "'.$this -> clock -> getTimestamp ().'"
            WHERE id = "1";');
      }
    }
  }
?>
