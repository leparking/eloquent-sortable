<?php

use LeParking\EloquentSortable\Tests\Models\Sortable;
use LeParking\EloquentSortable\Tests\Models\SortableColumn;
use LeParking\EloquentSortable\Tests\Models\SortableGroupBy;
use LeParking\EloquentSortable\Tests\Models\SortableInsertFirst;

$factory->define(Sortable::class, function ($faker) {
    return [
        'name' => $faker->name,
    ];
});

$factory->define(SortableColumn::class, function ($faker) use ($factory) {
    return $factory->raw(Sortable::class);
});

$factory->define(SortableGroupBy::class, function ($faker) use ($factory) {
    return $factory->raw(Sortable::class);
});

$factory->define(SortableInsertFirst::class, function ($faker) use ($factory) {
    return $factory->raw(Sortable::class);
});
