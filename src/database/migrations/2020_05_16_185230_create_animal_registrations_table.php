<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnimalRegistrationsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('animal_registrations', function(Blueprint $table)
		{
			$table->bigIncrements('id');
			$table->unsignedBigInteger('animal_id')->index('animal_registrations_animal_id_foreign');
			$table->unsignedBigInteger('registrator_id')->index('animal_registrations_registrator_id_foreign');
			$table->enum('club', ['CZKP','SOP','Other']);
			$table->boolean('breeding_available')->nullable();
			$table->timestamps();
			$table->enum('type', ['CZ','CZN'])->nullable();
			$table->string('registration_number');
			$table->softDeletes();
			$table->string('breeding_limitation')->nullable();
			$table->string('year', 4)->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('animal_registrations');
	}
}
