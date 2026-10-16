{{-- resources/views/members/_edit-field.blade.php
     Params: $idx (0,1,2..), $field (db field key), $label, $value,
             optional: $type (text|tel|email), $options (array), $role (field|category|job) --}}
@php
  $type = $type ?? 'text';
  $options = $options ?? [];
  $role = $role ?? 'field';
  $hasOptions = count($options) > 0;
  $name = 'detail-' . $idx;
@endphp
<label class="detail-edit-field {{ $hasOptions ? 'has-options' : '' }}">
  <span>{{ $label }}</span>
  <span class="detail-input-wrap">
    <input type="{{ $type }}" name="{{ $name }}" data-field="{{ $field }}" value="{{ $value }}" />
    @if ($hasOptions)
      <button
        type="button"
        class="detail-option-toggle"
        data-option-target="{{ $name }}"
        aria-label="Show {{ $label }} options"
        aria-expanded="false"
      >
        <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
      </button>
    @endif
  </span>
  @if ($hasOptions)
    <div class="detail-option-picker" hidden>
      <select
        class="detail-option-select"
        data-option-target="{{ $name }}"
        data-option-role="{{ $role }}"
        size="{{ min(max(count($options), 3), 5) }}"
        aria-label="{{ $label }} options"
      >
        @foreach ($options as $opt)
          <option value="{{ $opt }}" @selected($opt === $value)>{{ $opt }}</option>
        @endforeach
      </select>
    </div>
  @endif
</label>