<?php

namespace App\Services;

use App\Enums\SubmissionStatus;
use App\Models\PropertySubmission;
use Illuminate\Support\Collection;

class ClickUpSyncService
{
    public function __construct(
        protected ClickUpService $clickUp,
        protected SubmissionPublisher $publisher,
    ) {}

    /**
     * Pull the latest ClickUp status for each submission and advance the pipeline.
     *
     * ClickUp status types map to the pipeline as follows: `open`/`custom`
     * (To Do, In Progress, ...) keep the submission in review and record the
     * human-readable status name; `done`/`closed` move it to Ready, or publish
     * immediately when the owner marked it publish-ready.
     *
     * @param  Collection<int, PropertySubmission>  $submissions
     * @return array{updated: int, checked: int}
     */
    public function sync(Collection $submissions): array
    {
        $updated = 0;

        foreach ($submissions as $submission) {
            $status = $this->clickUp->getTaskStatus($submission->clickup_task_id);

            if ($status === null) {
                continue;
            }

            $changes = [];

            if ($submission->clickup_status !== $status['name']) {
                $changes['clickup_status'] = $status['name'];
            }

            if (in_array($status['type'], ['done', 'closed'], true)) {
                if ($submission->publish_ready) {
                    if ($changes !== []) {
                        $submission->update($changes);
                    }
                    $this->publisher->publish($submission);
                } else {
                    $submission->update([...$changes, 'status' => SubmissionStatus::Ready->value]);
                }

                $updated++;

                continue;
            }

            if ($submission->status !== SubmissionStatus::ClickUpReview->value) {
                $changes['status'] = SubmissionStatus::ClickUpReview->value;
            }

            if ($changes !== []) {
                $submission->update($changes);
                $updated++;
            }
        }

        return ['updated' => $updated, 'checked' => $submissions->count()];
    }
}
