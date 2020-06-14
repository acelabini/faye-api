<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\ApiController;
use App\Http\Resources\Users\User;
use App\Http\Resources\Users\Users;
use App\Repositories\AnswersRepository;
use App\Repositories\IncidentReportRepository;
use App\Repositories\ProcessedDataRepository;
use App\Repositories\RolesRepository;
use App\Repositories\UserRepository;
use App\Utils\Enumerators\RolesEnumerator;
use App\Utils\Enumerators\UserStatusEnumerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserController extends ApiController
{
    protected $userRepository;

    protected $rolesRepository;

    protected $answersRepository;

    protected $incidentReportRepository;

    protected $processedDataRepository;

    public function __construct(
        UserRepository $userRepository,
        RolesRepository $rolesRepository,
        AnswersRepository $answersRepository,
        IncidentReportRepository $incidentReportRepository,
        ProcessedDataRepository $processedDataRepository
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->rolesRepository = $rolesRepository;
        $this->answersRepository = $answersRepository;
        $this->incidentReportRepository = $incidentReportRepository;
        $this->processedDataRepository = $processedDataRepository;
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

            $data = [
                'name'      =>  $request->get('name'),
                'email'     =>  $request->get('email'),
                'role_id'   =>  $request->get('role_id'),
                'status'    =>  $request->get('status')
            ];
            if ($request->has('password') && $request->get('password') &&
                strlen($request->get('password')) > 4
            ) {
                $data = array_merge($data, [
                    'password' => Hash::make($request->get("password")),
                ]);
            }
            $user = $this->userRepository->update($user, $data);
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
                'password'  =>  Hash::make($request->has("password") ? $request->get("password") : "password"),
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

    public function dashboard()
    {
        return $this->runWithExceptionHandling(function () {
            $data = [
                'total_users' => $this->userRepository->all()->count(),
                'total_respondents' => $this->answersRepository->totalRespondents(),
                'incident_reports' => $this->incidentReportRepository->all()->count(),
                'processed_data' => $this->processedDataRepository->all()->count()
            ];

            $this->response->setData(['data' => $data]);
        });
    }

    public function signUp(Request $request)
    {
        return $this->runWithExceptionHandling(function () use ($request) {
            $this->validate($request, [
                'name'      =>  'required|string',
                'email'     =>  'required|email|unique:users,email',
                'password'  =>  'required'
            ]);

            $user = $this->userRepository->create([
                'name'      =>  $request->get('name'),
                'email'     =>  $request->get('email'),
                'role_id'   =>  RolesEnumerator::USER,
                'password'  =>  Hash::make($request->get("password")),
                'status'    =>  UserStatusEnumerator::ACTIVE
            ]);
            $this->response->setData(['data' => new User($user)]);
        });
    }
}