# Laravel Sortable v2

[![Latest Stable Version](https://img.shields.io/packagist/v/hippieua/laravel-sortablev2.svg?style=flat-square)](https://packagist.org/packages/hippieua/laravel-sortablev2)
[![Total Downloads](https://img.shields.io/packagist/dt/hippieua/laravel-sortablev2.svg?style=flat-square)](https://packagist.org/packages/hippieua/laravel-sortablev2)
[![License](https://img.shields.io/packagist/l/hippieua/laravel-sortablev2.svg?style=flat-square)](https://packagist.org/packages/hippieua/laravel-sortablev2)

Laravel Sortable v2 is a Laravel package designed to easily add sortable behavior to Eloquent models. This package allows you to manage the order of database records via simple trait inclusion.

## Features

- **Sortable Trait**: Include a trait in your Eloquent models to enable sortable functionality.
- **Automatic Order Management**: Automatically manages the order field during creation and provides methods to move records up or down.
- **Relation Support**: Handles sorting within the context of a parent relationship, ideal for nested resources or grouped items.

## Installation

To install the package, run the following command in your Laravel project:

```bash
composer require hippieua/laravel-sortablev2
```

## Usage

### Setup

1. **Include the Trait in Your Model**

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Hippieua\Sortable2\Sortable2;

class Chapter extends Model
{
    use Sortable2;
}
```

2. **Define Sortable Field and Optional Relation**

Override methods in your model if you need a custom sortable field or a specific relation for sorting:

```php
protected function getSortableField(): string
{
    return 'custom_order_field';  // Default is 'order_id'
}

protected function getSortableRelation(): ?BelongsTo
{
    return $this->belongsTo(ParentModel::class);  // Default is null
}
```

### Managing Order

- **Move an Item Up**

```php
$chapter = Chapter::find(1);
$chapter->moveUp();
```

- **Move an Item Down**

```php
$chapter = Chapter::find(1);
$chapter->moveDown();
```

## Events

The package hooks into several Eloquent model events to ensure data integrity:

- `creating`
- `saving`
- `updating`
- `deleting`
- `retrieved`
- Conditionally `restoring` if SoftDeletes is used.

## Requirements

- PHP >= 8.0
- Laravel 9.x to 11.x

## Contributing

Contributions are welcome, and any issues or pull requests should be submitted on the [GitHub repository](https://github.com/hippieua/laravel-sortablev2).

## License

The Laravel Sortable v2 package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
