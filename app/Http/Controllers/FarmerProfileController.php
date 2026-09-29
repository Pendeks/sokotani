<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FarmerProfileController extends Controller
{
    public function show(User $farmer): View
    {
        abort_unless($farmer->role === 'farmer', 404);

        return view('farmers.show', compact('farmer'));
    }

    public function edit(): View
    {
        return view('farmer.profile.edit', ['farmer' => auth()->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'farm_location' => ['required', 'string', 'max:160'],
            'farm_description' => ['required', 'string', 'max:3000'],
        ]);

        auth()->user()->update($validated);

        return redirect()->route('farmer.profile.edit')->with('status', 'Profil petani berhasil diperbarui.');
    }
}
