<?php

namespace LeParking\Sortable\Tests\Models;

class SortableColumn extends Sortable
{
    protected $sortable = [
        'column' => 'sort',
    ];
}
