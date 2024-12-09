<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    public function login(Request $request)
    {
        $validation = $this->requestValidation($request);

        return $this->handleException(function () use ($validation) {
            $user = \App\Models\User::where('email', $validation['email'])
                ->where('is_deleted', 0)
                ->first();

            if (!$user || !\Illuminate\Support\Facades\Hash::check($validation['password'], $user->password)) {
                return $this->returnErrorInvalidRequest('Invalid credentials');
            }

            if (!$token = auth()->login($user)) {
                return $this->returnErrorInvalidRequest('Failed to generate token');
            }

            $result = (object)[
                'token' => $token,
                'user' => $user,
            ];
            return $this->responseFormatter->format(true, 'Login Success', $result, $this->responseFormatter->statusCodeSuccess);
        });
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return $this->responseFormatter->format(true, 'Logged out successfully', null, $this->responseFormatter->statusCodeSuccess);
    }
    private function requestValidation(Request $request, $id = null)
    {
        $rules = [
            'email'     => 'required|email',
            'password'  => 'required|string|min:8',
        ];
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return ['errors' => $validation->errors()->toArray()];
        }

        $validatedData = $validation->validated();
        return $validatedData;
    }
}
