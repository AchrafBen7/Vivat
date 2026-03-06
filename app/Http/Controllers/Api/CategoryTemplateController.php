<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CategoryTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryTemplateController extends Controller
{
    public function index(): JsonResponse
    {
        $templates = CategoryTemplate::with('category')->get();

        return response()->json(['data' => $templates]);
    }

    public function show(CategoryTemplate $categoryTemplate): JsonResponse
    {
        $categoryTemplate->load('category');

        return response()->json(['data' => $categoryTemplate]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|uuid|exists:categories,id',
            'tone' => 'nullable|string|max:100',
            'structure' => 'nullable|string|max:100',
            'min_word_count' => 'nullable|integer|min:100|max:5000',
            'max_word_count' => 'nullable|integer|min:100|max:10000',
            'style_notes' => 'nullable|string|max:2000',
            'seo_rules' => 'nullable|string|max:2000',
        ]);

        $template = CategoryTemplate::create($validated);

        return response()->json(['data' => $template->load('category')], 201);
    }

    public function update(Request $request, CategoryTemplate $categoryTemplate): JsonResponse
    {
        $validated = $request->validate([
            'tone' => 'nullable|string|max:100',
            'structure' => 'nullable|string|max:100',
            'min_word_count' => 'nullable|integer|min:100|max:5000',
            'max_word_count' => 'nullable|integer|min:100|max:10000',
            'style_notes' => 'nullable|string|max:2000',
            'seo_rules' => 'nullable|string|max:2000',
        ]);

        $categoryTemplate->update($validated);

        return response()->json(['data' => $categoryTemplate->fresh('category')]);
    }

    public function destroy(CategoryTemplate $categoryTemplate): JsonResponse
    {
        $categoryTemplate->delete();

        return response()->json(null, 204);
    }
}
