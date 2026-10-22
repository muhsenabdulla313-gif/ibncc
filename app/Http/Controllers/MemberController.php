<?php

namespace App\Http\Controllers;

use App\Models\User;

class MemberController extends Controller
{

public function me()
{
    abort_unless(auth()->check(), 401);
    return $this->show(auth()->user());
}

    public function show(User $user)
    {
        if (! auth()->check()) {
    return redirect('/#login');
}
    abort_if(in_array($user->role?->name, ['admin', 'guest'], true), 404);

        $user->load([
            'role', 'socialLinks', 'galleryImages',
            'parish.forane.diocese.rite',
            'profile.job.category',
            'permanentAddress.country', 'permanentAddress.state', 'permanentAddress.district',
            'presentAddress.country', 'presentAddress.state', 'presentAddress.district',
        ]);

        $p = $user->profile;

        $similar = User::with([
                'profile.job', 'parish.forane.diocese.rite',
                'presentAddress.state', 'presentAddress.district',
            ])
            ->where('id', '!=', $user->id)
        ->whereHas('role', fn ($q) => $q->whereNotIn('name', ['admin', 'guest']))
            ->when($p->job_id, fn ($q, $jobId) => $q->whereHas('profile', fn ($pr) => $pr->where('job_id', $jobId)))
            ->limit(3)
            ->get();

        return view('members.show', [
            'user'       => $user,
            'p'          => $p,
            'present'    => $user->presentAddress,
            'permanent'  => $user->permanentAddress,
            'similar'    => $similar,
            'isOwner'    => auth()->id() === $user->id,
            'profileUrl' => route('members.show', $user),
            'notice'     => config('site.notice', 'GLOBAL BUSINESS CONCLAVE — 13th September, 9 AM to 8 PM — Monsoon Empress Hotel, NH Bypass, Palarivattom, Kochi'),
        ]);
    }
}
