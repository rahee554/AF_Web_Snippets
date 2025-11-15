# AFdropdown - Quick Reference & Examples

## ⚡ Quick Reference

### Basic Setup (Copy-Paste Ready)

```php
@livewire('afdropdown', [
    'model' => 'App\Models\YourModel',
    'column' => 'name',
])
```

### Parameters Cheat Sheet

```php
[
    // REQUIRED
    'model'              => 'App\Models\Customer',      // Model class
    'column'             => 'name',                     // Column to search
    
    // COMMON
    'placeholder'        => 'Search...',
    'minSearchLength'    => 1,
    'resultLimit'        => 25,
    'searchMode'         => 'basic',                    // basic|contains|advanced|exact
    
    // ADVANCED
    'columns'            => ['name', 'email'],          // Multi-column
    'additionalColumns'  => ['email', 'phone'],         // Display only
    'displayFormat'      => 'inline',                   // inline|block
    
    // PERFORMANCE
    'enableCache'        => true,
    'cacheTime'          => 3600,
    
    // CUSTOM
    'formatter'          => fn($item) => $item->name,
    'queryCallback'      => fn($q) => $q->where(...),
    'classes'            => 'form-control',
    'debounceTime'       => 300,
]
```

### Event Handling

```php
#[On('afdropdown:selected')]
public function selected($payload)
{
    // $payload['id']     = Model ID
    // $payload['label']  = Display label
    // $payload['data']   = Full model data
    // $payload['model']  = Model class
}
```

### Search Modes

| Mode | Use Case | Example |
|------|----------|---------|
| `basic` | Single column | `john` → finds "john", "johnny" |
| `contains` | Any column match | `john` → finds across all columns |
| `advanced` | All words in any column | `john doe` → both words needed |
| `exact` | Exact match | `john@example.com` → exact only |

---

## 📝 Copy-Paste Examples

### Example 1: Customer Selection

```blade
<!-- Livewire Component: app/Livewire/OrderForm.php -->
<?php
namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class OrderForm extends Component
{
    public ?int $customerId = null;
    public array $customerData = [];

    #[On('afdropdown:selected')]
    public function customerSelected($payload)
    {
        $this->customerId = $payload['id'];
        $this->customerData = $payload['data'];
    }

    public function render()
    {
        return view('livewire.order-form');
    }
}
?>

<!-- View: resources/views/livewire/order-form.blade.php -->
<div class="container">
    <h2>Create Order</h2>
    
    <div class="mb-3">
        <label class="form-label">Customer</label>
        @livewire('afdropdown', [
            'model' => 'App\Models\Customer',
            'column' => 'name',
            'placeholder' => 'Search customers...',
            'minSearchLength' => 1,
        ])
    </div>

    @if($customerId)
        <div class="alert alert-success">
            Selected: {{ $customerData['name'] ?? 'N/A' }}
            ({{ $customerData['email'] ?? 'N/A' }})
        </div>
    @endif
</div>
```

---

### Example 2: Product Search with Stock Display

```blade
@livewire('afdropdown', [
    'model' => 'App\Models\Product',
    'columns' => ['name', 'sku'],
    'searchMode' => 'contains',
    'additionalColumns' => ['sku', 'price'],
    'displayFormat' => 'inline',
    'formatter' => function($product) {
        return sprintf(
            '%s (SKU: %s) - $%.2f',
            $product->name,
            $product->sku,
            $product->price
        );
    },
    'queryCallback' => fn($q) => $q
        ->where('is_active', true)
        ->where('stock_quantity', '>', 0)
        ->orderBy('name'),
    'enableCache' => true,
    'cacheTime' => 3600,
])
```

---

### Example 3: User Selection in Admin Panel

```blade
@livewire('afdropdown', [
    'model' => 'App\Models\User',
    'columns' => ['name', 'email'],
    'searchMode' => 'contains',
    'minSearchLength' => 2,
    'resultLimit' => 15,
    'additionalColumns' => ['email', 'role'],
    'displayFormat' => 'block',
    'formatter' => fn($user) => "{$user->name} ({$user->email})",
    'queryCallback' => fn($q) => $q
        ->where('is_admin', true)
        ->where('is_active', true),
    'placeholder' => 'Search team members...',
])
```

---

### Example 4: Location Search (Multi-Term)

```blade
@livewire('afdropdown', [
    'model' => 'App\Models\Location',
    'columns' => ['city', 'state', 'country'],
    'searchMode' => 'advanced',  // All terms must match
    'minSearchLength' => 2,
    'resultLimit' => 20,
    'additionalColumns' => ['zip_code'],
    'displayFormat' => 'block',
    'formatter' => function($location) {
        return "{$location->city}, {$location->state} {$location->zip_code}";
    },
    'enableCache' => true,
    'cacheTime' => 86400,  // 24 hours
])
```

---

### Example 5: Invoice Line Item Product Selection

```php
// app/Livewire/InvoiceForm.php
<?php
namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\InvoiceItem;

class InvoiceForm extends Component
{
    public int $invoiceId;
    public array $lineItems = [];

    #[On('afdropdown:selected')]
    public function productSelected($payload)
    {
        $product = $payload['data'];
        
        $this->lineItems[] = [
            'product_id' => $payload['id'],
            'product_name' => $product['name'],
            'unit_price' => $product['price'],
            'quantity' => 1,
            'total' => $product['price'],
        ];
    }

    public function render()
    {
        return view('livewire.invoice-form');
    }
}
?>

<!-- resources/views/livewire/invoice-form.blade.php -->
<div>
    <div class="mb-4">
        <h5>Add Line Items</h5>
        @livewire('afdropdown', [
            'model' => 'App\Models\Product',
            'columns' => ['name', 'sku'],
            'searchMode' => 'contains',
            'additionalColumns' => ['sku', 'price'],
            'formatter' => fn($p) => "{$p->name} - SKU: {$p->sku}",
            'queryCallback' => fn($q) => $q->where('is_active', true),
            'enableCache' => true,
        ])
    </div>

    @if(count($lineItems) > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Unit Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lineItems as $item)
                    <tr>
                        <td>{{ $item['product_name'] }}</td>
                        <td>${{ number_format($item['unit_price'], 2) }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>${{ number_format($item['total'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
```

---

### Example 6: Category Selection with Loading State

```php
// app/Livewire/ProductForm.php
<?php
namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class ProductForm extends Component
{
    public ?int $categoryId = null;
    public bool $isLoading = false;

    #[On('afdropdown:selected')]
    public function categorySelected($payload)
    {
        $this->categoryId = $payload['id'];
        $this->isLoading = false;
    }

    public function render()
    {
        return view('livewire.product-form');
    }
}
?>

<!-- resources/views/livewire/product-form.blade.php -->
<form>
    <div class="form-group">
        <label>Category</label>
        @livewire('afdropdown', [
            'model' => 'App\Models\Category',
            'column' => 'name',
            'searchMode' => 'basic',
            'minSearchLength' => 1,
            'enableCache' => true,
            'cacheTime' => 86400,
            'placeholder' => 'Select a category...',
        ])
    </div>
    
    @if($categoryId)
        <div class="alert alert-info">Category selected: #{{ $categoryId }}</div>
    @endif
</form>
```

---

### Example 7: Dependent Dropdowns (Cascading)

```php
// app/Livewire/AddressForm.php
<?php
namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class AddressForm extends Component
{
    public ?int $countryId = null;
    public ?int $stateId = null;

    #[On('afdropdown:selected')]
    public function countrySelected($payload)
    {
        $this->countryId = $payload['id'];
        $this->stateId = null;  // Reset dependent field
    }

    #[On('afdropdown:selected')]
    public function stateSelected($payload)
    {
        $this->stateId = $payload['id'];
    }

    public function render()
    {
        return view('livewire.address-form');
    }
}
?>

<!-- resources/views/livewire/address-form.blade.php -->
<div>
    <div class="mb-3">
        <label>Country</label>
        @livewire('afdropdown', [
            'model' => 'App\Models\Country',
            'column' => 'name',
            'placeholder' => 'Select country...',
        ])
    </div>

    @if($countryId)
        <div class="mb-3">
            <label>State</label>
            @livewire('afdropdown', [
                'model' => 'App\Models\State',
                'column' => 'name',
                'queryCallback' => fn($q) => $q->where('country_id', $this->countryId),
                'placeholder' => 'Select state...',
            ], key("state-{$countryId}"))
        </div>
    @endif
</div>
```

---

### Example 8: Search with Custom Error Handling

```blade
@livewire('afdropdown', [
    'model' => 'App\Models\Customer',
    'columns' => ['name', 'email'],
    'searchMode' => 'contains',
    'throwErrors' => true,  // Show errors in UI
    'queryCallback' => function($query) {
        try {
            return $query->where('status', 'active');
        } catch (\Exception $e) {
            \Log::error('AFdropdown query error', ['error' => $e->message]);
            return $query;
        }
    },
])
```

---

## 🎯 Common Patterns

### Pattern 1: Form with Multiple Selects

```blade
<form wire:submit="submit">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label>Customer</label>
            @livewire('afdropdown', [
                'model' => 'App\Models\Customer',
                'column' => 'name',
            ])
        </div>
        <div class="col-md-6 mb-3">
            <label>Contact</label>
            @livewire('afdropdown', [
                'model' => 'App\Models\Contact',
                'columns' => ['name', 'email'],
                'searchMode' => 'contains',
            ])
        </div>
    </div>
</form>
```

---

### Pattern 2: Search with Filters

```blade
<div class="card">
    <div class="card-header">
        <select wire:model="status" class="form-control">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>
    <div class="card-body">
        @livewire('afdropdown', [
            'model' => 'App\Models\User',
            'column' => 'name',
            'queryCallback' => fn($q) => 
                $this->status 
                    ? $q->where('status', $this->status)
                    : $q,
        ])
    </div>
</div>
```

---

### Pattern 3: Conditional Display

```blade
<div class="form-group">
    @if($userRole === 'admin')
        @livewire('afdropdown', [
            'model' => 'App\Models\User',
            'column' => 'name',
        ])
    @else
        <input type="text" class="form-control" readonly>
    @endif
</div>
```

---

## 🔧 Troubleshooting Patterns

### Issue: No results with spaces

```php
// Solution: Use contains mode for space-separated terms
'searchMode' => 'advanced',
'minSearchLength' => 1,
```

---

### Issue: Slow with large datasets

```php
// Solution: Add query constraints
'queryCallback' => fn($q) => $q
    ->where('is_active', true)
    ->where('category_id', $this->categoryId)  // Filter early
    ->orderByDesc('updated_at'),
'enableCache' => true,
'resultLimit' => 15,  // Limit results
```

---

### Issue: Case-sensitive matching

```php
// Solution: AFdropdown uses LIKE (case-insensitive by default)
// If you need case-sensitive:
// Manually query: DB::raw("BINARY column")
```

---

## 📋 Checklist for Setup

- [ ] Model exists and is importable
- [ ] Column(s) exist in database
- [ ] Table has proper indexes on search columns
- [ ] Livewire 3.6+ installed
- [ ] Bootstrap 5 CSS loaded
- [ ] Alpine.js loaded (for keyboard nav)
- [ ] Event handler created in component
- [ ] Cache driver configured (if using caching)
- [ ] Error handling tested

---

**Version:** 3.6+  
**Last Updated:** 2024
