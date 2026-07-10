<?php

namespace App\Console\Commands;

use App\Models\PropertySubmission;
use App\Services\ClickUpSyncService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('clickup:sync')]
#[Description('Sync ClickUp task statuses for submissions awaiting review')]
class SyncClickUpSubmissions extends Command
{
    public function handle(ClickUpSyncService $sync): int
    {
        $submissions = PropertySubmission::query()
            ->with(['property', 'publishedProperty'])
            ->awaitingClickUp()
            ->get();

        $result = $sync->sync($submissions);

        $this->info("Checked {$result['checked']} submissions, updated {$result['updated']}.");

        return self::SUCCESS;
    }
}
