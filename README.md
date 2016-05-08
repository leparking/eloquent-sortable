# Eloquent Sortable

A Laravel 5.2 package to sort Eloquent models.

[![Build Status](https://travis-ci.org/leparking/eloquent-sortable.svg)](https://travis-ci.org/leparking/eloquent-sortable)

* [Installation](#installation)
* [Configuration](#configuration)
* [Usage](#usage)

## Installation

Add the git repository at the root of your `composer.json` file

```json
{
    ...
    "repositories": [
        {
            "url": "https://github.com/leparking/eloquent-sortable.git",
            "type": "git"
        }
    ],
    ...
}
```

Require the package with composer

```sh
composer require leparking/eloquent-sortable
```

Add the service provider to `config/app.php`

```php
    'providers' => [
        // ...
        LeParking\EloquentSortable\SortableServiceProvider::class,
    ],
```

## Configuration

Publish the default configuration file to `config/sortable.php`

```sh
php artisan vendor:publish
```

The settings in this file will apply to all sortable models, but can be
overridden on each model with the `$sortable` property.

Here is the available settings with their defaults:

```php
return [
     // Name of the column that will store the position.
    'column' => 'position',

     // Determine whether the position should be set automatically when creating
     // new sortable models.
    'sort_on_create' => true,

     // Determine if other models should be reordered when deleting a sortable model.
    'reorder_on_delete' => true,

     // If set to true, new models will be inserted at the first position.
    'insert_first' => false,

     // A column name or an array of columns names to group sortable models.
    'group_by' => false,
];
```

## Usage

Your sortable models should implement the `Sortable` interface and use the `SortableTrait`.

```php
use Illuminate\Database\Eloquent\Model;
use LeParking\EloquentSortable\Sortable;
use LeParking\EloquentSortable\SortableTrait;

class Book extends Model implements Sortable
{
    use SortableTrait;

    // Optional property to override default settings.
    protected $sortable = [
        // ...
    ];
}

```

The database table must have an integer column to store the position.

```php
Schema::create('books', function($table) {
    $table->integer('position')->unsigned();
});
```

The `position` attribute will be filled automatically when creating new models.

```php
$book = Book::create();
echo $book->position; // 1

$book2 = Book::create();
echo $book2->position; // 2
```

The `SortableTrait` provides a query scope to retrieve models ordered by the
position column.

```php
$books = Book::ordered();
$books = Book::ordered('desc');
```
