<?php

use Faker\Generator as Faker;
use Tests\Stubs\Owner;

$factory->define(Owner::class, function (Faker $faker) {
	return [
		'name' => $faker->name,
	];
});
