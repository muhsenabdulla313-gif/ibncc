<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Diocese;
use App\Models\District;
use App\Models\Forane;
use App\Models\Job;
use App\Models\JobCategory;
use App\Models\Parish;
use App\Models\Rite;
use App\Models\State;
use App\Models\User;
use Illuminate\Http\Request;

class NetworkingController extends Controller
{
    private const AGE_MAX = 80;      // slider max = "no age limit"
 
    public function index(Request $request)
    {
        if (! auth()->check()) {
            return redirect('/#login');                  // members only
        }
 
        $me       = auth()->user()->load('role', 'presentAddress');
        $mine     = $me->presentAddress;
        $isAdmin  = $me->role?->name === 'admin';
        $official = $isAdmin && $request->boolean('official');
        $age      = (int) $request->input('age', self::AGE_MAX);
        $sort     = $request->input('sort', 'recent');
 
        $users = User::with([
                'profile.job', 'parish.forane.diocese.rite',
                'presentAddress.country', 'presentAddress.state', 'presentAddress.district',
            ])
            ->whereHas('role', fn ($q) => $q->whereNotIn('name', ['admin', 'guest']))
            ->where('id', '!=', $me->id)
 
            // search: name or job name
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;
                $q->where(fn ($w) => $w
                    ->where('name', 'like', "%{$s}%")
                    ->orWhereHas('profile.job', fn ($j) => $j->where('name', 'like', "%{$s}%")));
            })
 
            // church chain
            ->when($request->filled('rite_id'),    fn ($q) => $q->whereHas('parish.forane.diocese', fn ($d) => $d->where('rite_id', $request->rite_id)))
            ->when($request->filled('diocese_id'), fn ($q) => $q->whereHas('parish.forane', fn ($f) => $f->where('diocese_id', $request->diocese_id)))
            ->when($request->filled('forane_id'),  fn ($q) => $q->whereHas('parish', fn ($p) => $p->where('forane_id', $request->forane_id)))
            ->when($request->filled('parish_id'),  fn ($q) => $q->where('parish_id', $request->parish_id))
 
            // location = present address
            ->when($request->filled('country_id') || $request->filled('state_id') || $request->filled('district_id'),
                fn ($q) => $q->whereHas('presentAddress', function ($a) use ($request) {
                    foreach (['country_id', 'state_id', 'district_id'] as $key) {
                        if ($request->filled($key)) { $a->where($key, $request->input($key)); }
                    }
                }))
 
            // employment + job
            ->when($request->filled('employment_status'), fn ($q) => $q->whereHas('profile', fn ($p) => $p->where('employment_status', $request->employment_status)))
            ->when($request->filled('job_id'),            fn ($q) => $q->whereHas('profile', fn ($p) => $p->where('job_id', $request->job_id)))
 
            // admin-only ("Filter Official")
            ->when($official && $request->filled('job_category_id'), fn ($q) => $q->whereHas('profile.job', fn ($j) => $j->where('job_category_id', $request->job_category_id)))
            ->when($official && $request->filled('official_job_id'), fn ($q) => $q->whereHas('profile', fn ($p) => $p->where('job_id', $request->official_job_id)))
            ->when($official && $request->filled('gender'),          fn ($q) => $q->whereHas('profile', fn ($p) => $p->where('gender', $request->gender)))
            ->when($official && $request->filled('blood_group'),     fn ($q) => $q->whereHas('profile', fn ($p) => $p->where('blood_group', $request->blood_group)))
            ->when($official && $age < self::AGE_MAX,                fn ($q) => $q->whereHas('profile', fn ($p) => $p->where('dob', '>=', now()->subYears($age + 1)->addDay())))
 
            // sorting
            ->tap(function ($q) use ($sort, $mine) {
                if ($sort === 'name') {
                    $q->orderBy('name');
                } elseif ($sort === 'nearest' && $mine) {
                    // same district first, then same state, then newest
                    $q->orderByRaw('(select count(*) from addresses a where a.user_id = users.id and a.type = ? and a.district_id <=> ?) desc', ['present', $mine->district_id])
                      ->orderByRaw('(select count(*) from addresses a where a.user_id = users.id and a.type = ? and a.state_id <=> ?) desc', ['present', $mine->state_id])
                      ->latest();
                } else {
                    $q->latest();
                }
            })
            ->paginate(12)
            ->withQueryString();
 
        return view('networking.index', [
            'users'              => $users,
            'isAdmin'            => $isAdmin,
            'currentLocation'    => collect([$mine?->district?->name, $mine?->state?->name, $mine?->country?->name])->filter()->implode(', ') ?: 'Worldwide',
 
            // parents: full lists
            'countries'          => Country::orderBy('name')->get(),
            'rites'              => Rite::orderBy('name')->get(),
            'jobCategories'      => JobCategory::orderBy('name')->get(),
            'professions'        => Job::orderBy('name')->get(),
 
            // children: only what belongs to the selected parent (keeps the chain after a reload)
            'states'             => State::where('country_id', $request->input('country_id', 0))->orderBy('name')->get(),
            'districts'          => District::where('state_id', $request->input('state_id', 0))->orderBy('name')->get(),
            'dioceses'           => Diocese::where('rite_id', $request->input('rite_id', 0))->orderBy('name')->get(),
            'foranes'            => Forane::where('diocese_id', $request->input('diocese_id', 0))->orderBy('name')->get(),
            'parishes'           => Parish::where('forane_id', $request->input('forane_id', 0))->orderBy('name')->get(),
            'jobs'               => Job::where('job_category_id', $request->input('job_category_id', 0))->orderBy('name')->get(),
 
            // fixed lists
            'employmentStatuses' => config('options.employment_status'),
            'genders'            => config('options.genders'),
            'bloodGroups'        => config('options.blood_groups'),
        ]);
    }
}
