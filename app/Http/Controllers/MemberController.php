<?php
namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        // Use v_member_summary for live borrow stats
        $query = DB::table('v_member_summary');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(fn($q) =>
                $q->where('name', 'like', "%$term%")
                  ->orWhere('email', 'like', "%$term%")
                  ->orWhere('membership_id', 'like', "%$term%")
            );
        }
        if ($request->filled('status'))            $query->where('status', $request->status);
        if ($request->filled('membership_status')) $query->where('membership_status', $request->membership_status);

        $members = $query->orderBy('name')->paginate(15);
        return view('members.index', compact('members'));
    }

    public function create()
    {
        return view('members.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                   => 'required|string|max:255',
            'email'                  => 'nullable|email|unique:members,email',
            'phone'                  => 'nullable|string|max:20',
            'address'                => 'nullable|string',
            'member_type'            => 'required|in:student,faculty,staff,public',
            'status'                 => 'required|in:active,inactive,suspended',
            'membership_start_date'  => 'required|date',
            'membership_expiry_date' => 'required|date|after:membership_start_date',
            'max_books'              => 'nullable|integer|min:1|max:20',
        ]);

        $data['max_books'] = $data['max_books'] ?? 5;
        // Trigger auto-generates membership_id

        $member = Member::create($data);
        return redirect()->route('members.show', $member)
            ->with('success', "Member '{$member->name}' registered! ID: {$member->membership_id}");
    }

    public function show(Member $member)
    {
        // Get the enriched row from v_member_summary
        $summary = DB::table('v_member_summary')->where('id', $member->id)->first();
        return view('members.show', compact('member', 'summary'));
    }

    public function edit(Member $member)
    {
        return view('members.edit', compact('member'));
    }

    public function update(Request $request, Member $member)
    {
        $data = $request->validate([
            'name'                   => 'required|string|max:255',
            'email'                  => 'nullable|email|unique:members,email,' . $member->id,
            'phone'                  => 'nullable|string|max:20',
            'address'                => 'nullable|string',
            'member_type'            => 'required|in:student,faculty,staff,public',
            'status'                 => 'required|in:active,inactive,suspended',
            'membership_start_date'  => 'required|date',
            'membership_expiry_date' => 'required|date',
            'max_books'              => 'nullable|integer|min:1|max:20',
        ]);

        $member->update($data);
        return redirect()->route('members.show', $member)
            ->with('success', 'Member updated successfully!');
    }

    public function destroy(Member $member)
    {
        $active = DB::table('v_active_borrowings')->where('member_id', $member->id)->count();
        if ($active > 0) {
            return back()->with('error', 'Cannot delete a member with active borrowings!');
        }
        $name = $member->name;
        $member->delete();
        return redirect()->route('members.index')->with('success', "Member '{$name}' deleted.");
    }
}
