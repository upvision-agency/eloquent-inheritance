<?php

namespace Tests\Unit;

use Orchestra\Testbench\TestCase;
use Cvsouth\EloquentInheritance\Providers\ServiceProvider;
use Cvsouth\EloquentInheritance\QueryBuilder;

use Tests\Stubs\Bird;

class QueryBuilderTest extends TestCase {

	protected function getPackageProviders($app) {
		return [ServiceProvider::class];
	}

	protected function setUp(): void {
		parent::setUp();
		$this->loadMigrationsFrom(__DIR__ .'/../database/migrations');
	}

	public function testOrderBy() {
		$query = (new Bird())->newModelQuery()->getQuery()
			->orderBy('wingspan', 'asc')
			->orderBy('name', 'desc');
		$this->assertEquals($query->orders[0]['column'], 'birds.wingspan');
		$this->assertEquals($query->orders[0]['direction'], 'asc');
		$this->assertEquals($query->orders[1]['column'], 'animals.name');
		$this->assertEquals($query->orders[1]['direction'], 'desc');
	}

	public function testSetModel() {
		$query = resolve(QueryBuilder::class);
		$query->setModel(new Bird());
		$this->assertInstanceOf(Bird::class, $query->model());
	}

	public function testGetModelClass() {
		$query = resolve(QueryBuilder::class);
		$query->setModel(new Bird());
		$this->assertEquals(Bird::class, $query->getModelClass());
	}

	public function testModelWithModel() {
		$query = (new Bird())->newModelQuery()->getQuery();
		$this->assertInstanceOf(Bird::class, $query->model());
	}

	public function testModelWithoutModelCanResolveFromTable() {
		$query = resolve(QueryBuilder::class);
		$query->from('birds');
		$this->assertInstanceOf(Bird::class, $query->model());
	}

	public function testPrefixWildcardColumn() {
		$query = resolve(QueryBuilder::class);
		$this->assertEquals('*', $query->prefixColumn('*'));
		$this->assertEquals('birds.*', $query->prefixColumn('birds.*'));
	}

	public function testPrefixAlreadyPrefixedColumn() {
		$query = resolve(QueryBuilder::class);
		$this->assertEquals('animals.wingspan', $query->prefixColumn('animals.wingspan'));
		$this->assertEquals('.*', $query->prefixColumn('.*'));
	}

	public function testPrefixOnSameClass() {
		$query = (new Bird())->newModelQuery()->getQuery();
		$this->assertEquals('birds.wingspan', $query->prefixColumn('wingspan'));
	}

	public function testPrefixOnParentClass() {
		$query = (new Bird())->newModelQuery()->getQuery();
		$this->assertEquals('animals.name', $query->prefixColumn('name'));
	}

	public function testSelectSingleColumn() {
		$query = (new Bird())->newModelQuery()->getQuery();
		$query->select('wingspan');
		$this->assertCount(1, $query->columns);
		$this->assertEquals('wingspan', $query->columns[0]);
	}

	public function testSelectMultipleColumns() {
		$query = (new Bird())->newModelQuery()->getQuery();
		$query->select(['wingspan', 'flying']);
		$this->assertCount(2, $query->columns);
		$this->assertEquals('wingspan', $query->columns[0]);
		$this->assertEquals('flying', $query->columns[1]);
	}

	public function testAddSelectSingleColumn() {
		$query = (new Bird())->newModelQuery()->getQuery();
		$query->select('wingspan');
		$query->addSelect('flying');
		$this->assertCount(2, $query->columns);
		$this->assertEquals('wingspan', $query->columns[0]);
		$this->assertEquals('flying', $query->columns[1]);
	}

	public function testAddSelectMultipleColumns() {
		$query = (new Bird())->newModelQuery()->getQuery();
		$query->select('wingspan');
		$query->addSelect(['flying', 'id']);
		$this->assertCount(3, $query->columns);
		$this->assertEquals('wingspan', $query->columns[0]);
		$this->assertEquals('flying', $query->columns[1]);
		$this->assertEquals('id', $query->columns[2]);
	}

}
