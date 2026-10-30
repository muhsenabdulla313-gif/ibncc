<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventImage;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with('images')->orderByDesc('event_date')->get();

        return view('admin.events.index', compact('events'));
    }

    public function store(Request $r)
    {
        $data = Arr::except($r->validate($this->rules(true)), ['gallery', 'cover_image', 'qr_image']);
        $data['is_active']   = $r->boolean('is_active');
        $data['slug']        = $this->slug($data['title']);
        $data['cover_image'] = $this->save($r->file('cover_image'));
        if ($r->hasFile('qr_image')) {
            $data['qr_image'] = $this->save($r->file('qr_image'), 600, true);
        }

        $event = Event::create($data);
        $this->saveGallery($r, $event);

        return back()->with('success', 'Event added.');
    }

    public function update(Request $r, Event $event)
    {
        $data = Arr::except($r->validate($this->rules(false)), ['gallery', 'cover_image', 'qr_image']);
        $data['is_active'] = $r->boolean('is_active');

        if ($r->hasFile('cover_image')) {
            $this->remove($event->cover_image);
            $data['cover_image'] = $this->save($r->file('cover_image'));
        }
        if ($r->hasFile('qr_image')) {
            $this->remove($event->qr_image);
            $data['qr_image'] = $this->save($r->file('qr_image'), 600, true);
        }

        $event->update($data);
        $this->saveGallery($r, $event);

        return back()->with('success', 'Event updated.');
    }

    public function destroy(Event $event)
    {
        $this->remove($event->cover_image);
        $this->remove($event->qr_image);
        foreach ($event->images as $img) {
            $this->remove($img->image);
        }
        $event->delete();

        return back()->with('success', 'Event deleted.');
    }

    public function destroyImage(EventImage $image)
    {
        $this->remove($image->image);
        $image->delete();

        return response()->json(['ok' => true]);
    }

    /* ---------- helpers ---------- */

    private function rules(bool $creating): array
    {
        return [
            'title'         => 'required|string|max:200',
            'tagline'       => 'nullable|string|max:200',
            'event_date'    => 'required|date',
            'time_text'     => 'nullable|string|max:100',
            'venue'         => 'nullable|string|max:255',
            'city'          => 'nullable|string|max:120',
            'intro'         => 'nullable|string',
            'highlights'    => 'nullable|string',
            'register_url'  => 'nullable|url|max:255',
            'contact_phone' => 'nullable|string|max:30',
            'contact_email' => 'nullable|email|max:150',
            'cover_image'   => ($creating ? 'required' : 'nullable') . '|image|max:5120',
            'qr_image'      => 'nullable|image|max:2048',
            'gallery.*'     => 'image|max:5120',
        ];
    }

    private function slug(string $title): string
    {
        $base = Str::slug($title) ?: 'event';
        $slug = $base;
        $i = 2;
        while (Event::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    private function saveGallery(Request $r, Event $event): void
    {
        foreach ((array) $r->file('gallery') as $file) {
            $event->images()->create([
                'image'      => $this->save($file),
                'sort_order' => $event->images()->count(),
            ]);
        }
    }

    // resize + compress (Intervention Image v3, GD) -> returns path relative to /public
    private function save(UploadedFile $file, int $width = 1600, bool $png = false): string
    {
        $dir = public_path('uploads/events');
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $name  = Str::random(20) . ($png ? '.png' : '.webp');
        $image = (new ImageManager(new Driver()))->read($file)->scaleDown(width: $width);
        ($png ? $image->toPng() : $image->toWebp(80))->save($dir . '/' . $name);

        return 'uploads/events/' . $name;
    }

    private function remove(?string $path): void
    {
        if ($path && Str::startsWith($path, 'uploads/events/')) {
            @unlink(public_path($path));
        }
    }
}
