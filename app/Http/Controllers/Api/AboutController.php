<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\About;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AboutController extends Controller
{
    // ✅ Public - Get active about info
    public function index()
    {
        $about = About::where('is_active', true)->first();

        if (!$about) {
            return response()->json([
                'success' => false,
                'message' => 'About information not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $about,
        ], 200);
    }

    // ✅ Admin only - Get all about records
    public function adminIndex(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $abouts = About::orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data'    => $abouts,
        ], 200);
    }

    // ✅ Admin only - Get single about record
    public function show(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $about = About::find($id);

        if (!$about) {
            return response()->json([
                'success' => false,
                'message' => 'About record not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $about,
        ], 200);
    }

    // ✅ Admin only - Create about info
    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title'            => 'required|string|max:255',
            'description'      => 'required|string',
            'vision'           => 'nullable|string',
            'mission'          => 'nullable|string',
            'address'          => 'nullable|string|max:500',
            'phone'            => 'nullable|string|max:20',
            'email'            => 'nullable|email|max:255',
            'established_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'logo'             => 'nullable|string',
            'banner_image'     => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Deactivate all existing records
        About::query()->update(['is_active' => false]);

        $about = About::create(array_merge(
            $validator->validated(),
            ['is_active' => true]
        ));

        return response()->json([
            'success' => true,
            'message' => 'About created successfully',
            'data'    => $about,
        ], 201);
    }

    // ✅ Admin only - Update about info
    public function update(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $about = About::find($id);

        if (!$about) {
            return response()->json([
                'success' => false,
                'message' => 'About record not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title'            => 'sometimes|string|max:255',
            'description'      => 'sometimes|string',
            'vision'           => 'nullable|string',
            'mission'          => 'nullable|string',
            'address'          => 'nullable|string|max:500',
            'phone'            => 'nullable|string|max:20',
            'email'            => 'nullable|email|max:255',
            'established_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'logo'             => 'nullable|string',
            'banner_image'     => 'nullable|string',
            'is_active'        => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $about->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'About updated successfully',
            'data'    => $about,
        ], 200);
    }

    // ✅ Admin only - Delete about info
    public function destroy(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $about = About::find($id);

        if (!$about) {
            return response()->json([
                'success' => false,
                'message' => 'About record not found',
            ], 404);
        }

        $about->delete();

        return response()->json([
            'success' => true,
            'message' => 'About deleted successfully',
        ], 200);
    }
}