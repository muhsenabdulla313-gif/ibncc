<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Event extends Model
{
    protected $guarded = [];

    protected $casts = [
        'event_date' => 'date',
        'is_active'  => 'boolean',
    ];

    public function images()
    {
        return $this->hasMany(EventImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

   public function scopeUpcoming($q)
{
    return $q->active()->whereDate('event_date', '>=', today())->orderBy('event_date');
}

public function scopePrevious($q)
{
    return $q->active()->whereDate('event_date', '<', today())->orderByDesc('event_date');
}

public function getIsUpcomingAttribute(): bool
{
    return $this->event_date->gte(today());
}

    // full http(s) urls stay as they are, anything else is relative to /public
    public static function assetUrl(?string $path): ?string
    {
        if (!$path) return null;
        return Str::startsWith($path, ['http://', 'https://']) ? $path : asset($path);
    }

    public function getCoverUrlAttribute(): ?string
    {
        return static::assetUrl($this->cover_image);
    }

    public function getQrUrlAttribute(): ?string
    {
        return static::assetUrl($this->qr_image);
    }

    // slider images for the "View More" modal (falls back to cover)
    public function getSlideUrlsAttribute(): array
    {
        $urls = $this->images->map(fn ($i) => $i->url)->all();
        return $urls ?: array_filter([$this->cover_url]);
    }

    // "IBNCC Global" + "Business Conclave" (last two words get the accent style)
    public function getTitleFirstAttribute(): string
    {
        $w = explode(' ', trim($this->title));
        return count($w) > 2 ? implode(' ', array_slice($w, 0, -2)) : '';
    }

    public function getTitleAccentAttribute(): string
    {
        $w = explode(' ', trim($this->title));
        return count($w) > 2 ? implode(' ', array_slice($w, -2)) : $this->title;
    }

    public function getVenueNameAttribute(): string
    {
        return trim(Str::before((string) $this->venue, ','));
    }

    public function getVenueAddressAttribute(): string
    {
        $v = (string) $this->venue;
        return str_contains($v, ',') ? trim(Str::after($v, ',')) : '';
    }

    /*
     * highlights textarea format:
     *   Card title
     *   Bullet heading | bullet description
     *   Bullet heading | bullet description
     *
     *   Next card title
     *   ...
     * (blank line separates cards)
     */
    public function getHighlightCardsAttribute(): array
    {
        $cards = [];
        foreach (preg_split('/\R\s*\R/', trim((string) $this->highlights)) as $block) {
            $lines = array_values(array_filter(array_map('trim', preg_split('/\R/', $block))));
            if (!$lines) continue;
            $title = array_shift($lines);
            $bullets = array_map(
                fn ($l) => array_pad(array_map('trim', explode('|', $l, 2)), 2, ''),
                $lines
            );
            $cards[] = ['title' => $title, 'bullets' => $bullets];
        }
        return $cards;
    }

    public function adminPayload(): array
    {
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'tagline'       => $this->tagline,
            'event_date'    => $this->event_date?->format('Y-m-d'),
            'time_text'     => $this->time_text,
            'venue'         => $this->venue,
            'city'          => $this->city,
            'intro'         => $this->intro,
            'highlights'    => $this->highlights,
            'register_url'  => $this->register_url,
            'contact_phone' => $this->contact_phone,
            'contact_email' => $this->contact_email,
            'is_active'     => $this->is_active,
            'cover_url'     => $this->cover_url,
            'qr_url'        => $this->qr_url,
            'images'        => $this->images->map(fn ($i) => ['id' => $i->id, 'url' => $i->url])->all(),
        ];
    }
}
