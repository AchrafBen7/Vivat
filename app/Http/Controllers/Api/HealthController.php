<?php

namespace App\Http\Controllers\Api;

use App\Services\PipelineHealthService;
use Illuminate\Http\JsonResponse;

class HealthController
{
    public function __invoke(PipelineHealthService $healthService): JsonResponse
    {
        $snapshot = $healthService->snapshot();

        return response()->json($snapshot, $snapshot['status'] === 'healthy' ? 200 : 503);
    }
}
