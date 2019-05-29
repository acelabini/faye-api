<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\ApiController;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\JWTAuth;

class AuthenticationController extends ApiController
{
    protected $userRepository;
    protected $auth;

    public function __construct(
        UserRepository $userRepository,
        JWTAuth $auth
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->auth = $auth;
    }

    public function login(Request $request)
    {
        return $this->runWithExceptionHandling(function () use ($request) {
            $this->validate($request, [
                'email'     =>  'required',
                'password'  =>  'required'
            ]);
            $token = $this->auth->attempt($request->only('email', 'password'));

            $this->response->setData(['data' => [
                'success'   =>  $token ? true : false,
                'token'     =>  $token,
                'user'      =>  $this->auth->user()
            ]]);
        });
    }

    public function logout(Request $request)
    {
        return $this->runWithExceptionHandling(function () use ($request) {
            $this->auth->parseToken()->invalidate();

            $this->response->setData(['data' => [
                'success' => true
            ]]);
        });
    }

    public function getAuthUser(Request $request)
    {
        return $this->runWithExceptionHandling(function () use ($request) {
            $user = $this->auth->user();
            $this->response->setData(['data' => $user->toArray()]);
        });
    }
}