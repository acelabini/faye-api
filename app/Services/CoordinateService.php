<?php

namespace App\Services;

use App\Models\LocationBarangays;
use Illuminate\Support\Arr;

class CoordinateService
{
    public static function generateBarangays()
    {
        $feed = file_get_contents(public_path("ph.xml"));
        $xml = simplexml_load_string($feed, "SimpleXMLElement", LIBXML_NOCDATA);
        $xml = json_encode($xml);
        $xml = json_decode($xml, true);
        $result = [];
        foreach (Arr::get($xml, "Document.Folder.Placemark") as $placemark) {
            $coordinates = Arr::get($placemark, "MultiGeometry.Polygon.outerBoundaryIs.LinearRing.coordinates");
            $coordinates = explode(" ", $coordinates);
            $coordinates = array_chunk($coordinates, 2);
            $flattened_array = [];
            array_walk_recursive($coordinates, function($a) use (&$flattened_array) { $flattened_array[] = $a; });
            $coordinates = $flattened_array;
//            foreach ($coordinates as $coordinate) {
//                $coor = explode(",", $coordinate);
//                $x .= "{lat:{$coor[1]}, lng:{$coor[0]}},";
//            }
            $center = explode(",", Arr::first($coordinates));
            $center = [
                'lat'    =>  $center[1],
                'lng'   =>  $center[0],
            ];

            LocationBarangays::create([
                'location_id'   =>  1,
                'name'          =>  Arr::get($placemark, "ExtendedData.SchemaData.SimpleData")[3],
                'center'        =>  json_encode($center),
                'area'          =>  rand(15,19)
            ]);
            $result[] = [
                'location_id'   =>  1,
                'name'          =>  Arr::get($placemark, "ExtendedData.SchemaData.SimpleData")[3],
                'center'        =>  json_encode($center),
                'area'          =>  rand(15,19)
            ];
        }

        LocationBarangays::create([
            'location_id'   =>  1,
            'name'          =>  'Bgy. 32 - San Roque',
            'center'        =>  json_encode([
                'lat'   =>  13.154304,
                'lng'   =>  123.754824
            ]),
            'area'          =>  16
        ]);
    }
}