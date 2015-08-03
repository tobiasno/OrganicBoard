<?php
  /**
   * @author Tobias Nolte <tobias@abygr.com>
   * @link http://www.mndcntrl.com/ Developer Blog
   */  

  class Database {

    /**
     * Path to configuration file for the database.
     *
     * It is recommendet to place the config file outside of
     * the web directory to prevent access by third parties.
     */
    private $config_file = "config/mysql_config.ini";

    // Stores database connection of type PDO.
    private $database;

    /**
     * Constructor
     * 
     * Parses the config file and creates the PDO object.
     */
    public function __construct () {
      $config = parse_ini_file ($this -> config_file);
      try {
        $this -> database = new PDO ('mysql:host='.$config['host'].';dbname='. $config['dbname'], $config['username'], $config['password']);
        $this -> database -> query("set character set utf8");
        $this -> database -> query("set names utf8");
      }
      catch (PDOException $e) {
        print "Error!: " . $e -> getMessage () . "<br/>";
        die ();
      }
    }

    /**
     * Destructor
     * 
     * Disconnects the Database.
     */
    public function __destruct () {
      $this -> database = null;
    }

    /**
     * Disconnects the Database.
     */
    public function disconnect () {
      $this -> database = null;
    }

    /**
     * query
     *
     * @param string $query Query to be executed
     * @return mixed[] PDO Object Query output
     */
    public function query ($query) {
      $statement = $this -> database -> prepare ($query);
      // $statement = $this -> database -> query ($query);
      $statement -> execute ();
      $result = $statement -> fetchAll ();
      return $result;
    }

    /**
     * cleanData strips the PDO object from unused values.
     *
     * @param mixed[] $data
     * @return mixed[] $data
     */
    public function cleanData ($data) {
      foreach ($data as &$subset) {
        for ($i = 0; $i < 8; $i++) {
          unset ($subset[$i]);
        }
      }
      return $data;
    }

    /**
     * gets id of last inserted or updated record
     *
     * @return int id of last changed record
     */
    public function getInsertId () {
      return $this -> database -> lastInsertId ();
    }
  }
?>
