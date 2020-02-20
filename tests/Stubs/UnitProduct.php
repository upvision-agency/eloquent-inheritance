<?php

namespace Tests\Stubs;

use Tests\Stubs\Product;

class UnitProduct extends Product {
	public $table = 'unit_products';

	public function getColorAttribute($value) {
		return strtoupper($value);
	}

	public function getNameAttribute() {
		return 'unitProduct';
	}

	public function getSerialAttribute() {
		return 10;
	}
}
