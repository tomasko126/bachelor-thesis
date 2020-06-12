<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLitterApprovalRequestsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('litter_approval_requests', function(Blueprint $table)
		{
			$table->bigIncrements('id');
			$table->unsignedBigInteger('litter_id')->index('litter_approval_requests_litter_id_foreign');
			$table->unsignedBigInteger('creator_id')->index('litter_approval_requests_creator_id_foreign');
			$table->unsignedBigInteger('registrator_id')->nullable()->index('litter_approval_requests_registrator_id_foreign');
			$table->string('registration_number')->nullable();
			$table->timestamps();
			$table->enum('state', ['Sent','Rejected','Approved']);
			$table->softDeletes();
			$table->string('registration_date', 10)->nullable();
			$table->string('creator_note', 500)->nullable();
			$table->string('registrator_note', 500)->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('litter_approval_requests');
	}
}
