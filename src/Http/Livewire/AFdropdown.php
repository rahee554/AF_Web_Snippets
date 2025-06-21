<?php

namespace ArtFlowStudio\Snippets\Http\Livewire;

use Livewire\Component;

class AFdropdown extends Component
{
    public string $model;
    public string $column;
    public string $search = '';
    public array $results = [];
    public $selectedId = null;
    public string $classes = '';
    public string $placeholder = 'Search...';
    public bool $isOpen = false;
    public int $minSearchLength = 3; // Add minimum search length

    public function mount($model, $column, $classes = 'form-control', $placeholder = 'Search...', $minSearchLength = 3)
    {
        $this->model = $model;
        $this->column = $column;
        $this->classes = $classes;
        $this->placeholder = $placeholder;
        $this->minSearchLength = $minSearchLength;
    }

    public function updatedSearch()
    {
        if (strlen($this->search) >= $this->minSearchLength) {
            $this->loadResults();
            $this->isOpen = true; // Open dropdown when search meets minimum length
        } else {
            $this->results = [];
            $this->isOpen = false; // Close dropdown if search is too short
        }
    }

    public function loadResults()
    {
        $this->results = [];
        
        if (strlen($this->search) >= $this->minSearchLength && class_exists($this->model)) {
            try {
                $model = new $this->model;
                $query = $model->where($this->column, 'like', '%' . $this->search . '%');
                
                $items = $query->limit(8)->get();
                
                $this->results = $items->map(function($item) {
                    return [
                        'id' => $item->getKey(),
                        'label' => $item->{$this->column}
                    ];
                })->toArray();
            } catch (\Exception $e) {
                // Handle exceptions silently or log them
                $this->results = [];
            }
        }
    }

    public function openDropdown()
    {
        if (strlen($this->search) >= $this->minSearchLength) {
            $this->isOpen = true;
            $this->loadResults();
        }
    }

    public function closeDropdown()
    {
        $this->isOpen = false;
    }

    public function select($id)
    {
        $this->selectedId = $id;
        $class = $this->model;
        
        try {
            $record = $class::find($id);
            $label = $record ? $record->{$this->column} : null;
            
            $this->search = $label;
            $this->isOpen = false;
            
            // Emit to parent
            $this->dispatch('afdropdown-selected', id: $id, label: $label, class: $class);
        } catch (\Exception $e) {
            // Handle exceptions silently or log them
        }
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->results = [];
        $this->isOpen = false;
        $this->selectedId = null; // Clear selected ID as well
    }

    public function render()
    {
        return view('snippets::livewire.afdropdown');
    }
}