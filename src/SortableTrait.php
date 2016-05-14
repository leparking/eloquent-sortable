<?php

namespace LeParking\Sortable;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Arr;

/**
 * Trait that adds sortable behaviour to Eloquent models.
 */
trait SortableTrait
{
    /**
     * Automatically fill the position attribute when creating a new sortable.
     */
    public function sortableCreating()
    {
        $this->setNextPosition();
    }

    /**
     * Increment the position of the others sortables when inserting a sortable
     * at the first position.
     */
    public function sortableCreated()
    {
        if ($this->shouldInsertFirst()) {
            $this->incrementOthersPositions();
        }
    }

    /**
     * Update the position when changing one of the group_by columns.
     */
    public function sortableUpdating()
    {
        if ($this->hasSortableGroupChanged()) {
            $this->setNextPosition();
        }
    }

    /**
     * Reorder the other sortables when one of the group_by columns have changed.
     */
    public function sortableUpdated()
    {
        if (!$this->hasSortableGroupChanged()) {
            return;
        }

        $this->decrementAbovePosition($this->getOriginalPosition(), $this->getOriginal());

        if ($this->shouldInsertFirst()) {
            $this->incrementOthersPositions();
        }
    }

    /**
     * Reorder the others sortables when deleting a sortable.
     */
    public function sortableDeleted()
    {
        $this->decrementAbovePosition();
    }

    /**
     * Fill the position attribute with the next position.
     *
     * @return $this
     */
    public function setNextPosition()
    {
        return $this->setPosition($this->getNextPosition());
    }

    /**
     * Get the position where a new sortable would be inserted.
     *
     * @return int
     */
    public function getNextPosition()
    {
        if ($this->shouldInsertFirst()) {
            return 1;
        }

        $position = $this->sortableQuery()->max($this->getSortableColumn());

        return $position + 1;
    }

    /**
     * Fill the position attribute.
     *
     * @param int $position
     * @return $this
     */
    public function setPosition($position)
    {
        $this->setAttribute($this->getSortableColumn(), $position);

        return $this;
    }

    /**
     * Get the sortable current position.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->getAttribute($this->getSortableColumn());
    }

    /**
     * Get the sortable old / original position
     *
     * @return int
     */
    public function getOriginalPosition() {
        return $this->getOriginal($this->getSortableColumn());
    }

    /**
     * Increment the position of all sortables.
     *
     * @param mixed $except The primary key of a sortable that won't be incremented.
     */
    protected function incrementPositions($except = null)
    {
        $query = $this->sortableQuery();

        if ($except) {
            $query->where($this->getKeyName(), '!=', $except);
        }

        return $query->increment($this->getSortableColumn());
    }

    /**
     * Increment positions of all others sortables.
     */
    protected function incrementOthersPositions()
    {
        $this->incrementPositions($this->getKey());
    }

    /**
     * Decrement positions of sortables above the given or current position.
     *
     * @param int|null $position
     * @param array $attributes
     */
    protected function decrementAbovePosition($position = null, array $attributes = [])
    {
        $column = $this->getSortableColumn();
        $position = $position ?: $this->getPosition();

        $this->sortableQuery($attributes)
            ->where($column, '>', $position)
            ->decrement($column);
    }

    /**
     * A query scope to order sortables by position.
     *
     * @param QueryBuilder $query
     * @param string $direction
     * @return QueryBuilder
     */
    public function scopeOrdered(QueryBuilder $query, $direction = 'asc')
    {
        return $query->orderBy($this->getSortableColumn(), $direction);
    }

    /**
     * Construct a new query builder to retrieve sortables in a group.
     *
     * @param array $attributes an array of group_by values. If empty, the model's attributes will be used.
     * @return QueryBuilder
     */
    protected function sortableQuery(array $attributes = [])
    {
        $query = $this->newQuery();

        $groups = $this->getSortableGroups();

        foreach ($groups as $column) {
            $value = Arr::get($attributes, $column, $this->getAttribute($column));

            $query->where($column, $value);
        }

        return $query;
    }

    /**
     * Check if one of the group_by columns has changed.
     *
     * @return bool
     */
    protected function hasSortableGroupChanged()
    {
        $groups = $this->getSortableGroups();

        foreach ($groups as $column) {
            if ($this->getOriginal($column) !== $this->getAttribute($column)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the name of the position column.
     *
     * @return string
     */
    protected function getSortableColumn()
    {
        return $this->getSortableConfig('column');
    }

    /**
     * Get the group_by columns in the current config.
     *
     * @return array
     */
    protected function getSortableGroups()
    {
        $group_by = $this->getSortableConfig('group_by');

        return array_filter((array) $group_by);
    }

    /**
     * Determine whether new sortables are inserted at the first or last position.
     *
     * @return bool
     */
    protected function shouldInsertFirst()
    {
        return $this->getSortableConfig('insert_first');
    }

    /**
     * Get the sortable configuration or a specific configuration value.
     *
     * Will merge the default configuration with per model configuration defined
     * in the $sortable property.
     *
     * @param string|null $key  if null, will return the whole configuration.
     * @return mixed
     * @throws \InvalidArgumentException  if $key does not exist in config.
     */
    private function getSortableConfig($key = null)
    {
        $config = config('sortable');

        if (property_exists($this, 'sortable')) {
            $config = array_merge($config, $this->sortable);
        }

        if ($key) {
            if (array_key_exists($key, $config)) {
                return $config[$key];
            }

            throw new \InvalidArgumentException("Unknown key $key in Sortable config");
        }

        return $config;
    }
}
