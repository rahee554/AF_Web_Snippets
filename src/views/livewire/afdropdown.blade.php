<div class="position-relative">
    <div class="position-relative">
        <input type="text"
            class="{{ $classes }}"
            wire:model.live.debounce.300ms="search"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            style="padding-right: 2.5rem;"
        />
        
        <!-- Times/Clear icon -->
        @if($search)
            <button type="button" 
                class="btn position-absolute top-50 end-0 translate-middle-y border-0 bg-transparent p-2"
                wire:click="clearSearch"
                style="z-index: 10;">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
                </svg>
            </button>
        @endif

        <!-- Loading spinner -->
        <div wire:loading wire:target="search" 
             class="position-absolute top-50 end-0 translate-middle-y pe-3">
            <div class="spinner-border spinner-border-sm text-secondary" role="status">
                <span class="visually-hidden">Searching...</span>
            </div>
        </div>
    </div>

    @if($isOpen)
        <ul class="dropdown-menu show w-100" style="max-height: 250px; overflow-y: auto; position: absolute; z-index: 1000;">
            @if(count($results) > 0)
                @foreach($results as $result)
                    <li>
                        <button type="button" class="dropdown-item" wire:click="select('{{ $result['id'] }}')">
                            {{ $result['label'] }}
                        </button>
                    </li>
                @endforeach
            @else
                <li>
                    @if(strlen($search) >= $minSearchLength)
                        <div class="dropdown-item-text d-flex justify-content-between align-items-center">
                            <span class="text-muted">No results found</span>
                            <button type="button" 
                                class="btn btn-sm btn-link p-0 text-muted"
                                wire:click="closeDropdown">
                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
                                </svg>
                            </button>
                        </div>
                    @else
                        <span class="dropdown-item-text text-muted">
                            Type at least {{ $minSearchLength }} characters to search...
                        </span>
                    @endif
                </li>
            @endif
        </ul>
    @endif
</div>