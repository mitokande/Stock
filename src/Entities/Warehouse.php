<?php

class Warehouse{
	public $idwarehouse;
	public $stock;
	public $reserved;
	public $reservedbackorders;
	public $reservedpicklists;
	public $reservedallocations;
	public $freestock;

	public function __construct($idwarehouse,$stock) {
		$this->idwarehouse = $idwarehouse;
		$this->stock = $stock;
		$this->freestock = $stock;
		$this->reserved = 0;
		$this->reservedallocations = 0;
		$this->reservedbackorders = 0;
		$this->reservedpicklists = 0;
	}
}