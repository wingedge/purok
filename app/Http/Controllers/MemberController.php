<?php 

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $members = Member::query()
            ->withCount('dependents') // Assuming you have a dependents relationship
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString(); // This keeps the search term in pagination links

        return view('members.index', compact('members'));
    }

    public function create()
    {
        return view('members.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => 'nullable|string|max:50',
            'email'     => 'nullable|email|max:255',
            'indigent'  => 'nullable|boolean',
            'birthday'  => 'nullable|date',
            'dependents.*.name' => 'nullable|string|max:255',
            'dependents.*.relationship' => 'nullable|string|max:255',
        ]);

        $member = Member::create([
            'name'     => $validated['name'],
            'phone'    => $validated['phone'] ?? null,
            'email'    => $validated['email'] ?? null,
            'indigent' => $request->boolean('indigent'),
            'birthday' => $validated['birthday'] ?? null,
        ]);

        foreach ($request->dependents ?? [] as $dep) {
            if (!empty($dep['name'])) {
                $member->dependents()->create([
                    'name' => $dep['name'],
                    'relationship' => $dep['relationship'] ?? null,
                ]);
            }
        }

        return redirect()
            ->route('members.index')
            ->with('success', 'Member created successfully.');
    }

    public function show(Member $member)
    {
        $member->load('dependents');
        return view('members.show', compact('member'));
    }

    public function edit(Member $member)
    {
        $member->load('dependents');
        return view('members.edit', compact('member'));
    }

    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => 'nullable|string|max:50',
            'email'     => 'nullable|email|max:255',
            'indigent'  => 'nullable|boolean',
            'birthday'  => 'nullable|date',
            'dependents.*.name' => 'nullable|string|max:255',
            'dependents.*.relationship' => 'nullable|string|max:255',
        ]);

        $member->update([
            'name'     => $validated['name'],
            'phone'    => $validated['phone'] ?? null,
            'email'    => $validated['email'] ?? null,
            'indigent' => $request->boolean('indigent'),
            'birthday' => $validated['birthday'] ?? null,
        ]);

        // Reset dependents
        $member->dependents()->delete();
        foreach ($request->dependents ?? [] as $dep) {
            if (!empty($dep['name'])) {
                $member->dependents()->create([
                    'name' => $dep['name'],
                    'relationship' => $dep['relationship'] ?? null,
                ]);
            }
        }

        return redirect()
            ->route('members.index')
            ->with('success', 'Member updated successfully.');
    }

    public function destroy(Member $member)
    {
        $member->delete();

        return redirect()
            ->route('members.index')
            ->with('success', 'Member deleted.');
    }

    /**
     * CSV IMPORT
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $rows = array_map('str_getcsv', file($request->file('csv_file')->getRealPath()));
        $header = array_map('trim', array_shift($rows));

        DB::beginTransaction();

        try {
            foreach ($rows as $row) {
                if (count(array_filter($row)) === 0) continue;

                $data = array_combine($header, $row);

                // Normalize empty values
                foreach ($data as $key => $value) {
                    $value = trim($value);
                    $data[$key] = $value === '' ? null : $value;
                }

                // Normalize indigent
                $data['indigent'] = in_array(
                    strtolower($data['indigent'] ?? ''),
                    ['yes', '1', 'true'],
                    true
                );

                $validator = Validator::make($data, [
                    'name'     => 'required|string|max:255',
                    'phone'    => 'nullable|string',
                    'email'    => 'nullable|email',
                    'birthday' => 'nullable|date',
                    'indigent' => 'boolean',
                ]);

                if ($validator->fails()) {
                    continue;
                }

                $member = Member::create([
                    'name'     => $data['name'],
                    'phone'    => $data['phone'],
                    'email'    => $data['email'],
                    'birthday' => $data['birthday'],
                    'indigent' => $data['indigent'],
                ]);

                if (!empty($data['dependent_names'])) {
                    foreach (explode('|', $data['dependent_names']) as $depName) {
                        $depName = trim($depName);
                        if ($depName) {
                            $member->dependents()->create([
                                'name' => $depName,
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('members.index')
                ->with('success', 'Members imported successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'csv_file' => 'Import failed: ' . $e->getMessage()
            ]);
        }
    }

}
