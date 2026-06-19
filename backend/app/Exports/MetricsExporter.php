<?php

namespace App\Exports;

use League\Csv\Writer;
use SplTempFileObject;

class MetricsExporter
{
    public function exportJson(array $data, string $outputPath): string
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($outputPath, $json);
        return $outputPath;
    }

    public function exportCsv(array $routeMetrics, string $outputPath): string
    {
        $writer = Writer::createFromFileObject(new SplTempFileObject);
        $writer->insertOne([
            'route_id', 'route_name', 'total_deliveries',
            'min_distance_to_warehouse_km', 'max_distance_to_warehouse_km',
            'avg_distance_to_warehouse_km', 'centroid_to_warehouse_km',
            'cluster_radius_km', 'avg_distance_to_centroid_km',
            'estimated_route_distance_km',
        ]);

        foreach ($routeMetrics as $row) {
            $writer->insertOne([
                $row['route_id'],
                $row['route_name'],
                $row['total_deliveries'],
                $row['min_distance_to_warehouse_km'],
                $row['max_distance_to_warehouse_km'],
                $row['avg_distance_to_warehouse_km'],
                $row['centroid_to_warehouse_km'],
                $row['cluster_radius_km'],
                $row['avg_distance_to_centroid_km'],
                $row['estimated_route_distance_km'],
            ]);
        }

        file_put_contents($outputPath, $writer->toString());
        return $outputPath;
    }

    public function exportDeliveriesCsv(array $deliveries, string $outputPath): string
    {
        $writer = Writer::createFromFileObject(new SplTempFileObject);
        $writer->insertOne([
            'delivery_id', 'route_id', 'latitude', 'longitude',
            'distance_to_warehouse_km', 'distance_to_centroid_km',
        ]);

        foreach ($deliveries as $row) {
            $writer->insertOne([
                $row['delivery_id'],
                $row['route_id'],
                $row['latitude'],
                $row['longitude'],
                $row['distance_to_warehouse_km'],
                $row['distance_to_centroid_km'],
            ]);
        }

        file_put_contents($outputPath, $writer->toString());
        return $outputPath;
    }
}
