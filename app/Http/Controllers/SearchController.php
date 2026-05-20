<?php
namespace App\Http\Controllers;

use App\Models\{Book, Member};
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->get('q', '');
        if (strlen($q) < 2) return response()->json([]);

        $results = [];

        // Books
        Book::where('title', 'like', "%$q%")
            ->orWhere('author', 'like', "%$q%")
            ->orWhere('isbn', 'like', "%$q%")
            ->limit(5)->get()
            ->each(function ($book) use (&$results) {
                $results[] = [
                    'type'     => 'Book',
                    'title'    => $book->title,
                    'subtitle' => 'by ' . $book->author . ' — ' . ($book->available_copies > 0 ? $book->available_copies . ' available' : 'No copies available'),
                    'url'      => route('books.show', $book),
                ];
            });

        // Members
        Member::where('name', 'like', "%$q%")
            ->orWhere('membership_id', 'like', "%$q%")
            ->orWhere('email', 'like', "%$q%")
            ->limit(4)->get()
            ->each(function ($member) use (&$results) {
                $results[] = [
                    'type'     => 'Member',
                    'title'    => $member->name,
                    'subtitle' => $member->membership_id . ' — ' . ucfirst($member->member_type),
                    'url'      => route('members.show', $member),
                ];
            });

        return response()->json($results);
    }
}
