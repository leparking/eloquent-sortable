<?php

namespace LeParking\EloquentSortable\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use LeParking\EloquentSortable\Sortable as SortableInterface;
use LeParking\EloquentSortable\SortableTrait;

class Sortable extends Model implements SortableInterface
{
    use SortableTrait;

    protected $table = 'sortables';

    public $timestamps = false;
}
