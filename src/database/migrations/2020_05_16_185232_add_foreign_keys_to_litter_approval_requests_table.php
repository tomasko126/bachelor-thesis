<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToLitterApprovalRequestsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('litter_approval_requests', function(Blueprint $table)
		{
			$table->foreign('creator_id')->references('id')->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('litter_id')->references('id')->on('litters')->onUpdate('NO ACTION')->onDelete('NO ACTION');
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
		Schema::table('litter_approval_requests', function(Blueprint $table)
		{
			$table->dropForeign('litter_approval_requests_creator_id_foreign');
			$table->dropForeign('litter_approval_requests_litter_id_foreign');
			$table->dropForeign('litter_approval_requests_registrator_id_foreign');
		});
	}
}
