<?php

namespace LeParking\EloquentSortable\Tests;

use Orchestra\Testbench\TestCase;

class SortableTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->withFactories(__DIR__ . '/database/factories');

        $this->artisan('migrate', [
          '--database' => 'testbench',
          '--realpath' => realpath(__DIR__ . '/database/migrations'),
        ]);
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');

        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function getPackageProviders($app)
    {
        return ['LeParking\EloquentSortable\SortableServiceProvider'];
    }

    public function testSetPositionWhenCreating()
    {
        $sortables = factory(Models\Sortable::class, 3)->create();

        foreach ($sortables as $i => $sortable) {
            $this->assertEquals($i + 1, $sortable->position);
        }
    }

    public function testSetPositionOnAnotherColumn()
    {
        $sortables = factory(Models\SortableColumn::class, 3)->create();

        foreach ($sortables as $i => $sortable) {
            $this->assertEquals($i + 1, $sortable->sort);
        }
    }

    public function testGroupBy()
    {
        $sortablesGroups = [];

        for ($i = 0; $i < 3; $i++) {
            $sortablesGroups[] = factory(Models\SortableGroupBy::class, 3)->create([
                'group' => $i,
            ]);
        }

        foreach ($sortablesGroups as $sortables) {
            foreach ($sortables as $i => $sortable) {
                $this->assertEquals($i + 1, $sortable->fresh()->position);
            }
        }
    }

    public function testInsertFirst()
    {
        $sortables = factory(Models\SortableInsertFirst::class, 3)->create();

        foreach ($sortables as $i => $sortable) {
            $this->assertEquals(count($sortables) - $i, $sortable->fresh()->position);
        }
    }

    public function testInsertFirstGroupBy()
    {
        $sortablesGroups = [];

        for ($i = 0; $i < 3; $i++) {
            $sortablesGroups[] = factory(Models\SortableInsertFirst::class, 3)->create([
                'group' => $i,
            ]);
        }

        foreach ($sortablesGroups as $sortables) {
            foreach ($sortables as $i => $sortable) {
                $this->assertEquals(count($sortables) - $i, $sortable->fresh()->position);
            }
        }
    }

    public function testReorderOnDelete()
    {
        $sortables = factory(Models\Sortable::class, 5)->create();

        $sortables[2]->delete();

        $sortables->splice(2, 1);

        foreach ($sortables as $i => $sortable) {
            $this->assertEquals($i + 1, $sortable->fresh()->position);
        }
    }

    public function testReorderOnDeleteGroupBy()
    {
        for ($i = 0; $i < 3; $i++) {
            $sortablesGroups[] = factory(Models\SortableGroupBy::class, 5)->create([
                'group' => $i,
            ]);
        }

        foreach ($sortablesGroups as $sortables) {
            $index = rand(0, count($sortables) - 2);
            $sortables[$index]->delete();

            $sortables->splice($index, 1);

            foreach ($sortables as $i => $sortable) {
                $this->assertEquals($i + 1, $sortable->fresh()->position);
            }
        }

        $sortables = factory(Models\SortableGroupBy::class, 5)->create();
    }

    public function testScopeOrdered()
    {
        factory(Models\SortableInsertFirst::class, 5)->create();

        $sortables = Models\SortableInsertFirst::ordered()->get();

        foreach ($sortables as $i => $sortable) {
            $this->assertEquals($i + 1, $sortable->position);
        }
    }

    public function testScopeOrderedDesc()
    {
        factory(Models\Sortable::class, 5)->create();

        $sortables = Models\Sortable::ordered('desc')->get();

        foreach ($sortables as $i => $sortable) {
            $this->assertEquals(count($sortables) - $i, $sortable->position);
        }
    }
}
