<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class HomeController extends ApiController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function profile()
    {
        return $this->runWithExceptionHandling(function () {
            $user = User::find(Auth::user()->id);
            $reports = [];
            foreach ($user->incidentReports as $report) {
                $reports[] = [
                    'message' => $report->message,
                    'status' => $report->status,
                    'media' => $report->media,
                    'incident_datetime' => Carbon::parse($report->incident_datetime)->format("F d, Y H:i A"),
                    'created_at' => $report->created_at->format("F d, Y H:i A"),
                    'barangay' => $report->barangay->name
                ];
            }
            $this->response->setData(['data' => [
                'profile' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'report' => $reports
            ]]);
        });
    }
}
