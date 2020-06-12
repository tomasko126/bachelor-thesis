<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToAnimalsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('animals', function(Blueprint $table)
		{
			$table->foreign('breeder_id')->references('id')->on('people')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('creator_id')->references('id')->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('father_id')->references('id')->on('animals')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('litter_id')->references('id')->on('litters')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('mother_id')->references('id')->on('animals')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('owner_id')->references('id')->on('people')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('animals', function(Blueprint $table)
		{
			$table->dropForeign('animals_breeder_id_foreign');
			$table->dropForeign('animals_creator_id_foreign');
			$table->dropForeign('animals_father_id_foreign');
			$table->dropForeign('animals_litter_id_foreign');
			$table->dropForeign('animals_mother_id_foreign');
			$table->dropForeign('animals_owner_id_foreign');
		});
	}
}
