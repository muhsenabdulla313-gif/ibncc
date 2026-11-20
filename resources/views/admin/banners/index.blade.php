@extends('layouts.admin')

@section('body')
<div class="page-inner">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold mb-1">Hero Promotional Banners</h3>
            <p class="text-muted mb-0">Manage rotating banners, text overlays, and promo buttons for the Trading homepage.</p>
        </div>
        <button class="btn btn-primary btn-round" id="btnAdd"><i class="fa fa-plus me-1"></i> Add Banner</button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa fa-exclamation-triangle me-1"></i> {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card card-round">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="width: 120px;">Image</th>
                        <th>Text Overlay / Details</th>
                        <th>Target Link</th>
                        <th>Position</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($banners as $b)
                        @php
                            $img = $b->image;
                            $imgUrl = asset($img);
                            if (str_starts_with($img, 'http')) {
                                $imgUrl = $img;
                            }
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <img src="{{ $imgUrl }}" alt="" style="width: 110px; height: 50px; object-fit: cover; border-radius: 6px;" class="border">
                            </td>
                            <td>
                                @if($b->title)
                                    @if($b->eyebrow)<small class="text-primary text-uppercase fw-bold d-block">{{ $b->eyebrow }}</small>@endif
                                    <strong class="text-dark">{{ $b->title }}</strong>
                                    @if($b->subtitle)<small class="text-muted d-block">{{ Str::limit($b->subtitle, 40) }}</small>@endif
                                    @if($b->cta_text)<span class="badge bg-light text-dark border mt-1">Button: {{ $b->cta_text }}</span>@endif
                                @else
                                    <span class="text-muted fst-italic">Image-only banner</span>
                                @endif
                            </td>
                            <td>
                                @if($b->link)
                                    <code class="text-primary">{{ $b->link }}</code>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ ucfirst($b->text_position ?: 'right') }}</span>
                            </td>
                            <td>{{ $b->sort_order }}</td>
                            <td>
                                <span class="badge bg-{{ $b->is_active ? 'success' : 'secondary' }}">
                                    {{ $b->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-icon btn-info btn-edit"
                                    data-url="{{ route('admin.banners.update', $b) }}"
                                    data-img="{{ $imgUrl }}"
                                    data-link="{{ $b->link }}"
                                    data-eyebrow="{{ $b->eyebrow }}"
                                    data-title="{{ $b->title }}"
                                    data-subtitle="{{ $b->subtitle }}"
                                    data-cta="{{ $b->cta_text }}"
                                    data-position="{{ $b->text_position ?: 'right' }}"
                                    data-order="{{ $b->sort_order }}"
                                    data-active="{{ $b->is_active ? 1 : 0 }}"
                                    title="Edit Banner">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.banners.destroy', $b) }}" class="d-inline"
                                    onsubmit="return confirm('Delete this banner?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-icon btn-danger" title="Delete"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-5">No banners yet. Click "Add Banner" to upload one.</td></tr>
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
                <h5 class="modal-title fw-bold" id="modalTitle">Add Banner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Banner Image <small class="text-muted">(auto resized to 1920x700 and compressed)</small></label>
                    <input type="file" name="image" id="bannerImage" class="form-control" accept="image/*">
                    <img id="preview" class="mt-2 w-100 rounded d-none border" style="max-height: 180px; object-fit: cover;" alt="">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Eyebrow / Small Tagline <small class="text-muted">(optional)</small></label>
                        <input type="text" name="eyebrow" id="bannerEyebrow" class="form-control" placeholder="e.g. IBNCC Presents or Farm & Garden">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Main Banner Title <small class="text-muted">(optional)</small></label>
                        <input type="text" name="title" id="bannerTitle" class="form-control" placeholder="e.g. Floral Bloom or Harvest Fresh">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Subtitle / Description <small class="text-muted">(optional)</small></label>
                    <input type="text" name="subtitle" id="bannerSubtitle" class="form-control" placeholder="e.g. Timeless pieces for moments that matter">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">CTA Button Label <small class="text-muted">(optional)</small></label>
                        <input type="text" name="cta_text" id="bannerCta" class="form-control" placeholder="e.g. Shop Now, Explore Deals">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Target Link <small class="text-muted">(e.g. /products or https://...)</small></label>
                        <input type="text" name="link" id="bannerLink" class="form-control" placeholder="/products">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Text Position</label>
                        <select name="text_position" id="bannerPosition" class="form-select">
                            <option value="right">Right Side (with gradient)</option>
                            <option value="left">Left Side (with gradient)</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Sort Order</label>
                        <input type="number" name="sort_order" id="sortOrder" class="form-control" min="0" value="0">
                    </div>
                </div>

                <div class="form-check mt-2">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive" checked>
                    <label class="form-check-label fw-bold" for="isActive">Show on Trading Homepage</label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-round">Save Banner</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal    = new bootstrap.Modal(document.getElementById('bannerModal'));
    const form     = document.getElementById('bannerForm');
    const method   = document.getElementById('formMethod');
    const title    = document.getElementById('modalTitle');
    const file     = document.getElementById('bannerImage');
    const preview  = document.getElementById('preview');
    const link     = document.getElementById('bannerLink');
    const eyebrow  = document.getElementById('bannerEyebrow');
    const bTitle   = document.getElementById('bannerTitle');
    const subtitle = document.getElementById('bannerSubtitle');
    const cta      = document.getElementById('bannerCta');
    const position = document.getElementById('bannerPosition');
    const order    = document.getElementById('sortOrder');
    const active   = document.getElementById('isActive');

    document.getElementById('btnAdd').addEventListener('click', function () {
        form.action = "{{ route('admin.banners.store') }}";
        method.value = 'POST';
        title.textContent = 'Add Banner';
        file.value = '';
        file.required = true;
        link.value = '';
        eyebrow.value = '';
        bTitle.value = '';
        subtitle.value = '';
        cta.value = '';
        position.value = 'right';
        preview.classList.add('d-none');
        order.value = 0;
        active.checked = true;
        modal.show();
    });

    document.querySelectorAll('.btn-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            form.action = this.dataset.url;
            method.value = 'PUT';
            title.textContent = 'Edit Banner';
            file.value = '';
            file.required = false;
            preview.src = this.dataset.img;
            preview.classList.remove('d-none');
            link.value = this.dataset.link || '';
            eyebrow.value = this.dataset.eyebrow || '';
            bTitle.value = this.dataset.title || '';
            subtitle.value = this.dataset.subtitle || '';
            cta.value = this.dataset.cta || '';
            position.value = this.dataset.position || 'right';
            order.value = this.dataset.order || 0;
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
