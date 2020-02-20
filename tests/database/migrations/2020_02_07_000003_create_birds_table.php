<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBirdsTable extends Migration {
	
	public function up() {
		Schema::create('birds', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->bigInteger('base_id')->unsigned()->index();
			$table->bigInteger('trainer_id')->unsigned();
			$table->boolean('flying')->default('false');
			$table->integer('wingspan');
			$table->integer('price');
			$table->integer('bird_number');
			$table->dateTime('acquired_at');
		});
	}

	public function down() {
		Schema::drop('birds');
	}

}
