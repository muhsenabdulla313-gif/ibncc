<?php

namespace App\Http\Controllers\Admin;
use App\Models\HeaderNotice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HeaderNoticeController extends Controller
{
     public function index()
    {
        $notices = HeaderNotice::latest()->get();

        return view('admin.header-notice.index', compact('notices'));
    }

    public function store(Request $request)
    {
        HeaderNotice::create($this->validated($request));

        return back()->with('success', 'Notice added.');
    }

    public function update(Request $request, HeaderNotice $headerNotice)
    {
        $headerNotice->update($this->validated($request));

        return back()->with('success', 'Notice updated.');
    }

    public function destroy(HeaderNotice $headerNotice)
    {
        $headerNotice->delete();

        return back()->with('success', 'Notice deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'text' => 'required|string|max:500',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
