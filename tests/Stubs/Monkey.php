<?php

namespace Tests\Stubs;

use Cvsouth\EloquentInheritance\InheritableModel;
use Tests\Stubs\Dog;

class Monkey extends InheritableModel {
	public $table = 'monkeys';
	protected $with = ['friend'];


	public function friend() {
		return $this->morphTo();
	}
}
