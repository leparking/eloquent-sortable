<?php

namespace LeParking\Sortable\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use LeParking\Sortable\Sortable as SortableInterface;
use LeParking\Sortable\SortableTrait;

class Sortable extends Model implements SortableInterface
{
    use SortableTrait;

    protected $table = 'sortables';

    public $timestamps = false;

    protected $fillable = ['name', 'position', 'sort', 'group'];
}
