<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SharedProfile;

class ActivityController extends Controller
{
    public function index()
    {
        $me   = auth()->user();
        $with = ['profile.job', 'presentAddress.district', 'presentAddress.state'];

        $saved = $me->savedProfiles()->with($with)->orderByPivot('created_at', 'desc')->get();

        $blocked = $me->blockedUsers()->with($with)->orderByPivot('created_at', 'desc')->get();

        $shared = SharedProfile::where('user_id', $me->id)
            ->with(['recipient', 'target.profile.job', 'target.presentAddress.district', 'target.presentAddress.state'])
            ->latest()->get()
            ->filter(fn ($s) => $s->target)
            ->unique('shared_user_id')
            ->values();

        return view('members.activity', [
            'user'    => $me->load('profile'),
            'p'       => $me->profile,
            'saved'   => $saved,
            'shared'  => $shared,
            'blocked' => $blocked,
            'notice'  => config('site.notice'),
        ]);
    }
}
