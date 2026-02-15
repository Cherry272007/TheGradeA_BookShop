<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index(){
        $categories = Category::latest()->get();

        return response()->json([
            'message' => 'All categories retrieved successfully',
            'data' => $categories
        ], 200);

    }
    public function store(Request $request){
        $validate = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive'
        ]);
        if($validate->fails()){
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validate->errors()
            ], 422);
        }
        $category = Category::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status ?? 'active'
        ]);
        return response()->json([
            'message' => 'Category created successfully',
            'data' => $category
        ], 201);
    }
    // Get single category
    public function show($id){
        $category = Category::findOrFail($id);
        if(!$category){
            return response()->json([
                'message' => 'Category not found'
            ], 404);
        }
        return response()->json([
            'message' => 'Category retrieved successfully',
            'data' => $category
        ], 200);
    }

    // Update category
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        if (!$category) {
            return response()->json([
                'message' => 'Category not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255|unique:categories,name,' . $id,
            'description' => 'nullable|string',
            'status' => 'in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $category->update($request->all());

        return response()->json([
            'message' => 'Category updated successfully',
            'data' => $category
        ], 200);
    }
    // Soft delete category
    public function destroy($id){
        $category = Category::findOrFail($id);
        if(!$category){
            return response()->json([
                'message' => 'Category not found'
            ], 404);
        }
        $category->delete();
        return response()->json([
            'message' => 'Category Moved to Trash'
        ], 200);
    }
    // Restore soft-deleted category
    public function restore($id){
        $category = Category::withTrashed()->findOrFail($id);
        $category->restore();   
        if(!$category){
            return response()->json([
                'message' => 'Category not found'
            ], 404);
        }
        return response()->json([
            'message' => 'Category restored successfully',
            'data' => $category
        ], 200);
    }

    // Permanently delete category
    public function permanentDelete($id){
        $category = Category::withTrashed()->findOrFail($id);
        if(!$category){
            return response()->json([
                'message' => 'Category not found'
            ], 404);
        }
        $category->forceDelete();
        return response()->json([
            'message' => 'Category permanently deleted successfully'
        ], 200);
    }

}

