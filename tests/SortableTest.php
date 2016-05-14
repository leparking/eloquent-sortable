<?php

namespace LeParking\Sortable\Tests;

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
        return ['LeParking\Sortable\SortableServiceProvider'];
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

    public function testChangingGroup()
    {
        $group1 = factory(Models\SortableGroupBy::class, 3)->create([
            'group' => 1,
        ]);

        $group2 = factory(Models\SortableGroupBy::class, 3)->create([
            'group' => 2,
        ]);

        $group1->first()->update(['group' => 2]);

        $group2->push($group1->first());
        $group1->splice(0, 1);

        foreach ($group1 as $i => $sortable) {
            $this->assertEquals($i + 1, $sortable->fresh()->position);
        }

        foreach ($group2 as $i => $sortable) {
            $this->assertEquals($i + 1, $sortable->fresh()->position);
        }
    }

    public function testChangingGroupInsertedFirst()
    {
        for ($i = 1; $i <= 2; $i++) {
            factory(Models\SortableInsertFirst::class, 5)->create(['group' => $i]);
            ${"group$i"} = Models\SortableInsertFirst::where('group', $i)->ordered()->get();
        }

        $sortable = $group1[2];
        $sortable->update(['group' => 2]);
        $this->assertEquals(1, $sortable->fresh()->position);

        $group2->prepend($sortable);
        $group1->splice(2, 1);

        foreach ($group1 as $i => $sortable) {
            $this->assertEquals($i + 1, $sortable->fresh()->position);
        }

        foreach ($group2 as $i => $sortable) {
            $this->assertEquals($i + 1, $sortable->fresh()->position);
        }
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
