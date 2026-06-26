<?php

	require_once(__DIR__ . '/Server/Server.php');

	class Setting {

		public $server;

		public function __construct() {
			$this->server = new Server();
		}

		public function getConnection() {
			try {
			    $conexion = new PDO(
			    	$this->server->getDsn(),
			    	$this->server->getUser(),
			    	$this->server->getPassword()
			    );
			    $conexion->setAttribute(
			    	PDO::ATTR_EMULATE_PREPARES, false
			    );
				$conexion->setAttribute(
					PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true
				);
				$conexion->setAttribute(
					PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION
				);
				return $conexion;
			} catch (PDOException $e) {
				echo "\n".$e->getMessage();
        		return null;
			}
		}

	}
	
?>