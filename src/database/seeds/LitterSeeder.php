<?php

use App\Animal;
use App\Litter;
use Illuminate\Database\Seeder;

class LitterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Litter::class, 40)->create()->each(function (Litter $litter) {
            $children = Animal::query()->where('mother_id', $litter->mother_id)->where('father_id', $litter->father_id)->get();

            foreach ($children as $child) {
                $child->litter_id = $litter->getAttributeValue('id');
                $child->saveOrFail();
            }
        });
    }
}
