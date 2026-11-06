<?php

namespace App\Http\Controllers;

use App\Mail\ContactReceived;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $message = ContactMessage::create($data);

        // The message is already saved. If the email can't be sent, don't fail the visitor's request.
        if ($to = config('matcha.notify_email')) {
            try {
                Mail::to($to)->send(new ContactReceived($message));
            } catch (Throwable $e) {
                report($e);
            }
        }

        return response()->json(['message' => 'Sent'], 201);
    }
}
