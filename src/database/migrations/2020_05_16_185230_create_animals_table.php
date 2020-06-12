<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnimalsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('animals', function(Blueprint $table)
		{
			$table->bigIncrements('id');
			$table->unsignedBigInteger('creator_id')->index('animals_creator_id_foreign');
			$table->unsignedBigInteger('breeder_id')->nullable()->index('animals_breeder_id_foreign');
			$table->unsignedBigInteger('owner_id')->nullable()->index('animals_owner_id_foreign');
			$table->unsignedBigInteger('mother_id')->nullable()->index('animals_mother_id_foreign');
			$table->unsignedBigInteger('father_id')->nullable()->index('animals_father_id_foreign');
			$table->unsignedBigInteger('litter_id')->nullable()->index('animals_litter_id_foreign');
			$table->string('name');
			$table->string('nickname')->nullable();
			$table->enum('sex', ['Male','Female']);
			$table->enum('eyes_color', ['Black','Dark Ruby','Ruby','Red','Pink','Odd eyed'])->nullable();
			$table->enum('ear_type', ['Standart','Standart Dg','Dumbo'])->nullable();
			$table->string('death_reason')->nullable();
			$table->timestamps();
			$table->softDeletes();
			$table->string('death_date')->nullable();
			$table->string('birthdate')->nullable();
			$table->string('fur_type');
			$table->string('fur_color');
			$table->string('markings');
			$table->string('owner_name')->nullable();
			$table->string('owner_contact')->nullable();
			$table->string('owner_member_card_number')->nullable();
			$table->boolean('breeding_available')->nullable();
			$table->string('breeding_limitation')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('animals');
	}
}
