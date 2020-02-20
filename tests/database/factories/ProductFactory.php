<?php

use Faker\Generator as Faker;
use Tests\Stubs\UnitProduct;

$factory->define(UnitProduct::class, function (Faker $faker) {
	return [
		'price' => $faker->numberBetween(0, 100),
		'description' => implode($faker->words(5), ' '),
		'units' => $faker->numberBetween(0, 100),
		'color' => $faker->word,
	];
});
