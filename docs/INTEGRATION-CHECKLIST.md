# AFdropdown Integration Checklist

Quick reference for integrating AFdropdown in your Livewire components.

## 📋 Basic Integration (5 Minutes)

### Step 1: Add Event Handler
```php
// In your Livewire component
use Livewire\Attributes\On;

#[On('afdropdown:selected')]
public function itemSelected($payload)
{
    $this->selectedId = $payload['id'];
    $this->selectedData = $payload['data'];
}
```

### Step 2: Add to Blade
```blade
@livewire('afdropdown', [
    'model' => 'App\Models\YourModel',
    'column' => 'name',
])
```

### Step 3: Use in Your Logic
```php
public function save()
{
    // Use $this->selectedId
}
```

---

## 🎯 Common Configurations

### Simple Search
```php
'model' => 'App\Models\Customer',
'column' => 'name',
```

### Multi-Field Search
```php
'model' => 'App\Models\Customer',
'columns' => ['name', 'email'],
'searchMode' => 'contains',
'minSearchLength' => 2,
```

### With Caching
```php
'enableCache' => true,
'cacheTime' => 3600,
```

### With Custom Formatter
```php
'formatter' => fn($item) => "{$item->name} ({$item->email})",
```

### With Query Filter
```php
'queryCallback' => fn($q) => $q->where('status', 'active'),
```

### Full Production Setup
```php
@livewire('afdropdown', [
    'model' => 'App\Models\Customer',
    'columns' => ['name', 'email'],
    'searchMode' => 'contains',
    'minSearchLength' => 2,
    'resultLimit' => 15,
    'enableCache' => true,
    'cacheTime' => 3600,
    'formatter' => fn($c) => "{$c->name} ({$c->email})",
    'queryCallback' => fn($q) => $q->where('status', 'active'),
    'additionalColumns' => ['email', 'phone'],
    'classes' => 'form-control form-control-lg',
    'placeholder' => 'Search customers...',
])
```

---

## 📚 Search Modes Quick Guide

| Mode | Use Case | Example |
|------|----------|---------|
| `basic` | Single field search | Customer name only |
| `contains` | Any field match | Name OR email OR phone |
| `advanced` | All terms in any field | "john doe" finds both |
| `exact` | Exact match | Email or ID match |

---

## ⌨️ Keyboard Navigation

| Key | Action |
|-----|--------|
| ↓ | Next result |
| ↑ | Previous result |
| ↩ | Select highlighted |
| Esc | Close dropdown |
| Type | Search |

---

## 🔄 Event Payload Structure

```php
$payload = [
    'id' => 123,                    // Model ID
    'label' => 'John Doe',          // Display label
    'model' => 'App\Models\Customer', // Model class
    'data' => [                     // Model data
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '555-1234',
    ],
];
```

---

## ✅ Testing Checklist

- [ ] Component renders without errors
- [ ] Can type in search input
- [ ] Results appear after minSearchLength characters
- [ ] Can select result with mouse
- [ ] Can navigate with arrow keys
- [ ] Can select with Enter key
- [ ] Event fires with correct payload
- [ ] Parent component receives and handles event
- [ ] Selected value displays correctly
- [ ] Can clear search and start over
- [ ] Works on mobile devices
- [ ] No console errors

---

## 🐛 Debug Mode

Enable for development:
```php
'throwErrors' => true,  // Shows errors in UI and console
```

Check these places for errors:
- Browser console (F12)
- Laravel logs (`storage/logs/laravel.log`)
- Network tab (check requests)

---

## 📁 File Locations

All AFdropdown files are located in:
```
vendor/artflow-studio/snippets/
```

| File | Purpose |
|------|---------|
| `src/Http/Livewire/AFdropdown.php` | Component class |
| `src/views/livewire/afdropdown.blade.php` | Template |
| `AFdropdown-GUIDE.md` | Complete documentation |
| `EXAMPLES.md` | Real-world examples |
| `README.md` | Overview |

---

## 🚀 Performance Tips

1. **Add database indexes** on search columns
2. **Increase minSearchLength** to 2+ to reduce queries
3. **Enable caching** for static data
4. **Use queryCallback** to filter early
5. **Reduce resultLimit** if not needed all 50

---

## 🆘 Quick Troubleshooting

| Problem | Solution |
|---------|----------|
| No results | Check minSearchLength, verify model/column names |
| Slow | Enable cache, add indexes, increase minSearchLength |
| Events not firing | Use correct event name, check browser console |
| Keyboard not working | Click input first to focus, check Alpine.js |
| Formatter not working | Ensure it returns string, not object |

---

## 📞 Get More Help

- **Full Guide**: Read `AFdropdown-GUIDE.md`
- **Examples**: See `EXAMPLES.md` for 8+ real cases
- **Issues**: Check troubleshooting section in GUIDE.md
- **Status**: Enable `throwErrors` for debug info

---

## 🎓 Next Steps

1. ✅ Read this checklist
2. ✅ Review EXAMPLES.md for similar use case
3. ✅ Copy example configuration
4. ✅ Add event handler to component
5. ✅ Test in browser
6. ✅ Check browser console for errors
7. ✅ Deploy to production

---

**Quick Start Time:** ~5 minutes  
**Full Integration Time:** ~15 minutes  
**Documentation Time:** ~30 minutes

**Total Implementation:** < 1 hour ⚡
