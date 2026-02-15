<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    //fetch all books
    public function index(){
        $books = Book::with('category')->latest()->get();
        return response()->json([
            'message' => 'All Books retrieved successfully',
            'data' =>$books
        ],200);
    }

    //create book
    public function store(Request $request) {
        $validated = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'author'      => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'status'      => 'required|in:active,out of stock,inactive',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048' 
        ]);

        if ($validated->fails()) {
            return response()->json([
                'message' => 'Validation Failed',
                'errors'  => $validated->errors(),
            ], 422);
        }
        // 2. Duplicate Check
        $exists = Book::where('title', $request->title)
                    ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This book already exists in the catalog.'
            ], 409);
        }
        $imagePath = null;
        if ($request->hasFile('cover_image')) {
            $imagePath = $request->file('cover_image')->store('books', 'public');
        }

        $book = Book::create([
            'title'       => $request->title,
            'author'      => $request->author,
            'price'       => $request->price,
            'stock'       => $request->stock,
            'status'      => $request->status,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'cover_image' => $imagePath 
        ]);
        return response()->json([
            'message' => 'Book created successfully',
            'data'    => $book->load('category') 
        ], 201);
    }
    
    //Show single book (edit)
    public function show($id){
        $book=Book::findOrFail($id);
        if(!$book){
            return response()->json([
                'message'=>'Book not found'
            ],404);
        }
        return response()->json([
            'message'=>'Book retrieved successfully',
            'data'=> $book
        ],200);
    }

    //Update Book
    public function update(Request $request, $id)
    {
    // 1. Find the book first
    $book = Book::find($id); 

    if (!$book) {
        return response()->json(['message' => 'Book not found'], 404);
    }

    // 2. Validate immediately
    $validated = Validator::make($request->all(), [
        'title'       => 'sometimes|required|string|max:255',
        'author'      => 'sometimes|required|string|max:255',
        'price'       => 'sometimes|required|numeric|min:0',
        'stock'       => 'sometimes|required|integer|min:0',
        'status'      => 'sometimes|required|in:active,out of stock,inactive',
        'description' => 'nullable|string',
        'category_id' => 'nullable|exists:categories,id',
        'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048' 
    ]);

    if ($validated->fails()) {
        return response()->json([
            'message' => 'Validation Failed',
            'errors'  => $validated->errors()
        ], 422);
    }


    $data = $request->only(['title', 'author', 'price', 'stock','status', 'description', 'category_id']);


    if ($request->hasFile('cover_image')) {
        // Delete old one
        if ($book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
        }
        
        $data['cover_image'] = $request->file('cover_image')->store('books', 'public');
    }

    // 5. Perform the update
    $book->update($data);

    return response()->json([
        'message' => 'Book updated successfully',
        'data'    => $book->load('category') // Reloads relationship and triggers your URL Accessor
    ], 200);
    }

    // Restock book logic
    public function restock(Request $request, $id) {
        $request->validate(['quantity' => 'required|integer|min:1']);

        $book = Book::findOrFail($id);
        $book->increment('stock', $request->quantity);

        // Auto-activate if it was out of stock
        if ($book->status === 'out of stock' && $book->stock > 0) {
            $book->update(['status' => 'active']);
        }

        return response()->json([
            'message' => "Stock updated! New total: {$book->stock}",
            'data' => $book
        ], 200);
    }
    //Soft delete book
    public function destroy($id){
        $book = Book::findOrFail($id);
        if(!$book){
            return response()->json([
                'message' => 'Book not found'
            ], 404);
        }
        $book->delete();
        return response()->json([
            'message' => 'Book Moved to Trash successfully'
        ], 200);
    }

    // Restore book from trash
    public function restore($id){
        $book = Book::withTrashed()->findOrFail($id);
        $book->restore();
        if(!$book){
            return response()->json([
                'message' => 'Book not found'
            ], 404);
        }
        return response()->json([
            'message' => 'Book Restored successfully'
        ], 200);
    }

    // Permanently delete book
    public function permanentDelete($id){
        $book = Book::withTrashed()->findOrFail($id);
        if(!$book){
            return response()->json([
                'message' => 'Book not found'
            ], 404);
        }
        $book->forceDelete();
        return response()->json([
            'message' => 'Book permanently deleted successfully'
        ], 200);
    }


    public function SearchFilter(Request $request)
{
    $query = Book::query();

    // ស្វែងរកតាមឈ្មោះសៀវភៅ
    if ($request->has('search')) {
        $query->where('title', 'like', '%' . $request->search . '%');
    }

    // ចម្រាញ់តាមប្រភេទ (Category)
    if ($request->has('category_id')) {
        $query->where('category_id', $request->category_id);
    }

    // ចម្រាញ់តាមតម្លៃ (Price Range)
    if ($request->has('min_price')) {
        $query->where('price', '>=', $request->min_price);
    }

    return $query->paginate(10); // ប្រើ Paginate ដើម្បីកុំឱ្យទិន្នន័យមកច្រើនពេកនាំឱ្យយឺត
}
}
