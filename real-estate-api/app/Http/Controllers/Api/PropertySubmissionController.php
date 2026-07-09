<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePropertySubmissionRequest;
use App\Http\Resources\PropertySubmissionResource;
use App\Services\ClickUpService;
use App\Services\N8nWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PropertySubmissionController extends Controller
{
    public function __construct(
        protected N8nWebhookService $n8n,
        protected ClickUpService $clickUp,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $submissions = $request->user()
            ->submissions()
            ->with('property')
            ->latest()
            ->get();

        return PropertySubmissionResource::collection($submissions);
    }

    public function store(StorePropertySubmissionRequest $request): JsonResponse
    {
        $submission = $request->user()->submissions()->create($request->validated());
        $submission->load(['property', 'user']);

        if ($submission->status !== 'draft') {
            $this->n8n->send($submission);
            $this->clickUp->createTask($submission);
        }

        return (new PropertySubmissionResource($submission))->response()->setStatusCode(201);
    }
}
