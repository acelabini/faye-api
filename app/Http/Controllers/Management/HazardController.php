<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\ApiController;
use App\Http\Resources\Hazards\GetHazard;
use App\Http\Resources\Hazards\LocationHazard;
use App\Repositories\HazardsRepository;
use App\Repositories\LocationHazardsRepository;
use App\Utils\Enumerators\HazardEnumerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class HazardController extends ApiController
{
    protected $hazardsRepository;

    protected $locationHazardsRepository;

    public function __construct(HazardsRepository $hazardsRepository)
    {
        parent::__construct();
        $this->hazardsRepository = $hazardsRepository;
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
}