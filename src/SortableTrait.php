<?php

namespace LeParking\Sortable;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;

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
            $this->sortableQuery()
                ->where($this->getKeyName(), '!=', $this->getKey())
                ->increment($this->getSortableColumn());
        }
    }

    /**
     * Reorder the others sortables when deleting a sortable.
     */
    public function sortableDeleted()
    {
        $column = $this->getSortableColumn();

        $this->sortableQuery()
            ->where($column, '>', $this->getPosition())
            ->decrement($column);
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
     * Construct a new query builder for the sortable model.
     *
     * @return QueryBuilder
     */
    protected function sortableQuery()
    {
        $query = $this->newQuery();

        $group_by = $this->getSortableConfig('group_by');

        foreach ((array) $group_by as $column) {
            if ($column) {
                $query->where($column, $this->getAttribute($column));
            }
        }

        return $query;
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
