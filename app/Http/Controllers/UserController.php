<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserLoginRequest;

use App\Http\Resources\UserResource;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use Illuminate\Http\support\Str;
use Illuminate\Http\support\Facades\Hash;

use Illuminate\Http\Exceptions\HttpResponseException;

class UserController extends Controller
{
    public function register(UserRegisterRequest $request): JsonResponse
    {
        $user = User::create($request->validation());

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    public function login(UserLoginRequest $request): userResource
    {
        $data = $request->validated();
        $user = User::where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw new HttpResponseException (response([
                'errors' => [
                    'message' => ['username or password wrong'],
                ]
                ],401));
    }
    $user->remember_token = str::uuid()->toString();
    $user->save();
    return new UserResources($user);
  }
}