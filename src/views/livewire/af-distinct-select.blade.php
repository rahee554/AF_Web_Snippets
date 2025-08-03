
<div class="position-relative">
    <!-- Hidden input to hold the actual value for form submission -->
    <input type="hidden" wire:model="value" name="{{ $column }}" />
    
    <div class="position-relative">
        <input type="text"
            class="{{ $classes }}"
            wire:model.live.debounce.300ms="search"
            wire:focus="openDropdown"
            wire:blur="blurInput"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            style="padding-right: 2.5rem;"
        />
        
        <!-- Clear icon -->
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
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>

    @if($isOpen && count($filteredValues) > 0)
        <ul class="dropdown-menu show w-100" style="max-height: 250px; overflow-y: auto; position: absolute; z-index: 1000;">
            @foreach($filteredValues as $distinctValue)
                <li>
                    <button type="button" 
                        class="dropdown-item d-flex justify-content-between align-items-center" 
                        wire:click="selectValue('{{ addslashes($distinctValue) }}')"
                        title="Click to select: {{ $distinctValue }}">
                        <span>{{ $distinctValue }}</span>
                        @if($value === $distinctValue)
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="text-success">
                                <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                            </svg>
                        @endif
                    </button>
                </li>
            @endforeach
            
            @if(count($distinctValues) > count($filteredValues))
                <li>
                    <div class="dropdown-item-text text-muted text-center small">
                        Showing {{ count($filteredValues) }} of {{ count($distinctValues) }} existing values
                    </div>
                </li>
            @endif
        </ul>
    @elseif($isOpen && strlen($search) >= $minSearchLength && count($filteredValues) === 0)
        <ul class="dropdown-menu show w-100" style="position: absolute; z-index: 1000;">
            <li>
                <div class="dropdown-item-text d-flex justify-content-between align-items-center">
                    <span class="text-muted">No existing values found - you can type a new one</span>
                    <button type="button" 
                        class="btn btn-sm btn-link p-0 text-muted"
                        wire:click="closeDropdown">
                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
                        </svg>
                    </button>
                </div>
            </li>
        </ul>
    @endif
</div>
