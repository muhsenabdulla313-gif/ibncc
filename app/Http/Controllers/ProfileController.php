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
use App\Models\SocialLink;
use App\Models\State;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\{AdminMessage, Message};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
class ProfileController extends Controller
{
    private const SECTIONS = ['church', 'personal', 'contact', 'permanent', 'present', 'employment', 'job'];

    private const FIELDS = [
        'church' => ['rite', 'diocese', 'forane', 'parish'],
        'personal' => ['name', 'dob', 'gender', 'blood_group'],
        'contact' => ['phone', 'email', 'whatsapp', 'preferred_contact'],
        'permanent' => ['house', 'district', 'state', 'country', 'pin'],
        'present' => ['house', 'district', 'state', 'country', 'pin'],
        'employment' => ['employment_status', 'organization', 'work_mode', 'experience_years'],
        'job' => ['job_category', 'job'],
    ];

    /* ===================================================================
       GET /profile  : the owner's own page (same view as other members)
       =================================================================== */
    public function me()
    {
        $user = auth()->user()->load([
            'role',
            'socialLinks',
            'galleryImages',
            'parish.forane.diocese.rite',
            'profile.job.category',
            'permanentAddress.country',
            'permanentAddress.state',
            'permanentAddress.district',
            'presentAddress.country',
            'presentAddress.state',
            'presentAddress.district',
        ]);
        $p = $user->profile;

        $me = auth()->user();
        $isOwner = true;

        // the blocked person can't open the blocker's profile
        abort_if($user->id !== $me->id && $user->hasBlocked($me), 404);

        $isSaved = !$isOwner && $me->savedProfiles()->whereKey($user->id)->exists();
        $isBlocked = !$isOwner && $me->hasBlocked($user);
        $savedIds = $me->savedProfiles()->pluck('users.id')->all();
        $shareList = User::shareCandidates($me);

        $conversations = $isOwner ? Message::conversationsFor($me) : collect();
        $adminMessages = $isOwner
            ? AdminMessage::where('user_id', $me->id)->latest()->limit(20)->get()
            : collect();

        $names = fn($model) => $model::orderBy('name')->pluck('name')->unique()->values()->all();

        $opts = [
            'rites' => $names(Rite::class),
            'dioceses' => $names(Diocese::class),
            'foranes' => $names(Forane::class),
            'parishes' => $names(Parish::class),
            'countries' => $names(Country::class),
            'states' => $names(State::class),
            'districts' => $names(District::class),
            'genders' => config('options.genders'),
            'blood_groups' => config('options.blood_groups'),
            'employment_statuses' => config('options.employment_status'),
            'work_modes' => config('options.work_modes'),
            'experience' => ['0-2 years', '3-5 years', '6-10 years', '11-15 years', '16+ years'],
            'job_categories' => $names(JobCategory::class),
            'jobs' => $p->job
                ? Job::where('job_category_id', $p->job->job_category_id)->orderBy('name')->pluck('name')->all()
                : [],
        ];

        $jobsByCategory = JobCategory::with('jobs')->orderBy('name')->get()
            ->mapWithKeys(fn($c) => [$c->name => $c->jobs->pluck('name')->sort()->values()->all()])
            ->all();

        $similar = User::with('profile.job', 'presentAddress.district')
            ->whereHas('role', fn($q) => $q->whereNotIn('name', ['admin', 'guest']))
            ->where('id', '!=', $user->id)
            ->latest()->limit(7)->get();

        return view('members.show', [
            'user' => $user,
            'p' => $p,
            'present' => $user->presentAddress,
            'permanent' => $user->permanentAddress,
            'similar' => $similar,
            'isOwner' => $isOwner,
            'profileUrl' => route('members.show', $user->uuid),
            'notice' => config('site.notice', 'GLOBAL BUSINESS CONCLAVE — 13th September, 9 AM to 8 PM — Monsoon Empress Hotel, NH Bypass, Palarivattom, Kochi'),
            'hiddenSections' => $p->hiddenList(),
            'opts' => $opts,
            'jobsByCategory' => $jobsByCategory,

            // new
            'isSaved' => $isSaved,
            'isBlocked' => $isBlocked,
            'savedIds' => $savedIds,
            'shareList' => $shareList,
            'conversations' => $conversations,
            'adminMessages' => $adminMessages,
        ]);
    }


    public function update(Request $request): JsonResponse
    {
        $user = $request->user();


        return match ($request->input('action')) {
            'section' => $this->section($request, $user),
            'brief' => $this->brief($request, $user),
            'work' => $this->work($request, $user),
            'privacy' => $this->privacy($request, $user),
            'photo', 'cover' => $this->image($request, $user, $request->input('action')),
            'gallery' => $this->gallery($request, $user),
            'social.save' => $this->socialSave($request, $user),
            'social.delete' => $this->socialDelete($request, $user),
            'password' => $this->password($request, $user),
            default => abort(422, 'Unknown action.'),
        };
    }

    /* ---------------- detail sections (church, personal, ...) ---------------- */
    private function section(Request $request, User $user): JsonResponse
    {
        $section = (string) $request->input('section');
        abort_unless(isset(self::FIELDS[$section]), 422, 'Unknown section.');

        $raw = array_values((array) $request->input('values', []));
        $v = [];
        foreach (self::FIELDS[$section] as $i => $name) {
            $v[$name] = trim((string) ($raw[$i] ?? ''));
        }

        match ($section) {
            'church' => $this->saveChurch($user, $v),
            'personal' => $this->savePersonal($user, $v),
            'contact' => $this->saveContact($user, $v),
            'permanent', 'present' => $this->saveAddress($user, $section, $v),
            'employment' => $this->saveEmployment($user, $v),
            'job' => $this->saveJob($user, $v),
        };

        return response()->json(['message' => 'Saved']);
    }

    private function saveChurch(User $user, array $v): void
    {
        foreach (['rite' => 'Rite', 'diocese' => 'Diocese', 'forane' => 'Forane', 'parish' => 'Parish'] as $k => $label) {
            if ($v[$k] === '') {
                $this->fail($k, "Select your {$label}.");
            }
        }

        $parish = Parish::where('name', $v['parish'])
            ->whereHas('forane', fn($f) => $f->where('name', $v['forane'])
                ->whereHas('diocese', fn($d) => $d->where('name', $v['diocese'])
                    ->whereHas('rite', fn($r) => $r->where('name', $v['rite']))))
            ->first();

        if (!$parish) {
            $this->fail('parish', 'This Rite, Diocese, Forane and Parish combination does not exist. Pick from the lists.');
        }

        $user->update(['parish_id' => $parish->id]);
    }

    private function savePersonal(User $user, array $v): void
    {
        Validator::make($v, [
            'name' => 'required|string|max:100',
            'gender' => ['nullable', 'in:' . implode(',', config('options.genders'))],
            'blood_group' => ['nullable', 'in:' . implode(',', config('options.blood_groups'))],
        ])->validate();

        $dob = null;
        if ($v['dob'] !== '') {
            try {
                $dob = Carbon::parse($v['dob']);
            } catch (\Throwable) {
                $this->fail('dob', 'Enter a valid date of birth.');
            }
            if ($dob->isFuture() || $dob->year < 1900) {
                $this->fail('dob', 'Enter a valid date of birth.');
            }
        }

        $user->update(['name' => $v['name']]);
        $user->profile()->updateOrCreate([], [
            'dob' => $dob?->toDateString(),
            'gender' => $v['gender'] ?: null,
            'blood_group' => $v['blood_group'] ?: null,
        ]);
    }

    private function saveContact(User $user, array $v): void
    {
        $this->applyPhone($user, $v['phone']);
        $this->applyEmail($user, $v['email']);

        $whatsapp = $v['whatsapp'] !== '' ? substr(preg_replace('/\D+/', '', $v['whatsapp']), -10) : null;
        if ($whatsapp !== null && strlen($whatsapp) !== 10) {
            $this->fail('whatsapp', 'Enter a valid 10-digit WhatsApp number.');
        }

        $pref = mb_strtolower(trim($v['preferred_contact']));
        $preferred = match (true) {
            $pref === '' => null,
            str_contains($pref, 'both')
            || (str_contains($pref, 'phone') && str_contains($pref, 'whatsapp')) => 'both',
            str_contains($pref, 'whatsapp') => 'whatsapp',
            str_contains($pref, 'phone') => 'phone',
            default => $this->fail('preferred_contact', 'Choose Phone, WhatsApp or Both.'),
        };

        $user->profile()->updateOrCreate([], ['whatsapp' => $whatsapp, 'preferred_contact' => $preferred]);
    }

    /** Phone: no OTP. Must be a unique 10-digit number. */
    private function applyPhone(User $user, string $raw): void
    {
        $phone = substr(preg_replace('/\D+/', '', $raw), -10);
        if ($phone === '' || $phone === $user->phone) {
            return;
        }
        if (strlen($phone) !== 10) {
            $this->fail('phone', 'Enter a valid 10-digit mobile number.');
        }
        if (User::where('phone', $phone)->where('id', '!=', $user->id)->exists()) {
            $this->fail('phone', 'This mobile number is already registered.');
        }
        $user->update(['phone' => $phone]);
    }

    /** Email: saved as unverified when changed, so the Verify button + OTP popup appears. */
    private function applyEmail(User $user, string $raw): void
    {
        $email = mb_strtolower(trim($raw));
        if ($email === mb_strtolower((string) $user->email)) {
            return; // unchanged (or already verified through the OTP popup), keep email_verified_at
        }

        if ($email !== '') {
            Validator::make(['email' => $email], [
                'email' => ['email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            ], ['email.unique' => 'This email is already registered.'])->validate();
        }

        $user->forceFill(['email' => $email ?: null, 'email_verified_at' => null])->save();
    }

    private function saveAddress(User $user, string $type, array $v): void
    {
        if ($v['pin'] !== '' && !preg_match('/^\d{4,10}$/', $v['pin'])) {
            $this->fail('pin', 'Enter a valid PIN code.');
        }
        if (mb_strlen($v['house']) > 255) {
            $this->fail('house', 'House / Street is too long.');
        }

        $country = $state = $district = null;

        if ($v['country'] !== '') {
            $country = Country::where('name', $v['country'])->first()
                ?? $this->fail('country', 'Select a valid country from the list.');
        }
        if ($v['state'] !== '') {
            $state = State::where('name', $v['state'])
                ->when($country, fn($q) => $q->where('country_id', $country->id))->first()
                ?? $this->fail('state', 'Select a valid state from the list.');
        }
        if ($v['district'] !== '') {
            $district = District::where('name', $v['district'])
                ->when($state, fn($q) => $q->where('state_id', $state->id))->first()
                ?? $this->fail('district', 'Select a valid city / district from the list.');
        }

        $user->addresses()->updateOrCreate(['type' => $type], [
            'house' => $v['house'] ?: null,
            'country_id' => $country?->id,
            'state_id' => $state?->id,
            'district_id' => $district?->id,
            'pin' => $v['pin'] ?: null,
        ]);
    }

    private function saveEmployment(User $user, array $v): void
    {
        Validator::make($v, [
            'employment_status' => ['nullable', 'in:' . implode(',', config('options.employment_status'))],
            'organization' => 'nullable|string|max:255',
            'work_mode' => ['nullable', 'in:' . implode(',', config('options.work_modes'))],
        ], [
            'employment_status.in' => 'Select a valid employment status from the list.',
            'work_mode.in' => 'Select a valid work mode from the list.',
        ])->validate();

        // "12 years", "3-5 years", "16+ years"  ->  first number
        $years = null;
        if ($v['experience_years'] !== '') {
            if (!preg_match('/\d+/', $v['experience_years'], $m)) {
                $this->fail('experience_years', 'Enter your experience in years.');
            }
            $years = min((int) $m[0], 60);
        }

        $user->profile()->updateOrCreate([], [
            'employment_status' => $v['employment_status'] ?: null,
            'organization' => $v['organization'] ?: null,
            'work_mode' => $v['work_mode'] ?: null,
            'experience_years' => $years,
        ]);
    }

    private function saveJob(User $user, array $v): void
    {
        $job = null;

        if ($v['job_category'] !== '' || $v['job'] !== '') {
            if ($v['job_category'] === '' || $v['job'] === '') {
                $this->fail('job', 'Select both a category and a job.');
            }
            $job = Job::where('name', $v['job'])
                ->whereHas('category', fn($c) => $c->where('name', $v['job_category']))
                ->first()
                ?? $this->fail('job', 'This job does not belong to that category. Pick from the lists.');
        }

        $user->profile()->updateOrCreate([], ['job_id' => $job?->id]);
    }

    /* ---------------- profile brief ---------------- */
    private function brief(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'bio' => 'nullable|string|max:300',
            'status' => 'required|in:available,busy',
        ]);

        $user->update(['name' => $data['name']]);
        $user->profile()->updateOrCreate([], ['about' => $data['bio'] ?: null, 'status' => $data['status']]);

        if ($request->has('phone')) {
            $this->applyPhone($user, (string) $request->input('phone'));
        }
        if ($request->has('email')) {
            $this->applyEmail($user, (string) $request->input('email'));
        }

        return response()->json(['message' => 'Saved']);
    }

    /* ---------------- work details ---------------- */
    private function work(Request $request, User $user): JsonResponse
    {
        $data = $request->validate(['work_description' => 'nullable|string|max:1500']);

        $user->profile()->updateOrCreate([], ['work_description' => trim((string) ($data['work_description'] ?? '')) ?: null]);

        return response()->json(['message' => 'Saved']);
    }

    /* ---------------- eye icon: hide a section from other viewers ---------------- */
    private function privacy(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'section' => ['required', 'in:' . implode(',', self::SECTIONS)],
            'hidden' => 'required|boolean',
        ]);

        $list = $user->profile->hiddenList();
        $list = $request->boolean('hidden')
            ? array_values(array_unique(array_merge($list, [$data['section']])))
            : array_values(array_diff($list, [$data['section']]));

        $user->profile()->updateOrCreate([], ['hidden_sections' => $list]);

        return response()->json(['message' => 'Saved', 'hidden' => $list]);
    }

    /* ---------------- photo / cover ---------------- */
    private function image(Request $request, User $user, string $column): JsonResponse
    {
        $request->validate(['file' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096']);

        $old = $user->profile->{$column};
        $path = $request->file('file')->store("profiles/{$column}", 'public');

        $user->profile()->updateOrCreate([], [$column => $path]);

        if ($old) {
            Storage::disk('public')->delete($old);
        }

        return response()->json(['message' => 'Saved', 'url' => asset('storage/' . $path)]);
    }

    /* ---------------- gallery ---------------- */
    private function gallery(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'files' => 'required|array|max:10',
            'files.*' => 'image|mimes:jpg,jpeg,png,webp|max:4096',
            'title' => 'nullable|string|max:100',
        ]);

        foreach ($request->file('files') as $file) {
            $user->galleryImages()->create([
                'path' => $file->store('gallery', 'public'),
                'title' => $request->input('title') ?: null,
            ]);
        }

        return response()->json(['message' => 'Saved']);
    }

    /* ---------------- social links ---------------- */
    private function socialSave(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'id' => 'nullable|integer',
            'name' => ['required', 'in:' . implode(',', config('options.social_platforms'))],
            'url' => 'required|url|max:255',
        ], ['name.in' => 'Select a valid platform.', 'url.url' => 'Enter a full link starting with https://']);

        if (!empty($data['id'])) {
            $link = SocialLink::where('user_id', $user->id)->findOrFail($data['id']);

            $clash = SocialLink::where('user_id', $user->id)->where('name', $data['name'])->where('id', '!=', $link->id)->exists();
            if ($clash) {
                $this->fail('name', 'This platform is already added.');
            }

            $link->update(['name' => $data['name'], 'url' => $data['url']]);
        } else {
            $user->socialLinks()->updateOrCreate(['name' => $data['name']], ['url' => $data['url']]);
        }

        return response()->json(['message' => 'Saved']);
    }

    private function socialDelete(Request $request, User $user): JsonResponse
    {
        $data = $request->validate(['id' => 'required|integer']);
        SocialLink::where('user_id', $user->id)->where('id', $data['id'])->delete();

        return response()->json(['message' => 'Deleted']);
    }

    /* ---------------- change password ---------------- */
    private function password(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'current' => 'required|string',
            'new' => 'required|string|min:6|max:64',
            'confirm' => 'required|same:new',
        ], ['confirm.same' => 'Passwords do not match.']);

        if (!Hash::check($data['current'], $user->password)) {
            $this->fail('current', 'Current password is incorrect.');
        }

        $user->update(['password' => $data['new']]);     // hashed by the model cast

        return response()->json(['message' => 'Password updated']);
    }

    /* ---------------- helper ---------------- */
    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
   

}
