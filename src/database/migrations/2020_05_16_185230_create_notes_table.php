<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('notes', function(Blueprint $table)
		{
			$table->bigIncrements('id');
			$table->unsignedBigInteger('creator_id')->index('notes_creator_id_foreign');
			$table->unsignedBigInteger('animal_id')->nullable()->index('notes_animal_id_foreign');
			$table->unsignedBigInteger('litter_id')->nullable()->index('notes_litter_id_foreign');
			$table->text('note');
			$table->boolean('public');
			$table->timestamps();
			$table->enum('category', ['general','warning','alert']);
			$table->softDeletes();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('notes');
	}
}
