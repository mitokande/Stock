<?php


class ResultData{

	public $success;
	public $code;
	public $data;

	public function __construct($success,$code,$data) {
		$this->success = $success;
		$this->code = $code;
		$this->data = $data;
	}
}