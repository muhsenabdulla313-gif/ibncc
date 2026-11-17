<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/** Stores product images in public/uploads/products (cPanel friendly, no storage:link). Plain GD, no extra package. */
class ProductImageService
{
    public function store(Product $product, UploadedFile $file, int $sort): ProductImage
    {
        $dir = public_path('uploads/products');
        File::ensureDirectoryExists($dir);

        $base = $product->id . '-' . Str::lower(Str::random(12));
        $ext  = ImageResizer::save($file->getRealPath(), $dir . DIRECTORY_SEPARATOR . $base, 1400);
        $name = $base . '.' . $ext;

        return $product->images()->create([
            'path'       => 'uploads/products/' . $name,
            'alt'        => $product->title,
            'sort_order' => $sort,
            'is_primary' => false,
        ]);
    }

    public function delete(ProductImage $image): void
    {
        if (str_starts_with($image->path, 'uploads/products/')) {
            File::delete(public_path($image->path));
        }
        $image->delete();
    }
}
