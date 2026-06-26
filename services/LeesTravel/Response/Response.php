<?php

	class Response {

		//status 200 servicio correcto
		//status 201 Problemas con el servicio
		//status 202 Mensajes Custom de error
		//status 401 Mensaje de expiro session

		public function responseSuccess($values) {
			return json_encode($this->structSuccess('',$values));
		}

		public function responseSuccessValidation($value) {
			return json_encode($value);
		}

		public function responseMessageSuccess($message) {
			return json_encode($this->structMessageSuccess($message));
		}

		public function responseError() {
			return json_encode($this->structMessageErrorService());
		}

		public function responseErrorMessage($message) {
			return json_encode($this->structMessageErrorCustom($message));
		}

		public function responseErrorSession($message) {
			return json_encode($this->structMessageExpireSession($message));
		}

		//Response from array
		public function responseMessageArray($state, $message, $values) {
			if ($state == true) { 
				return $this->structSuccess($message, $values);
			} else {
				$data = [
					'status' => 203,
					'message' => $message,
					'data' => null
				];
				return $data;
			}
		}

		private function structSuccess($message, $value) {
			$data = [
				'status' => 200,
				'message' => $message,
				'data' => $value
			];
			return $data;
		}

		private function structMessageSuccess($message) {
			$data = [
				'status' => 200,
				'message' => $message,
				'data' => null
			];
			return $data;
		}

		public function structMessageErrorService() {
			$data = [
				'status' => 201,
				'message' => 'Problemas con el servicio',
				'data' => null
			];
			return $data;
		}

		public function structMessageErrorCustom($message) {
			$data = [
				'status' => 202,
				'message' => $message,
				'data' => null
			];
			return $data;
		}

		public function structMessageExpireSession($message) {
			$data = [
				'status' => 401,
				'message' => $message,
				'data' => null
			];
			return $data;
		}
	}
?>