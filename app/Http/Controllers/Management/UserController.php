<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\ApiController;
use App\Http\Resources\Users\User;
use App\Http\Resources\Users\Users;
use App\Repositories\RolesRepository;
use App\Repositories\UserRepository;
use App\Utils\Enumerators\UserStatusEnumerator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends ApiController
{
    protected $userRepository;

    protected $rolesRepository;

    public function __construct(UserRepository $userRepository, RolesRepository $rolesRepository)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->rolesRepository = $rolesRepository;
    }

    public function getUsers(Request $request)
    {
        return $this->runWithExceptionHandling(function () use ($request) {
            $users = $this->userRepository->all();

            $this->response->setData(['data' => new Users($users)]);
        });
    }

    public function viewUser(Request $request, $userId)
    {
        return $this->runWithExceptionHandling(function () use ($request, $userId) {
            $user = $this->userRepository->get($userId);

            $this->response->setData(['data' => new User($user)]);
        });
    }

    public function editUser(Request $request, $userId)
    {
        return $this->runWithExceptionHandling(function () use ($request, $userId) {
            $user = $this->userRepository->get($userId);
            $this->validate($request, [
                'name'      =>  'required|string',
                'email'     =>  'required|email|unique:users,email,'.$userId,
                'status'    =>  [
                    'required',
                    Rule::in(array_values(UserStatusEnumerator::getConstants()))
                ],
                'role_id'   =>  'required|exists:roles,id'
            ]);

            $user = $this->userRepository->update($user, [
                'name'      =>  $request->get('name'),
                'email'     =>  $request->get('email'),
                'role_id'   =>  $request->get('role_id'),
                'status'    =>  $request->get('status')
            ]);
            $this->response->setData(['data' => new User($user)]);
        });
    }

    public function addUser(Request $request)
    {
        return $this->runWithExceptionHandling(function () use ($request) {
            $this->validate($request, [
                'name'      =>  'required|string',
                'email'     =>  'required|email|unique:users,email',
                'status'    =>  [
                    'required',
                    Rule::in(array_values(UserStatusEnumerator::getConstants()))
                ],
                'role_id'   =>  'required|exists:roles,id'
            ]);

            $user = $this->userRepository->create([
                'name'      =>  $request->get('name'),
                'email'     =>  $request->get('email'),
                'role_id'   =>  $request->get('role_id'),
                'status'    =>  $request->get('status')
            ]);
            $this->response->setData(['data' => new User($user)]);
        });
    }

    public function getRoles()
    {
        return $this->runWithExceptionHandling(function () {
            $roles = $this->rolesRepository->all();

            $this->response->setData(['data' => $roles]);
        });
    }
}