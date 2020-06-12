<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLittersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('litters', function(Blueprint $table)
		{
			$table->bigIncrements('id');
			$table->unsignedBigInteger('father_id')->nullable()->index('litters_father_id_foreign');
			$table->unsignedBigInteger('mother_id')->nullable()->index('litters_mother_id_foreign');
			$table->unsignedBigInteger('creator_id')->index('litters_creator_id_foreign');
			$table->unsignedBigInteger('owner_id')->index('litters_owner_id_foreign');
			$table->string('line')->nullable();
			$table->string('genetic_information')->nullable();
			$table->integer('for_breeding')->nullable();
			$table->integer('for_petting')->nullable();
			$table->timestamps();
			$table->softDeletes();
			$table->string('birthdate')->nullable();
			$table->string('label');
			$table->integer('babies_born');
			$table->integer('babies_reared');
			$table->integer('reared_boys');
			$table->integer('reared_girls');
			$table->string('breeder_name')->nullable();
			$table->string('breeder_contact')->nullable();
			$table->enum('type', ['VP','PP','NV']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('litters');
	}
}
