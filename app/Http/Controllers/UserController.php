<?php

namespace App\Http\Controllers;

use App\Events\UserUpdated;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use App\Language;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:administrator');
    }

    /**
     * Show the application dashboard or respond with list of users.
     *
     * @return \Illuminate\Contracts\Support\Renderable | \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            return UserResource::collection(User::with(['language', 'roles'])->get());
        }

        return view('users');
    }

    /**
     * Respond with all user roles.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function roles()
    {
        return RoleResource::collection(Role::all());
    }

    /**
     * Create new User and respond with JSON.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'language' => ['required', 'exists:App\Language,id'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['exists:Spatie\Permission\Models\Role,id'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->markEmailAsVerified();

        $user->language()->associate(Language::find($data['language']))->save();

        if (isset($data['roles']) && $data['roles']) {
            $user->syncRoles($data['roles']);
        }

        return new UserResource($user);
    }

    /**
     * Update existing User and respond with JSON.
     *
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['sometimes', 'required', 'string', 'min:8', 'confirmed'],
            'language' => ['required', 'exists:App\Language,id'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['exists:Spatie\Permission\Models\Role,id'],
        ]);

        $updated = [
            'name' => $data['name'],
        ];

        $user->name = $data['name'];

        if (isset($data['email']) && $user->email !== $data['email']) {
            $updated['email'] = $data['email'];
        }

        if (isset($data['password']) && $data['password']) {
            $updated['password'] = Hash::make($data['password']);
        }

        $user->update($updated);

        $user->language()->associate(Language::find($data['language']))->save();

        $roles = $data['roles'] ?? [];

        // Retain administrator role for current user
        if (Auth::user()->is($user) && $user->hasRole('administrator')) {
            $administrator = Role::findByName('administrator');
            if (!in_array($administrator->id, $roles)) {
                $roles[] = $administrator->id;
            }
        }

        $user->syncRoles($roles);

        broadcast(new UserUpdated($user->refresh()));

        return new UserResource($user->refresh());
    }

    /**
     * Delete a User and respond with JSON.
     *
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(User $user)
    {
        if (Auth::user()->is($user)) {
            return response()->json([
                'message' => 'You can not delete yourself!',
            ], 422);
        }

        $user->delete();

        return new UserResource($user);
    }
}
