@extends('layouts.admin')

@section('body')
<div class="container">
  <div class="page-inner">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="fw-bold mb-0">Events</h3>
      <button type="button" class="btn btn-primary" id="evAddBtn">
        <i class="fa fa-plus"></i> Add Event
      </button>
    </div>

    @if (session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $er) <li>{{ $er }}</li> @endforeach
        </ul>
      </div>
    @endif

    <div class="card">
      <div class="card-body table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>Cover</th>
              <th>Title</th>
              <th>Date</th>
              <th>Type</th>
              <th>Location</th>
              <th>Photos</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($events as $e)
              <tr>
                <td><img src="{{ $e->cover_url }}" alt="" style="width:70px;height:46px;object-fit:cover;border-radius:6px"></td>
                <td>
                  <strong>{{ $e->title }}</strong><br>
                  <small class="text-muted">/events/{{ $e->slug }}</small>
                </td>
                <td>{{ $e->event_date->format('d M Y') }}</td>
                <td>
  @if ($e->event_date->isToday())
    <span class="badge bg-warning">Today</span>
  @elseif ($e->is_upcoming)
    <span class="badge bg-success">Upcoming</span>
  @else
    <span class="badge bg-secondary">Previous</span>
  @endif
</td>
<td>{{ $e->is_upcoming ? $e->venue : $e->city }}</td>
<td>{{ $e->images->count() }}</td>
<td><span class="badge bg-{{ $e->is_active ? 'primary' : 'dark' }}">{{ $e->is_active ? 'Visible' : 'Hidden' }}</span></td>
                <td class="text-end text-nowrap">
                  <button type="button" class="btn btn-sm btn-info ev-edit" data-event="{{ json_encode($e->adminPayload()) }}">
                    <i class="fa fa-edit"></i>
                  </button>
                  <form.
                   action="{{ route('admin.events.destroy', $e) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Delete this event and its photos?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="8" class="text-center text-muted py-4">No events yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

{{-- ONE modal for add + edit --}}
<div class="modal fade" id="evModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <form class="modal-content" id="evForm" method="POST" enctype="multipart/form-data" action="{{ route('admin.events.store') }}">
      @csrf
      <input type="hidden" name="_method" value="PUT" id="evMethod" disabled>

      <div class="modal-header">
        <h5 class="modal-title" id="evModalTitle">Add Event</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Title *</label>
            <input type="text" name="title" id="evTitle" class="form-control" required>
          </div>
        

          <div class="col-md-6">
            <label class="form-label">Tagline</label>
            <input type="text" name="tagline" id="evTagline" class="form-control" placeholder="Connect. Learn. Grow. Expand.">
          </div>
          <div class="col-md-3">
            <label class="form-label">Date *</label>
            <input type="date" name="event_date" id="evDate" class="form-control" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Time</label>
            <input type="text" name="time_text" id="evTime" class="form-control" placeholder="9:00 AM – 8:00 PM">
          </div>

          <div class="col-md-8">
            <label class="form-label">Venue (full address)</label>
            <input type="text" name="venue" id="evVenue" class="form-control" placeholder="Monsoon Empress Hotel, NH Bypass, Palarivattom, Kochi">
          </div>
          <div class="col-md-4">
            <label class="form-label">City (shown on previous cards)</label>
            <input type="text" name="city" id="evCity" class="form-control" placeholder="Kochi, Kerala">
          </div>

          <div class="col-12">
            <label class="form-label">Intro</label>
            <textarea name="intro" id="evIntro" rows="2" class="form-control"></textarea>
          </div>

          <div class="col-12">
            <label class="form-label">Highlights <small class="text-muted">(card title on first line, then one bullet per line as <code>Heading | description</code>; blank line starts a new card)</small></label>
            <textarea name="highlights" id="evHighlights" rows="8" class="form-control"></textarea>
          </div>

          <div class="col-md-4">
            <label class="form-label">Register link <small class="text-muted">(empty = email)</small></label>
            <input type="url" name="register_url" id="evRegUrl" class="form-control" placeholder="https://...">
          </div>
          <div class="col-md-4">
            <label class="form-label">Contact phone</label>
            <input type="text" name="contact_phone" id="evPhone" class="form-control">
          </div>
          <div class="col-md-4">
            <label class="form-label">Contact email</label>
            <input type="email" name="contact_email" id="evEmail" class="form-control">
          </div>

          <div class="col-md-4">
            <label class="form-label">Cover image <span id="evCoverReq">*</span></label>
            <input type="file" name="cover_image" id="evCover" class="form-control" accept="image/*">
            <img id="evCoverPrev" src="" alt="" class="mt-2 d-none" style="height:70px;border-radius:6px">
          </div>
          <div class="col-md-4">
            <label class="form-label">Registration QR (optional)</label>
            <input type="file" name="qr_image" class="form-control" accept="image/*">
            <img id="evQrPrev" src="" alt="" class="mt-2 d-none" style="height:70px;border-radius:6px">
          </div>
          <div class="col-md-4">
            <label class="form-label">Gallery photos (slider)</label>
            <input type="file" name="gallery[]" class="form-control" accept="image/*" multiple>
          </div>

          <div class="col-12" id="evGalleryWrap" style="display:none">
            <label class="form-label">Current gallery</label>
            <div class="d-flex flex-wrap gap-2" id="evGallery"></div>
          </div>

          <div class="col-12">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="is_active" value="1" id="evActive" checked>
              <label class="form-check-label" for="evActive">Active (visible on website)</label>
            </div>
          </div>
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
  const modal   = new bootstrap.Modal(document.getElementById('evModal'));
  const form    = document.getElementById('evForm');
  const method  = document.getElementById('evMethod');
  const gallery = document.getElementById('evGallery');
  const csrf    = document.querySelector('#evForm input[name=_token]').value;
  const storeUrl = @json(route('admin.events.store'));
  const baseUrl  = @json(url('admin/events'));
  const $ = id => document.getElementById(id);

  const preview = (id, url) => {
    $(id).src = url || '';
    $(id).classList.toggle('d-none', !url);
  };

  $('evAddBtn').onclick = () => {
    form.reset();
    form.action = storeUrl;
    method.disabled = true;
    $('evModalTitle').textContent = 'Add Event';
    $('evCover').required = true;
    $('evCoverReq').style.display = '';
    $('evActive').checked = true;
    preview('evCoverPrev'); preview('evQrPrev');
    $('evGalleryWrap').style.display = 'none';
    modal.show();
  };

  document.querySelectorAll('.ev-edit').forEach(btn => btn.onclick = () => {
    const e = JSON.parse(btn.dataset.event);
    form.reset();
    form.action = baseUrl + '/' + e.id;
    method.disabled = false;
    $('evModalTitle').textContent = 'Edit Event';
    $('evCover').required = false;
    $('evCoverReq').style.display = 'none';

    $('evTitle').value = e.title || '';
    $('evTagline').value = e.tagline || '';
    $('evDate').value = e.event_date || '';
    $('evTime').value = e.time_text || '';
    $('evVenue').value = e.venue || '';
    $('evCity').value = e.city || '';
    $('evIntro').value = e.intro || '';
    $('evHighlights').value = e.highlights || '';
    $('evRegUrl').value = e.register_url || '';
    $('evPhone').value = e.contact_phone || '';
    $('evEmail').value = e.contact_email || '';
    $('evActive').checked = !!e.is_active;
    preview('evCoverPrev', e.cover_url);
    preview('evQrPrev', e.qr_url);

    gallery.innerHTML = '';
    e.images.forEach(img => {
      const box = document.createElement('div');
      box.className = 'position-relative';
      box.innerHTML = '<img src="' + img.url + '" style="height:70px;border-radius:6px">' +
        '<button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 p-0 px-1" style="line-height:1">&times;</button>';
      box.querySelector('button').onclick = () => {
        if (!confirm('Remove this photo?')) return;
        fetch(baseUrl + '/images/' + img.id, {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        }).then(r => r.ok && box.remove());
      };
      gallery.appendChild(box);
    });
    $('evGalleryWrap').style.display = e.images.length ? '' : 'none';
    modal.show();
  });
});
</script>
@endsection
