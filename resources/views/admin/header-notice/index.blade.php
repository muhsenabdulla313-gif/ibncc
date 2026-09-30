@extends('layouts.admin')

@section('body')
<div class="page-inner">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Header Notices</h3>
        <button class="btn btn-primary btn-round" id="btnAdd">
            <i class="fa fa-plus"></i> Add Notice
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Text</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notices as $n)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $n->text }}</td>
                            <td>
                                <span class="badge bg-{{ $n->is_active ? 'success' : 'secondary' }}">
                                    {{ $n->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-info btn-edit"
                                    data-url="{{ route('admin.header-notice.update', $n) }}"
                                    data-text="{{ $n->text }}"
                                    data-active="{{ $n->is_active ? 1 : 0 }}">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.header-notice.destroy', $n) }}"
                                    class="d-inline" onsubmit="return confirm('Delete this notice?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">No notices yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal (create + edit) --}}
<div class="modal fade" id="noticeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" id="noticeForm" class="modal-content">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            <div class="modal-header">
                <h5 class="modal-title" id="noticeModalTitle">Add Notice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Notice Text</label>
                    <textarea name="text" id="noticeText" rows="3"
                        class="form-control @error('text') is-invalid @enderror" required>{{ old('text') }}</textarea>
                    @error('text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-check">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="noticeActive" checked>
                    <label class="form-check-label" for="noticeActive">Show on website</label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('noticeModal');
    const modal   = new bootstrap.Modal(modalEl);
    const form    = document.getElementById('noticeForm');
    const method  = document.getElementById('formMethod');
    const title   = document.getElementById('noticeModalTitle');
    const text    = document.getElementById('noticeText');
    const active  = document.getElementById('noticeActive');

    // Add
    document.getElementById('btnAdd').addEventListener('click', function () {
        form.action  = "{{ route('admin.header-notice.store') }}";
        method.value = 'POST';
        title.textContent = 'Add Notice';
        text.value = '';
        active.checked = true;
        modal.show();
    });

    // Edit
    document.querySelectorAll('.btn-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            form.action  = this.dataset.url;
            method.value = 'PUT';
            title.textContent = 'Edit Notice';
            text.value = this.dataset.text;
            active.checked = this.dataset.active === '1';
            modal.show();
        });
    });

    // Reopen modal if validation failed
    @if($errors->any())
        form.action  = "{{ route('admin.header-notice.store') }}";
        modal.show();
    @endif
});
</script>
@endsection