<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainersTable extends Migration {
	
	public function up() {
		Schema::create('trainers', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('name');
			$table->integer('trainer_number');
			$table->integer('age');
			$table->timestamps();
		});
	}

	public function down() {
		Schema::drop('trainers');
	}

}
