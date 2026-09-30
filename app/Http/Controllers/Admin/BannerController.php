<?php

namespace App\Http\Controllers\Admin;
use App\Models\Banner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    private const W = 1920;
    private const H = 700;
    private const DIR = 'uploads/banners';

    public function index()
    {
        $banners = Banner::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.banners.index', compact('banners'));
    }

    private function rules(bool $imageRequired): array
    {
        return [
            'image' => ($imageRequired ? 'required' : 'nullable') . '|image|mimes:jpg,jpeg,png,webp|max:8192',
            'link' => ['nullable', 'string', 'max:255', 'regex:/^(https?:\/\/|\/)/i'],
            'alt' => 'nullable|string|max:150',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }

    private function fields(Request $request): array
    {
        return [
            'link' => $request->input('link'),
            'open_new_tab' => $request->boolean('open_new_tab'),
            'alt' => $request->input('alt'),
            'sort_order' => $request->input('sort_order', 0),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    public function store(Request $request)
    {
        $request->validate($this->rules(true));

        Banner::create($this->fields($request) + [
            'image' => $this->saveImage($request->file('image')),
        ]);

        return back()->with('success', 'Banner added.');
    }

    public function update(Request $request, Banner $banner)
    {
        $request->validate($this->rules(false));

        $data = $this->fields($request);

        if ($request->hasFile('image')) {
            $this->deleteImage($banner->image);
            $data['image'] = $this->saveImage($request->file('image'));
        }

        $banner->update($data);

        return back()->with('success', 'Banner updated.');
    }



    public function destroy(Banner $banner)
    {
        $this->deleteImage($banner->image);
        $banner->delete();

        return back()->with('success', 'Banner deleted.');
    }

    private function saveImage($file): string
    {
        $src = imagecreatefromstring(file_get_contents($file->getRealPath()));
        $sw = imagesx($src);
        $sh = imagesy($src);

        // cover-crop to W x H
        $scale = max(self::W / $sw, self::H / $sh);
        $cw = (int) round(self::W / $scale);
        $ch = (int) round(self::H / $scale);
        $cx = (int) round(($sw - $cw) / 2);
        $cy = (int) round(($sh - $ch) / 2);

        $dst = imagecreatetruecolor(self::W, self::H);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
        imagecopyresampled($dst, $src, 0, 0, $cx, $cy, self::W, self::H, $cw, $ch);

        $dir = public_path(self::DIR);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $name = uniqid('banner_') . '.jpg';
        imagejpeg($dst, $dir . '/' . $name, 80);

        imagedestroy($src);
        imagedestroy($dst);

        return self::DIR . '/' . $name;
    }

    private function deleteImage(?string $path): void
    {
        if ($path && file_exists(public_path($path))) {
            @unlink(public_path($path));
        }
    }
}
