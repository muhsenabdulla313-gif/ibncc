<?php

namespace App\Services;

/** Resize + save an uploaded image with plain PHP GD (no Composer package needed). */
class ImageResizer
{
    /**
     * Scales down (never up) so the longest side is <= $max and saves to "$destNoExt.jpg|png".
     * Returns the extension used.
     */
    public static function save(string $src, string $destNoExt, int $max): string
    {
        $info = @getimagesize($src);
        if (!$info) abort(422, 'Invalid image file.');
        [$w, $h, $type] = $info;

        $img = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($src),
            IMAGETYPE_PNG  => imagecreatefrompng($src),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($src) : false,
            IMAGETYPE_GIF  => imagecreatefromgif($src),
            default        => false,
        };
        if (!$img) abort(422, 'Unsupported image type. Use JPG, PNG, WEBP or GIF.');

        // respect phone-camera rotation
        if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $o = @exif_read_data($src)['Orientation'] ?? 1;
            $rot = [3 => 180, 6 => -90, 8 => 90][$o] ?? 0;
            if ($rot) { $img = imagerotate($img, $rot, 0); $w = imagesx($img); $h = imagesy($img); }
        }

        $ratio = min(1, $max / max($w, $h));
        $nw = max(1, (int) round($w * $ratio));
        $nh = max(1, (int) round($h * $ratio));

        $png = $type === IMAGETYPE_PNG;
        $out = imagecreatetruecolor($nw, $nh);
        if ($png) {
            imagealphablending($out, false);
            imagesavealpha($out, true);
            imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));
        } else {
            imagefill($out, 0, 0, imagecolorallocate($out, 255, 255, 255));
        }
        imagecopyresampled($out, $img, 0, 0, 0, 0, $nw, $nh, imagesx($img), imagesy($img));

        $png ? imagepng($out, $destNoExt . '.png', 6) : imagejpeg($out, $destNoExt . '.jpg', 82);
        return $png ? 'png' : 'jpg';
    }
}
