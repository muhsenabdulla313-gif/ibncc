@extends('layouts.admin')

@section('body')
<div class="page-inner">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Hero Banners</h3>
        <button class="btn btn-primary btn-round" id="btnAdd"><i class="fa fa-plus"></i> Add Banner</button>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr><th>#</th><th>Image</th><th>Order</th><th>Status</th><th class="text-end">Action</th></tr>
                </thead>
                <tbody>
                    @forelse($banners as $b)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><img src="{{ asset($b->image) }}" alt="" style="height:60px;border-radius:6px"></td>
                            <td>{{ $b->sort_order }}</td>
                            <td>
                                <span class="badge bg-{{ $b->is_active ? 'success' : 'secondary' }}">
                                    {{ $b->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-info btn-edit"
                                    data-url="{{ route('admin.banners.update', $b) }}"
                                    data-img="{{ asset($b->image) }}"
                                        data-link="{{ $b->link }}"

                                    data-order="{{ $b->sort_order }}"
                                    data-active="{{ $b->is_active ? 1 : 0 }}">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.banners.destroy', $b) }}" class="d-inline"
                                    onsubmit="return confirm('Delete this banner?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No banners yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="bannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" id="bannerForm" enctype="multipart/form-data" class="modal-content">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Banner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Image <small class="text-muted">(auto resized to 1920x700 and compressed)</small></label>
                    <input type="file" name="image" id="bannerImage" class="form-control" accept="image/*">
                    <img id="preview" class="mt-2 w-100 rounded d-none" alt="">
                </div>
                <div class="mb-3">
    <label class="form-label">Link <small class="text-muted">(optional, e.g. /shop or https://...)</small></label>
    <input type="text" name="link" id="bannerLink" class="form-control" placeholder="/shop">
</div>
                <div class="mb-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" id="sortOrder" class="form-control" min="0" value="0">
                </div>
                <div class="form-check">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive" checked>
                    <label class="form-check-label" for="isActive">Show on website</label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal   = new bootstrap.Modal(document.getElementById('bannerModal'));
    const form    = document.getElementById('bannerForm');
    const link   = document.getElementById('bannerLink');

    const method  = document.getElementById('formMethod');
    const title   = document.getElementById('modalTitle');
    const file    = document.getElementById('bannerImage');
    const preview = document.getElementById('preview');
    const order   = document.getElementById('sortOrder');
    const active  = document.getElementById('isActive');

    document.getElementById('btnAdd').addEventListener('click', function () {
        form.action = "{{ route('admin.banners.store') }}";
        method.value = 'POST';
        title.textContent = 'Add Banner';
        file.value = '';
        link.value = '';
        file.required = true;
        preview.classList.add('d-none');
        order.value = 0;
        active.checked = true;
        modal.show();
    });

    document.querySelectorAll('.btn-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            form.action = this.dataset.url;
            link.value = this.dataset.link || '';
            method.value = 'PUT';
            title.textContent = 'Edit Banner';
            file.value = '';
            file.required = false;
            preview.src = this.dataset.img;
            preview.classList.remove('d-none');
            order.value = this.dataset.order;
            active.checked = this.dataset.active === '1';
            modal.show();
        });
    });

    file.addEventListener('change', function () {
        if (this.files[0]) {
            preview.src = URL.createObjectURL(this.files[0]);
            preview.classList.remove('d-none');
        }
    });
});
</script>
@endpush
@endsection