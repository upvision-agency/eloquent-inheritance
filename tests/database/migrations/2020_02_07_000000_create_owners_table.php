<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOwnersTable extends Migration {
	
	public function up() {
		Schema::create('owners', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('name');
			$table->timestamps();
		});
	}

	public function down() {
		Schema::drop('owners');
	}

}
