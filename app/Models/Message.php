<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
class Message extends Model
{
    protected $fillable = ['sender_id', 'receiver_id', 'body', 'attachments', 'delivered_at', 'read_at'];

    protected $casts = [
        'attachments' => 'array',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function scopeBetween($q, int $a, int $b)
    {
        return $q->where(function ($x) use ($a, $b) {
            $x->where(fn ($y) => $y->where('sender_id', $a)->where('receiver_id', $b))
              ->orWhere(fn ($y) => $y->where('sender_id', $b)->where('receiver_id', $a));
        });
    }

    public static function conversationsFor(User $me): Collection
    {
        $blocked = $me->blockedIds();

        $rows = static::query()
            ->selectRaw('CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END AS partner_id, MAX(id) AS last_id', [$me->id])
            ->where(fn ($q) => $q->where('sender_id', $me->id)->orWhere('receiver_id', $me->id))
            ->groupBy('partner_id')
            ->orderByDesc('last_id')
            ->limit(50)
            ->get()
            ->reject(fn ($r) => in_array((int) $r->partner_id, $blocked, true));

        if ($rows->isEmpty()) {
            return collect();
        }

        $last   = static::whereIn('id', $rows->pluck('last_id'))->get()->keyBy('id');
        $users  = User::with('profile')->whereIn('id', $rows->pluck('partner_id'))->get()->keyBy('id');
        $unread = static::where('receiver_id', $me->id)->whereNull('read_at')
            ->selectRaw('sender_id, COUNT(*) AS c')->groupBy('sender_id')->pluck('c', 'sender_id');

        $noImg = asset('assets/images/no-image.avif');

        return $rows->map(function ($r) use ($last, $users, $unread, $me, $noImg) {
            $u = $users[$r->partner_id] ?? null;
            $m = $last[$r->last_id] ?? null;
            if (! $u || ! $m) {
                return null;
            }
            $text = $m->body ?: 'Attachment';
            return (object) [
                'uuid'      => $u->uuid,
                'name'      => $u->name,
                'photo_url' => $u->profile?->photo_url ?: $noImg,
                'preview'   => ($m->sender_id === $me->id ? 'You: ' : '') . Str::limit($text, 40),
                'online'    => false,
                'unread'    => ($unread[$u->id] ?? 0) > 0,
                    'messages'  => collect(),

            ];
        })->filter()->values();
    }
}
