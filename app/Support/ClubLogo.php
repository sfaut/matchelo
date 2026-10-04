<?php

namespace App\Support;

use App\Models\Club;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Enregistre le logo d'un club : image recadrée au centre en carré, redimensionnée et
 * convertie en WebP sur le disque public (GD, sans dépendance).
 */
class ClubLogo
{
    /**
     * Côté du logo enregistré, en pixels.
     *
     * @var int
     */
    public const int SIZE = 256;

    /**
     * Qualité WebP (0 à 100).
     *
     * @var int
     */
    public const int QUALITY = 85;

    /**
     * Recadre, redimensionne et enregistre le logo, puis renvoie son chemin sur le disque public.
     *
     * @param  UploadedFile  $file  image envoyée (PNG, JPEG ou WebP, déjà validée)
     * @param  Club  $club  club auquel appartient le logo
     *
     * @throws RuntimeException si l'image ne peut pas être lue ou encodée
     */
    public function store(UploadedFile $file, Club $club): string
    {
        $source = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));

        if ($source === false) {
            throw new RuntimeException('Image de logo illisible.');
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $side = min($width, $height);

        $logo = imagecreatetruecolor(self::SIZE, self::SIZE);
        imagealphablending($logo, false);
        imagesavealpha($logo, true);
        imagecopyresampled(
            $logo, $source,
            0, 0, intdiv($width - $side, 2), intdiv($height - $side, 2),
            self::SIZE, self::SIZE, $side, $side,
        );

        ob_start();
        $encoded = imagewebp($logo, null, self::QUALITY);
        $contents = (string) ob_get_clean();

        if (! $encoded) {
            throw new RuntimeException('Encodage WebP du logo impossible.');
        }

        $path = "clubs/logos/{$club->id}.webp";
        Storage::disk('public')->put($path, $contents);

        return $path;
    }
}
