<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ChartImageService
{
    public static function generateSimpleChart($logs)
    {
        $width = 700;
        $height = 300;

        $img = imagecreatetruecolor($width, $height);

        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);
        $red   = imagecolorallocate($img, 255, 99, 132);
        $blue  = imagecolorallocate($img, 54, 162, 235);

        imagefill($img, 0, 0, $white);

        // Axis
        imageline($img, 50, 20, 50, 260, $black);
        imageline($img, 50, 260, 680, 260, $black);

        $temps = $logs->pluck('temperature')->toArray();
        $hums  = $logs->pluck('humidity')->toArray();

        $maxTemp = max($temps);
        $maxHum  = max($hums);
        $maxVal  = max($maxTemp, $maxHum);

        $count = count($temps);
        if ($count < 2) return null;

        for ($i = 0; $i < $count - 1; $i++) {
            $x1 = 50 + ($i * 600 / $count);
            $x2 = 50 + (($i + 1) * 600 / $count);

            $y1t = 260 - ($temps[$i] / $maxVal * 220);
            $y2t = 260 - ($temps[$i + 1] / $maxVal * 220);

            $y1h = 260 - ($hums[$i] / $maxVal * 220);
            $y2h = 260 - ($hums[$i + 1] / $maxVal * 220);

            imageline($img, $x1, $y1t, $x2, $y2t, $red);
            imageline($img, $x1, $y1h, $x2, $y2h, $blue);
        }

        $path = 'charts/laporan_chart.png';
        Storage::disk('public')->put($path, '');

        imagepng($img, storage_path('app/public/' . $path));
        imagedestroy($img);

        return $path;
    }
}
