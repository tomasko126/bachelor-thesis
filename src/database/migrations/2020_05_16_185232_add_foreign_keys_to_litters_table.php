<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToLittersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('litters', function(Blueprint $table)
		{
			$table->foreign('creator_id')->references('id')->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('father_id')->references('id')->on('animals')->onUpdate('NO ACTION')->onDelete('NO ACTION');
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
		Schema::table('litters', function(Blueprint $table)
		{
			$table->dropForeign('litters_creator_id_foreign');
			$table->dropForeign('litters_father_id_foreign');
			$table->dropForeign('litters_mother_id_foreign');
			$table->dropForeign('litters_owner_id_foreign');
		});
	}
}
