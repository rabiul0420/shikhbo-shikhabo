<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminSchoolController extends Controller
{
    public function index(): View
    {
        $schools = School::query()
            ->latest()
            ->get();

        return view('admin.schools.index', compact('schools'));
    }

    public function store(Request $request): RedirectResponse
    {
        School::create($request->validate([
            'title' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'status' => ['required', Rule::in(School::STATUSES)],
        ]));

        return redirect()
            ->route('admin.schools.index')
            ->with('status', 'School added.');
    }

    public function update(Request $request, School $school): RedirectResponse
    {
        $school->update($request->validate([
            'title' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'status' => ['required', Rule::in(School::STATUSES)],
        ]));

        return redirect()
            ->route('admin.schools.index')
            ->with('status', 'School updated.');
    }

    public function destroy(School $school): RedirectResponse
    {
        $school->delete();

        return redirect()
            ->route('admin.schools.index')
            ->with('status', 'School deleted.');
    }
}
