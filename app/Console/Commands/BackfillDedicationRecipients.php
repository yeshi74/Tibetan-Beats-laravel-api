<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Dedication;
use App\Models\User;

class BackfillDedicationRecipients extends Command
{
    protected $signature = 'dedications:backfill-recipients';
    protected $description = 'Fill to_user_id where missing, based on to_name';

    public function handle()
    {
        $count = 0;

        Dedication::whereNull('to_user_id')
            ->chunkById(100, function ($dedications) use (&$count) {
                foreach ($dedications as $d) {
                    if (!$d->to_name) continue;

                    $user = User::whereRaw('LOWER(name) = ?', [strtolower($d->to_name)])->first();

                    if ($user) {
                        $d->to_user_id = $user->id;
                        $d->save();
                        $count++;
                    }
                }
            });

        $this->info("✅ Backfilled $count dedications with to_user_id.");
    }
}
