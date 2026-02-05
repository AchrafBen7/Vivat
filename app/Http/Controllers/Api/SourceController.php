<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSourceRequest;
use App\Http\Requests\UpdateSourceRequest;
use App\Http\Resources\SourceResource;
use App\Models\Source;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SourceController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $sources = Source::query()->orderBy('name')->get();

        return SourceResource::collection($sources);
    }

    public function store(StoreSourceRequest $request): JsonResponse|SourceResource
    {
        $this->authorize('create', Source::class);
        $source = Source::create($request->validated());

        return (new SourceResource($source))->response()->setStatusCode(201);
    }

    public function show(Source $source): SourceResource
    {
        return new SourceResource($source);
    }

    public function update(UpdateSourceRequest $request, Source $source): SourceResource
    {
        $this->authorize('update', $source);
        $source->update($request->validated());

        return new SourceResource($source->fresh());
    }

    public function destroy(Source $source): JsonResponse
    {
        $this->authorize('delete', $source);
        $source->delete();

        return response()->json(null, 204);
    }
}
