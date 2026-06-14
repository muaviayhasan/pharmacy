<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with('roles', 'branches')->latest();

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($role = $request->string('role')->value()) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $role));
        }

        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }

        if ($branch = $request->integer('branch')) {
            $query->whereHas('branches', fn ($q) => $q->where('branches.id', $branch));
        }

        $users = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => User::count(),
            'active' => User::where('status', 'active')->count(),
            'blocked' => User::where('status', 'blocked')->count(),
            'two_factor' => User::where('two_factor_enabled', true)->count(),
        ];

        return view('users.index', [
            'users' => $users,
            'roles' => Role::orderBy('name')->get(),
            'branches' => Branch::orderBy('name')->get(),
            'stats' => $stats,
            'filters' => $request->only('search', 'role', 'status', 'branch'),
        ]);
    }

    public function create()
    {
        return view('users.create', [
            'roles' => Role::orderBy('name')->get(),
            'branches' => Branch::orderBy('name')->get(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'],
            'two_factor_enabled' => $request->boolean('two_factor_enabled'),
            'default_branch_id' => $data['default_branch_id'] ?? null,
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);

        $user->syncRoles([$data['role']]);
        $this->syncBranches($user, $data['branches'] ?? []);

        return redirect()->route('users.index')->with('status', "User \"{$user->name}\" created.");
    }

    public function edit(User $user)
    {
        $user->load('roles', 'branches');

        return view('users.edit', [
            'user' => $user,
            'roles' => Role::orderBy('name')->get(),
            'branches' => Branch::orderBy('name')->get(),
            'userBranchIds' => $user->branches->pluck('id')->all(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'],
            'two_factor_enabled' => $request->boolean('two_factor_enabled'),
            'default_branch_id' => $data['default_branch_id'] ?? null,
        ]);

        if (! empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        $user->syncRoles([$data['role']]);
        $this->syncBranches($user, $data['branches'] ?? []);

        return redirect()->route('users.index')->with('status', "User \"{$user->name}\" updated.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('status', 'User deleted.');
    }

    public function toggleBlock(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot block your own account.');
        }

        $user->update(['status' => $user->status === 'blocked' ? 'active' : 'blocked']);

        return back()->with('status', "User \"{$user->name}\" is now {$user->status}.");
    }

    public function sendPasswordReset(User $user): RedirectResponse
    {
        Password::sendResetLink(['email' => $user->email]);

        return back()->with('status', "Password reset link sent to {$user->email}.");
    }

    private function syncBranches(User $user, array $branchIds): void
    {
        $user->branches()->sync(
            collect($branchIds)->mapWithKeys(fn ($id) => [
                $id => ['access_level' => 'full', 'status' => 'active'],
            ])->all()
        );
    }
}
