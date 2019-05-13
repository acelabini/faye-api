<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\ApiController;
use App\Repositories\UserRepository;
use App\Utils\Enumerators\RolesEnumerator;
use App\Validations\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\RegistrationResource;

class RegisterController extends ApiController
{
    protected $userRepository;

    public function __construct(
        UserRepository $userRepository
    ) {
        parent::__construct();
        $this->middleware('guest');
        $this->userRepository = $userRepository;
    }

    protected function register(Request $request)
    {
        return $this->runWithExceptionHandling(function () use ($request) {
            $this->validate($request, Registration::getRules());
            $isAdmin = Auth::check() && Auth::user()->role_id == RolesEnumerator::ADMIN;
            $user = $this->userRepository->create([
                'name'      =>  $request->get('name'),
                'email'     =>  $request->get('email'),
                'password'  =>  Hash::make($request->get('password')),
                'role_id'   =>  $isAdmin && $request->has('role_id')
                                ? $request->get('role_id') : RolesEnumerator::USER
            ]);

            $this->response->setData(['data' => new RegistrationResource($user)]);
        });
    }
}