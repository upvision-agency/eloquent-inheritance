<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnimalsTable extends Migration {
	
	public function up() {
		Schema::create('animals', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->bigInteger('base_id')->unsigned()->index();
			$table->bigInteger('owner_id')->unsigned();
			$table->string('name');
			$table->string('species');
			$table->string('secret');
			$table->integer('animal_number');
			$table->dateTime('born_at');
			$table->boolean('male');
		});
	}

	public function down() {
		Schema::drop('animals');
	}

}
