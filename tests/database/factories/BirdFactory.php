<?php

use Faker\Generator as Faker;
use Tests\Stubs\Bird;
use Tests\Stubs\Owner;
use Tests\Stubs\Trainer;

$factory->define(Bird::class, function (Faker $faker) {
	return [
		'owner_id' => factory(Owner::class)->create()->id,
		'trainer_id' => factory(Trainer::class)->create()->id,
		'name' => $faker->name,
		'species' => implode($faker->words(4), ' '),
		'wingspan' => $faker->numberBetween(1, 100),
		'flying' => $faker->boolean(70),
		'male' => $faker->boolean(30),
		'born_at' => $faker->dateTimeThisDecade(),
		'secret' => $faker->word,
		'acquired_at' => $faker->dateTimeThisDecade(),
		'price' => $faker->numberBetween(),
		'bird_number' => $faker->numberBetween(),
		'animal_number' => $faker->numberBetween(),
	];
});
