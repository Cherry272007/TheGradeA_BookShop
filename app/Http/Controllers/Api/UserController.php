<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
// use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // Get logged-in user profile
    public function profile(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()
        ], 200);
    }

    // Update user profile
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'          => 'sometimes|string|max:255',
            'phone_number'  => 'sometimes|string|max:20',
            'address'       => 'sometimes|string|max:255',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('profile_image')) {
            // Delete old file
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // Store new file
            $path = $request->file('profile_image')->store('profiles', 'public');
            $user->profile_image = $path;
        }

        // Use fill() then save() to be explicit
        $user->fill($request->only('name', 'phone_number', 'address'));
        $user->save();

        // Add the full URL to the response so the frontend can display it immediately
        $user->profile_image_url = $user->profile_image ? asset('storage/' . $user->profile_image) : null;

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully!',
            'data' => $user
        ]);
    }

    // Admin Only: List all users (For User Management)
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data' => User::all()
        ]);
    }



    public function changePassword(Request $request)
{
    // 1. Validation
    $request->validate([
        'current_password' => 'required',
        'new_password' => ['required', 'confirmed', Password::min(8)], 
    ]);

    $user = $request->user();

    // 2. Security Check: Does the current password match?
    if (!Hash::check($request->current_password, $user->password)) {
        return response()->json([
            'status' => 'error',
            'message' => 'The current password you entered is incorrect.'
        ], 401);
    }

    // 3. Update & Save
    $user->password = Hash::make($request->new_password);
    $user->save();

    return response()->json([
        'status' => 'success',
        'message' => 'Password changed successfully!'
    ], 200);
}


/**
     * Soft Delete: Deactivates the user without removing the row.
     */

public function selfDelete(Request $request)
{
    $user = $request->user();

    // Optional: Revoke all tokens so they are logged out everywhere
    $user->tokens()->delete();

    // Soft delete the user
    $user->delete();

    return response()->json([
        'status' => 'success',
        'message' => 'Your account has been deactivated successfully.'
    ], 200);
}
    // Soft Delete (Moves to trash)
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User soft deleted successfully']);
    }

    // Restore (Bring back from trash)
    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return response()->json(['message' => 'User restored successfully']);
    }

    // Permanent Delete (Wipe from database)
    public function permanentDelete($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        
        // Also delete their profile image from storage if it exists
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $user->forceDelete();

        return response()->json(['message' => 'User permanently deleted']);
    }
}