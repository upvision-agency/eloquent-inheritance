<?php

namespace Tests\Unit;

use Orchestra\Testbench\TestCase;
use Cvsouth\EloquentInheritance\Providers\ServiceProvider;
use Cvsouth\EloquentInheritance\InheritableModel;
use Cvsouth\EloquentInheritance\QueryBuilder;
use Cvsouth\EloquentInheritance\Builder;

use Tests\Stubs\Animal;
use Tests\Stubs\Bird;

class BuilderTest extends TestCase {

	protected $totalBirds = 10;

	protected function getPackageProviders($app) {
		return [ServiceProvider::class];
	}

	protected function setUp(): void {
		parent::setUp();
		$this->loadMigrationsFrom(__DIR__ .'/../database/migrations');
		$this->withFactories(__DIR__ .'/../database/factories');
		$this->artisan('migrate')->run();
		factory(Bird::class, $this->totalBirds)->create();
	}

	protected function tearDown(): void {
		$this->artisan('migrate:reset')->run();
	}

	public function testSetModel() {
		$model = new Animal();
		$builder = $model->newModelQuery();
		$this->assertInstanceOf(Builder::class, $builder);
		$this->assertInstanceOf(QueryBuilder::class, $builder->getQuery());
		$this->assertInstanceOf(Animal::class, $builder->getModel());
		$this->assertInstanceOf(Animal::class, $builder->getQuery()->model());
	}

	public function testGetAllWithElevate() {
		$bird = Animal::query()->get(['*'], true)->first();
		$this->assertInstanceOf(Bird::class, $bird);
		$this->assertNotNull($bird->name);
		$this->assertNotNull($bird->species);
		$this->assertNotNull($bird->flying);
	}

	public function testGetAllWithoutElevate() {
		$animal = Animal::query()->get(['*'], false)->first();
		$this->assertInstanceOf(Animal::class, $animal);
		$this->assertNotNull($animal->name);
		$this->assertNotNull($animal->species);
		$this->assertNull($animal->flying);
	}

	public function testWherePrefix() {
		$query = Bird::where('name', 'foo')->getQuery();
		$this->assertEquals($query->wheres[0]['column'], 'animals.name');
	}

	public function testIncrement() {
		$wingspan = Bird::findOrFail(1)->wingspan;
		Bird::where('id', 1)->increment('wingspan');
		$newWingspan = Bird::findOrFail(1)->wingspan;
		$this->assertEquals($wingspan + 1, $newWingspan);
	}

	public function testIncrementMultiple() {
		$wingspan = Bird::findOrFail(1)->wingspan;
		Bird::where('id', 1)->increment('wingspan', 5);
		$newWingspan = Bird::findOrFail(1)->wingspan;
		$this->assertEquals($wingspan + 5, $newWingspan);
	}

	public function testDecrement() {
		$wingspan = Bird::findOrFail(1)->wingspan;
		Bird::where('id', 1)->decrement('wingspan');
		$newWingspan = Bird::findOrFail(1)->wingspan;
		$this->assertEquals($wingspan - 1, $newWingspan);
	}

	public function testDecrementMultiple() {
		$wingspan = Bird::findOrFail(1)->wingspan;
		Bird::where('id', 1)->decrement('wingspan', 5);
		$newWingspan = Bird::findOrFail(1)->wingspan;
		$this->assertEquals($wingspan - 5, $newWingspan);
	}

	public function testPaginate() {
		$perPage = 2;
		$birds = Bird::paginate($perPage);
		$this->assertEquals($this->totalBirds, $birds->total());
		$this->assertEquals(1, $birds->currentPage());
		$this->assertEquals($this->totalBirds / $perPage, $birds->lastPage());
		$this->assertEquals($perPage, $birds->perPage());
	}

	public function testSimplePaginate() {
		$perPage = 2;
		$birds = Bird::simplePaginate($perPage);
		$this->assertEquals(1, $birds->currentPage());
		$this->assertEquals($perPage, $birds->perPage());
		$this->assertTrue($birds->hasMorePages());
	}

}
