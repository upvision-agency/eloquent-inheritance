<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration {
	
	public function up() {
		Schema::create('products', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->bigInteger('base_id')->unsigned()->index();
			$table->integer('price');
			$table->string('description');
		});
	}

	public function down() {
		Schema::drop('products');
	}

}
