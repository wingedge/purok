<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use App\Models\PurokCertificate;

class PurokCertificateController extends Controller
{
    // Display the list of logs
    public function index(Request $request)
    {
        $search = $request->input('search');

        $requests = PurokCertificate::with(['member.dependents'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('member', function ($m) {
                        $m->where('name', 'like', "%".request('search')."%");
                    })
                    ->orWhereHas('member.dependents', function ($d) {
                        $d->where('name', 'like', "%".request('search')."%");
                    });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('purok_certificates.index', compact('requests'));
    }

    // Show the creation form
    public function create()
    {
        return view('purok_certificates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'request_date' => 'required|date',
            'purpose' => 'required|string|max:1000',
        ]);

        PurokCertificate::create($validated);

        return redirect()->route('purok_certificates.index')
            ->with('success', 'Certificate log created successfully!');
    }

    public function searchMembers(Request $request)
    {
        $term = $request->input('q');
        if (strlen($term) < 2) return response()->json([]);

        $members = Member::query()
            ->where('name', 'like', "%{$term}%")
            ->orWhereHas('dependents', fn($q) => $q->where('name', 'like', "%{$term}%"))
            ->with('dependents')->limit(10)->get()
            ->map(fn($m) => [
                'id' => $m->id, 
                'name' => $m->name, 
                'deps' => $m->dependents->pluck('name')->implode(', ')
            ]);

        return response()->json($members);
    }

    // Show the edit form
    public function edit(PurokCertificate $purok_certificate)
    {
        // Load the member and dependents so the search component can show the current name
        $purok_certificate->load('member');
        return view('purok_certificates.edit', compact('purok_certificate'));
    }

    // Update the record
    public function update(Request $request, PurokCertificate $purok_certificate)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'request_date' => 'required|date',
            'purpose' => 'required|string|max:1000',
        ]);

        $purok_certificate->update($validated);

        return redirect()->route('purok_certificates.index')
            ->with('success', 'Log updated successfully!');
    }

    // Remove the record
    public function destroy(PurokCertificate $purok_certificate)
    {
        $purok_certificate->delete();

        return redirect()->route('purok_certificates.index')
            ->with('success', 'Log deleted successfully!');
    }
}
