<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MenuItemController extends Controller
{
    /** Admin list includes unavailable items too. */
    public function index(): JsonResponse
    {
        return response()->json(MenuItem::orderBy('sort_order')->orderBy('id')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $item = MenuItem::create($this->validated($request));

        return response()->json($item, 201);
    }

    public function update(Request $request, MenuItem $menuItem): JsonResponse
    {
        $menuItem->update($this->validated($request));

        return response()->json($menuItem);
    }

    public function destroy(MenuItem $menuItem): Response
    {
        $menuItem->delete();

        return response()->noContent();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'price' => ['required', 'integer', 'min:0', 'max:100000'],
            // Either a path in your React /public folder ("/chaimatcha.jpg") or a full https:// URL.
            'image' => ['nullable', 'string', 'max:255', 'regex:/^(\/|https?:\/\/)/'],
            'is_available' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:10000'],
        ]);
    }
}
