<?php

namespace ArtFlowStudio\Snippets\Http\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Database\Eloquent\Builder;

/**
 * AFDropdown - Advanced Searchable Select Component for Livewire 3.6+
 * 
 * A powerful, production-ready Livewire 3 component for searchable dropdowns with:
 * - Real-time filtering with debouncing
 * - Event dispatch (Livewire 3.6+)
 * - Advanced query building & customization
 * - Caching support for performance
 * - Multiple search columns
 * - Custom formatters & filters
 * - Keyboard navigation
 * - Accessibility features
 * - Type hints & proper validation
 * 
 * Usage Examples:
 * 
 * Basic:
 * @livewire('afdropdown', [
 *     'model' => App\Models\Customer::class,
 *     'column' => 'name',
 * ])
 * 
 * Advanced:
 * @livewire('afdropdown', [
 *     'model' => App\Models\Customer::class,
 *     'columns' => ['name', 'email'],
 *     'classes' => 'form-control form-control-lg',
 *     'placeholder' => 'Search customers by name or email...',
 *     'minSearchLength' => 2,
 *     'resultLimit' => 15,
 *     'additionalColumns' => ['phone', 'email'],
 *     'enableCache' => true,
 *     'queryCallback' => fn($query) => $query->where('status', 'active'),
 *     'formatter' => fn($item) => "{$item->name} - {$item->email}",
 *     'searchMode' => 'advanced',
 * ])
 */
class AFdropdown extends Component
{
    // Basic Properties
    public string $model;
    public string|array $column;
    public string $search = '';
    public array $results = [];
    public $selectedId = null;
    public $selectedData = null;
    
    // UI Properties
    public string $classes = 'form-control';
    public string $placeholder = 'Search...';
    public bool $isOpen = false;
    
    // Search Configuration
    public int $minSearchLength = 2;
    public int $resultLimit = 15;
    public string $searchMode = 'basic'; // basic, advanced, exact, contains
    public array $columns = []; // Multiple columns to search
    
    // Customization
    public string $displayFormat = 'label';
    public array $additionalColumns = [];
    public $formatter = null; // Custom formatter callable
    public $queryCallback = null; // Custom query modifier
    
    // Performance
    public bool $enableCache = false;
    public int $cacheTime = 3600; // 1 hour
    
    // Keyboard Navigation
    public int $highlightedIndex = -1;
    
    // Debounce & Performance
    public string $debounceTime = '300ms';
    
    // Error Handling
    public ?string $lastError = null;
    public bool $throwErrors = false;
    
    // Event Callbacks
    public $onSelect = null;
    public $onClear = null;

    /**
     * Mount component with configuration
     */
    public function mount(
        $model,
        $column = null,
        string $classes = 'form-control',
        string $placeholder = 'Search...',
        int $minSearchLength = 2,
        int $resultLimit = 15,
        string $displayFormat = 'label',
        array $additionalColumns = [],
        bool $enableCache = false,
        $formatter = null,
        $queryCallback = null,
        string $searchMode = 'basic',
        array $columns = [],
        string $debounceTime = '300ms',
    ) {
        $this->model = is_string($model) ? $model : $model::class;
        
        // Support both single column and multiple columns
        if ($columns !== [] && is_array($columns)) {
            $this->columns = $columns;
            $this->column = $columns[0] ?? $column;
        } else {
            $this->column = $column ?? 'name';
            $this->columns = [$this->column];
        }
        
        $this->classes = $classes ?: 'form-control';
        $this->placeholder = $placeholder;
        $this->minSearchLength = max(1, $minSearchLength);
        $this->resultLimit = max(1, min($resultLimit, 50)); // Max 50 results
        $this->displayFormat = $displayFormat;
        $this->additionalColumns = $additionalColumns;
        $this->enableCache = $enableCache;
        $this->formatter = $formatter;
        $this->queryCallback = $queryCallback;
        $this->searchMode = $searchMode;
        $this->debounceTime = $debounceTime;
    }



    /**
     * Update search and load results
     */
    public function updatedSearch()
    {
        $this->highlightedIndex = -1;
        
        if (strlen($this->search) >= $this->minSearchLength) {
            $this->loadResults();
            $this->isOpen = true;
        } else {
            $this->results = [];
            $this->isOpen = false;
        }
    }

    /**
     * Load results from database with advanced query building
     */
    public function loadResults(): array
    {
        $this->results = [];
        $this->lastError = null;
        
        if (strlen($this->search) < $this->minSearchLength || !class_exists($this->model)) {
            return [];
        }

        try {
            $searchTerm = $this->normalizeSearch($this->search);
            
            // Build query - get query builder from model
            $query = $this->buildQuery(($this->model)::query(), $searchTerm);
            
            // Apply custom callback if provided
            if ($this->queryCallback && is_callable($this->queryCallback)) {
                $query = call_user_func($this->queryCallback, $query);
            }
            
            // Get cache key if enabled
            if ($this->enableCache) {
                $cacheKey = $this->getCacheKey($searchTerm);
                $items = cache()->remember(
                    $cacheKey,
                    $this->cacheTime,
                    fn() => $query->limit($this->resultLimit)->get()
                );
            } else {
                $items = $query->limit($this->resultLimit)->get();
            }
            
            $this->results = $items->map(fn($item) => $this->formatResult($item))->toArray();
            
            return $this->results;
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            
            if ($this->throwErrors) {
                throw $e;
            }
            
            return [];
        }
    }

    /**
     * Build advanced query based on search mode
     */
    private function buildQuery(Builder $baseQuery, string $searchTerm): Builder
    {
        return match($this->searchMode) {
            'exact' => $baseQuery->where($this->columns[0], '=', $searchTerm),
            'contains' => $this->buildContainsQuery($baseQuery, $searchTerm),
            'advanced' => $this->buildAdvancedQuery($baseQuery, $searchTerm),
            default => $this->buildBasicQuery($baseQuery, $searchTerm),
        };
    }

    /**
     * Build basic query - searches one column with LIKE
     */
    private function buildBasicQuery(Builder $query, string $searchTerm): Builder
    {
        return $query->where($this->columns[0], 'LIKE', "%{$searchTerm}%");
    }

    /**
     * Build contains query - searches multiple columns
     */
    private function buildContainsQuery(Builder $query, string $searchTerm): Builder
    {
        return $query->where(function(Builder $q) use ($searchTerm) {
            foreach ($this->columns as $column) {
                $q->orWhere($column, 'LIKE', "%{$searchTerm}%");
            }
        });
    }

    /**
     * Build advanced query - word-by-word matching across columns
     */
    private function buildAdvancedQuery(Builder $query, string $searchTerm): Builder
    {
        $words = explode(' ', $searchTerm);
        
        return $query->where(function(Builder $q) use ($words) {
            foreach ($words as $word) {
                $q->where(function(Builder $subQ) use ($word) {
                    foreach ($this->columns as $column) {
                        $subQ->orWhere($column, 'LIKE', "%{$word}%");
                    }
                });
            }
        });
    }

    /**
     * Normalize search term
     */
    private function normalizeSearch(string $search): string
    {
        return trim(mb_strtolower($search));
    }

    /**
     * Get cache key for search
     */
    private function getCacheKey(string $searchTerm): string
    {
        return "afdropdown_{$this->model}_{$this->searchMode}_{$searchTerm}";
    }

    /**
     * Format individual result for display
     */
    private function formatResult($item): array
    {
        // Use custom formatter if provided
        if ($this->formatter && is_callable($this->formatter)) {
            $label = call_user_func($this->formatter, $item);
        } else {
            // Default formatting based on displayFormat
            $label = match($this->displayFormat) {
                'full' => $this->buildFullLabel($item),
                default => $item->{$this->columns[0]} ?? '',
            };
        }
        
        // Build additional display info
        $additionalInfo = [];
        foreach ($this->additionalColumns as $col) {
            if ($item->offsetExists($col)) {
                $additionalInfo[$col] = $item->{$col};
            }
        }
        
        // Get data array, handle both array and collection results from only()
        $columns = array_merge($this->columns, $this->additionalColumns);
        $itemArray = $item->toArray();
        $dataArray = array_intersect_key($itemArray, array_flip($columns));
        
        return [
            'id' => $item->getKey(),
            'label' => $label,
            'data' => $dataArray,
            'additionalInfo' => $additionalInfo,
            'fullData' => $itemArray,
        ];
    }

    /**
     * Build full label from multiple columns
     */
    private function buildFullLabel($item): string
    {
        $labels = [];
        foreach ($this->columns as $col) {
            if (isset($item->{$col})) {
                $labels[] = $item->{$col};
            }
        }
        return implode(' - ', array_filter($labels));
    }

    /**
     * Handle item selection and dispatch event
     */
    public function select(int|string $id)
    {
        $this->highlightedIndex = -1;
        $this->selectedId = $id;
        
        try {
            $record = $this->model::find($id);
            
            if ($record) {
                $label = $record->{$this->columns[0]};
                $this->search = $label;
                $this->selectedData = $record->toArray();
                $this->isOpen = false;
                
                // Dispatch event with full payload (Livewire 3.6+)
                $this->dispatch('afdropdown:selected', [
                    'id' => $id,
                    'label' => $label,
                    'model' => $this->model,
                    'data' => $this->selectedData,
                ]);
                
                // Execute callback if provided
                if ($this->onSelect && is_callable($this->onSelect)) {
                    call_user_func($this->onSelect, $record);
                }
                
                $this->lastError = null;
            }
        } catch (\Exception $e) {
            $this->lastError = "Failed to select item: {$e->getMessage()}";
            
            if ($this->throwErrors) {
                throw $e;
            }
        }
    }

    /**
     * Clear search and reset component
     */
    public function clearSearch()
    {
        $this->search = '';
        $this->results = [];
        $this->isOpen = false;
        $this->selectedId = null;
        $this->selectedData = null;
        $this->highlightedIndex = -1;
        $this->lastError = null;
        
        // Dispatch clear event
        $this->dispatch('afdropdown:cleared');
        
        // Execute callback if provided
        if ($this->onClear && is_callable($this->onClear)) {
            call_user_func($this->onClear);
        }
    }

    /**
     * Open dropdown
     */
    public function openDropdown()
    {
        if (strlen($this->search) >= $this->minSearchLength && count($this->results) > 0) {
            $this->isOpen = true;
        }
    }

    /**
     * Close dropdown
     */
    public function closeDropdown()
    {
        $this->isOpen = false;
        $this->highlightedIndex = -1;
    }

    /**
     * Navigate results with keyboard - UP
     */
    public function previousResult()
    {
        if ($this->highlightedIndex > 0) {
            $this->highlightedIndex--;
        } elseif ($this->highlightedIndex === -1 && count($this->results) > 0) {
            $this->highlightedIndex = count($this->results) - 1;
        }
    }

    /**
     * Navigate results with keyboard - DOWN
     */
    public function nextResult()
    {
        if ($this->highlightedIndex < count($this->results) - 1) {
            $this->highlightedIndex++;
        } else {
            $this->highlightedIndex = -1;
        }
    }

    /**
     * Select highlighted result - ENTER
     */
    public function selectHighlighted()
    {
        if ($this->highlightedIndex >= 0 && isset($this->results[$this->highlightedIndex])) {
            $this->select($this->results[$this->highlightedIndex]['id']);
        }
    }

    /**
     * Clear cache manually
     */
    public function clearCache()
    {
        if ($this->enableCache) {
            cache()->flush();
        }
    }

    /**
     * Render component
     */
    public function render()
    {
        return view('snippets::livewire.afdropdown');
    }
}
