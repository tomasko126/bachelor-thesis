<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToAnimalRegistrationsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('animal_registrations', function(Blueprint $table)
		{
			$table->foreign('animal_id')->references('id')->on('animals')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('registrator_id')->references('id')->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('animal_registrations', function(Blueprint $table)
		{
			$table->dropForeign('animal_registrations_animal_id_foreign');
			$table->dropForeign('animal_registrations_registrator_id_foreign');
		});
	}
}
