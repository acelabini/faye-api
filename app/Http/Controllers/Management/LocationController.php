<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\ApiController;
use App\Http\Resources\Hazards\GetHazard;
use App\Http\Resources\Hazards\LocationHazard;
use App\Models\LocationBarangays;
use App\Models\LocationHazards;
use App\Repositories\HazardsRepository;
use App\Repositories\LocationHazardsRepository;
use App\Utils\Enumerators\HazardEnumerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class LocationController extends ApiController
{
    protected $locationHazardsRepository;

    public function __construct(LocationHazardsRepository $locationHazardsRepository)
    {
        parent::__construct();
        $this->locationHazardsRepository = $locationHazardsRepository;
    }

    public function getLocations(Request $request)
    {
        return $this->runWithExceptionHandling(function () use ($request) {
            $locations = LocationBarangays::orderBy('name', 'asc')->get();

            $response = [];
            foreach ($locations as $location) {
                $response[] = [
                    'id'            =>  $location->id,
                    'name'          =>  $location->name,
                    'center'        =>  $location->center,
                    'area'          =>  $location->area,
                    'created_at'    =>  $location->created_at->format('M-d-Y H:i:s')
                ];
            }
            $this->response->setData(['data' => $response]);
        });
    }

    public function viewHazardLocation(Request $request, $hazardLocationId)
    {
        return $this->runWithExceptionHandling(function () use ($hazardLocationId) {
            $hazardLocation = $this->locationHazardsRepository->get($hazardLocationId);

            $this->response->setData(['data' => new LocationHazard($hazardLocation)]);
        });
    }

    public function addHazardLocation(Request $request)
    {
        return $this->runWithExceptionHandling(function () use ($request) {
            $this->validate($request, [
                'location_id'   =>  'required|exists:location_barangays,id',
                'hazard_id'     =>  'required|exists:hazards,id',
                'status'    =>  [
                    'required',
                    Rule::in(array_values(HazardEnumerator::getConstants()))
                ]
            ]);

            $findDupes = $this->locationHazardsRepository->search([
                ['location_id', $request->get('location_id')],
                ['hazard_id', $request->get('hazard_id')]
            ]);
            if ($findDupes->isNotEmpty()) {
                throw new \InvalidArgumentException('Location hazard already exist.');
            }

            $hazardLocation = $this->locationHazardsRepository->create([
                'location_id'   =>  $request->get('location_id'),
                'hazard_id'     =>  $request->get('hazard_id'),
                'status'        =>  $request->get('status')
            ]);

            $this->response->setData(['data' => new LocationHazard($hazardLocation)]);
        });
    }

    public function editHazardLocation(Request $request, $hazardLocationId)
    {
        return $this->runWithExceptionHandling(function () use ($request, $hazardLocationId) {
            $hazardLocationId = (int)$hazardLocationId;
            $this->validate($request, [
                'id'            =>  'required|exists:location_hazards,id',
                'location_id'   =>  'required|exists:location_barangays,id',
                'hazard_id'     =>  'required|exists:hazards,id',
                'status'    =>  [
                    'required',
                    Rule::in(array_values(HazardEnumerator::getConstants()))
                ]
            ]);
            $findDupes = $this->locationHazardsRepository->search([
                ['location_id', $request->get('location_id')],
                ['hazard_id', $request->get('hazard_id')]
            ]);

            if ($findDupes->isNotEmpty() && $findDupes->first()->id !== $hazardLocationId) {
                throw new \InvalidArgumentException('Location hazard already exist.');
            }

            $hazard = $this->locationHazardsRepository->get($hazardLocationId);
            $hazard = $this->locationHazardsRepository->update($hazard, [
                'location_id'   =>  $request->get('location_id'),
                'hazard_id'     =>  $request->get('hazard_id'),
                'status'        =>  $request->get('status')
            ]);

            $this->response->setData(['data' => new LocationHazard($hazard)]);
        });
    }

    public function getHazardsLocations(Request $request)
    {
        return $this->runWithExceptionHandling(function () use ($request) {
            $hazards = LocationHazards::orderBy('created_at', 'desc')->get();

            $response = [];
            foreach ($hazards as $hazard) {
                $response[] = new LocationHazard($hazard);
            }

            $this->response->setData(['data' => $response]);
        });
    }

    public function deleteHazardLocation(Request $request, $hazardLocationId)
    {
        return $this->runWithExceptionHandling(function () use ($hazardLocationId) {
            $hazard = $this->locationHazardsRepository->get($hazardLocationId);
            $this->locationHazardsRepository->delete($hazard);

            $this->response->setData(['data' => $hazard]);
        });
    }

    public function barangays(Request $request)
    {
        return $this->runWithExceptionHandling(function () use ($request) {
            $locations = LocationBarangays::select('id', 'name')->orderBy('name', 'asc')->get();

            $this->response->setData(['data' => $locations]);
        });
    }
}