<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminFarmerController extends Controller
{
    public function index(): View
    {
        $farmers = User::query()
            ->where('role', 'farmer')
            ->withCount('products')
            ->orderBy('is_verified')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.farmers.index', compact('farmers'));
    }

    public function setVerification(Request $request, User $farmer): RedirectResponse
    {
        abort_unless($farmer->role === 'farmer', 404);

        $validated = $request->validate(['is_verified' => ['required', 'boolean']]);
        $farmer->forceFill(['is_verified' => (bool) $validated['is_verified']])->save();

        return back()->with('status', $farmer->is_verified
            ? 'Petani berhasil diverifikasi.'
            : 'Status verifikasi petani telah dicabut.');
    }
}
