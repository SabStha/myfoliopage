<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    public function index() { 
        $books = Book::where('user_id', Auth::id())->latest('finished_at')->paginate(20); 
        return view('admin.books.index', compact('books')); 
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() { return view('admin.books.create'); }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) { 
        $data = $request->validate([
            'title'=>'required|string|max:255',
            'author'=>'nullable|string|max:255',
            'progress'=>'nullable|string|max:255',
            'finished_at'=>'nullable|date',
            'notes'=>'nullable|string'
        ]); 
        $data['user_id'] = Auth::id();
        Book::create($data); 
        return redirect()->route('admin.books.index')->with('status','Book added'); 
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book) { 
        if ($book->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        return redirect()->route('admin.books.edit', $book); 
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book) { 
        if ($book->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        return view('admin.books.edit', compact('book')); 
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book) { 
        if ($book->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        $data = $request->validate([
            'title'=>'required|string|max:255',
            'author'=>'nullable|string|max:255',
            'progress'=>'nullable|string|max:255',
            'finished_at'=>'nullable|date',
            'notes'=>'nullable|string'
        ]); 
        $book->update($data); 
        return redirect()->route('admin.books.index')->with('status','Book updated'); 
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book) { 
        if ($book->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        $book->delete(); 
        return redirect()->route('admin.books.index')->with('status','Book deleted'); 
    }
}
