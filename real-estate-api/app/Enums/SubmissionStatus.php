<?php

namespace App\Enums;

enum SubmissionStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case AiProcessing = 'ai_processing';
    case ClickUpReview = 'clickup_review';
    case Ready = 'ready';
    case Published = 'published';
    case Rejected = 'rejected';

    /**
     * Statuses the owner may still edit or delete.
     *
     * @return list<string>
     */
    public static function editable(): array
    {
        return [self::Draft->value, self::Rejected->value];
    }

    /**
     * Statuses inside the review pipeline (webhooks may act on these).
     *
     * @return list<string>
     */
    public static function pipeline(): array
    {
        return [
            self::Pending->value,
            self::AiProcessing->value,
            self::ClickUpReview->value,
            self::Ready->value,
        ];
    }

    /**
     * Statuses that are still waiting on a ClickUp task outcome.
     *
     * @return list<string>
     */
    public static function awaitingClickUp(): array
    {
        return [self::AiProcessing->value, self::ClickUpReview->value];
    }
}
