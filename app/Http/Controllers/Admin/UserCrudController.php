<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserCrudController extends Controller
{
    /** GET /admin/users : the one page */
    public function index()
    {
        return view('admin.users', [
            'roles' => Role::orderBy('id')->get(['id', 'name']),
        ]);
    }

    /** GET /admin/users/data : list + search + filter + pagination (JSON) */
    public function data(Request $r): JsonResponse
    {
        $s = trim((string) $r->query('search'));

        $page = User::with(['role', 'profile.job'])
            ->when($s !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$s}%")
                ->orWhere('phone', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")))
            ->when($r->filled('role_id'), fn ($q) => $q->where('role_id', $r->query('role_id')))
            ->latest('id')
            ->paginate(10);

        return response()->json([
            'data' => $page->getCollection()->map(fn ($u) => $this->row($u))->values(),
            'meta' => [
                'page'  => $page->currentPage(),
                'last'  => $page->lastPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    /** GET /admin/users/{id} : full details for the View popup */
    public function show(int $id): JsonResponse
    {
        $u = User::with([
            'role', 'profile.job.category',
            'parish.forane.diocese.rite',
            'presentAddress.district', 'presentAddress.state', 'presentAddress.country',
            'permanentAddress.district', 'permanentAddress.state', 'permanentAddress.country',
        ])->findOrFail($id);

        $p = $u->profile;
        $addr = fn ($a) => $a
            ? collect([$a->house, $a->district?->name, $a->state?->name, $a->country?->name, $a->pin])->filter()->implode(', ')
            : null;

        return response()->json($this->row($u) + [
            'uuid'      => $u->uuid,
            'rite'      => $u->rite_name,
            'diocese'   => $u->diocese_name,
            'forane'    => $u->forane_name,
            'parish'    => $u->parish?->name,
            'dob'       => $p->dob?->format('j M Y'),
            'gender'    => $p->gender,
            'blood'     => $p->blood_group,
            'whatsapp'  => $p->whatsapp,
            'employment'=> $p->employment_status,
            'org'       => $p->organization,
            'category'  => $p->job?->category?->name,
            'about'     => $p->about,
            'present'   => $addr($u->presentAddress),
            'permanent' => $addr($u->permanentAddress),
        ]);
    }

    /** POST /admin/users */
    public function store(Request $r): JsonResponse
    {
        $data = $this->validated($r);
        $data['password'] = $r->validate(['password' => 'required|string|min:6|max:64'])['password'];

        $u = User::create($data);

        return response()->json(['message' => 'User created.', 'id' => $u->id], 201);
    }

    /** PUT /admin/users/{id} */
    public function update(Request $r, int $id): JsonResponse
    {
        $u = User::findOrFail($id);
        $data = $this->validated($r, $u->id);

        if ($u->id === Auth::guard('admin')->id() && (int) $data['role_id'] !== (int) $u->role_id) {
            return response()->json(['message' => 'You cannot change your own role.'], 422);
        }

        if ($r->filled('password')) {
            $data['password'] = $r->validate(['password' => 'string|min:6|max:64'])['password'];
        }

        // keep the old verified time if the email did not change and stays verified
        if ($data['email_verified_at'] && $u->email_verified_at && $data['email'] === $u->email) {
            $data['email_verified_at'] = $u->email_verified_at;
        }

        $u->update($data);

        return response()->json(['message' => 'User updated.']);
    }

    /** DELETE /admin/users/{id} */
    public function destroy(int $id): JsonResponse
    {
        $u = User::findOrFail($id);

        if ($u->id === Auth::guard('admin')->id()) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        try {
            $u->delete();
        } catch (QueryException $e) {
            return response()->json(['message' => 'This user has linked records and cannot be deleted.'], 409);
        }

        return response()->json(['message' => 'User deleted.']);
    }

    /* ---------------- helpers ---------------- */

    private function validated(Request $r, ?int $ignore = null): array
    {
        $r->merge([
            'phone' => substr(preg_replace('/\D+/', '', (string) $r->input('phone')), -10),
            'email' => ($e = mb_strtolower(trim((string) $r->input('email')))) !== '' ? $e : null,
        ]);

        $v = $r->validate([
            'name'     => 'required|string|max:100',
            'phone'    => ['required', 'digits:10', Rule::unique('users', 'phone')->ignore($ignore)],
            'email'    => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($ignore)],
            'role_id'  => 'required|exists:roles,id',
            'verified' => 'nullable|boolean',
        ], [
            'phone.digits'  => 'Enter a valid 10-digit phone number.',
            'phone.unique'  => 'This phone number is already registered.',
            'email.unique'  => 'This email is already registered.',
        ]);

        unset($v['verified']);
        $v['email_verified_at'] = ($r->boolean('verified') && $v['email']) ? now() : null;

        return $v;
    }

    private function row(User $u): array
    {
        return [
            'id'       => $u->id,
            'name'     => $u->name,
            'email'    => $u->email,
            'phone'    => $u->phone,
            'role_id'  => $u->role_id,
            'role'     => $u->role?->name,
            'verified' => (bool) $u->email_verified_at,
            'photo'    => $u->profile->photo_url ?: asset('assets/images/no-image.avif'),
            'job'      => $u->profile->job?->name,
            'joined'   => $u->created_at?->format('j M Y'),
        ];
    }
}
