<?php
namespace App\Http\Controllers;

use App\Models\{Book, BookCategory, BookCopy};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{

    public function index(Request $request)
    {
        $query = Book::with('category')->where('is_active', true);

        if ($request->filled('search'))   $query->search($request->search);
        if ($request->filled('category')) $query->where('category_id', $request->category);
        if ($request->filled('available'))$query->where('available_copies', '>', 0);

        $books      = $query->orderBy('title')->paginate(15);
        $categories = BookCategory::orderBy('name')->get();

        return view('books.index', compact('books', 'categories'));
    }

    public function create()
    {
        $categories = BookCategory::orderBy('name')->get();
        return view('books.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'author'         => 'required|string|max:255',
            'category_id'    => 'required|exists:book_categories,id',
            'isbn'           => 'nullable|string|max:20|unique:books,isbn',
            'publisher'      => 'nullable|string|max:255',
            'published_year' => 'nullable|integer|min:1000|max:' . now()->year,
            'description'    => 'nullable|string',
            'language'       => 'nullable|string|max:50',
            'pages'          => 'nullable|integer|min:1',
            'location'       => 'nullable|string|max:100',
            'total_copies'   => 'required|integer|min:1',
            'cover_image'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }

        $data['available_copies'] = $data['total_copies'];
        $book = Book::create($data);

        // Auto-create physical copy records
        for ($i = 1; $i <= $data['total_copies']; $i++) {
            BookCopy::create([
                'book_id'     => $book->id,
                'copy_number' => 'COPY-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'status'      => 'available',
                'condition'   => 'good',
            ]);
        }

        return redirect()->route('books.show', $book)
            ->with('success', "Book '{$book->title}' added successfully!");
    }

    public function show(Book $book)
    {
        $book->load(['category', 'copies']);
        return view('books.show', compact('book'));
    }

    public function edit(Book $book)
    {
        $categories = BookCategory::orderBy('name')->get();
        return view('books.edit', compact('book', 'categories'));
    }

    public function update(Request $request, Book $book)
    {
        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'author'         => 'required|string|max:255',
            'category_id'    => 'required|exists:book_categories,id',
            'isbn'           => 'nullable|string|max:20|unique:books,isbn,' . $book->id,
            'publisher'      => 'nullable|string|max:255',
            'published_year' => 'nullable|integer|min:1000|max:' . now()->year,
            'description'    => 'nullable|string',
            'language'       => 'nullable|string|max:50',
            'pages'          => 'nullable|integer|min:1',
            'location'       => 'nullable|string|max:100',
            'total_copies'   => 'required|integer|min:1',
            'cover_image'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('cover_image')) {
            if ($book->cover_image) Storage::disk('public')->delete($book->cover_image);
            $data['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }

        $book->update($data);
        return redirect()->route('books.show', $book)
            ->with('success', "Book updated successfully!");
    }

    public function destroy(Book $book)
    {
        if ($book->borrowings()->where('status', 'borrowed')->exists()) {
            return back()->with('error', 'Cannot delete a book with active borrowings!');
        }
        $title = $book->title;
        $book->delete();
        return redirect()->route('books.index')
            ->with('success', "Book '{$title}' deleted.");
    }
}
