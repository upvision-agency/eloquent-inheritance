<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCatsTable extends Migration {
	
	public function up() {
		Schema::create('cats', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->bigInteger('base_id')->unsigned()->index();
			$table->string('name');
			$table->softDeletes();
		});
	}

	public function down() {
		Schema::drop('cats');
	}

}
