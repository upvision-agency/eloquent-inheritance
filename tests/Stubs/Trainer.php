<?php

namespace Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use Tests\Stubs\Bird;

class Trainer extends Model {
	protected $casts = ['age' => 'int'];

	public function birds() {
		return $this->hasMany(Bird::class);
	}

	public function getTrainerNumberAttribute($value) {
		return number_format($value, 0, '', ' ');
	}

}
