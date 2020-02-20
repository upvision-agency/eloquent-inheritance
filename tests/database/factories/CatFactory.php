<?php

use Faker\Generator as Faker;
use Tests\Stubs\Cat;

$factory->define(Cat::class, function (Faker $faker) {
	return [
		'name' => $faker->name,
	];
});
