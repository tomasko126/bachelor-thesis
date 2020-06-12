<?php

use App\LitterApprovalRequest;
use Illuminate\Database\Seeder;

class LitterApprovalRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(LitterApprovalRequest::class, 40)->create();
    }
}
