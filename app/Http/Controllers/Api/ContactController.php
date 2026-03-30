<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    // ✅ Public - Submit contact form
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'phone'   => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $contact = Contact::create(array_merge(
            $validator->validated(),
            [
                'status'  => 'pending',
                'user_id' => $request->user()?->id, // null if guest, filled if logged in
            ]
        ));


        return response()->json([
            'success' => true,
            'message' => 'Your message has been sent successfully!',
            'data'    => $contact,
        ], 201);
    }

    // ✅ Admin only - Get all messages (Includes RepliedBy user info)
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $query = Contact::query()->with('repliedBy'); // Eager load the admin info

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name',    'like', '%' . $request->search . '%')
                  ->orWhere('email',   'like', '%' . $request->search . '%')
                  ->orWhere('subject', 'like', '%' . $request->search . '%');
            });
        }

        $contacts = $query->orderBy('created_at', 'desc')
                          ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data'    => $contacts,
        ], 200);
    }

    // ✅ Admin only - View single message (Includes Reply Message)
    public function show(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $contact = Contact::with('repliedBy')->find($id);

        if (!$contact) {
            return response()->json(['success' => false, 'message' => 'Contact message not found'], 404);
        }

        if ($contact->status === 'pending') {
            $contact->update(['status' => 'read']);
        }

        return response()->json([
            'success' => true,
            'data'    => $contact,
        ], 200);
    }

    // ✅ Admin only - SUBMIT A REPLY
    public function reply(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json(['success' => false, 'message' => 'Contact message not found'], 404);
        }

        // Validate the incoming reply text
        $validator = Validator::make($request->all(), [
            'reply_message' => 'required|string|min:5|max:3000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a valid reply message.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $contact->update([
            'status'        => 'replied',
            'reply_message' => $request->reply_message,
            'replied_at'    => now(),
            'replied_by'    => $request->user()->UserID, // Uses the UserID from your auth user
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reply saved successfully',
            'data'    => $contact->load('repliedBy'),
        ], 200);
    }

    // ✅ Admin only - Delete message
    public function destroy(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json(['success' => false, 'message' => 'Contact message not found'], 404);
        }

        $contact->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contact deleted successfully',
        ], 200);
    }

    // ✅ Admin only - Statistics
    public function stats(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'total'   => Contact::count(),
                'pending' => Contact::where('status', 'pending')->count(),
                'read'    => Contact::where('status', 'read')->count(),
                'replied' => Contact::where('status', 'replied')->count(),
            ],
        ], 200);
    }

    public function userIndex(Request $request)
    {
        $user = $request->user();

        $contacts = Contact::where(function ($q) use ($user) {
                $q->where('email', $user->email);          // matches guest submissions too
                if ($user->id) {
                    $q->orWhere('user_id', $user->id);     // matches logged-in submissions
                }
            })
            ->orderBy('created_at', 'desc')
            ->get(['id', 'subject', 'message', 'status', 'reply_message', 'replied_at', 'created_at']);

        return response()->json([
            'success' => true,
            'data'    => $contacts,
        ], 200);
    }

}