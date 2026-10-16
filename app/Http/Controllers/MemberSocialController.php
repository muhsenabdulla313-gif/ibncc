<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdminMessage;
use App\Models\Message;
use App\Models\User;
use App\Models\SharedProfile;

class MemberSocialController extends Controller
{
    private function other(string $uuid): User
{
    $u = User::where('uuid', $uuid)->whereKeyNot(auth()->id())->first();

    abort_if(! $u, 422, 'Select a member first.');

    return $u;
}

    private function status(Message $m): string
    {
        return $m->read_at ? 'read' : ($m->delivered_at ? 'delivered' : 'sent');
    }

    private function payload(Message $m, User $me): array
    {
        $c = $m->created_at;
        return [
            'id'     => $m->id,
            'body'   => $m->body,
            'is_out' => $m->sender_id === $me->id,
            'status' => $this->status($m),
            'time'   => $c->format('g:i A'),
            'day'    => $c->isToday() ? 'Today' : ($c->isYesterday() ? 'Yesterday' : $c->format('j M Y')),
            'attachments' => collect($m->attachments ?? [])->map(fn ($a) => [
                'name' => $a['name'],
                'url'  => asset('storage/' . $a['path']),
                'is_image' => $a['is_image'],
            ])->all(),
        ];
    }

    /* ---------- save / block ---------- */

    public function toggleSave(string $uuid)
    {
        $me = auth()->user();
        $u = $this->other($uuid);

        if (in_array($u->id, $me->blockedIds(), true)) {
            return response()->json(['message' => 'Unblock this member first.'], 403);
        }

        $me->savedProfiles()->toggle($u->id);

        return response()->json(['saved' => $me->savedProfiles()->whereKey($u->id)->exists()]);
    }

    public function toggleBlock(string $uuid)
    {
        $me = auth()->user();
        $u = $this->other($uuid);

        if ($me->hasBlocked($u)) {
            $me->blockedUsers()->detach($u->id);
            return response()->json(['blocked' => false]);
        }

        $me->blockedUsers()->syncWithoutDetaching([$u->id]);
        $me->savedProfiles()->detach($u->id);

        return response()->json(['blocked' => true]);
    }

    /* ---------- messages ---------- */

    public function conversations()
    {
        $me = auth()->user();

        // mark everything received as delivered
        Message::where('receiver_id', $me->id)->whereNull('delivered_at')->update(['delivered_at' => now()]);

        $list = Message::conversationsFor($me);

        return response()->json([
            'conversations' => $list,
            'unread' => $list->where('unread', true)->count(),
        ]);
    }

    public function thread(Request $request, string $uuid)
    {
        $me = auth()->user();
        $other = $this->other($uuid);

        abort_if(in_array($other->id, $me->blockedIds(), true), 403, 'Conversation unavailable.');

        Message::where('sender_id', $other->id)->where('receiver_id', $me->id)->whereNull('read_at')
            ->update(['read_at' => now(), 'delivered_at' => now()]);

        $after = (int) $request->query('after', 0);
        $q = Message::between($me->id, $other->id);

        $msgs = $after
            ? $q->where('id', '>', $after)->orderBy('id')->get()
            : $q->latest('id')->limit(100)->get()->reverse()->values();

        $statuses = Message::where('sender_id', $me->id)->where('receiver_id', $other->id)
            ->latest('id')->limit(50)->get()
            ->mapWithKeys(fn ($m) => [$m->id => $this->status($m)]);

        return response()->json([
            'messages' => $msgs->map(fn ($m) => $this->payload($m, $me))->values(),
            'statuses' => $statuses,
        ]);
    }

    public function send(Request $request, string $uuid)
    {
        $me = auth()->user();
        $other = $this->other($uuid);

        if (in_array($other->id, $me->blockedIds(), true)) {
            return response()->json(['message' => "You can't message this member."], 403);
        }

        $request->validate([
            'body'    => 'nullable|string|max:2000',
            'files'   => 'nullable|array|max:5',
            'files.*' => 'file|max:5120|mimes:jpg,jpeg,png,webp,avif,gif,pdf,doc,docx,txt',
        ]);

        $files = [];
        foreach ($request->file('files', []) as $f) {
            $files[] = [
                'path' => $f->store('messages', 'public'),
                'name' => $f->getClientOriginalName(),
                'is_image' => str_starts_with((string) $f->getMimeType(), 'image/'),
            ];
        }

        $body = trim((string) $request->input('body'));
        if ($body === '' && ! $files) {
            return response()->json(['message' => 'Write a message or attach a file.'], 422);
        }

        $m = Message::create([
            'sender_id'   => $me->id,
            'receiver_id' => $other->id,
            'body'        => $body ?: null,
            'attachments' => $files ?: null,
        ]);

        return response()->json(['message' => $this->payload($m, $me)]);
    }

    /* ---------- admin messages ---------- */

    public function readAdmin()
    {
        AdminMessage::where('user_id', auth()->id())->whereNull('read_at')->update(['read_at' => now()]);
        return response()->json(['ok' => true]);
    }

    /* ---------- share ---------- */

    public function shareSearch(Request $request)
    {
        $me = auth()->user();
        $savedIds = $me->savedProfiles()->pluck('users.id')->all();

        $items = User::shareCandidates($me, trim((string) $request->query('q')))->map(fn ($m) => [
            'uuid'  => $m->uuid,
            'name'  => $m->name,
            'role'  => $m->profile?->job?->name ?: 'Member',
            'place' => $m->presentAddress?->district?->name,
            'photo' => $m->profile?->photo_url ?: asset('assets/images/no-image.avif'),
            'saved' => in_array($m->id, $savedIds, true),
        ])->values();

        return response()->json(['members' => $items]);
    }
    public function share(Request $request, string $uuid)
{
    $me = auth()->user();
    $request->validate(['to' => 'required|string']);

    $profile = User::where('uuid', $uuid)->firstOrFail();
    $to      = $this->other($request->input('to'));
    $blocked = $me->blockedIds();

    if (in_array($to->id, $blocked, true) || in_array($profile->id, $blocked, true)) {
        return response()->json(['message' => "You can't share this profile."], 403);
    }

    Message::create([
        'sender_id'   => $me->id,
        'receiver_id' => $to->id,
        'body'        => "Check out {$profile->name}'s profile on CC Hub: " . route('members.show', $profile->uuid),
    ]);

    SharedProfile::create([
        'user_id'        => $me->id,
        'shared_user_id' => $profile->id,
        'recipient_id'   => $to->id,
    ]);

    return response()->json(['ok' => true]);
}
}
