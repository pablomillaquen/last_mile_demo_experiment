<?php

namespace App\Services;

class MapRendererService
{
    private array $palette = [
        [0x4E, 0x79, 0xA7],
        [0xE1, 0x57, 0x59],
        [0x76, 0xB7, 0xB2],
        [0xFF, 0xBE, 0x0D],
        [0x59, 0xA1, 0x4F],
        [0x8C, 0x56, 0x4B],
        [0x49, 0x92, 0xBA],
        [0xE7, 0x6C, 0x3B],
        [0xD7, 0xAC, 0x5C],
        [0x94, 0x6E, 0xBD],
        [0xA4, 0x6B, 0x5C],
        [0x7F, 0x7F, 0x7F],
    ];

    private int $width = 1000;
    private int $height = 800;

    public function renderOverview(float $warehouseLat, float $warehouseLng, array $routes, array $allDeliveries, string $outputPath): string
    {
        $bounds = $this->calculateBounds($allDeliveries, $warehouseLat, $warehouseLng);
        $img = $this->createImage($this->width, $this->height);

        $this->drawBackground($img);
        $this->drawGrid($img, $bounds);
        $this->drawPolylines($img, $routes, $bounds);
        $this->drawDeliveries($img, $routes, $bounds);
        $this->drawWarehouse($img, $warehouseLat, $warehouseLng, $bounds);
        $this->drawScaleBar($img, $bounds);
        $this->drawLegend($img, $routes);

        imagepng($img, $outputPath);
        imagedestroy($img);

        return $outputPath;
    }

    public function renderRouteMap(float $warehouseLat, float $warehouseLng, array $deliveries, string $routeName, string $outputPath): string
    {
        $w = 800;
        $h = 600;
        $allPoints = array_merge($deliveries, [['latitude' => $warehouseLat, 'longitude' => $warehouseLng]]);
        $bounds = $this->calculateBounds($allPoints, $warehouseLat, $warehouseLng);
        $img = $this->createImage($w, $h);

        $this->drawBackground($img);
        $this->drawGrid($img, $bounds);
        $this->drawWarehouse($img, $warehouseLat, $warehouseLng, $bounds);

        $color = $this->palette[0];
        $markerColor = imagecolorallocate($img, $color[0], $color[1], $color[2]);

        $prevXY = null;
        foreach ($deliveries as $d) {
            $xy = $this->latLngToPixel((float) $d['latitude'], (float) $d['longitude'], $bounds, $w, $h);
            if ($prevXY) {
                imageline($img, (int) $prevXY[0], (int) $prevXY[1], (int) $xy[0], (int) $xy[1], $markerColor);
            }
            imagefilledellipse($img, (int) $xy[0], (int) $xy[1], 10, 10, $markerColor);
            $prevXY = $xy;
        }

        imagepng($img, $outputPath);
        imagedestroy($img);

        return $outputPath;
    }

    public function renderAnomalyMap(float $warehouseLat, float $warehouseLng, array $routes, array $anomalies, array $allDeliveries, string $outputPath): string
    {
        $bounds = $this->calculateBounds($allDeliveries, $warehouseLat, $warehouseLng);
        $img = $this->createImage($this->width, $this->height);

        $this->drawBackground($img);
        $this->drawGrid($img, $bounds);
        $this->drawPolylines($img, $routes, $bounds);
        $this->drawDeliveries($img, $routes, $bounds);
        $this->drawWarehouse($img, $warehouseLat, $warehouseLng, $bounds);

        $anomalyIds = array_map(fn($a) => $a['delivery_id'], $anomalies);
        $red = imagecolorallocate($img, 0xE1, 0x57, 0x59);
        $anomalyColor = imagecolorallocate($img, 0xFF, 0x00, 0x00);

        foreach ($allDeliveries as $d) {
            if (in_array($d['delivery_id'] ?? $d['id'], $anomalyIds)) {
                $xy = $this->latLngToPixel((float) $d['latitude'], (float) $d['longitude'], $bounds, $this->width, $this->height);
                imagefilledellipse($img, (int) $xy[0], (int) $xy[1], 16, 16, $red);
                imageellipse($img, (int) $xy[0], (int) $xy[1], 22, 22, $anomalyColor);
            }
        }

        $this->drawLegend($img, $routes);
        imagepng($img, $outputPath);
        imagedestroy($img);

        return $outputPath;
    }

    private function latLngToPixel(float $lat, float $lng, array $bounds, int $w, int $h): array
    {
        $x = ($lng - $bounds['min_lng']) / ($bounds['max_lng'] - $bounds['min_lng']) * ($w - 80) + 40;
        $latRad = deg2rad($lat);
        $minLatRad = deg2rad($bounds['min_lat']);
        $maxLatRad = deg2rad($bounds['max_lat']);
        $y = (log(tan($latRad) + 1 / cos($latRad)) - log(tan($minLatRad) + 1 / cos($minLatRad)))
            / (log(tan($maxLatRad) + 1 / cos($maxLatRad)) - log(tan($minLatRad) + 1 / cos($minLatRad)));
        $y = ($h - 40) - $y * ($h - 80) - 20;

        return [$x, $y];
    }

    private function calculateBounds(array $deliveries, float $warehouseLat, float $warehouseLng): array
    {
        $lats = [$warehouseLat];
        $lngs = [$warehouseLng];

        foreach ($deliveries as $d) {
            $lats[] = (float) ($d['latitude'] ?? $d['lat']);
            $lngs[] = (float) ($d['longitude'] ?? $d['lng']);
        }

        $minLat = min($lats);
        $maxLat = max($lats);
        $minLng = min($lngs);
        $maxLng = max($lngs);

        $latMargin = ($maxLat - $minLat) * 0.1;
        $lngMargin = ($maxLng - $minLng) * 0.1;

        if ($latMargin < 0.001) $latMargin = 0.01;
        if ($lngMargin < 0.001) $lngMargin = 0.01;

        return [
            'min_lat' => $minLat - $latMargin,
            'max_lat' => $maxLat + $latMargin,
            'min_lng' => $minLng - $lngMargin,
            'max_lng' => $maxLng + $lngMargin,
        ];
    }

    private function createImage(int $w, int $h): \GdImage
    {
        $img = imagecreatetruecolor($w, $h);
        imageantialias($img, true);
        return $img;
    }

    private function drawBackground(\GdImage $img): void
    {
        $bg = imagecolorallocate($img, 0xF5, 0xF5, 0xF0);
        imagefilledrectangle($img, 0, 0, $this->width - 1, $this->height - 1, $bg);
    }

    private function drawGrid(\GdImage $img, array $bounds): void
    {
        $gridColor = imagecolorallocate($img, 0xDD, 0xDD, 0xDD);
        for ($i = 0; $i < 5; $i++) {
            $x = (int) (40 + ($this->width - 80) * $i / 4);
            imageline($img, $x, 20, $x, $this->height - 20, $gridColor);
            $y = (int) (20 + ($this->height - 80) * $i / 4);
            imageline($img, 40, $y, $this->width - 40, $y, $gridColor);
        }
    }

    private function drawWarehouse(\GdImage $img, float $lat, float $lng, array $bounds): void
    {
        $xy = $this->latLngToPixel($lat, $lng, $bounds, $this->width, $this->height);
        $red = imagecolorallocate($img, 0xE1, 0x57, 0x59);
        $x = (int) $xy[0];
        $y = (int) $xy[1];
        imagefilledrectangle($img, $x - 6, $y - 6, $x + 6, $y + 6, $red);
        $white = imagecolorallocate($img, 0xFF, 0xFF, 0xFF);
        imagerectangle($img, $x - 6, $y - 6, $x + 6, $y + 6, $white);

        $black = imagecolorallocate($img, 0x00, 0x00, 0x00);
        imagestring($img, 2, $x + 10, $y - 6, 'Bodega', $black);
    }

    private function drawDeliveries(\GdImage $img, array $routes, array $bounds): void
    {
        foreach ($routes as $i => $route) {
            $colorIdx = $i % count($this->palette);
            $c = $this->palette[$colorIdx];
            $col = imagecolorallocate($img, $c[0], $c[1], $c[2]);

            $deliveries = $route['deliveries'] ?? [];
            foreach ($deliveries as $d) {
                $xy = $this->latLngToPixel((float) $d['latitude'], (float) $d['longitude'], $bounds, $this->width, $this->height);
                imagefilledellipse($img, (int) $xy[0], (int) $xy[1], 8, 8, $col);
            }
        }
    }

    private function drawPolylines(\GdImage $img, array $routes, array $bounds): void
    {
        foreach ($routes as $i => $route) {
            $colorIdx = $i % count($this->palette);
            $c = $this->palette[$colorIdx];
            $col = imagecolorallocate($img, $c[0], $c[1], $c[2]);

            $warehouseLat = (float) ($route['warehouse_lat'] ?? 0);
            $warehouseLng = (float) ($route['warehouse_lng'] ?? 0);
            $prevXY = $this->latLngToPixel($warehouseLat, $warehouseLng, $bounds, $this->width, $this->height);

            $deliveries = $route['deliveries'] ?? [];
            foreach ($deliveries as $d) {
                $xy = $this->latLngToPixel((float) $d['latitude'], (float) $d['longitude'], $bounds, $this->width, $this->height);
                imageline($img, (int) $prevXY[0], (int) $prevXY[1], (int) $xy[0], (int) $xy[1], $col);
                $prevXY = $xy;
            }
        }
    }

    private function drawScaleBar(\GdImage $img, array $bounds): void
    {
        $lngRange = $bounds['max_lng'] - $bounds['min_lng'];
        $pixelPerDegree = ($this->width - 80) / $lngRange;
        $kmPerDegree = 111.32;
        $barLengthKm = 5;
        $barLengthPx = (int) ($barLengthKm / $kmPerDegree * $pixelPerDegree);

        if ($barLengthPx < 30) {
            $barLengthKm = 10;
            $barLengthPx = (int) ($barLengthKm / $kmPerDegree * $pixelPerDegree);
        }
        if ($barLengthPx < 30) {
            $barLengthKm = 20;
            $barLengthPx = (int) ($barLengthKm / $kmPerDegree * $pixelPerDegree);
        }
        if ($barLengthPx > 300) {
            $barLengthKm = 1;
            $barLengthPx = (int) ($barLengthKm / $kmPerDegree * $pixelPerDegree);
        }

        $barX = $this->width - 40 - $barLengthPx;
        $barY = $this->height - 40;

        $black = imagecolorallocate($img, 0x00, 0x00, 0x00);
        $white = imagecolorallocate($img, 0xFF, 0xFF, 0xFF);
        imagefilledrectangle($img, $barX - 1, $barY - 1, $barX + $barLengthPx + 1, $barY + 6, $white);
        imagefilledrectangle($img, $barX, $barY, $barX + $barLengthPx, $barY + 4, $black);
        imagestring($img, 1, $barX, $barY + 8, "{$barLengthKm} km", $black);
    }

    private function drawLegend(\GdImage $img, array $routes): void
    {
        $black = imagecolorallocate($img, 0x00, 0x00, 0x00);
        $bg = imagecolorallocate($img, 0xFF, 0xFF, 0xFF);

        $legendX = 45;
        $legendY = 25;
        $itemH = 16;
        $count = count($routes);
        $legendH = $count * $itemH + 10;

        imagefilledrectangle($img, $legendX - 5, $legendY - 5, $legendX + 150, $legendY + $legendH, $bg);
        imagerectangle($img, $legendX - 5, $legendY - 5, $legendX + 150, $legendY + $legendH, $black);

        foreach ($routes as $i => $route) {
            $colorIdx = $i % count($this->palette);
            $c = $this->palette[$colorIdx];
            $col = imagecolorallocate($img, $c[0], $c[1], $c[2]);

            $y = $legendY + $i * $itemH + 4;
            imagefilledellipse($img, $legendX + 7, $y + 4, 8, 8, $col);
            $name = $route['route_name'] ?? "Ruta {$i}";
            if (strlen($name) > 18) {
                $name = substr($name, 0, 16) . '..';
            }
            imagestring($img, 1, $legendX + 16, $y, $name, $black);
        }
    }
}
