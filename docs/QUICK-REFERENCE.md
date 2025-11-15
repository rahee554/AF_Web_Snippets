# AFdropdown - Quick Reference & Troubleshooting

**Last Updated:** November 9, 2025  
**Status:** ✅ Production Ready

---

## ⚡ Quick Start (5 minutes)

### 1. Add AFdropdown to Your Blade Template
```blade
<div class="mb-3">
    <label class="form-label">Select Customer</label>
    @livewire('afdropdown', [
        'model'             => App\Models\Customer::class,
        'columns'           => ['name', 'email'],
        'searchMode'        => 'contains',
        'minSearchLength'   => 2,
        'placeholder'       => 'Search customer...',
        'enableCache'       => true,
    ])
</div>
```

### 2. Add Event Handler to Component
```php
use Livewire\Attributes\On;

class YourComponent extends Component
{
    #[On('afdropdown:selected')]
    public function customerSelected($payload)
    {
        $this->customer_id = $payload['id'];
        $customer = \App\Models\Customer::find($payload['id']);
        // Do your logic here
    }
}
```

### 3. Done! 🎉
- Type to search (min 2 characters)
- Use arrow keys to navigate
- Press Enter to select
- Escape to close

---

## 🔍 Configuration Options

```php
@livewire('afdropdown', [
    // ==================== REQUIRED ====================
    'model' => App\Models\Customer::class,
    
    // ==================== SEARCH ====================
    'columns'           => ['name', 'email'],      // Columns to search
    'searchMode'        => 'contains',             // basic|contains|advanced|exact
    'minSearchLength'   => 2,                      // Min chars to search
    'resultLimit'       => 20,                     // Max results (1-50)
    
    // ==================== DISPLAY ====================
    'classes'           => 'form-control',         // Input CSS classes
    'placeholder'       => 'Search...',            // Placeholder text
    'displayFormat'     => 'full',                 // label|full
    'additionalColumns' => ['email', 'phone'],    // Extra display columns
    'debounceTime'      => 300,                    // Debounce in ms
    
    // ==================== PERFORMANCE ====================
    'enableCache'       => true,                   // Enable caching
    'cacheTime'         => 3600,                   // Cache TTL (seconds)
    
    // ==================== CUSTOMIZATION ====================
    'formatter'         => null,                   // Custom formatter callable
    'queryCallback'     => null,                   // Custom query modifier
    
    // ==================== ERROR HANDLING ====================
    'throwErrors'       => false,                  // Debug mode
])
```

---

## 🎯 Search Modes Explained

### 1. **basic** - Single Column LIKE
```
Query: WHERE column LIKE '%term%'
Speed: ⚡⚡⚡ Fastest
Use Case: Quick simple search
Example: Search by name only
```

### 2. **contains** - Multi-Column OR
```
Query: WHERE name LIKE '%term%' OR email LIKE '%term%'
Speed: ⚡⚡ Fast
Use Case: Search across multiple columns
Example: Search by name OR email
```

### 3. **advanced** - Word-by-Word AND
```
Query: WHERE (name LIKE '%word1%' OR ...) AND (name LIKE '%word2%' OR ...)
Speed: ⚡ Moderate
Use Case: Strict word matching
Example: "John Doe" finds "John" AND "Doe"
```

### 4. **exact** - Exact Match
```
Query: WHERE column = 'value'
Speed: ⚡⚡⚡ Fastest
Use Case: Exact lookup
Example: Find customer with exact ID
```

---

## ⌨️ Keyboard Shortcuts

| Key | Action | Result |
|-----|--------|--------|
| ↑ Up | Previous | Highlight previous result |
| ↓ Down | Next | Highlight next result |
| Enter | Select | Select highlighted item |
| Escape | Close | Close dropdown |
| Ctrl+A | Select All | (In search field) |
| Backspace | Delete | Remove last character |

---

## 🐛 Troubleshooting

### Problem: "No results found"

**Possible Causes:**
1. Search term too short (check minSearchLength)
2. No matching records in database
3. Column name misspelled
4. Database connection issue

**Solution:**
```php
// Check database has data
php artisan tinker
>>> App\Models\Customer::pluck('name')->toArray();

// Check column exists
>>> \Schema::hasColumn('customers', 'name');

// Test search manually
>>> App\Models\Customer::where('name', 'LIKE', '%john%')->get();
```

---

### Problem: "Call to undefined method query()"

**Cause:** AFdropdown received Model instead of Builder

**Solution:** 
Already fixed in v2.0.0! If you see this error:
```php
// ❌ WRONG - Passes Model
$query = $this->buildQuery($model, $search);

// ✅ RIGHT - Passes Builder
$query = $this->buildQuery(($this->model)::query(), $search);
```

---

### Problem: Event handler not triggered

**Possible Causes:**
1. Event handler not defined in component
2. Wrong event name in handler
3. Component doesn't have `#[On(...)]` attribute

**Solution:**
```php
// Make sure you have:
use Livewire\Attributes\On;

class YourComponent extends Component
{
    // ✅ CORRECT - Use exact event name
    #[On('afdropdown:selected')]
    public function customerSelected($payload)
    {
        dd($payload); // Check payload here
    }
}
```

---

### Problem: Keyboard navigation not working

**Possible Causes:**
1. JavaScript not loaded
2. Livewire not initialized
3. Alpine.js not working

**Solution:**
```js
// Check in browser console
console.log(Livewire);  // Should show Livewire object
console.log(Alpine);     // Should show Alpine object

// Check Livewire is loaded
// Should see: "@livewire:navigating" or similar
```

---

### Problem: Slow search results

**Possible Causes:**
1. Large dataset without indexes
2. Complex query with many columns
3. No caching enabled

**Solution:**
```php
// Add database indexes
ALTER TABLE customers ADD INDEX idx_name (name);
ALTER TABLE customers ADD INDEX idx_email (email);

// Enable caching
'enableCache' => true,
'cacheTime' => 3600,  // 1 hour

// Reduce result limit
'resultLimit' => 10,  // Instead of 20+

// Use basic search mode
'searchMode' => 'basic',  // Instead of advanced
```

---

## 📊 Performance Tips

### 1. Use Caching
```php
'enableCache' => true,
'cacheTime' => 3600,  // 1 hour cache
```

### 2. Add Database Indexes
```sql
ALTER TABLE customers ADD INDEX idx_name (name);
ALTER TABLE customers ADD INDEX idx_email (email);
ALTER TABLE customers ADD INDEX idx_phone (phone);
```

### 3. Limit Results
```php
'resultLimit' => 15,  // Instead of 50
```

### 4. Use Basic Search Mode
```php
'searchMode' => 'basic',  // Fastest option
```

### 5. Increase Min Search Length
```php
'minSearchLength' => 3,  // Skip 1-2 char searches
```

---

## 🎨 Customization Examples

### Custom Formatter
```php
@livewire('afdropdown', [
    'model' => App\Models\Customer::class,
    'columns' => ['name'],
    'formatter' => fn($customer) => "{$customer->name} ({$customer->email})",
])
```

### Custom Query Filter
```php
@livewire('afdropdown', [
    'model' => App\Models\Customer::class,
    'columns' => ['name'],
    'queryCallback' => fn($query) => $query->where('status', 'active'),
])
```

### Custom CSS Classes
```php
@livewire('afdropdown', [
    'model' => App\Models\Customer::class,
    'columns' => ['name'],
    'classes' => 'form-control form-control-lg border-primary',
])
```

---

## ✅ Verification Checklist

Before going live, verify:

- [ ] Component loads without errors
- [ ] Search works (type and get results)
- [ ] Keyboard navigation works (arrows + enter)
- [ ] Event handler receives payload
- [ ] Form data gets populated
- [ ] No console errors
- [ ] No database errors
- [ ] Responsive on mobile
- [ ] Accessible (keyboard only)
- [ ] Performance acceptable (< 400ms)

---

## 📞 Getting Help

### Documentation Files
- **AFdropdown-GUIDE.md** - Complete reference
- **EXAMPLES.md** - Real-world examples
- **TESTING-RESULTS.md** - Test results
- **FINAL-COMPLETION-REPORT.md** - Project summary

### Quick Links
- GitHub Repo: [ArtflowERP]
- Issue Tracker: [Report Issues]
- Discussions: [Ask Questions]

### Performance Monitoring
```php
// Enable Livewire debug mode
LIVEWIRE_DEBUG=true

// Check database queries
php artisan tinker
>>> DB::enableQueryLog();
>>> // Run your search
>>> DB::getQueryLog();
```

---

## 🚀 Deployment Checklist

```bash
# Before deploying to production:

# 1. Run tests
php artisan test

# 2. Check syntax
php -l vendor/artflow-studio/snippets/src/Http/Livewire/AFdropdown.php

# 3. Add database indexes
# Run SQL in migration:
ALTER TABLE customers ADD INDEX idx_name (name);
ALTER TABLE customers ADD INDEX idx_email (email);

# 4. Clear cache
php artisan cache:clear
php artisan config:clear

# 5. Deploy code
git pull origin main

# 6. Monitor logs
tail -f storage/logs/laravel.log

# 7. Test live
# Visit: /branch/invoices/create
# Search for customer
# Verify selection works
```

---

## 📈 Version History

| Version | Date | Changes |
|---------|------|---------|
| 2.0.0 | Nov 9, 2025 | Production release - All features, bugs fixed, live tested ✅ |
| 1.0.0 | Previous | Initial basic component |

---

## 📝 License & Support

**License:** Artflow ERP License  
**Support:** Development Team  
**Status:** ✅ Production Ready

---

**Last Updated:** November 9, 2025  
**Component Version:** 2.0.0  
**Livewire:** 3.6+ ✅  
**Laravel:** 12+ ✅  
**PHP:** 8.2+ ✅

🎉 **Ready for Production!** 🎉
