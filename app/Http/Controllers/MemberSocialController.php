<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdminMessage;
use App\Models\Message;
use App\Models\User;
use App\Models\SharedProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
class MemberSocialController extends Controller
{
    private function other(string $uuid): User
    {
        $u = User::with('role')->where('uuid', $uuid)->whereKeyNot(auth()->id())->first();

        abort_if(!$u, 422, 'Select a member first.');
        abort_if($u->isAdmin(), 403, 'You cannot message an admin.');

        return $u;
    }

    private function status(Message $m): string
    {
        return $m->read_at ? 'read' : ($m->delivered_at ? 'delivered' : 'sent');
    }



    /**
     * Search for people who can receive a shared profile.
     */



    /**
     * Share a member's profile with another member.
     */

    private function payload(Message $m, User $me): array
    {
        $c = $m->created_at;
        return [
            'id' => $m->id,
            'body' => $m->body,
            'is_out' => $m->sender_id === $me->id,
            'status' => $this->status($m),
            'time' => $c->format('g:i A'),
            'day' => $c->isToday() ? 'Today' : ($c->isYesterday() ? 'Yesterday' : $c->format('j M Y')),
            'attachments' => collect($m->attachments ?? [])->map(fn($a) => [
                'name' => $a['name'],
                'url' => asset('storage/' . $a['path']),
                'is_image' => $a['is_image'],
            ])->all(),
        ];
    }

    /* ---------- save / block ---------- */


    public function toggleSave(Request $request, string $uuid): JsonResponse
    {
        $me = $request->user();
        $target = User::where('uuid', $uuid)->firstOrFail();

        abort_if($target->id === $me->id, 422, 'You cannot save your own profile.');

        $row = ['user_id' => $me->id, 'saved_user_id' => $target->id];
        $state = $request->input('state');            // 'on' | 'off' | null
        $saved = DB::table('saved_profiles')->where($row)->exists();

        if ($state === 'off' || ($state === null && $saved)) {
            DB::table('saved_profiles')->where($row)->delete();      // <- removes it from the database
            return response()->json(['saved' => false, 'message' => 'Removed from saved profiles.']);
        }

        if (!$saved) {
            DB::table('saved_profiles')->insert($row + ['created_at' => now(), 'updated_at' => now()]);
        }

        return response()->json(['saved' => true, 'message' => 'Profile saved.']);
    }

    /** POST /profile/members/{uuid}/block */
    public function toggleBlock(Request $request, string $uuid): JsonResponse
    {
        $me = $request->user();
        $target = User::where('uuid', $uuid)->firstOrFail();

        abort_if($target->id === $me->id, 422, 'You cannot block yourself.');

        $row = ['user_id' => $me->id, 'blocked_user_id' => $target->id];
        $state = $request->input('state');          // 'on' | 'off' | null
        $blocked = DB::table('blocked_profiles')->where($row)->exists();

        if ($state === 'off' || ($state === null && $blocked)) {
            DB::table('blocked_profiles')->where($row)->delete();    // <- removes it from the database
            return response()->json(['blocked' => false, 'message' => 'Member unblocked.']);
        }

        if (!$blocked) {
            DB::table('blocked_profiles')->insert($row + ['created_at' => now(), 'updated_at' => now()]);
        }

        // blocking also removes any saved bookmark between the two of you
        DB::table('saved_profiles')
            ->where(fn($q) => $q->where(['user_id' => $me->id, 'saved_user_id' => $target->id]))
            ->orWhere(fn($q) => $q->where(['user_id' => $target->id, 'saved_user_id' => $me->id]))
            ->delete();

        return response()->json(['blocked' => true, 'message' => 'Member blocked.']);
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
            ->mapWithKeys(fn($m) => [$m->id => $this->status($m)]);

        return response()->json([
            'messages' => $msgs->map(fn($m) => $this->payload($m, $me))->values(),
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
            'body' => 'nullable|string|max:2000',
            'files' => 'nullable|array|max:5',
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
        if ($body === '' && !$files) {
            return response()->json(['message' => 'Write a message or attach a file.'], 422);
        }

        $m = Message::create([
            'sender_id' => $me->id,
            'receiver_id' => $other->id,
            'body' => $body ?: null,
            'attachments' => $files ?: null,
        ]);
        if (preg_match('#/members/([0-9a-f\-]{36})#i', $body, $mm)) {
            $shared = User::where('uuid', $mm[1])->first();
            if ($shared && $shared->id !== $me->id) {
                SharedProfile::firstOrCreate([
                    'user_id' => $me->id,
                    'shared_user_id' => $shared->id,
                    'recipient_id' => $other->id,
                ]);
            }
        }

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

        $items = User::shareCandidates($me, trim((string) $request->query('q')))->map(fn($m) => [
            'uuid' => $m->uuid,
            'name' => $m->name,
            'role' => $m->profile?->job?->name ?: 'Member',
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

        $profile = User::with('role')->where('uuid', $uuid)->firstOrFail();
        abort_if($profile->isAdmin(), 403, 'This profile cannot be shared.');
        $to = $this->other($request->input('to'));
        $blocked = $me->blockedIds();

        if (in_array($to->id, $blocked, true) || in_array($profile->id, $blocked, true)) {
            return response()->json(['message' => "You can't share this profile."], 403);
        }

        Message::create([
            'sender_id' => $me->id,
            'receiver_id' => $to->id,
            'body' => "Check out {$profile->name}'s profile on CC Hub: " . route('members.show', $profile->uuid),
        ]);

        SharedProfile::create([
            'user_id' => $me->id,
            'shared_user_id' => $profile->id,
            'recipient_id' => $to->id,
        ]);

        return response()->json(['ok' => true]);
    }
}
