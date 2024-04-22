<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\Users;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\support\Str;
use Illuminate\support\Facades\Hash;
use Illuminate\support\Facades\Auth;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserController extends Controller
{
    public function register (UserRegisterRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    public function login(UserLoginRequset $request): UserResource
    {
        $data = $request->validated();
        $user = User::where('email', $data['email'])->first();
        if(!$user || !Hash::check($data['password'], $user->password)) {
            throw new HttpResponseException (response([
                'errors' => [
                    'message' => ['username or password wrong'],
                ]
            ],401));
        }

        $user->remember_token = Str::uuid()->toString();
        $user->save();
        return new UserResource($user);
    }

    public function get(Request $request): UserResource
    {
        $user = Auth::user();
        return new UserResource($user);
    }

    public function update(UserUpdateRequest $request): UserResource
    {
        $data = $request->validated();
        $user = Auth::user();

        if (isset($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        if (isset($data['name'])) {
            $user->name = $data['name'];
        }
        $user->save();
        return new UserResource($user);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = Auth::user();
        $user->remember_token = null;
        $user->save();
        return response()->json([
            "data" => true
        ])->setStatusCode(200);
    }
}
