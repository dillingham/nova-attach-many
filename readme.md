# Nova Attach Many

[![Latest Version on Github](https://img.shields.io/github/release/dillingham/nova-attach-many.svg?style=flat-square)](https://packagist.org/packages/dillingham/nova-attach-many)
[![Total Downloads](https://img.shields.io/packagist/dt/dillingham/nova-attach-many.svg?style=flat-square)](https://packagist.org/packages/dillingham/nova-attach-many)

Nova package that provides a way to attach relationships easily

### Installation

```bash
composer require dillingham/nova-attach-many
```

```php
use NovaAttachMany\AttachMany;
```
```php
public function fields(Request $request)
{
    return [
        AttachMany::make('Permissions'),
    ];
}
```

### Validation

You can set min, max, size or custom rule objects

```php
->rules('min:5', 'max:10', 'size:10', new CustomRule)
```

### Options

Here are a few customization options

- `->showCounts()` Shows "selected/total"
- `->hideToolbar()` Removes search & select all
- `->height('500px')` Set custom height
- `->fullWidth()` Set to full width

### Search & Select

The search has some interesting logic

- search then select a group of items by toggling "select all"
- search then deselect a group of items by toggling "select all"
- select all, search and then deselect a group of items
- deselect all, search and then select a group of items