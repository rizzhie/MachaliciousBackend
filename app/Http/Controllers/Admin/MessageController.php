<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class MessageController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(ContactMessage::latest()->limit(200)->get());
    }

    public function markRead(ContactMessage $contactMessage): JsonResponse
    {
        $contactMessage->update(['read_at' => $contactMessage->read_at ?? now()]);

        return response()->json($contactMessage);
    }

    public function destroy(ContactMessage $contactMessage): Response
    {
        $contactMessage->delete();

        return response()->noContent();
    }
}
