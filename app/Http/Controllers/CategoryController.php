<?php
namespace App\Http\Controllers;

use App\Models\BookCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    public function index()
    {
        $categories = BookCategory::withCount('books')->orderBy('name')->get();
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        // create & index share the same view
        return redirect()->route('categories.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:book_categories,name',
            'description' => 'nullable|string',
        ]);

        BookCategory::create($data);
        return redirect()->route('categories.index')
            ->with('success', "Category '{$data['name']}' added!");
    }

    public function edit(BookCategory $category)
    {
        $categories = BookCategory::withCount('books')->orderBy('name')->get();
        return view('categories.index', compact('categories', 'category'));
    }

    public function update(Request $request, BookCategory $category)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:book_categories,name,' . $category->id,
            'description' => 'nullable|string',
        ]);

        $category->update($data);
        return redirect()->route('categories.index')
            ->with('success', "Category updated successfully!");
    }

    public function destroy(BookCategory $category)
    {
        if ($category->books()->exists()) {
            return back()->with('error', 'Cannot delete a category that has books assigned to it!');
        }

        $name = $category->name;
        $category->delete();
        return redirect()->route('categories.index')
            ->with('success', "Category '{$name}' deleted.");
    }

    public function show(BookCategory $category)
    {
        return redirect()->route('categories.index');
    }
}
