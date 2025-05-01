<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255', 'exists:users,username'],
            'password' => ['required', 'string'],
            'remember_me' => ['nullable', 'boolean'], // Or 'string' if you expect a specific value
        ]);
        if (Auth::attempt($request->only('username', 'password'), $request->boolean('remember_me'))) {
            $user = Auth::user();
            $token = $user->createToken('access_token')->plainTextToken;
            return response()->json([
                'success' => true,
                '_token' => $token,
                'user' => $user
            ]);
        }

        return response()->json(
            [
                'message' => 'Invalid username or password',
                'errors' =>
                ['login' => ['invalid username or password']]
            ],
            422
        );
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'username' => ['required', 'string', 'max:255', 'min:4'],
            'phone_number' => ['required', 'numeric'],
            'password' => ['nullable', 'string', 'max:100'],
            // 'role' => ['nullable', 'string']
        ]);
        $user->update([
            'username' => $request['username'],
            'phone_number' => $request['phone_number'],
        ]);
        if ($request->filled('password')) {
            $user->update([
                'password' =>  bcrypt($request['password']),
            ]);
        }
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'passwordUpdated' => $request->filled('password') ? true : false,
            'user' => $user->fresh()
        ]);
    }

    public function logout()
    {
        request()->user()->currentAccessToken()->delete();
        return response()->noContent();
    }
    /**
     * Display the specified resource.
     */
    public function show()
    {
        return response()->json([
            'success' => true,
            'user' => request()->user()
        ]);
    }
}
