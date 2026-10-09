{{-- resources/views/pagination/ibncc.blade.php  (same <button class="page-btn"> markup as the static design) --}}
@if ($paginator->hasPages())
  <nav class="networking-pagination" aria-label="Profile results pages">

    @if (! $paginator->onFirstPage())
      <button type="button" class="page-btn page-prev" aria-label="Previous page"
              onclick="window.location.href='{{ $paginator->previousPageUrl() }}'">
        <i class="fa-solid fa-chevron-left"></i>
      </button>
    @endif

    @foreach ($elements as $element)
      @if (is_string($element))
        <span class="page-ellipsis" aria-hidden="true">…</span>
      @endif

      @if (is_array($element))
        @foreach ($element as $page => $url)
          @if ($page == $paginator->currentPage())
            <button type="button" class="page-btn is-active" aria-current="page">{{ $page }}</button>
          @else
            <button type="button" class="page-btn" onclick="window.location.href='{{ $url }}'">{{ $page }}</button>
          @endif
        @endforeach
      @endif
    @endforeach

    @if ($paginator->hasMorePages())
      <button type="button" class="page-btn page-next" aria-label="Next page"
              onclick="window.location.href='{{ $paginator->nextPageUrl() }}'">
        <i class="fa-solid fa-chevron-right"></i>
      </button>
    @endif

  </nav>
@endif