# 🏗️ AFSnippets Implementation Blueprint

## Top 3 Quick-Win Components to Start With

### 1️⃣ AFFormField (HIGHEST PRIORITY)

**Why First?**
- Used in 95% of forms
- Simple to build
- Maximum immediate impact
- Foundation for other components

**File Structure:**
```
vendor/artflow-studio/snippets/src/
├── Http/Livewire/AFFormField.php
└── resources/views/livewire/af-form-field.blade.php
```

**PHP Component:**
```php
<?php
namespace ArtFlowStudio\Snippets\Http\Livewire;

use Livewire\Component;

class AFFormField extends Component
{
    public string $name;
    public string $label;
    public string $type = 'text'; // text, email, password, number, date, textarea, select, checkbox
    public string $placeholder = '';
    public bool $required = false;
    public string $helpText = '';
    public string $icon = '';
    public array $options = []; // for select/radio/checkbox
    public $value = null;
    public string $classes = 'form-control';
    public bool $disabled = false;
    public array $attributes = [];

    public function mount(
        $name,
        $label = '',
        $type = 'text',
        $placeholder = '',
        $required = false,
        $helpText = '',
        $icon = '',
        $options = [],
        $value = null,
        $classes = 'form-control',
        $disabled = false,
        $attributes = [],
    ) {
        $this->name = $name;
        $this->label = $label ?: ucfirst(str_replace('_', ' ', $name));
        $this->type = $type;
        $this->placeholder = $placeholder;
        $this->required = $required;
        $this->helpText = $helpText;
        $this->icon = $icon;
        $this->options = $options;
        $this->value = $value;
        $this->classes = $classes;
        $this->disabled = $disabled;
        $this->attributes = $attributes;
    }

    public function render()
    {
        $error = null; // Get from parent or session if needed
        
        return view('snippets::livewire.af-form-field', [
            'error' => $error,
        ]);
    }
}
```

**Blade Template:**
```blade
<!-- resources/views/livewire/af-form-field.blade.php -->
<div class="form-group mb-3">
    @if($label)
        <label for="{{ $name }}" class="form-label">
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    <div class="input-group" @if($icon) @endif>
        @if($icon)
            <span class="input-group-text">
                <i class="ki-outline {{ $icon }}"></i>
            </span>
        @endif

        @switch($type)
            @case('textarea')
                <textarea 
                    id="{{ $name }}"
                    name="{{ $name }}"
                    class="form-control @error($name) is-invalid @enderror {{ $classes }}"
                    placeholder="{{ $placeholder }}"
                    @if($required) required @endif
                    @if($disabled) disabled @endif
                    {!! $attributes->merge(['wire:model' => $name])->toHtml() !!}
                >{{ old($name, $value) }}</textarea>
                @break

            @case('select')
                <select 
                    id="{{ $name }}"
                    name="{{ $name }}"
                    class="form-select @error($name) is-invalid @enderror {{ $classes }}"
                    @if($required) required @endif
                    @if($disabled) disabled @endif
                    {!! $attributes->merge(['wire:model' => $name])->toHtml() !!}
                >
                    <option value="">{{ $placeholder ?: 'Select...' }}</option>
                    @foreach($options as $val => $label)
                        <option value="{{ $val }}" @if(old($name, $value) == $val) selected @endif>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @break

            @case('checkbox')
                <input 
                    type="checkbox"
                    id="{{ $name }}"
                    name="{{ $name }}"
                    class="form-check-input @error($name) is-invalid @enderror"
                    value="1"
                    @if(old($name, $value)) checked @endif
                    @if($disabled) disabled @endif
                    {!! $attributes->merge(['wire:model' => $name])->toHtml() !!}
                />
                @break

            @default
                <input 
                    type="{{ $type }}"
                    id="{{ $name }}"
                    name="{{ $name }}"
                    class="form-control @error($name) is-invalid @enderror {{ $classes }}"
                    placeholder="{{ $placeholder }}"
                    value="{{ old($name, $value) }}"
                    @if($required) required @endif
                    @if($disabled) disabled @endif
                    {!! $attributes->merge(['wire:model' => $name])->toHtml() !!}
                />
                @break
        @endswitch
    </div>

    @if($helpText)
        <small class="form-text text-muted d-block mt-2">{{ $helpText }}</small>
    @endif

    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
```

**Usage in Your App:**
```blade
<!-- In your product form -->
@livewire('af-form-field', [
    'name' => 'name',
    'label' => 'Product Name',
    'type' => 'text',
    'required' => true,
    'placeholder' => 'Enter product name...',
    'helpText' => 'This will be displayed on your store',
    'icon' => 'ki-note-2',
])

@livewire('af-form-field', [
    'name' => 'sale_price',
    'label' => 'Sale Price',
    'type' => 'number',
    'required' => true,
    'icon' => 'ki-dollar',
])

@livewire('af-form-field', [
    'name' => 'description',
    'label' => 'Description',
    'type' => 'textarea',
    'placeholder' => 'Describe your product...',
    'helpText' => 'Markdown supported',
])
```

---

### 2️⃣ AFModal (HIGH PRIORITY)

**Why Second?**
- Used for create/edit/delete workflows
- Essential for modern UX
- Depends on no other new component
- Can be built quickly using Bootstrap 5

**PHP Component:**
```php
<?php
namespace ArtFlowStudio\Snippets\Http\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class AFModal extends Component
{
    public string $title = '';
    public string $size = 'md'; // sm, md, lg, xl
    public string $actionLabel = 'Submit';
    public string $closeLabel = 'Close';
    public $actionCallback = null;
    public $onClose = null;
    public bool $backdrop = true;
    public bool $isOpen = false;
    public bool $showHeader = true;
    public bool $form = false;
    public bool $showFooter = true;

    #[On('modal:open')]
    public function open()
    {
        $this->isOpen = true;
        $this->dispatch('modal-opened');
    }

    #[On('modal:close')]
    public function close()
    {
        $this->isOpen = false;
        if ($this->onClose) {
            $this->dispatch($this->onClose);
        }
    }

    public function handleAction()
    {
        if ($this->actionCallback) {
            $this->dispatch($this->actionCallback);
        }
        $this->close();
    }

    public function render()
    {
        return view('snippets::livewire.af-modal');
    }
}
```

**Blade Template (Bootstrap 5):**
```blade
<!-- resources/views/livewire/af-modal.blade.php -->
<div class="modal fade @if($isOpen) show @endif" 
     id="af-modal-{{ $this->id }}" 
     tabindex="-1" 
     style="display: @if($isOpen) block @else none @endif; background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-{{ $size }}">
        <div class="modal-content">
            @if($showHeader)
                <div class="modal-header">
                    <h5 class="modal-title">{{ $title }}</h5>
                    <button type="button" class="btn-close" wire:click="close()"></button>
                </div>
            @endif

            <div class="modal-body">
                {{ $slot }}
            </div>

            @if($showFooter)
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="close()">
                        {{ $closeLabel }}
                    </button>
                    <button type="button" class="btn btn-primary" wire:click="handleAction()">
                        {{ $actionLabel }}
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Backdrop -->
@if($isOpen && $backdrop)
    <div class="modal-backdrop fade show"></div>
@endif
```

**Usage Example:**
```blade
<!-- Trigger Button -->
<button class="btn btn-primary" wire:click="$dispatch('modal:open')">
    Create Product
</button>

<!-- Modal Component -->
@livewire('af-modal', [
    'title' => 'Create Product',
    'actionLabel' => 'Save Product',
    'actionCallback' => 'saveProduct',
    'size' => 'lg',
])
    @livewire('af-form-field', ['name' => 'name', 'label' => 'Product Name', 'required' => true])
    @livewire('af-form-field', ['name' => 'sku', 'label' => 'SKU'])
    @livewire('af-form-field', ['name' => 'sale_price', 'label' => 'Sale Price', 'type' => 'number'])
@endlivewire
```

---

### 3️⃣ AFStatusBadge (HIGH PRIORITY)

**Why Third?**
- Immediate use in your product table
- Simple to implement (30 min)
- Improves table readability
- No dependencies

**PHP Component:**
```php
<?php
namespace ArtFlowStudio\Snippets\Http\Livewire;

use Livewire\Component;

class AFStatusBadge extends Component
{
    public $value;
    public string $type = 'enum'; // boolean, enum, color
    public array $mapping = [];
    public array $labels = [];
    public array $colors = [];
    public bool $showIcon = false;
    public string $iconPrefix = 'ki-';

    public function mount(
        $value,
        $type = 'enum',
        $mapping = [],
        $labels = [],
        $colors = [],
        $showIcon = false,
    ) {
        $this->value = $value;
        $this->type = $type;
        $this->mapping = $mapping;
        $this->labels = $labels;
        $this->colors = $colors;
        $this->showIcon = $showIcon;
    }

    public function getLabel()
    {
        if (!empty($this->labels)) {
            return $this->labels[$this->value] ?? ($this->mapping[$this->value] ?? $this->value);
        }
        
        if ($this->type === 'boolean') {
            return $this->value ? 'Active' : 'Inactive';
        }
        
        return $this->value;
    }

    public function getColor()
    {
        if (!empty($this->colors)) {
            return $this->colors[$this->value] ?? 'secondary';
        }

        if ($this->type === 'boolean') {
            return $this->value ? 'success' : 'danger';
        }

        return 'secondary';
    }

    public function render()
    {
        return view('snippets::livewire.af-status-badge');
    }
}
```

**Blade Template:**
```blade
<!-- resources/views/livewire/af-status-badge.blade.php -->
<span class="badge bg-{{ $this->getColor() }}">
    @if($showIcon)
        <i class="ki-outline {{ $iconPrefix }}check"></i>
    @endif
    {{ $this->getLabel() }}
</span>
```

**Usage in aftable:**
```blade
@livewire('aftable', [
    'model' => '\App\Models\Product',
    'columns' => [
        [
            'key' => 'is_active',
            'label' => 'Status',
            'raw' => '@livewire("af-status-badge", ["value" => $row->is_active, "type" => "boolean"])',
        ],
        [
            'key' => 'status',
            'label' => 'Order Status',
            'raw' => '@livewire("af-status-badge", ["value" => $row->status, "type" => "enum", "colors" => ["pending" => "warning", "completed" => "success", "cancelled" => "danger"]])',
        ],
    ],
])
```

---

## Implementation Checklist: Phase 1

- [ ] **AFFormField**
  - [ ] Create `AFFormField.php` class
  - [ ] Create `af-form-field.blade.php` view
  - [ ] Write unit tests (at least 5 test cases)
  - [ ] Add PHPDoc comments
  - [ ] Update README with examples
  - [ ] Test in your ProductForm

- [ ] **AFModal**
  - [ ] Create `AFModal.php` class
  - [ ] Create `af-modal.blade.php` view
  - [ ] Write unit tests (at least 3 test cases)
  - [ ] Add PHPDoc comments
  - [ ] Test open/close behavior
  - [ ] Verify keyboard navigation (ESC to close)

- [ ] **AFStatusBadge**
  - [ ] Create `AFStatusBadge.php` class
  - [ ] Create `af-status-badge.blade.php` view
  - [ ] Write unit tests (at least 4 test cases)
  - [ ] Integration test with aftable
  - [ ] Test all color variants
  - [ ] Update aftable documentation

- [ ] **Package Updates**
  - [ ] Update `composer.json` version (v2.1.0)
  - [ ] Update `README.md` with new components
  - [ ] Create `CHANGELOG.md` entry
  - [ ] Add new components to documentation index
  - [ ] Create examples for each component

- [ ] **Testing & QA**
  - [ ] Run full test suite
  - [ ] Test in real project (ArtflowERP)
  - [ ] Cross-browser testing
  - [ ] Mobile responsiveness check
  - [ ] Accessibility audit

- [ ] **Release**
  - [ ] Tag version 2.1.0
  - [ ] Push to GitHub
  - [ ] Update Packagist
  - [ ] Announce on README

---

## Code Quality Standards for Each Component

```php
// MUST HAVE:
✅ PHP Component class with PSR-12 formatting
✅ Blade template with Bootstrap 5 classes
✅ Comprehensive PHPDoc blocks
✅ Type hints on all parameters
✅ Property types defined
✅ Error handling & validation
✅ 2-3 unit tests minimum
✅ Usage example in README
✅ Accessibility attributes (aria-*, role)
✅ Responsive design

// OPTIONAL BUT RECOMMENDED:
✨ Event listener examples
✨ Integration with parent component docs
✨ Performance notes
✨ Common pitfalls section
✨ Customization guide
```

---

## Quick Reference: Component Load Order

When building out Phase 1, implement in this order:

```
1. AFFormField
   ├─ Create component
   ├─ Create view
   ├─ Write tests
   └─ Integrate

2. AFStatusBadge (depends on nothing)
   ├─ Create component
   ├─ Create view
   ├─ Write tests
   └─ Integrate with aftable

3. AFModal (depends on nothing)
   ├─ Create component
   ├─ Create view
   ├─ Write tests
   └─ Test with AFFormField
```

---

## File Locations Reference

**New Component Directory:**
```
vendor/artflow-studio/snippets/
├── src/
│   ├── Http/
│   │   └── Livewire/
│   │       ├── AFFormField.php          ← NEW
│   │       ├── AFModal.php              ← NEW
│   │       ├── AFStatusBadge.php        ← NEW
│   │       ├── AFdropdown.php           (existing)
│   │       └── AFDistinctSelect.php     (existing)
│   └── resources/
│       └── views/
│           └── livewire/
│               ├── af-form-field.blade.php       ← NEW
│               ├── af-modal.blade.php            ← NEW
│               ├── af-status-badge.blade.php     ← NEW
│               ├── afdropdown.blade.php          (existing)
│               └── af-distinct-select.blade.php  (existing)
└── composer.json
```

**Documentation:**
```
vendor/artflow-studio/snippets/
├── README.md                 ← Update with new components
├── CHANGELOG.md              ← Add v2.1.0 entry
├── docs/
│   ├── af-form-field.md     ← NEW
│   ├── af-modal.md          ← NEW
│   ├── af-status-badge.md   ← NEW
│   └── afdropdown.md        (existing)
```

---

## Success Metrics After Phase 1

After implementing Phase 1, you should see:

- ✅ 60%+ less form markup code
- ✅ Consistent form styling across projects
- ✅ Reusable modal patterns
- ✅ Better table readability with status badges
- ✅ Faster development on new features
- ✅ Easier onboarding for new developers
- ✅ Improved code maintainability

---

## Next Phase (Phase 2) Preview

After Phase 1 is complete, Phase 2 components:

1. **AFConfirmDialog** - For delete confirmations
2. **AFCard** - For page/section containers
3. **AFEmptyState** - For no-data UI

These will build on the foundation of Phase 1 components.

---

*Ready to start implementing? Begin with AFFormField!*
