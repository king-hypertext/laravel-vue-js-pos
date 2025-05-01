<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::whereRole('shop-attendant')->get();
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_unless($request->user()->role == 'manager', 403, 'You are not allowed to perform this action');

        $request->validate([
            'username' => ['required', 'string', 'max:255', 'min:4'],
            'phone_number' => ['required', 'numeric', 'max_digits:10', 'unique:users,phone_number'],
            'password' => ['required', 'string', 'max:100'],
            'role' => ['nullable', 'string']
        ]);
        $user =  User::create([
            'username' => $request['username'],
            'phone_number' => $request['phone_number'],
            'password' =>  bcrypt($request['password']),
            'role' => $request['role']
        ]);
        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    /**
     * Display the specified resource.
     */
    // public function show(User $user)
    // {
    //     //
    // }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255', 'min:4'],
            'phone_number' => ['required', 'numeric', 'min_digits:10'],
            // 'password' => ['required', 'string', 'max:100'],
            'role' => ['nullable', 'string']
        ]);
        $user->update([
            'username' => $request['username'],
            'phone_number' => $request['phone_number'],
            // 'password' =>  bcrypt($request['password']),
            'role' => $request['role']
        ]);
        return response()->json([
            'success' => true,
            'user' => User::whereRole('shop-attendant')->fresh()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        abort_unless(request()->user()->role == 'manager', 403, 'You are not allowed to perform this action');
        $user->delete();
        return response()->noContent();
        return response()->json([
            'success' => true,
            'data' => $this->index()
        ]);
    }
}
