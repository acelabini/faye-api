<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\ApiController;
use App\Http\Resources\Hazards\GetHazard;
use App\Http\Resources\Hazards\LocationHazard;
use App\Repositories\HazardsRepository;
use App\Repositories\IncidentReportRepository;
use App\Repositories\LocationHazardsRepository;
use App\Utils\Enumerators\HazardEnumerator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class HazardController extends ApiController
{
    protected $hazardsRepository;

    protected $locationHazardsRepository;

    protected $incidentReportRepository;

    public function __construct(
        HazardsRepository $hazardsRepository,
        IncidentReportRepository $incidentReportRepository
    ) {
        parent::__construct();
        $this->hazardsRepository = $hazardsRepository;
        $this->incidentReportRepository = $incidentReportRepository;
    }

    public function viewHazard(Request $request, $hazardId)
    {
        return $this->runWithExceptionHandling(function () use ($hazardId) {
            $hazard = $this->hazardsRepository->get($hazardId);

            $this->response->setData(['data' => new GetHazard($hazard)]);
        });
    }

    public function addHazard(Request $request)
    {
        return $this->runWithExceptionHandling(function () use ($request) {
            $this->validate($request, [
                'name'      =>  'required|string|unique:hazards,name',
                'status'    =>  [
                    'required',
                    Rule::in(array_values(HazardEnumerator::getConstants()))
                ]
            ]);

            $hazard = $this->hazardsRepository->create([
                'created_by'    =>  Auth::user()->id,
                'name'          =>  $request->get('name'),
                'status'        =>  $request->get('status')
            ]);

            $this->response->setData(['data' => new GetHazard($hazard)]);
        });
    }

    public function editHazard(Request $request, $hazardId)
    {
        return $this->runWithExceptionHandling(function () use ($request, $hazardId) {
            $this->validate($request, [
                'name'  =>  'required|string|unique:hazards,name,'.$hazardId,
                'status'    =>  [
                    'required',
                    Rule::in(array_values(HazardEnumerator::getConstants()))
                ]
            ]);
            $hazard = $this->hazardsRepository->get($hazardId);
            $hazard = $this->hazardsRepository->update($hazard, [
                'name'      =>  $request->get('name'),
                'status'    =>  $request->get('status')
            ]);

            $this->response->setData(['data' => new GetHazard($hazard)]);
        });
    }

    public function getHazards(Request $request)
    {
        return $this->runWithExceptionHandling(function () use ($request) {
            $hazards = $this->hazardsRepository->all();

            $response = [];
            foreach ($hazards as $hazard) {
                $response[] = [
                    'id'            =>  $hazard->id,
                    'name'          =>  $hazard->name,
                    'created_by'    =>  $hazard->createdBy->name,
                    'status'        =>  $hazard->status,
                    'created_at'    =>  $hazard->created_at->format('M-d-Y H:i:s')
                ];
            }
            $this->response->setData(['data' => $response]);
        });
    }

    public function deleteHazard(Request $request, $hazardId)
    {
        return $this->runWithExceptionHandling(function () use ($hazardId) {
            $hazard = $this->hazardsRepository->get($hazardId);
            $this->hazardsRepository->delete($hazard);

            $this->response->setData(['data' => $hazard]);
        });
    }

    public function reportIncident(Request $request)
    {
        return $this->runWithExceptionHandling(function () use ($request) {
            $this->validate($request, [
                'report_name'      =>  'required|string',
                'report_message'   =>  'required',
                'report_datetime'  =>  'required|date_format:Y-m-d H:i',
                'barangay_id'      =>  'required|exists:location_barangays,id',
                'report_media'     =>  'required|max:3000|mimes:video/x-ms-asf,video/x-flv,video/mp4,application/x-mpegURL,video/MP2T,video/3gpp,video/quicktime,video/x-msvideo,video/x-ms-wmv,video/avi,jpeg,jpg,png'
            ]);

            $file = $request->file("report_media");
            $fileName = date("YmdHis")."_". uniqid()."_".$file->getClientOriginalName();
            $file->move(public_path("incident-report"), $fileName);
            $report = $this->incidentReportRepository->create([
                'name'      =>  $request->get('report_name'),
                'message'   =>  $request->get('report_message'),
                'media'     =>  $fileName,
                'barangay_id' => $request->get('barangay_id'),
                'incident_datetime' => Carbon::parse($request->get("report_datetime"))
            ]);

            $this->response->setData(['data' => $report]);
        });
    }

    public function getReportIncidents(Request $request)
    {
        return $this->runWithExceptionHandling(function () use ($request) {
            $reports = $this->incidentReportRepository->getReports();

            $this->response->setData(['data' => $reports]);
        });
    }

    public function updateIncidentStatus(Request $request, $id)
    {
        return $this->runWithExceptionHandling(function () use ($request, $id) {
            $report = $this->incidentReportRepository->get($id);
            $status = $request->get('status');
            if (!in_array($status, ['confirmed', 'rejected'])) {
                throw new \InvalidArgumentException("Invalid status", Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $this->incidentReportRepository->update($report, [
                'status' => $status
            ]);
            $this->response->setData(['data' => $report]);
        });
    }
}