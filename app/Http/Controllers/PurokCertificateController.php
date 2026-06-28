<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Certificates\CreatePurokCertificate;
use App\Actions\Certificates\DeletePurokCertificate;
use App\Actions\Certificates\ListPurokCertificates;
use App\Actions\Certificates\SearchCertificateMembers;
use App\Actions\Certificates\UpdatePurokCertificate;
use Illuminate\Http\Request;
use App\Models\PurokCertificate;

class PurokCertificateController extends Controller
{
    // Display the list of logs
    public function index(Request $request)
    {
        $search = $request->input('search');

        $requests = app(ListPurokCertificates::class)->execute(is_string($search) ? $search : null);

        return view('purok_certificates.index', compact('requests'));
    }

    // Show the creation form
    public function create()
    {
        return view('purok_certificates.create');
    }

    public function store(Request $request, CreatePurokCertificate $createPurokCertificate)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'request_date' => 'required|date',
            'purpose' => 'required|string|max:1000',
        ]);

        $createPurokCertificate->execute($validated);

        return redirect()->route('purok_certificates.index')
            ->with('success', 'Certificate log created successfully!');
    }

    public function searchMembers(Request $request, SearchCertificateMembers $searchCertificateMembers)
    {
        return response()->json($searchCertificateMembers->execute($request->input('q'))->values());
    }

    // Show the edit form
    public function edit(PurokCertificate $purok_certificate)
    {
        // Load the member and dependents so the search component can show the current name
        $purok_certificate->load('member');
        return view('purok_certificates.edit', compact('purok_certificate'));
    }

    // Update the record
    public function update(
        Request $request,
        PurokCertificate $purok_certificate,
        UpdatePurokCertificate $updatePurokCertificate
    )
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'request_date' => 'required|date',
            'purpose' => 'required|string|max:1000',
        ]);

        $updatePurokCertificate->execute($purok_certificate, $validated);

        return redirect()->route('purok_certificates.index')
            ->with('success', 'Log updated successfully!');
    }

    // Remove the record
    public function destroy(PurokCertificate $purok_certificate, DeletePurokCertificate $deletePurokCertificate)
    {
        $deletePurokCertificate->execute($purok_certificate);

        return redirect()->route('purok_certificates.index')
            ->with('success', 'Log deleted successfully!');
    }
}
