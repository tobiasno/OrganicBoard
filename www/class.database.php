<?php
  class Database {

    /*
     * Path to configuration file for the database.
     *
     * It is recommendet to place the config file outside of
     * the web directory to prevent access by third parties.
     */
    private $config_file = "./db_config.ini";

    // Stores database connection of type PDO.
    private $database;

    /*
     * Constructor
     * 
     * Parses the config file and creates the PDO object.
     *
     * @param null
     * @return null
     */
    public function __construct () {
      $config = parse_ini_file ($this -> config_file);
      try {
        $this -> database = new PDO ('mysql:host='.$config['host'].';dbname='. $config['dbname'], $config['username'], $config['password']);
        // $this -> database -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // $this -> database -> query("set character set utf8");
        // $this -> database -> query("set names utf8");
      }
      catch (PDOException $e) {
        print "Error!: " . $e -> getMessage () . "<br/>";
        die ();
      }
    }

    /*
     * Destructor
     * 
     * Disconnects the Database.
     *
     * @param null
     * @return null
     */
    public function __destruct () {
      $this -> database = null;
    }

    public function disconnect () {
      $this -> database = null;
    }

    /*
     * query
     *
     * @param string Query to be executed
     * @return PDO Object Query output
     */
    public function query ($query) {
      $statement = $this -> database -> prepare ($query);
      // $statement = $this -> database -> query ($query);
      $statement -> execute ();
      $result = $statement -> fetchAll ();
      return $result;
    }

    /*
     * cleanData strips the PDO object from unused values.
     *
     * @param $data : array
     * @return $data : array
     */
    public function cleanData ($data) {
      foreach ($data as &$subset) {
        for ($i = 0; $i < 8; $i++) {
          unset ($subset[$i]);
        }
      }
      return $data;
    }
  }
?>
