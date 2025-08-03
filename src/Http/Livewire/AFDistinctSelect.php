<?php

namespace ArtFlowStudio\Snippets\Http\Livewire;

use Livewire\Component;

class AFDistinctSelect extends Component
{
    public string $model;
    public string $column;
    public string $value = '';
    public string $search = '';
    public string $wireModel = '';
    public array $distinctValues = [];
    public array $filteredValues = [];
    public string $classes = '';
    public string $placeholder = 'Select or type...';
    public bool $isOpen = false;
    public int $minSearchLength = 1;
    public int $maxResults = 10;

    public function mount(
        $model, 
        $column, 
        $value = '',
        $wireModel = '',
        $classes = 'form-control', 
        $placeholder = 'Select or type...', 
        $minSearchLength = 1,
        $maxResults = 10
    ) {
        $this->model = $model;
        $this->column = $column;
        $this->value = ($value === null) ? '' : (string)$value;
        $this->search = ($value === null) ? '' : (string)$value;
        $this->wireModel = $wireModel ?: $column;
        $this->classes = $classes;
        $this->placeholder = $placeholder;
        $this->minSearchLength = $minSearchLength;
        $this->maxResults = $maxResults;
        
        $this->loadDistinctValues();
    }

    public function loadDistinctValues()
    {
        $this->distinctValues = [];
        
        if (class_exists($this->model)) {
            try {
                $model = new $this->model;
                $values = $model->whereNotNull($this->column)
                    ->where($this->column, '!=', '')
                    ->distinct()
                    ->pluck($this->column)
                    ->filter()
                    ->sort()
                    ->values()
                    ->toArray();
                
                $this->distinctValues = $values;
                $this->filteredValues = $values;
            } catch (\Exception $e) {
                $this->distinctValues = [];
                $this->filteredValues = [];
            }
        }
    }

    public function updatedSearch()
    {
        // Ensure search is always a string
        $this->search = ($this->search === null) ? '' : (string)$this->search;
        
        // Filter values as user types, but don't update parent component yet
        if (strlen(trim($this->search)) >= $this->minSearchLength) {
            $this->filterValues();
            // Only open dropdown if there are matches
            if (count($this->filteredValues) > 0) {
                $this->isOpen = true;
            } else {
                $this->isOpen = false;
            }
        } else {
            $this->filteredValues = $this->distinctValues;
            $this->isOpen = false;
        }
    }

    public function updatedValue()
    {
        // Ensure value is always a string
        $this->value = ($this->value === null) ? '' : (string)$this->value;
        // If the value is not in the distinct list and is not empty, auto-close dropdown
        if (!in_array($this->value, $this->distinctValues, true) && strlen($this->value) >= $this->minSearchLength) {
            $this->isOpen = false;
        }
        $this->updateParent();
    }

    protected function updateParent()
    {
        // Ensure value is always a string before dispatching
        $this->value = ($this->value === null) ? '' : (string)$this->value;
        
        // Emit event to update parent component
        $this->dispatch('updateField', field: $this->wireModel, value: $this->value);
    }

    public function filterValues()
    {
        if (empty($this->search)) {
            $this->filteredValues = $this->distinctValues;
        } else {
            $this->filteredValues = array_filter($this->distinctValues, function($value) {
                return stripos($value, $this->search) !== false;
            });
        }
        
        $this->filteredValues = array_slice($this->filteredValues, 0, $this->maxResults);
    }

    public function openDropdown()
    {
        // Only open dropdown if user has typed enough characters
        if (strlen(trim($this->search)) >= $this->minSearchLength) {
            $this->filterValues();
            if (count($this->filteredValues) > 0) {
                $this->isOpen = true;
            } else {
                $this->isOpen = false;
            }
        } else {
            $this->isOpen = false;
        }

    }

    // New: update value to input on blur
    public function blurInput()
    {
        // When input loses focus, set value to whatever is in search
        $this->value = ($this->search === null) ? '' : (string)$this->search;
        $this->isOpen = false;
        $this->updateParent();
    }

    public function closeDropdown()
    {
        $this->isOpen = false;
    }

    public function selectValue($selectedValue)
    {
        // When a value is selected from dropdown, set both value and search to the exact database value
        // This ensures consistent spelling and avoids typos
        $exactValue = ($selectedValue === null) ? '' : trim((string)$selectedValue);
        $this->value = $exactValue;
        $this->search = $exactValue;
        $this->isOpen = false;
        $this->updateParent();
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->value = '';
        $this->filteredValues = $this->distinctValues;
        $this->isOpen = false;
        $this->updateParent();
    }

    public function render()
    {
        // Render without extending or using a layout
        return view('snippets::livewire.af-distinct-select');
    }

    // Handle Livewire property hydration - ensure strings are never null
    public function hydrate()
    {
        $this->value = ($this->value === null) ? '' : (string)$this->value;
        $this->search = ($this->search === null) ? '' : (string)$this->search;
        $this->wireModel = ($this->wireModel === null) ? '' : (string)$this->wireModel;
        $this->classes = ($this->classes === null) ? '' : (string)$this->classes;
        $this->placeholder = ($this->placeholder === null) ? 'Select or type...' : (string)$this->placeholder;
    }
}