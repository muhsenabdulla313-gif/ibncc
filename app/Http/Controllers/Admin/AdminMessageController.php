<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\AdminMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
class AdminMessageController extends Controller
{
      public function index(Request $request)
    {
        if (! $request->expectsJson()) {
            return view('admin.admin-messages.index');
        }
 
        $search = trim((string) $request->query('q', ''));
 
        $page = AdminMessage::with('user:id,name,phone')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($w) use ($search) {
                    $w->where('subject', 'like', "%{$search}%")
                      ->orWhere('body', 'like', "%{$search}%")
                      ->orWhereHas('user', fn ($u) => $u
                          ->where('name', 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(20);
 
        return response()->json([
            'data'  => $page->getCollection()->map(fn ($m) => $this->row($m))->values(),
            'page'  => $page->currentPage(),
            'last'  => $page->lastPage(),
            'total' => $page->total(),
        ]);
    }
 
    /* POST /admin/admin-messages : one member (by phone) or all members */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'audience' => 'required|in:one,all',
            'phone'    => 'required_if:audience,one|nullable|digits:10',
            'subject'  => 'required|string|max:150',
            'body'     => 'required|string|max:2000',
        ], [
            'phone.required_if' => "Enter the member's 10-digit phone number.",
            'phone.digits'      => 'Phone number must be exactly 10 digits.',
        ]);
 
        if ($data['audience'] === 'one') {
            $user = User::where('phone', $data['phone'])->first();
            if (! $user) {
                throw ValidationException::withMessages(['phone' => 'No member found with this phone number.']);
            }
 
            AdminMessage::create([
                'user_id' => $user->id,
                'subject' => $data['subject'],
                'body'    => $data['body'],
            ]);
 
            return response()->json(['message' => "Message sent to {$user->name}."], 201);
        }
 
        $now = now();
        $count = 0;
 
        User::whereHas('role', fn ($r) => $r->where('name', 'member'))
            ->select('id')
            ->chunkById(500, function ($users) use ($data, $now, &$count) {
                $rows = $users->map(fn ($u) => [
                    'user_id'    => $u->id,
                    'subject'    => $data['subject'],
                    'body'       => $data['body'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();
 
                AdminMessage::insert($rows);
                $count += count($rows);
            });
 
        return response()->json(['message' => "Message sent to {$count} members."], 201);
    }
 
    /* PUT /admin/admin-messages/{adminMessage} */
    public function update(Request $request, AdminMessage $adminMessage): JsonResponse
    {
        $data = $request->validate([
            'subject' => 'required|string|max:150',
            'body'    => 'required|string|max:2000',
        ]);
 
        if ($request->boolean('mark_unread')) {
            $data['read_at'] = null;
        }
 
        $adminMessage->update($data);
 
        return response()->json(['message' => 'Message updated.']);
    }
 
    /* DELETE /admin/admin-messages/{adminMessage} */
    public function destroy(AdminMessage $adminMessage): JsonResponse
    {
        $adminMessage->delete();
 
        return response()->json(['message' => 'Message deleted.']);
    }
 
    private function row(AdminMessage $m): array
    {
        return [
            'id'      => $m->id,
            'name'    => $m->user?->name ?? 'Deleted user',
            'phone'   => $m->user?->phone,
            'subject' => $m->subject,
            'body'    => $m->body,
            'read'    => (bool) $m->read_at,
            'sent'    => $m->created_at?->format('d M Y, h:i A'),
        ];
    }
}
