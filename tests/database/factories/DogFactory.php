<?php

use Faker\Generator as Faker;
use Tests\Stubs\Dog;

$factory->define(Dog::class, function (Faker $faker) {
	return [
		'name' => $faker->name,
	];
});
