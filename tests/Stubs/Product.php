<?php

namespace Tests\Stubs;

use Cvsouth\EloquentInheritance\InheritableModel;

class Product extends InheritableModel {
	public $table = 'products';

	public function getDescriptionAttribute($value) {
		return '#'. $value;
	}

	public function getTypeAttribute() {
		return 'product';
	}

	public function getSerialAttribute() {
		return 5;
	}

	public function getIsExpensiveAttribute() {
		return $this->price > 50;
	}

	public function getProductIdAttribute() {
		return $this->id_as(self::class);
	}
}
