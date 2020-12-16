<?php
class Database{

    private $dbCofig;
    public $conn;
    
    public function __construct($config) {
        $this->dbCofig = $config['db'];
    }

    public function getConnection(){
 
        $this->conn = null;
        $this->conn = new mysqli($this->dbCofig["host"], $this->dbCofig["username"], $this->dbCofig["password"],  $this->dbCofig["dbname"]);
        $this->conn->set_charset('UTF8');
        if ($this->conn->connect_error) {
            die('Connect Error (' . $this->conn->connect_errno . ') '
                    . $this->conn->connect_error);
        }
 
        return $this->conn;
    }
}
?>