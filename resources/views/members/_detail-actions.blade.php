{{-- resources/views/members/_detail-actions.blade.php
     Params: $label (e.g. "Church Details"), $key (e.g. "church"), $isOwner, $hiddenSections --}}
@php $isHiddenSection = in_array($key, $hiddenSections ?? [], true); @endphp
<span class="detail-actions">
  @if ($isOwner)
    <button
      type="button"
      class="detail-action"
      aria-label="Edit {{ $label }}"
      data-action="edit"
    >
      <i class="fa-solid fa-pen" aria-hidden="true"></i>
    </button>
    <button
      type="button"
      class="detail-action detail-action--privacy {{ $isHiddenSection ? 'is-hidden-section' : '' }}"
      aria-label="{{ $isHiddenSection ? 'Show' : 'Hide' }} {{ $label }} {{ $isHiddenSection ? 'to' : 'from' }} other viewers"
      title="{{ $isHiddenSection ? 'Hidden from other viewers - click to show' : 'Visible to other viewers - click to hide' }}"
      data-action="privacy"
      data-section="{{ $key }}"
      aria-pressed="{{ $isHiddenSection ? 'true' : 'false' }}"
    >
      <i class="fa-solid {{ $isHiddenSection ? 'fa-eye-slash' : 'fa-eye' }}" aria-hidden="true"></i>
    </button>
  @endif
  <span class="detail-chevron" aria-hidden="true"
    ><i class="fa-solid fa-chevron-down"></i
  ></span>
</span>