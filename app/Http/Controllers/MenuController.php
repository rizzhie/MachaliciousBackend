<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    /** Public menu: only items marked available. */
    public function index(): JsonResponse
    {
        return response()->json(
            MenuItem::where('is_available', true)->orderBy('sort_order')->orderBy('id')->get()
        );
    }
}
