# Nova Attach Many

[![Latest Version on Github](https://img.shields.io/github/release/dillingham/nova-attach-many.svg?style=flat-square)](https://packagist.org/packages/dillingham/nova-attach-many)
[![Total Downloads](https://img.shields.io/packagist/dt/dillingham/nova-attach-many.svg?style=flat-square)](https://packagist.org/packages/dillingham/nova-attach-many) [![Twitter Follow](https://img.shields.io/twitter/follow/im_brian_d?color=%231da1f1&label=Twitter&logo=%231da1f1&logoColor=%231da1f1&style=flat-square)](https://twitter.com/im_brian_d)

Belongs To Many create & edit form UI for Nova. Enables attaching relationships easily and includes validation.

![attach-many](https://user-images.githubusercontent.com/29180903/52160651-be7fd580-2687-11e9-9ece-27332b3ce6bf.png)

### Installation

```bash
composer require letsgoi/nova-attach-many
```

### Usage

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

You can explicitly define the relationship & Nova resource:

```php
AttachMany::make('Field Name', 'relationshipName', RelatedResource::class);
```

### Display on detail:

This package only provides the create / edit views that BelongsToMany does not.

BelongsToMany should be used for displaying the table on detail views.

```php
public function fields(Request $request)
{
    return [
        AttachMany::make('Permissions'),
        BelongsToMany::make('Permissions'),
    ];
}
```

### Validation

You can set min, max, size or custom rule objects

```php
->rules('min:5', 'max:10', 'size:10', new CustomRule)
```

<img src="https://user-images.githubusercontent.com/29180903/52160802-9ee9ac80-2689-11e9-9657-80e3c0d83b27.png" width="75%" />


### Options

Here are a few customization options

- `->showCounts()` Shows "selected/total"
- `->showPreview()` Shows only selected
- `->hideToolbar()` Removes search & select all
- `->height('500px')` Set custom height
- `->fullWidth()` Set to full width
- `->showRefresh()` Request the resources again
- `->showSubtitle()` Show the resource's subtitle
- `->help('<b>Tip:</b> help text')` Set the help text

### All Options Demo
<img src="https://user-images.githubusercontent.com/29180903/53781117-6978ee80-3ed5-11e9-8da4-d2f2408f1ffb.png" width="75%"/>

### Relatable
The attachable resources will be filtered by relatableQuery()
So you can filter which resources are able to be attached

### Being Notified of Changes

You can add a method to the resource to be notified of the changes that have happened:

The method must be a camel cased version of the attribute name, followed by `Synced`. For example:

```php
public function fields(Request $request)
{
    return [
        AttachMany::make('Permissions'),
    ];
}
```

```php
public function permissionsSynced(array $changes)
{
    $changes['attached']; // An array of IDs of attached models
    $changes['detached']; // An array of IDs of detached models
    $changes['updated']; // An array of IDs of updated models
}
```


### Authorization
This field also respects policies: ie Role / Permission
- RolePolicy: attachAnyPermission($user, $role)
- RolePolicy: attachPermission($user, $role, $permission)
- PermissionPolicy: viewAny($user)

---

# Development
Install dependencies:
```bash
docker compose run node npm install
docker compose run composer install
docker compose run node npm run nova:install
```
Compile assets for production:
```bash
docker compose run node npm run production
```

# Author

Hi 👋, Im Brian D. I created this Nova package [and others](https://novapackages.com/collaborators/dillingham)

Hope you find it useful. Feel free to reach out with feedback.

Follow me on twitter: [@im_brian_d](https://twitter.com/im_brian_d)
