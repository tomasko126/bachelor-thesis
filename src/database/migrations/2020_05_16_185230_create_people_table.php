<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePeopleTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('people', function(Blueprint $table)
		{
			$table->bigIncrements('id');
			$table->unsignedBigInteger('station_id')->nullable()->index('people_station_id_foreign');
			$table->unsignedBigInteger('creator_id')->index('people_creator_id_foreign');
			$table->unsignedBigInteger('user_id')->nullable()->index('people_user_id_foreign');
			$table->string('name');
			$table->string('email')->nullable();
			$table->string('telephone_number')->nullable();
			$table->timestamps();
			$table->softDeletes();
			$table->string('member_card_number')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('people');
	}
}
