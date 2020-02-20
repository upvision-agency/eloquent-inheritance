<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnitProductsTable extends Migration {
	
	public function up() {
		Schema::create('unit_products', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->bigInteger('base_id')->unsigned()->index();
			$table->integer('units');
			$table->string('color');
		});
	}

	public function down() {
		Schema::drop('unit_products');
	}

}
