<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportPropertiesRequest;
use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use App\Services\PropertyCsvImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PropertyController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $properties = Property::query()
            ->when($request->query('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'ilike', "%{$search}%")
                        ->orWhere('location', 'ilike', "%{$search}%")
                        ->orWhere('type', 'ilike', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        return PropertyResource::collection($properties);
    }

    public function show(Property $property): PropertyResource
    {
        return new PropertyResource($property);
    }

    public function store(StorePropertyRequest $request): JsonResponse
    {
        $property = Property::create($request->validated());

        return (new PropertyResource($property))->response()->setStatusCode(201);
    }

    public function update(UpdatePropertyRequest $request, Property $property): PropertyResource
    {
        $property->update($request->validated());

        return new PropertyResource($property);
    }

    public function destroy(Property $property): JsonResponse
    {
        $property->delete();

        return response()->json(['message' => 'Property deleted.']);
    }

    public function import(ImportPropertiesRequest $request, PropertyCsvImportService $importer): JsonResponse
    {
        $result = $importer->import($request->file('file'));

        return response()->json($result);
    }
}
