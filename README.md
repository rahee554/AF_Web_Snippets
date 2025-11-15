<div align="center">
  <h1>🚀 AF Web Snippets</h1>
  <p><strong>A powerful collection of Laravel web snippets to supercharge your development workflow</strong></p>
  
  ![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-777BB4?style=flat-square&logo=php)
  ![Laravel Version](https://img.shields.io/badge/laravel-%3E%3D12.0-FF2D20?style=flat-square&logo=laravel)
  ![Livewire](https://img.shields.io/badge/livewire-3.6+-4E56A6?style=flat-square&logo=livewire)
  ![License](https://img.shields.io/badge/license-MIT-green?style=flat-square)
  ![Version](https://img.shields.io/badge/version-2.0.0-orange?style=flat-square)
</div>

---

## 📋 Table of Contents

- [✨ Features](#-features)
- [🔧 Installation](#-installation)
- [🚀 Quick Start](#-quick-start)
- [📚 Components](#-components)
  - [**AFdropdown** - Advanced Searchable Dropdown](#afdropdown---advanced-searchable-dropdown-⭐-main)
  - [Dynamic Dropdown](#-dynamic-dropdown-legacy)
  - [Unique ID Generator](#-unique-id-generator)
  - [Data Formatters](#-data-formatters)
- [� AFdropdown Quick Guide](#-afdropdown-quick-guide)
- [📖 Detailed Documentation](#-detailed-documentation)
- [🔍 Examples](#-examples)
- [📄 License](#-license)

---

## ✨ Features

### ⭐ NEW - AFdropdown Component
- 🔍 **Multiple Search Modes** - basic, contains, advanced, exact
- 📊 **Multi-Column Search** - Search across multiple fields
- ⌨️ **Keyboard Navigation** - Arrow keys, Enter, Escape
- 💾 **Result Caching** - Redis/Cache support for performance
- 🎨 **Custom Formatters** - Format results your way
- � **Custom Callbacks** - Modify queries dynamically
- ⚡ **Livewire 3.6+** - Modern event dispatch & attributes
- ♿ **Accessibility** - Full ARIA support
- 📱 **Responsive** - Bootstrap 5 compatible

### Core Features
- 🎯 **Advanced Dropdowns** - Livewire-powered searchable dropdowns
- 🆔 **Unique ID Generator** - Multiple ID generation strategies
- 📱 **Data Formatters** - Format phone numbers and CNIC
- 📱 **Responsive Design** - Bootstrap-compatible components
- ⚡ **Performance Optimized** - Efficient queries with debouncing
- 🛠️ **Highly Customizable** - Extensive configuration options
- 🔒 **Secure** - Built-in validation and error handling

---

## 🔧 Installation

Install the package via Composer:

```bash
composer require artflow-studio/snippets
```

Publish package assets (optional):

```bash
php artisan vendor:publish --provider="ArtFlowStudio\Snippets\SnippetsServiceProvider"
```

---

## 🚀 Quick Start

### AFdropdown (NEW)

```blade
<!-- Basic Usage -->
@livewire('afdropdown', [
    'model' => 'App\Models\Customer',
    'column' => 'name',
])
```

Handle selection:

```php
use Livewire\Attributes\On;

class MyComponent extends Component
{
    #[On('afdropdown:selected')]
    public function customerSelected($payload)
    {
        $customerId = $payload['id'];
        $customerData = $payload['data'];
        // Handle selection...
    }
}
```

---

## 📚 Components

### AFdropdown - Advanced Searchable Dropdown ⭐ **MAIN**

**Modern Livewire 3.6+ component with enterprise-grade features**

#### Key Features
✅ Real-time filtering with debouncing  
✅ 4 search modes (basic, contains, advanced, exact)  
✅ Multi-column searching  
✅ Result caching for performance  
✅ Keyboard navigation support  
✅ Custom formatters & queries  
✅ Full accessibility (ARIA)  
✅ Comprehensive error handling  

#### Basic Usage

```php
@livewire('afdropdown', [
    'model' => 'App\Models\Customer',
    'column' => 'name',
])
```

#### Advanced Usage

```php
@livewire('afdropdown', [
    'model' => 'App\Models\Customer',
    'columns' => ['name', 'email'],
    'searchMode' => 'contains',
    'minSearchLength' => 2,
    'resultLimit' => 15,
    'enableCache' => true,
    'cacheTime' => 3600,
    'placeholder' => 'Search customers...',
    'additionalColumns' => ['email', 'phone'],
    'formatter' => fn($c) => "{$c->name} ({$c->email})",
    'queryCallback' => fn($q) => $q->where('status', 'active'),
])
```

#### Search Modes

| Mode | Description | Example |
|------|-------------|---------|
| `basic` | Single column LIKE search | `john` → finds "john", "johnny" |
| `contains` | Multi-column OR search | `john` → searches all columns |
| `advanced` | Word-by-word matching | `john doe` → both words needed |
| `exact` | Exact match only | `john@email.com` → exact match |

#### Event Handling

```php
#[On('afdropdown:selected')]
public function handleSelection($payload)
{
    // $payload['id']     - Model ID
    // $payload['label']  - Display label
    // $payload['data']   - Full model data
    // $payload['model']  - Model class name
}

#[On('afdropdown:cleared')]
public function handleClear()
{
    // Search was cleared
}
```

#### Keyboard Navigation

| Key | Action |
|-----|--------|
| ↓ | Next result |
| ↑ | Previous result |
| ↩ | Select highlighted |
| Esc | Close dropdown |

#### 📖 Full Documentation

**Complete guides with detailed examples:**
- 📄 **[AFdropdown-GUIDE.md](./AFdropdown-GUIDE.md)** - Complete reference guide (300+ lines)
- 📄 **[EXAMPLES.md](./EXAMPLES.md)** - 8+ real-world examples

---

### Dynamic Dropdown (Legacy)

Basic searchable dropdown component. For new projects, use **AFdropdown** instead.

```php
@livewire('dynamic-dropdown', [
    'data' => $items,
    'label' => 'name',
    'value' => 'id',
])
```

---

### Unique ID Generator

Generate unique identifiers for your models:

```php
use ArtFlowStudio\Snippets\Traits\GeneratesUniqueIds;

class Invoice extends Model
{
    use GeneratesUniqueIds;
    
    protected $uniqueIdColumn = 'invoice_number';
    protected $uniqueIdStrategy = '6digit'; // or 'base36'
}

// Usage
$invoice = Invoice::create([...]);
// invoice_number is auto-generated: "123456" or "ABC123D"
```

---

### Data Formatters

Format phone numbers and CNIC:

```php
use ArtFlowStudio\Snippets\Traits\FormatsData;

class Customer extends Model
{
    use FormatsData;
}

// Pakistani phone number formatting
Customer::formatPhoneNumber('03001234567'); // +92-300-1234567

// CNIC formatting
Customer::formatCNIC('12345-1234567-1'); // 12345-1234567-1
```

---

## 🎯 AFdropdown Quick Guide

### Installation & Setup

1. **Component loads in Livewire:**
```bash
# Already available at: vendor/artflow-studio/snippets/src/Http/Livewire/AFdropdown.php
```

2. **Add to your Livewire component:**
```php
#[On('afdropdown:selected')]
public function selected($payload) { }
```

3. **Use in blade:**
```blade
@livewire('afdropdown', ['model' => 'App\Models\YourModel', 'column' => 'name'])
```

### Common Configurations

**Basic Customer Search:**
```php
['model' => 'App\Models\Customer', 'column' => 'name']
```

**Multi-Field Search:**
```php
[
    'model' => 'App\Models\Customer',
    'columns' => ['name', 'email', 'phone'],
    'searchMode' => 'contains',
]
```

**With Caching:**
```php
[
    'model' => 'App\Models\Product',
    'column' => 'name',
    'enableCache' => true,
    'cacheTime' => 3600,
]
```

**Custom Format:**
```php
[
    'model' => 'App\Models\User',
    'column' => 'name',
    'formatter' => fn($u) => "{$u->name} ({$u->email})",
]
```

### Performance Tips

- ✅ Use `minSearchLength` to reduce queries (set to 2)
- ✅ Enable `enableCache` for static data
- ✅ Use `queryCallback` to filter early
- ✅ Add database indexes on search columns
- ✅ Reduce `resultLimit` if not needed

---

## 📖 Detailed Documentation

### AFdropdown Complete Guides

#### [AFdropdown-GUIDE.md](./AFdropdown-GUIDE.md) - **300+ Lines**
Comprehensive reference covering:
- All configuration options
- All 4 search modes with examples
- Caching strategies
- Event handling
- Keyboard navigation
- 5+ real-world examples
- Complete troubleshooting guide
- Performance optimization
- Best practices

#### [EXAMPLES.md](./EXAMPLES.md) - **Copy-Paste Ready**
Production-ready examples:
- Basic customer selection
- Product search with stock
- User selection in admin
- Location search (multi-term)
- Invoice line items
- Category selection
- Dependent dropdowns
- Error handling patterns

---

## 🔍 Examples

### Example 1: Basic Customer Selection

```php
// Component
class CreateOrder extends Component
{
    public ?int $customerId = null;

    #[On('afdropdown:selected')]
    public function customerSelected($payload)
    {
        $this->customerId = $payload['id'];
    }

    public function render()
    {
        return view('create-order');
    }
}

// View
@livewire('afdropdown', [
    'model' => 'App\Models\Customer',
    'column' => 'name',
    'placeholder' => 'Search customers...',
])
```

### Example 2: Multi-Column Product Search

```blade
@livewire('afdropdown', [
    'model' => 'App\Models\Product',
    'columns' => ['name', 'sku'],
    'searchMode' => 'contains',
    'formatter' => fn($p) => "{$p->name} (SKU: {$p->sku})",
    'queryCallback' => fn($q) => $q->where('stock_quantity', '>', 0),
    'enableCache' => true,
    'cacheTime' => 3600,
])
```

### Example 3: Advanced Location Search

```blade
@livewire('afdropdown', [
    'model' => 'App\Models\Location',
    'columns' => ['city', 'state', 'country'],
    'searchMode' => 'advanced',  // All terms must match
    'minSearchLength' => 2,
    'formatter' => fn($l) => "{$l->city}, {$l->state} {$l->country}",
])
```

---

## 📋 Troubleshooting

### No Results Showing?

1. Check `minSearchLength` setting
2. Verify model and column names exist
3. Test query in Tinker: `php artisan tinker`
4. Check that model has data

### Slow Performance?

1. Enable caching: `'enableCache' => true`
2. Add database indexes
3. Use `queryCallback` to filter early
4. Reduce `resultLimit`

### Keyboard Navigation Not Working?

1. Click input to focus first
2. Check browser console (F12) for errors
3. Verify Alpine.js is loaded
4. Try in different browser

---

## 📄 License

MIT License - See LICENSE file for details

---

## 👥 Contributors

Built with ❤️ for the Laravel & Livewire community

---

## 📞 Support

For issues and questions:
1. Check [AFdropdown-GUIDE.md](./AFdropdown-GUIDE.md) for detailed documentation
2. Review [EXAMPLES.md](./EXAMPLES.md) for code samples
3. See troubleshooting sections
4. Check error logs

---

**Latest Version:** 2.0.0  
**Livewire:** 3.6+  
**Laravel:** 12+  
**PHP:** 8.2+


### Service Provider Registration

The package uses Laravel's auto-discovery feature. If you're using Laravel 5.5+, the service provider will be automatically registered.

### Publish Assets (Optional)

```bash
php artisan vendor:publish --provider="ArtFlowStudio\Snippets\SnippetsServiceProvider"
```

---

## 🚀 Quick Start

Add the following line at the end of your `<body>` tag in your Blade layout:

```blade
@stack('scripts')
```

**That's it!** You're ready to use AF Web Snippets in your Laravel application.

---

## 📚 Components

### 🎯 Dynamic Dropdown (AFDropdown)

A powerful Livewire component for searchable dropdowns with real-time filtering, minimum search length, and elegant UI.

#### Basic Usage

```blade
<livewire:afdropdown 
    :model="App\Models\User::class" 
    column="name" 
    placeholder="Search users..." 
/>
```

#### Advanced Configuration

```blade
<livewire:a-f-dropdown 
    :model="App\Models\User::class" 
    column="name" 
    classes="form-control form-control-lg" 
    placeholder="Search users..."
    :min-search-length="2"
/>
```

#### AFDropdown Event System

AFDropdown is a fully event-driven component. When a user selects an item, it dispatches events that parent components can listen to and react upon.

##### Event Flow Diagram

```
User Types in Search Input
        ↓
updatedSearch() fires with debounce.300ms
        ↓
loadResults() queries database
        ↓
Results display in dropdown
        ↓
User clicks item
        ↓
select($id) method fires
        ↓
'afdropdown-selected' event dispatched to parent
        ↓
Parent component listener catches event
        ↓
Parent handles selection data and updates state
```

##### Event Data Structure

When AFDropdown emits the `afdropdown-selected` event, it sends the following data:

```php
[
    'id'    => 123,                              // Primary key of selected item
    'label' => 'John Doe',                       // Value of search column
    'class' => 'App\\Models\\Customer',          // Full model class name
    'data'  => [                                 // Complete model data
        'id' => 123,
        'name' => 'John Doe',
        'email' => 'john@example.com',
        // ... all model attributes
    ]
]
```

##### Listening to Events with #[On] Attribute (Recommended)

```php
<?php

namespace App\Livewire\BranchManager\Invoices;

use Livewire\Attributes\On;
use Livewire\Component;

class CreateInvoice extends Component
{
    public $customer_id = '';
    public $customer_name = '';
    
    #[On('afdropdown-selected')]
    public function handleCustomerSelected($data)
    {
        // Only process Customer model selections
        if ($data['class'] === 'App\\Models\\Customer') {
            $this->customer_id = $data['id'];
            $this->customer_name = $data['label'];
            
            // You can also access complete model data
            $email = $data['data']['email'] ?? null;
            
            // Emit event to other components if needed
            $this->dispatch('customer-selected', ['id' => $this->customer_id]);
        }
    }
    
    public function render()
    {
        return view('livewire.invoices.create-invoice');
    }
}
```

##### Listening to Multiple Dropdowns

Use the model class to differentiate between different dropdown selections:

```php
#[On('afdropdown-selected')]
public function handleSelection($data)
{
    match ($data['class']) {
        'App\\Models\\Customer' => $this->handleCustomer($data),
        'App\\Models\\Supplier' => $this->handleSupplier($data),
        'App\\Models\\Product' => $this->handleProduct($data),
        default => null
    };
}

private function handleCustomer($data)
{
    $this->customer_id = $data['id'];
}

private function handleSupplier($data)
{
    $this->supplier_id = $data['id'];
}

private function handleProduct($data)
{
    $this->product_id = $data['id'];
}
```

##### Listening for Clear Events

AFDropdown also emits a `afdropdown-cleared` event when the user clears the search:

```php
#[On('afdropdown-cleared')]
public function handleSearchCleared()
{
    $this->customer_id = '';
    $this->customer_name = '';
}
```

#### Listening to Selection Events in Livewire Components

The AFDropdown component emits the `afdropdown-selected` event when a user selects an item. Parent Livewire components can listen to this event:

```php
<?php

namespace App\Livewire;

use Livewire\Component;

class CreateInvoice extends Component
{
    public $customer_id = '';

    #[On('afdropdown-selected')]
    public function handleCustomerSelected($data)
    {
        if ($data['class'] === 'App\\Models\\Customer') {
            $this->customer_id = $data['id'];
        }
    }

    public function render()
    {
        return view('livewire.invoices.create-invoice');
    }
}
```

Or using the traditional `getListeners()` method:

```php
public function getListeners()
{
    return [
        'afdropdown-selected' => 'handleCustomerSelected',
    ];
}

public function handleCustomerSelected($data)
{
    if ($data['class'] === 'App\\Models\\Customer') {
        $this->customer_id = $data['id'];
    }
}
```

#### Using AFDropdown in Blade Templates

Use the `@livewire` directive to mount the component:

```blade
<label class="form-label">Customer <span class="text-danger">*</span></label>
@livewire('afdropdown', [
    'model' => 'App\Models\Customer',
    'column' => 'name',
    'classes' => 'form-control form-control-sm',
    'placeholder' => 'Search customers...',
    'minSearchLength' => 2,
    'resultLimit' => 10,
    'additionalColumns' => ['email', 'phone']
])
@error('customer_id') <span class="text-danger">{{ $message }}</span> @enderror
```

#### AFDropdown Configuration Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `model` | string | required | Full namespace to Eloquent model class |
| `column` | string | required | Database column to search and display |
| `classes` | string | 'form-control' | CSS classes for input styling |
| `placeholder` | string | 'Search...' | Input placeholder text |
| `minSearchLength` | int | 3 | Minimum characters before showing results |
| `resultLimit` | int | 8 | Maximum number of results to display |
| `displayFormat` | string | 'label' | Format for displaying results |
| `additionalColumns` | array | [] | Additional columns to show in results |

#### Advanced: Custom Search Filtering

To customize the search query (e.g., filter by branch, status, etc.), extend the component:

```php
<?php

namespace App\Livewire;

use ArtFlowStudio\Snippets\Http\Livewire\AFdropdown;

class CustomCustomerDropdown extends AFdropdown
{
    public function loadResults()
    {
        $this->results = [];
        
        if (strlen($this->search) >= $this->minSearchLength && class_exists($this->model)) {
            try {
                $model = new $this->model;
                $query = $model->where($this->column, 'like', '%' . $this->search . '%')
                    ->where('is_active', true)  // Only active customers
                    ->where('branch_id', auth()->user()->branch_id)  // User's branch only
                    ->whereNull('deleted_at');  // Not deleted
                
                $items = $query->limit($this->resultLimit)->get();
                
                $this->results = $items->map(function($item) {
                    return $this->formatResult($item);
                })->toArray();
            } catch (\Exception $e) {
                $this->results = [];
            }
        }
    }
}
```

Then register it in your service provider:

```php
Livewire::component('custom-customer-dropdown', CustomCustomerDropdown::class);
```

#### Example: Customer Selection in Invoices

```blade
<form wire:submit="saveInvoice">
    <div class="row mb-4">
        <div class="col-md-6">
            <label class="form-label">Customer <span class="text-danger">*</span></label>
            @livewire('afdropdown', [
                'model' => 'App\Models\Customer',
                'column' => 'name',
                'classes' => 'form-control',
                'placeholder' => 'Search customers...',
            ])
            @error('customer_id')
                <span class="text-danger text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>
    
    <!-- Invoice items and other fields -->
    
    <button type="submit" class="btn btn-primary">Save Invoice</button>
</form>
```

### 🔍 Distinct Select (AFDistinctSelect)

A specialized component for preventing spelling mistakes by showing existing values from the database. Perfect for fields like cities, countries, or categories where you want consistent data entry.

#### Basic Usage

```blade
@AFDistinctSelect([
    'model' => App\Models\Hotel::class,
    'column' => 'city',
    'value' => $city,
    'wireModel' => 'city',
    'classes' => 'form-control',
    'placeholder' => 'Select or type city name'
])
```

#### Advanced Configuration

```blade
@AFDistinctSelect([
    'model' => App\Models\Product::class,
    'column' => 'category',
    'value' => $selectedCategory,
    'wireModel' => 'category',
    'classes' => 'form-control form-control-sm',
    'placeholder' => 'Select category...',
    'minSearchLength' => 2,
    'maxResults' => 15
])
```

#### Handling Updates in Parent Component

```php
class YourLivewireComponent extends Component
{
    public $city = '';
    
    protected $listeners = ['updateField' => 'setField'];
    
    public function setField($field, $value)
    {
        $this->$field = $value;
    }
}
```

#### AFDistinctSelect Features

- ✅ **Spelling Prevention** - Shows existing values to prevent duplicates
- ✅ **Type-ahead Search** - Real-time filtering of existing values
- ✅ **Exact Value Selection** - Updates input with exact database spelling
- ✅ **Data Consistency** - Ensures uniform data entry
- ✅ **Bootstrap Compatible** - Seamless integration with Bootstrap styling
- ✅ **Customizable** - Flexible configuration options

#### Configuration Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `model` | string | required | Eloquent model class |
| `column` | string | required | Database column to search and display |
| `value` | string | '' | Current field value |
| `wireModel` | string | column name | Livewire property to bind |
| `classes` | string | 'form-control' | CSS classes for input |
| `placeholder` | string | 'Select or type...' | Input placeholder text |
| `minSearchLength` | int | 1 | Minimum characters before search |
| `maxResults` | int | 10 | Maximum results to show |

#### AFDropdown Features

- ✅ **Real-time Search** - Debounced search with 300ms delay
- ✅ **Minimum Search Length** - Configurable minimum characters (default: 3)
- ✅ **Loading States** - Visual feedback during search
- ✅ **Clear Button** - Easy reset functionality
- ✅ **Keyboard Navigation** - Accessible dropdown interaction
- ✅ **Bootstrap Compatible** - Seamless integration with Bootstrap styling

#### AFDropdown Configuration Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `model` | string | required | Eloquent model class |
| `column` | string | required | Database column to search and display |
| `classes` | string | 'form-control' | CSS classes for input |
| `placeholder` | string | 'Search...' | Input placeholder text |
| `min-search-length` | int | 3 | Minimum characters before search |

---

### 🆔 Unique ID Generator

Generate unique identifiers for your models with multiple strategies and collision detection.

#### Basic Usage

```php
// Generate unique ID for a model
$uniqueId = generateUniqueID(User::class, 'user_id');

// Generate 6-digit unique ID
$id = unique6digitID(); // Returns: "123456"

// Generate Base36 unique ID
$id = generateUniqueBase36ID(); // Returns: "AB12CD"
```

#### Advanced Usage

```php
// In your model
class User extends Model
{
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->user_id = generateUniqueID(self::class, 'user_id');
        });
    }
}
```

#### ID Generation Methods

1. **`unique6digitID()`** - Generates 6-digit numeric IDs (100000-999999)
2. **`generateUniqueBase36ID()`** - Generates Base36 IDs with timestamp encoding
3. **`generateUniqueID($model, $column)`** - Generates unique IDs with collision checking

---

### 📱 Data Formatters

Format common data types for Pakistani users with intelligent detection and formatting.

#### Pakistani Phone Number Formatter

```php
// Format Pakistani phone numbers
echo formatContactPK('03001234567');    // +923001234567
echo formatContactPK('00923001234567'); // +923001234567
echo formatContactPK('+923001234567');  // +923001234567
echo formatContactPK('923001234567');   // +923001234567

// International numbers pass through
echo formatContactPK('+12345678901');   // +12345678901
```

#### Pakistani CNIC Formatter

```php
// Format Pakistani CNIC numbers
echo formatCnicPK('1234567890123');     // 12345-6789012-3
echo formatCnicPK('12345-6789012-3');   // 12345-6789012-3

// Handle special cases
echo formatCnicPK('PASSPORT123');       // PASSPORT123 (unchanged)
```

#### Usage in Models

```php
class User extends Model
{
    // Automatically format phone on save
    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = formatContactPK($value);
    }
    
    // Automatically format CNIC on save  
    public function setCnicAttribute($value)
    {
        $this->attributes['cnic'] = formatCnicPK($value);
    }
}
```

---

## 🎨 Customization

### Styling AFDropdown

```css
/* Custom dropdown styles */
.afdropdown-wrapper .dropdown-menu {
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.afdropdown-wrapper .dropdown-item:hover {
    background-color: #f8f9fa;
}
```

---

## 🔍 Examples

### Dropdown with Event Handling

```blade
<livewire:a-f-dropdown 
    :model="App\Models\Category::class" 
    column="name" 
    placeholder="Select category..."
    wire:key="category-dropdown"
/>

<script>
document.addEventListener('livewire:init', function () {
    Livewire.on('afdropdown-selected', (data) => {
        // Update related dropdowns
        @this.set('selected_category_id', data.id);
        
        // Show success message
        toastr.success(`Selected: ${data.label}`);
    });
});
</script>
```

---

## 🛠️ Requirements

- PHP >= 8.0
- Laravel >= 9.0
- Livewire >= 3.0
- Bootstrap >= 5.0 (for styling)

---

## 🤝 Contributing

We welcome contributions! Please feel free to submit a Pull Request. For major changes, please open an issue first to discuss what you would like to change.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 👥 Authors

- **[@RaHee554](https://www.github.com/rahee554)** - *Initial work and maintenance*

---

## 🙏 Acknowledgments

- Laravel community for the amazing framework
- Livewire team for the reactive components
- Bootstrap team for the UI framework
- All contributors who help improve this package

---

<div align="center">
  <p>Made with ❤️ for the Laravel community</p>
  <p>
    <a href="https://github.com/artflow-studio/snippets">⭐ Star us on GitHub</a> |
    <a href="https://github.com/artflow-studio/snippets/issues">🐛 Report Bug</a> |
    <a href="https://github.com/artflow-studio/snippets/issues">💡 Request Feature</a>
  </p>
</div>
