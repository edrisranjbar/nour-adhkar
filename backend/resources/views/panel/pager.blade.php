@if ($items->hasPages())
    <div class="pager">
        @if ($items->previousPageUrl())
            <a class="btn sm" href="{{ $items->previousPageUrl() }}">قبلی</a>
        @endif
        <span class="muted">صفحه {{ \App\Support\PanelFormat::digits($items->currentPage()) }} از {{ \App\Support\PanelFormat::digits($items->lastPage()) }}</span>
        @if ($items->nextPageUrl())
            <a class="btn sm" href="{{ $items->nextPageUrl() }}">بعدی</a>
        @endif
    </div>
@endif
