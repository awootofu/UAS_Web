<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request): View
    {
        $query = User::with(['prodi', 'jabatan']);

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by prodi
        if ($request->filled('prodi')) {
            $query->where('prodi_id', $request->prodi);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15);
        $prodis = Prodi::orderBy('nama_prodi')->get();
        $roles = User::ROLES;

        return view('users.index', compact('users', 'prodis', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $prodis = Prodi::orderBy('nama_prodi')->get();
        $jabatans = Jabatan::orderBy('nama_jabatan')->get();
        $roles = User::ROLES;

        return view('users.create', compact('prodis', 'jabatans', 'roles'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => 'required|in:' . implode(',', User::ROLES),
            'prodi_id' => 'nullable|exists:prodi,id',
            'jabatan_id' => 'nullable|exists:jabatan,id',
            'nip' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        User::create($validated);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dibuat.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): View
    {
        $user->load(['prodi', 'jabatan', 'renstras', 'evaluasis', 'rtls']);

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the user.
     */
    public function edit(User $user): View
    {
        $prodis = Prodi::orderBy('nama_prodi')->get();
        $jabatans = Jabatan::orderBy('nama_jabatan')->get();
        $roles = User::ROLES;

        return view('users.edit', compact('user', 'prodis', 'jabatans', 'roles'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => 'required|in:' . implode(',', User::ROLES),
            'prodi_id' => 'nullable|exists:prodi,id',
            'jabatan_id' => 'nullable|exists:jabatan,id',
            'nip' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $user->update($validated);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        // Prevent deactivating yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "User berhasil {$status}.");
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
