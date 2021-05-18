<?php
class DB{
	private $hostname;
	private $dbname;
	private $user;
	private $pass;

	public function connect(){
		try {
			$this->hostname = "localhost";
			$this->dbname = "standbyme";
			$this->user = "standbymeuser";
			$this->pass = "qwerty";
			$db = new PDO ("mysql:host=$this->hostname;dbname=$this->dbname", $this->user, $this->pass);
			return $db;
		} catch (PDOException $e) {
			echo "Errore: " . $e->getMessage();
		}
    }
	
}



