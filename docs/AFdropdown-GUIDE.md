# AFdropdown - Complete Guide

Advanced Searchable Dropdown Component for Livewire 3.6+

## 📋 Table of Contents

1. [Overview](#overview)
2. [Basic Usage](#basic-usage)
3. [Configuration](#configuration)
4. [Search Modes](#search-modes)
5. [Advanced Features](#advanced-features)
6. [Keyboard Navigation](#keyboard-navigation)
7. [Events & Callbacks](#events--callbacks)
8. [Caching](#caching)
9. [Real-World Examples](#real-world-examples)
10. [Troubleshooting](#troubleshooting)

---

## Overview

**AFdropdown** is a powerful, production-ready Livewire 3 component for building searchable dropdown fields. It provides:

- ✅ Real-time filtering with debouncing
- ✅ Multiple search modes (basic, contains, advanced, exact)
- ✅ Multi-column search capabilities
- ✅ Keyboard navigation (Arrow Keys, Enter, Escape)
- ✅ Result caching for performance
- ✅ Custom formatters and query callbacks
- ✅ Livewire 3.6+ event dispatch
- ✅ Full accessibility support
- ✅ Bootstrap 5 styling
- ✅ Type hints & error handling

---

## Basic Usage

### Minimal Setup

```blade
@livewire('afdropdown', [
    'model' => 'App\Models\Customer',
    'column' => 'name',
])
```

This creates a searchable dropdown that queries the `customers` table by the `name` column.

### In a Livewire Component

```php
// app/Livewire/OrderForm.php
use Livewire\Component;

class OrderForm extends Component
{
    public ?int $customerId = null;
    public $customerData = [];

    #[On('afdropdown:selected')]
    public function customerSelected($payload)
    {
        $this->customerId = $payload['id'];
        $this->customerData = $payload['data'];
        // Handle customer selection
    }

    public function render()
    {
        return view('livewire.order-form');
    }
}
```

```blade
<!-- resources/views/livewire/order-form.blade.php -->
<div>
    <div class="mb-3">
        <label for="customer" class="form-label">Select Customer</label>
        @livewire('afdropdown', [
            'model' => 'App\Models\Customer',
            'column' => 'name',
            'placeholder' => 'Search for a customer...',
        ])
    </div>
    
    @if($customerId)
        <p>Selected Customer: {{ $customerData['name'] ?? 'N/A' }}</p>
    @endif
</div>
```

---

## Configuration

### All Available Parameters

```php
@livewire('afdropdown', [
    // Model Configuration (Required)
    'model'                => 'App\Models\Customer',        // Model class or instance
    'column'              => 'name',                        // Default search column (string)
    
    // Search Configuration
    'columns'             => ['name', 'email'],             // Multi-column search (array)
    'searchMode'          => 'basic',                       // Search mode: basic|contains|advanced|exact
    'minSearchLength'     => 1,                             // Min characters before searching
    'resultLimit'         => 25,                            // Max results to display
    
    // UI Configuration
    'classes'             => 'form-control form-control-lg', // Bootstrap classes
    'placeholder'         => 'Search...',                   // Input placeholder text
    'debounceTime'        => 300,                           // Debounce milliseconds
    
    // Advanced Configuration
    'additionalColumns'   => ['phone', 'email', 'city'],    // Display info columns
    'displayFormat'       => 'inline',                      // inline|block|detail
    
    // Performance
    'enableCache'         => true,                          // Enable result caching
    'cacheTime'           => 3600,                          // Cache duration (seconds)
    
    // Callbacks & Formatters
    'formatter'           => null,                          // Custom display formatter
    'queryCallback'       => null,                          // Custom query modifier
    'onSelect'            => null,                          // Selection callback
    'onClear'             => null,                          // Clear callback
    
    // Error Handling
    'throwErrors'         => false,                         // Debug mode
])
```

### Common Configurations

#### Simple Text Search
```php
'column' => 'name',
'searchMode' => 'basic',
'minSearchLength' => 2,
```

#### Multi-Column Search
```php
'columns' => ['name', 'email', 'phone'],
'searchMode' => 'contains',
'minSearchLength' => 1,
```

#### Professional Setup
```php
'columns' => ['name', 'email'],
'searchMode' => 'advanced',
'minSearchLength' => 2,
'resultLimit' => 15,
'enableCache' => true,
'cacheTime' => 7200,
'additionalColumns' => ['phone', 'email', 'city'],
'displayFormat' => 'block',
```

---

## Search Modes

AFdropdown supports 4 different search algorithms:

### 1. **basic** (Default)
Searches a single column with LIKE pattern matching.

```php
'searchMode' => 'basic',
'column' => 'name',
// Searches: "john" → finds "john", "johnny", "johnson"
```

**Best for:** Simple single-column searches

### 2. **contains**
Searches multiple columns with OR logic.

```php
'searchMode' => 'contains',
'columns' => ['name', 'email', 'phone'],
// "john" → finds rows where name OR email OR phone contains "john"
```

**Best for:** Multi-field search where any field match is valid

### 3. **advanced**
Word-by-word matching across all columns (AND logic).

```php
'searchMode' => 'advanced',
'columns' => ['name', 'email', 'city'],
// "john doe ny" → finds all three words in any column combination
// More strict matching than 'contains'
```

**Best for:** Precise searches requiring multiple terms

### 4. **exact**
Exact match on primary column (case-insensitive).

```php
'searchMode' => 'exact',
'column' => 'email',
// "john@example.com" → finds exact email match only
```

**Best for:** Email, SKU, or unique identifier searches

---

## Advanced Features

### Custom Display Formatter

Format results with custom logic:

```php
'formatter' => function($item) {
    return sprintf(
        '%s (%s) - %s',
        $item->name,
        $item->email,
        $item->city
    );
},

// Output: "John Doe (john@example.com) - New York"
```

Or use a callable class:

```php
'formatter' => new CustomerFormatter(),

// app/Formatters/CustomerFormatter.php
class CustomerFormatter {
    public function __invoke($customer)
    {
        return "{$customer->name} - {$customer->email}";
    }
}
```

### Custom Query Callback

Modify the query before execution:

```php
'queryCallback' => function($query) {
    return $query
        ->where('status', 'active')
        ->where('subscription_active', true)
        ->orderBy('name');
},
```

### Multiple Callbacks

```php
'queryCallback' => fn($q) => $q
    ->whereIn('status', ['active', 'trial'])
    ->with('company')
    ->withCount('orders')
    ->orderByDesc('orders_count'),
```

### Additional Display Columns

Show extra information without including in search:

```php
'additionalColumns' => ['email', 'phone', 'city'],
'displayFormat' => 'block',

// Output:
// John Doe
// (john@example.com, 555-1234, New York)
```

---

## Keyboard Navigation

AFdropdown provides full keyboard support:

| Key | Action |
|-----|--------|
| `↓ Arrow Down` | Next result |
| `↑ Arrow Up` | Previous result |
| `↩ Enter` | Select highlighted result |
| `Esc` | Close dropdown |
| `Type` | Search |
| `Backspace` | Clear character |

### Usage

Simply press keys when dropdown is open:

```
User presses:
- Type "joh" → Shows matching results
- ↓ → Highlights first result (blue)
- ↓ → Highlights second result (blue)
- ↩ → Selects highlighted result
- Auto-closes dropdown
- Parent component receives 'afdropdown:selected' event
```

---

## Events & Callbacks

### Selection Event

When user selects a result:

```php
#[On('afdropdown:selected')]
public function customerSelected($payload)
{
    // $payload contains:
    // - id: The model ID
    // - label: The displayed label
    // - data: Full model data
    // - model: Model class name
    
    $this->customerId = $payload['id'];
    $this->processCustomer($payload['data']);
}
```

### Clear Event

When user clears the search:

```php
#[On('afdropdown:cleared')]
public function searchCleared($payload)
{
    // User clicked the clear button
    // Payload contains current search term
    $this->reset('customerId');
}
```

### Component Callbacks

Handle selection within AFdropdown:

```php
'onSelect' => function($id, $data) {
    Log::info('Selected', ['id' => $id]);
},

'onClear' => function() {
    Log::info('Cleared search');
},
```

---

## Caching

Improve performance by caching search results:

### Enable Caching

```php
'enableCache' => true,
'cacheTime' => 3600,  // 1 hour
```

### Cache Behavior

- Results are cached per search term and model
- Cache key: `afdropdown_{model}_{searchMode}_{term}`
- Automatic cache expiration after `cacheTime` seconds
- Manual clear with `$component->clearCache()`

### Manual Cache Management

In your Livewire component:

```php
public function refresh()
{
    // Clear AFdropdown cache
    $this->dispatch('clear-afdropdown-cache');
}
```

### When to Use Caching

✅ **Use caching for:**
- Rarely changing data (categories, countries)
- Public search lists
- High-traffic pages
- Complex query results

❌ **Don't cache:**
- Real-time data (stock levels)
- User-specific results
- Frequently updated records

---

## Real-World Examples

### 1. Customer Selection in Orders

```php
// app/Livewire/CreateOrder.php
class CreateOrder extends Component
{
    public ?int $customerId = null;
    public string $customerName = '';

    #[On('afdropdown:selected')]
    public function customerSelected($payload)
    {
        $this->customerId = $payload['id'];
        $this->customerName = $payload['label'];
    }

    public function save()
    {
        Order::create([
            'customer_id' => $this->customerId,
            'total' => 0,
        ]);
    }
}
```

```blade
<!-- resources/views/livewire/create-order.blade.php -->
<form wire:submit="save">
    <div class="mb-3">
        <label class="form-label">Customer</label>
        @livewire('afdropdown', [
            'model' => 'App\Models\Customer',
            'columns' => ['name', 'email'],
            'searchMode' => 'contains',
            'additionalColumns' => ['email', 'phone'],
            'queryCallback' => fn($q) => $q->where('status', 'active'),
            'placeholder' => 'Search customers by name or email...',
        ])
    </div>
    
    <button type="submit" class="btn btn-primary">Create Order</button>
</form>
```

---

### 2. Employee Search with Company Filter

```php
class HRPanel extends Component
{
    public ?int $employeeId = null;
    public int $companyId = 1;

    #[On('afdropdown:selected')]
    public function employeeSelected($payload)
    {
        $this->employeeId = $payload['id'];
    }

    public function render()
    {
        return view('livewire.hr-panel');
    }
}
```

```blade
<div>
    <select wire:model="companyId" class="form-control mb-3">
        <option value="">Select Company</option>
        @foreach($companies as $company)
            <option value="{{ $company->id }}">{{ $company->name }}</option>
        @endforeach
    </select>

    @if($companyId)
        @livewire('afdropdown', [
            'model' => 'App\Models\Employee',
            'columns' => ['first_name', 'last_name', 'email'],
            'searchMode' => 'advanced',
            'additionalColumns' => ['email', 'department'],
            'queryCallback' => fn($q) => $q
                ->where('company_id', $this->companyId)
                ->where('status', 'active'),
            'formatter' => fn($emp) => "{$emp->first_name} {$emp->last_name} ({$emp->email})",
            'enableCache' => true,
            'cacheTime' => 1800,
        ])
    @endif
</div>
```

---

### 3. Product Selection with Custom Formatter

```php
@livewire('afdropdown', [
    'model' => 'App\Models\Product',
    'columns' => ['name', 'sku'],
    'searchMode' => 'contains',
    'resultLimit' => 20,
    'enableCache' => true,
    'cacheTime' => 3600,
    'formatter' => function($product) {
        return sprintf(
            '%s - $%s (Stock: %d)',
            $product->name,
            number_format($product->price, 2),
            $product->stock_quantity
        );
    },
    'queryCallback' => fn($q) => $q
        ->where('is_active', true)
        ->with('category')
        ->withCount('orderItems'),
    'additionalColumns' => ['sku', 'category.name'],
])
```

---

### 4. Tag Selection with Multiple Values

```blade
@livewire('afdropdown', [
    'model' => 'App\Models\Tag',
    'column' => 'name',
    'searchMode' => 'basic',
    'minSearchLength' => 1,
    'resultLimit' => 50,
    'placeholder' => 'Search and select tags...',
    'enableCache' => true,
    'cacheTime' => 86400,  // 24 hours
])
```

---

### 5. Advanced Location Search

```php
@livewire('afdropdown', [
    'model' => 'App\Models\Location',
    'columns' => ['city', 'state', 'country'],
    'searchMode' => 'advanced',
    'minSearchLength' => 2,
    'displayFormat' => 'block',
    'additionalColumns' => ['state', 'country', 'postal_code'],
    'formatter' => function($location) {
        return "{$location->city}, {$location->state}, {$location->country}";
    },
    'queryCallback' => fn($q) => $q
        ->where('is_verified', true)
        ->orderBy('country')
        ->orderBy('state')
        ->orderBy('city'),
    'enableCache' => true,
    'cacheTime' => 86400,
])
```

---

## Troubleshooting

### Results Not Showing

**Problem:** Dropdown returns no results

**Solutions:**
1. Check minimum search length:
   ```php
   'minSearchLength' => 1,  // Lower to 1
   ```

2. Verify model and column names:
   ```php
   'model' => 'App\Models\Customer',
   'column' => 'name',  // Check this exists
   ```

3. Test query manually in Tinker:
   ```bash
   php artisan tinker
   >>> Customer::where('name', 'LIKE', '%john%')->get();
   ```

---

### Slow Performance

**Problem:** Component is slow or sluggish

**Solutions:**
1. Enable caching:
   ```php
   'enableCache' => true,
   'cacheTime' => 3600,
   ```

2. Increase minimum search length:
   ```php
   'minSearchLength' => 2,  // Requires 2+ chars
   ```

3. Reduce result limit:
   ```php
   'resultLimit' => 10,  // Show fewer results
   ```

4. Add query indexes:
   ```php
   Schema::table('customers', function (Blueprint $table) {
       $table->index('name');
       $table->index('email');
   });
   ```

5. Use custom query callback with constraints:
   ```php
   'queryCallback' => fn($q) => $q
       ->where('status', 'active')
       ->orderBy('updated_at', 'desc')
   ```

---

### Events Not Firing

**Problem:** Livewire events not being received

**Solutions:**
1. Use correct event syntax:
   ```php
   #[On('afdropdown:selected')]
   public function handle($payload)
   {
       // Handle event
   }
   ```

2. Verify component name matches:
   ```blade
   <!-- Ensure this is inside your Livewire component -->
   @livewire('afdropdown', [...])
   ```

3. Check event payload:
   ```php
   #[On('afdropdown:selected')]
   public function handle($payload)
   {
       dd($payload);  // Debug event data
   }
   ```

---

### Keyboard Navigation Not Working

**Problem:** Arrow keys and Enter don't work

**Solutions:**
1. Ensure input is focused:
   - Click input field first
   - Then use arrow keys

2. Check JavaScript errors:
   - Open browser console (F12)
   - Look for JavaScript errors

3. Verify Alpine.js is loaded:
   ```blade
   <!-- Should be in your layout -->
   @vite('resources/js/app.js')
   ```

---

### Custom Formatter Not Applied

**Problem:** Formatter function not being used

**Solutions:**
1. Ensure formatter is callable:
   ```php
   'formatter' => function($item) {
       return $item->name;  // Must return string
   },
   ```

2. Check return type:
   ```php
   // ✅ Correct
   'formatter' => fn($item) => "{$item->name} ({$item->email})",
   
   // ❌ Wrong
   'formatter' => fn($item) => $item,  // Returns object, not string
   ```

---

### Cache Not Working

**Problem:** Results always being queried, not cached

**Solutions:**
1. Verify cache is enabled:
   ```php
   'enableCache' => true,
   ```

2. Check cache configuration:
   ```bash
   php artisan config:cache
   ```

3. Clear cache manually:
   ```bash
   php artisan cache:clear
   ```

4. Verify cache driver is working:
   ```php
   php artisan tinker
   >>> cache()->put('test', 'value');
   >>> cache()->get('test');  // Should return 'value'
   ```

---

## Performance Tips

1. **Use indexes** on frequently searched columns
2. **Enable caching** for static data
3. **Increase minimum search length** to reduce results
4. **Use query callbacks** to add filters
5. **Limit result display** to reasonable numbers
6. **Lazy load** expensive relationships only when needed

---

## Best Practices

✅ **DO:**
- Use specific column names
- Add query callbacks for filters
- Enable caching for static data
- Use keyboard navigation
- Test with production data volumes

❌ **DON'T:**
- Search on unindexed columns
- Return huge result sets
- Cache rapidly changing data
- Forget to validate selections
- Use without error handling

---

## Support & Issues

For issues, questions, or feature requests, refer to the main package documentation or create an issue in the repository.

---

**Last Updated:** 2024
**Version:** 3.6+
**Livewire Compatibility:** 3.6 or higher
