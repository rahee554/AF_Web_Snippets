<div class="afdropdown-wrapper position-relative" 
     x-data="afdropdownComponent(@entangle('highlightedIndex'), @entangle('search'), @entangle('isOpen'))"
     x-on:keydown.escape="closeDropdown()"
>
    <div class="afdropdown-input-wrapper position-relative">
        <input type="text"
            class="{{ $classes }}"
            wire:model.live.debounce.{{ $debounceTime }}="search"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            role="combobox"
            aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
            aria-controls="afdropdown-results"
            aria-label="{{ $placeholder }}"
            x-ref="searchInput"
            @keydown.arrow-up.prevent="$wire.previousResult()"
            @keydown.arrow-down.prevent="$wire.nextResult()"
            @keydown.enter.prevent="$wire.selectHighlighted()"
            @focus="$wire.openDropdown()"
            style="padding-right: 2.5rem;"
        />
        
        <!-- Clear Button -->
        @if($search)
            <button type="button" 
                class="afdropdown-clear-btn btn position-absolute top-50 end-0 translate-middle-y border-0 bg-transparent p-2"
                wire:click="clearSearch"
                aria-label="Clear search"
                title="Clear search ({{ $search }})"
                style="z-index: 10;">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
                </svg>
            </button>
        @endif

        <!-- Loading Indicator -->
        <div wire:loading 
             wire:target="search,loadResults" 
             class="afdropdown-loading position-absolute top-50 end-0 translate-middle-y pe-3"
             aria-live="polite"
             aria-label="Loading search results">
            <div class="spinner-border spinner-border-sm text-secondary" role="status">
                <span class="visually-hidden">Searching...</span>
            </div>
        </div>
    </div>

    <!-- Error Message -->
    @if($lastError)
        <div class="alert alert-danger alert-sm mt-2 p-2 mb-0" role="alert">
            <small><strong>Error:</strong> {{ $lastError }}</small>
        </div>
    @endif

    <!-- Dropdown Results -->
    @if($isOpen && strlen($search) >= $minSearchLength)
        <div class="afdropdown-results-container position-relative w-100">
            <ul class="afdropdown-results dropdown-menu show w-100" 
                id="afdropdown-results"
                role="listbox"
                x-on:click-away="closeDropdown()"
                style="max-height: 300px; overflow-y: auto; position: absolute; z-index: 1000;">
                
                {{-- Has Results --}}
                @if(count($results) > 0)
                    @foreach($results as $index => $result)
                        <li role="option" wire:key="result-{{ $result['id'] }}">
                            <button type="button" 
                                class="afdropdown-result-item dropdown-item {{ $highlightedIndex === $index ? 'active' : '' }}" 
                                wire:click="select('{{ $result['id'] }}')"
                                @click="$wire.select('{{ $result['id'] }}')"
                                role="button"
                                aria-selected="{{ $highlightedIndex === $index ? 'true' : 'false' }}">
                                <span class="afdropdown-result-label">
                                    {{ $result['label'] }}
                                </span>
                                @if(!empty($result['additionalInfo']))
                                    <span class="afdropdown-result-info text-muted small ms-2">
                                        ({{ implode(', ', array_values($result['additionalInfo'])) }})
                                    </span>
                                @endif
                            </button>
                        </li>
                    @endforeach
                    
                    <!-- Result Counter -->
                    <li class="dropdown-divider my-1"></li>
                    <li class="dropdown-item-text text-muted small px-3 py-1">
                        Showing {{ count($results) }} of {{ count($results) }}{{ count($results) >= $resultLimit ? '+' : '' }} results
                    </li>
                @else
                    {{-- No Results --}}
                    <li role="option" aria-disabled="true">
                        <div class="afdropdown-no-results dropdown-item-text d-flex justify-content-between align-items-center">
                            <span class="text-muted">
                                @if(strlen($search) >= $minSearchLength)
                                    <i class="ki-outline ki-information text-warning"></i>
                                    No results found for "{{ $search }}"
                                @else
                                    Type at least {{ $minSearchLength }} character{{ $minSearchLength > 1 ? 's' : '' }} to search
                                @endif
                            </span>
                        </div>
                    </li>
                @endif
            </ul>
        </div>
    @elseif($isOpen && strlen($search) < $minSearchLength)
        <div class="afdropdown-hint-container position-relative w-100">
            <div class="alert alert-info alert-sm p-2" style="position: absolute; z-index: 1000; width: 100%;">
                <small>
                    <i class="ki-outline ki-information"></i>
                    Type at least <strong>{{ $minSearchLength }} character{{ $minSearchLength > 1 ? 's' : '' }}</strong> to search...
                </small>
            </div>
        </div>
    @endif
</div>

@push('styles')
   <style>
    .afdropdown-wrapper {
        width: 100%;
    }

    .afdropdown-input-wrapper input:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .afdropdown-results {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        max-height: 300px;
        overflow-y: auto;
    }

    .afdropdown-result-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: background-color 0.15s ease-in-out, color 0.15s ease-in-out;
        padding: 0.5rem 1rem !important;
        color: inherit;
    }

    .afdropdown-result-item:hover {
        background-color: #e9ecef;
        color: inherit;
    }

    .afdropdown-result-item.active {
        background-color: #0d6efd;
        color: white;
    }

    .afdropdown-result-item.active .afdropdown-result-info {
        color: rgba(255, 255, 255, 0.8) !important;
    }

    .afdropdown-result-label {
        font-weight: 500;
        flex: 1;
    }

    .afdropdown-result-info {
        font-size: 0.85rem;
        color: #6c757d;
        white-space: nowrap;
        margin-left: 0.5rem;
    }

    .afdropdown-clear-btn {
        transition: color 0.15s ease-in-out;
        color: #6c757d;
    }

    .afdropdown-clear-btn:hover {
        color: #dc3545;
    }

    .afdropdown-no-results,
    .afdropdown-hint {
        padding: 0.75rem 1rem;
    }

    .afdropdown-hint-container {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 999;
        margin-top: 0.25rem;
    }

    /* Scrollbar styling */
    .afdropdown-results::-webkit-scrollbar {
        width: 8px;
    }

    .afdropdown-results::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .afdropdown-results::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .afdropdown-results::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Loading spinner animation */
    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
        border-width: 0.15em;
    }
</style> 
@endpush

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('afdropdownComponent', (highlightedIndex, search, isOpen) => ({
                init() {
                    // Alpine component initialization
                },
                closeDropdown() {
                    this.$wire.closeDropdown();
                }
            }));
        });
    </script>
@endpush
