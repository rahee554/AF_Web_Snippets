<div align="center">
  <h1>🚀 AF Web Snippets</h1>
  <p><strong>A powerful collection of Laravel web snippets to supercharge your development workflow</strong></p>
  
  ![PHP Version](https://img.shields.io/badge/php-%3E%3D8.0-777BB4?style=flat-square&logo=php)
  ![Laravel Version](https://img.shields.io/badge/laravel-%3E%3D9.0-FF2D20?style=flat-square&logo=laravel)
  ![Livewire](https://img.shields.io/badge/livewire-3.0-4E56A6?style=flat-square&logo=livewire)
  ![License](https://img.shields.io/badge/license-MIT-green?style=flat-square)
  ![Version](https://img.shields.io/badge/version-1.0.0-orange?style=flat-square)
</div>

---

## 📋 Table of Contents

- [✨ Features](#-features)
- [🔧 Installation](#-installation)
- [🚀 Quick Start](#-quick-start)
- [📚 Components](#-components)
  - [Dynamic Dropdown (AFDropdown)](#-dynamic-dropdown-afdropdown)
  - [Unique ID Generator](#-unique-id-generator)
  - [Data Formatters](#-data-formatters)
- [🎨 Customization](#-customization)
- [🔍 Examples](#-examples)
- [🤝 Contributing](#-contributing)
- [📄 License](#-license)
- [👥 Authors](#-authors)

---

## ✨ Features

- 🎯 **Dynamic Dropdown** - Livewire-powered searchable dropdowns with real-time filtering
- 🆔 **Unique ID Generator** - Multiple ID generation strategies (6-digit & Base36)
- 📱 **Data Formatters** - Format Pakistani phone numbers and CNIC
- 📱 **Responsive Design** - Bootstrap-compatible components
- ⚡ **Performance Optimized** - Efficient database queries with debouncing
- 🛠️ **Highly Customizable** - Extensive configuration options
- 🔒 **Secure** - Built-in validation and error handling

---

## 🔧 Installation

Install the package via Composer:

```bash
composer require artflow-studio/snippets
```

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

#### Listening to Selection Events

```javascript
document.addEventListener('livewire:init', function () {
    Livewire.on('afdropdown-selected', (data) => {
        console.log('Selected:', data.id, data.label, data.class);
        // Handle the selection
    });
});
```

#### AFDropdown Features

- ✅ **Real-time Search** - Debounced search with 300ms delay
- ✅ **Minimum Search Length** - Configurable minimum characters (default: 3)
- ✅ **Loading States** - Visual feedback during search
- ✅ **Clear Button** - Easy reset functionality
- ✅ **Keyboard Navigation** - Accessible dropdown interaction
- ✅ **Bootstrap Compatible** - Seamless integration with Bootstrap styling

#### Configuration Options

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
