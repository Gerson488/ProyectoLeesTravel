<?php
	class Server {

		public function getDsn() {
			return "mysql:host=localhost;port=TUPUERTO;dbname=TUBASEDEDATOS;charset=utf8";
		}

		public function getUser() {
			return "TUUSUARIO";
		}

		public function getPassword() {
			return "TUPASSWORD";
		}
	}
?>