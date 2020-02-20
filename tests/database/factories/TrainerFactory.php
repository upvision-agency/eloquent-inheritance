<?php

use Faker\Generator as Faker;
use Tests\Stubs\Trainer;

$factory->define(Trainer::class, function (Faker $faker) {
	return [
		'name' => $faker->name,
		'trainer_number' => $faker->numberBetween(),
		'age' => $faker->numberBetween(20, 80),
	];
});
