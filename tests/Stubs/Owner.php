<?php

namespace Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use Tests\Stubs\Animal;

class Owner extends Model {
	
	public function animals() {
		return $this->hasMany(Animal::class);
	}

}
