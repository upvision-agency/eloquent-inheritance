<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDogsTable extends Migration {
	
	public function up() {
		Schema::create('dogs', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->bigInteger('base_id')->unsigned()->index();
			$table->string('name');
		});
	}

	public function down() {
		Schema::drop('dogs');
	}

}
