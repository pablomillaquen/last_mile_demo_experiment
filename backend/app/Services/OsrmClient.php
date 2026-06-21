<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class OsrmClient
{
    const OSRM_OK = 'Ok';
    const OSRM_NO_ROUTE = 'NoRoute';
    const OSRM_NO_SEGMENT = 'NoSegment';

    private Client $http;

    public function __construct(private string $baseUrl = 'http://osrm:5000')
    {
        $this->http = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 10.0,
            'connect_timeout' => 5.0,
        ]);
    }

    public function route(float $lng1, float $lat1, float $lng2, float $lat2): array
    {
        $url = sprintf(
            '/route/v1/driving/%s,%s;%s,%s?overview=full&steps=false&alternatives=false&geometries=geojson',
            $lng1, $lat1, $lng2, $lat2
        );

        try {
            $response = $this->http->get($url);
            $data = json_decode($response->getBody()->getContents(), true);

            if (!isset($data['code'])) {
                return [
                    'distance_km' => null,
                    'duration_min' => null,
                    'geometry' => null,
                    'code' => 'UnknownResponse',
                ];
            }

            if ($data['code'] !== self::OSRM_OK) {
                return [
                    'distance_km' => null,
                    'duration_min' => null,
                    'geometry' => null,
                    'code' => $data['code'],
                ];
            }

            if (empty($data['routes'])) {
                return [
                    'distance_km' => null,
                    'duration_min' => null,
                    'geometry' => null,
                    'code' => 'NoRoutesFound',
                ];
            }

            $route = $data['routes'][0];
            $geometry = $route['geometry']['coordinates'] ?? null;
            $decodedGeometry = null;

            if ($geometry !== null) {
                $decodedGeometry = array_map(
                    fn($coord) => [(float) $coord[1], (float) $coord[0]],
                    $geometry
                );
            }

            return [
                'distance_km' => $route['distance'] / 1000,
                'duration_min' => $route['duration'] / 60,
                'geometry' => $decodedGeometry,
                'code' => self::OSRM_OK,
            ];
        } catch (GuzzleException $e) {
            return [
                'distance_km' => null,
                'duration_min' => null,
                'geometry' => null,
                'code' => 'ConnectionError',
                'error' => $e->getMessage(),
            ];
        }
    }
}
