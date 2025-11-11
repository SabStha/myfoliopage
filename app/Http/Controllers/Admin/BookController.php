<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index() { $books = Book::latest('finished_at')->paginate(20); return view('admin.books.index', compact('books')); }

    /**
     * Show the form for creating a new resource.
     */
    public function create() { return view('admin.books.create'); }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) { $data = $request->validate(['title'=>'required|string|max:255','author'=>'nullable|string|max:255','progress'=>'nullable|string|max:255','finished_at'=>'nullable|date','notes'=>'nullable|string']); Book::create($data); return redirect()->route('admin.books.index')->with('status','Book added'); }

    /**
     * Display the specified resource.
     */
    public function show(Book $book) { return redirect()->route('admin.books.edit', $book); }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book) { return view('admin.books.edit', compact('book')); }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book) { $data = $request->validate(['title'=>'required|string|max:255','author'=>'nullable|string|max:255','progress'=>'nullable|string|max:255','finished_at'=>'nullable|date','notes'=>'nullable|string']); $book->update($data); return redirect()->route('admin.books.index')->with('status','Book updated'); }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book) { $book->delete(); return redirect()->route('admin.books.index')->with('status','Book deleted'); }
}
