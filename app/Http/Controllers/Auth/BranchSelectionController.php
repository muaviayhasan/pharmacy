<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BranchSelectionController extends Controller
{
    public function show(Request $request)
    {
        $branches = $request->user()->branches;

        if ($branches->isEmpty()) {
            return redirect()->route('dashboard');
        }

        if ($branches->count() === 1) {
            $branch = $branches->first();
            session(['active_branch_id' => $branch->id, 'active_branch_name' => $branch->name]);

            return redirect()->route('dashboard');
        }

        return view('auth.select-branch', ['branches' => $branches]);
    }

    public function select(Request $request): RedirectResponse
    {
        $allowed = $request->user()->branches->pluck('id')->all();

        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'in:'.implode(',', $allowed ?: [0])],
        ]);

        $branch = $request->user()->branches->firstWhere('id', (int) $validated['branch_id']);
        session(['active_branch_id' => $branch->id, 'active_branch_name' => $branch->name]);

        return redirect()->intended(route('dashboard'));
    }
}
