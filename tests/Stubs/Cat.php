<?php

namespace Tests\Stubs;

use Cvsouth\EloquentInheritance\InheritableModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\Stubs\Dog;

class Cat extends InheritableModel {
	use SoftDeletes;

	public $table = 'cats';

	public function friend() {
		return $this->morphTo();
	}
}
