<?php

namespace Tests\Unit;

use ReflectionObject;
use Orchestra\Testbench\TestCase;
use Illuminate\Support\LazyCollection;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Cvsouth\EloquentInheritance\Providers\ServiceProvider;
use Cvsouth\EloquentInheritance\InheritableModel;
use Cvsouth\EloquentInheritance\QueryBuilder;
use Cvsouth\EloquentInheritance\Builder;

use Tests\Stubs\Animal;
use Tests\Stubs\Bird;
use Tests\Stubs\Dog;
use Tests\Stubs\Cat;
use Tests\Stubs\Monkey;

class InheritableModelTest extends TestCase {

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

	public function testNewEloquentBuilder() {
		$model = new InheritableModel();
		$query = resolve(QueryBuilder::class);
		$this->assertInstanceOf(Builder::class, $model->newEloquentBuilder($query));
	}

	public function testTableForAttributeOnItself() {
		$bird = new Bird();
		$this->assertEquals('birds', $bird->tableForAttribute('wingspan'));
	}

	public function testTableForAttributeOnParent() {
		$bird = new Bird();
		$this->assertEquals('animals', $bird->tableForAttribute('name'));
	}

	public function testModelClassForAttributeOnItself() {
		$bird = new Bird();
		$this->assertEquals(Bird::class, $bird->modelClassForAttribute('wingspan'));
	}

	public function testModelClassForAttributeOnParent() {
		$bird = new Bird();
		$this->assertEquals(Animal::class, $bird->modelClassForAttribute('name'));
	}

	public function testCreateNew() {
		$bird = InheritableModel::createNew(Bird::class, [
			'id' => 1,
			'name' => 'Foo',
			'species' => 'Bar',
			'flying' => 1,
			'wingspan' => 5,
		]);
		$this->assertInstanceOf(Bird::class, $bird);
		$this->assertEquals(1, $bird->id);
		$this->assertEquals('Foo', $bird->name);
		$this->assertEquals('Bar', $bird->species);
		$this->assertEquals(1, $bird->flying);
		$this->assertEquals(5, $bird->wingspan);
		$this->assertTrue($bird->exists);
	}

	public function testTopClassWithBaseId() {
		$bird = Animal::find(1);
		$this->assertEquals(Bird::class, InheritableModel::topClassWithBaseId($bird->base_id));
	}

	public function testElevateMultiple() {
		$animals = Animal::get(['*'], false);
		$birds = InheritableModel::elevateMultiple($animals);
		$this->assertInstanceOf(Collection::class, $birds);
		$this->assertInstanceOf(Bird::class, $birds->first());
	}

	public function testElevateMultipleNotCollection() {
		$animals = Animal::get(['*'], false)->all();
		$birds = InheritableModel::elevateMultiple($animals);
		$this->assertTrue(is_array($birds));
		$this->assertInstanceOf(Bird::class, $birds[0]);
	}

	public function testElevateMultipleEmptyCollection() {
		$result = InheritableModel::elevateMultiple(collect());
		$this->assertInstanceOf(Collection::class, $result);
		$this->assertCount(0, $result);
	}

	public function testElevateMultipleEmptyArray() {
		$result = InheritableModel::elevateMultiple([]);
		$this->assertTrue(is_array($result));
		$this->assertCount(0, $result);
	}

	public function testGetBaseId() {
		$bird = new Bird(['base_id' => 5]);
		$this->assertEquals(5, $bird->getBaseId());
	}

	public function testGetBaseIdEmpty() {
		$bird = new Bird();
		$this->assertNull($bird->getBaseId());
	}

	public function testToArray() {
		$bird = Bird::with(['owner', 'trainer'])->first()->toArray();
		$this->assertArrayHasKey('id', $bird);
		$this->assertArrayHasKey('base_id', $bird);
		$this->assertArrayHasKey('owner_id', $bird);
		$this->assertArrayHasKey('trainer_id', $bird);
		$this->assertArrayHasKey('owner', $bird);
		$this->assertArrayHasKey('trainer', $bird);
		$this->assertArrayHasKey('flying', $bird);
		$this->assertArrayHasKey('wingspan', $bird);
		$this->assertArrayHasKey('name', $bird);
		$this->assertArrayHasKey('species', $bird);
	}

	public function testRecursiveAttributesToArray() {
		$bird = Bird::first();
		$array = $bird->toArray();
		
		$this->assertArrayNotHasKey('secret', $bird);
		$this->assertArrayNotHasKey('price', $array);
		
		$this->assertArrayHasKey('born_at', $array);
		$this->assertArrayHasKey('acquired_at', $array);
		
		$this->assertEquals($array['bird_number'], $bird->bird_number);
		$this->assertEquals($array['animal_number'], $bird->animal_number);

		$this->assertIsBool($array['flying']);
		$this->assertEquals($bird->flying, $array['flying']);
		$this->assertIsBool($array['male']);
		$this->assertEquals($bird->male, $array['male']);

		$this->assertArrayHasKey('formatted_acquired_at', $array);
		$this->assertArrayHasKey('formatted_born_at', $array);
	}

	public function testGetRecursiveAttributes() {
		$attributes = [
			'id' => 1,
			'base_id' => 1,
			'trainer_id' => 1,
			'flying' => false,
			'wingspan' => 94,
			'price' => 500517231,
			'bird_number' => 1704756954,
			'acquired_at' => '2012-02-22 19:15:35',
			'owner_id' => 1,
			'name' => 'Miss Kaylie Cruickshank PhD',
			'species' => 'ut aspernatur aliquam modi',
			'secret' => 'nemo',
			'animal_number' => 1823374935,
			'born_at' => '2014-03-06 14:53:48',
			'male' => true,
		];
		$array = (new Bird($attributes))->getRecursiveAttributes();
		$this->assertEquals($attributes, $array);
	}

	public function testIdAs() {
		$bird = factory(Bird::class)->make();
		$bird->id = 123;
		$bird->save();
		$this->assertNotEquals($bird->id_as(Bird::class), $bird->id_as(Animal::class));
	}

	public function testElevate() {
		$animal = Animal::take(1)->get(['*'], false)->first();
		$this->assertInstanceOf(Bird::class, $animal->elevate());
	}

	public function testGetFillable() {
		$this->assertEquals([], (new Dog())->getFillable());
		$this->assertEquals(['name'], (new Animal())->getFillable());
		$this->assertEquals(['flying'], (new Bird())->getFillable());
	}

	public function testGetGuarded() {
		$this->assertEquals([], (new Dog())->getGuarded());
		$this->assertEquals(['species'], (new Animal())->getGuarded());
		$this->assertEquals(['wingspan'], (new Bird())->getGuarded());
	}

	public function testGetHidden() {
		$this->assertEquals([], (new Dog())->getHidden());
		$this->assertEquals(['secret'], (new Animal())->getHidden());
		$this->assertEquals(['price'], (new Bird())->getHidden());
	}

	public function testGetDates() {
		$this->assertEquals([], (new Dog())->getDates());
		$this->assertEquals(['born_at'], (new Animal())->getDates());
		$this->assertEquals(['acquired_at'], (new Bird())->getDates());
	}

	public function testGetColumns() {
		$animalColumns = (new Animal())->getColumns();
		$birdColumns = (new Bird())->getColumns();
		$this->assertEquals([
			'id',
			'base_id',
			'owner_id',
			'name',
			'species',
			'secret',
			'animal_number',
			'born_at',
			'male',
		], $animalColumns);
		$this->assertEquals([
			'id',
			'base_id',
			'trainer_id',
			'flying',
			'wingspan',
			'price',
			'bird_number',
			'acquired_at',
		], $birdColumns);
	}

	public function testGetRecursiveHidden() {
		$this->assertEquals(['secret'], (new Animal())->getRecursiveHidden());
		$this->assertEquals(['price', 'secret'], (new Bird())->getRecursiveHidden());
	}

	public function testGetRecursiveFillable() {
		$this->assertEquals(['name'], (new Animal())->getRecursiveFillable());
		$this->assertEquals(['flying', 'name'], (new Bird())->getRecursiveFillable());
	}

	public function testGetRecursiveColumns() {
		$this->assertEquals([
			'id',
			'base_id',
			'owner_id',
			'name',
			'species',
			'secret',
			'animal_number',
			'born_at',
			'male',
		], (new Animal())->getRecursiveColumns());
		$this->assertEquals([
			'id',
			'base_id',
			'trainer_id',
			'flying',
			'wingspan',
			'price',
			'bird_number',
			'acquired_at',
			'owner_id',
			'name',
			'species',
			'secret',
			'animal_number',
			'born_at',
			'male',
		], array_values((new Bird())->getRecursiveColumns()));
	}

	public function testGetRecursiveGuarded() {
		$this->assertEquals(['species'], (new Animal())->getRecursiveGuarded());
		$this->assertEquals(['wingspan', 'species'], (new Bird())->getRecursiveGuarded());
	}

	public function testGetRecursiveDates() {
		$this->assertEquals(['born_at'], (new Animal())->getRecursiveDates());
		$this->assertEquals(['acquired_at', 'born_at'], (new Bird())->getRecursiveDates());
	}

	public function testFill() {
		$bird = new Bird();
		$bird->fill(['name' => 'foo', 'flying' => true]);
		$animal = (new ReflectionObject($bird))->getProperty('parent_');
		$animal->setAccessible(true);
		$animal = $animal->getValue($bird);
		
		$this->assertEquals('foo', $animal->name);
		$this->assertEquals(5, $bird->flying);
	}

	public function testSave() {
		$bird = factory(Bird::class)->make();
		$bird->save();
		$savedBird = Bird::where('id', $bird->id_as(Bird::class))->get(['*'], false)->first();
		$savedAnimal = Animal::where('id', $bird->id_as(Animal::class))->get(['*'], false)->first();
		$this->assertEquals($bird->trainer_id, $savedBird->trainer_id);
		$this->assertEquals($bird->flying, $savedBird->flying);
		$this->assertEquals($bird->wingspan, $savedBird->wingspan);
		$this->assertEquals($bird->price, $savedBird->price);
		$this->assertEquals($bird->bird_number, $savedBird->bird_number);
		$this->assertEquals($bird->acquired_at, $savedBird->acquired_at);

		$this->assertEquals($bird->owner_id, $savedAnimal->owner_id);
		$this->assertEquals($bird->name, $savedAnimal->name);
		$this->assertEquals($bird->species, $savedAnimal->species);
		$this->assertEquals($bird->secret, $savedAnimal->secret);
		$this->assertEquals($bird->animal_number, $savedAnimal->animal_number);
		$this->assertEquals($bird->born_at, $savedAnimal->born_at);
		$this->assertEquals($bird->male, $savedAnimal->male);
	}

	public function testDelete() {
		$bird = Bird::first();
		$bird->delete();
		$deletedBird = Bird::where('id', $bird->id_as(Bird::class))->get(['*'], false)->first();
		$deletedAnimal = Animal::where('id', $bird->id_as(Animal::class))->get(['*'], false)->first();
		$this->assertNull($deletedBird);
		$this->assertNull($deletedAnimal);
	}

	public function testTableName() {
		$this->assertEquals('animals', Animal::tableName());
		$this->assertEquals('birds', Bird::tableName());
		$this->assertEquals('animals', Animal::tableName('name'));
		$this->assertEquals('birds', Bird::tableName('wingspan'));
		$this->assertEquals('animals', Bird::tableName('name'));
	}

	public function testGetTableName() {
		$animal = new Animal();
		$bird = new Bird();
		$this->assertEquals('animals', $animal->getTableName());
		$this->assertEquals('birds', $bird->getTableName());
		$this->assertEquals('animals', $animal->getTableName('name'));
		$this->assertEquals('birds', $bird->getTableName('wingspan'));
		$this->assertEquals('animals', $bird->getTableName('name'));
	}

	public function testHasAttribute() {
		$bird = new Bird();
		$this->assertTrue($bird->hasAttribute('wingspan'));
		$this->assertFalse($bird->hasAttribute('name'));
	}

	public function testClasses() {
		$this->assertEquals([InheritableModel::class, Animal::class, Bird::class, Dog::class], InheritableModel::classes());
		$this->assertEquals([Animal::class, Bird::class], Animal::classes());
		$this->assertEquals([Dog::class], Dog::classes());
	}

	public function testTableClasses() {
		$this->assertEquals([
			'base_models' => InheritableModel::class,
			'animals' => Animal::class,
			'birds' => Bird::class,
			'dog' => Dog::class,
		], InheritableModel::tableClasses());
		$this->assertEquals([
			'animals' => Animal::class,
			'birds' => Bird::class,
		], Animal::tableClasses());
		$this->assertEquals([
			'dog' => Dog::class,
		], Dog::tableClasses());
	}

	public function testSelect() {
		$builder = Animal::select('name');
		$this->assertInstanceOf(Builder::class, $builder);
		$this->assertEquals(['name'], $builder->getQuery()->columns);
	}

	public function testWhere() {
		$builder = Animal::where('name', 'foo');
		$where = $builder->getQuery()->wheres[0];
		$this->assertInstanceOf(Builder::class, $builder);
		$this->assertEquals('animals.name', $where['column']);
		$this->assertEquals('foo', $where['value']);
	}

	public function testChunk() {
		Animal::chunk(5, function($animals) {
			$this->assertInstanceOf(Collection::class, $animals);
			$this->assertCount(5, $animals);
			$this->assertInstanceOf(Bird::class, $animals->first());

			return false;
		});
	}

	public function testAddSelect() {
		$builder = Animal::select('name')->addSelect('species');
		$this->assertInstanceOf(Builder::class, $builder);
		$this->assertEquals(['name', 'species'], $builder->getQuery()->columns);
	}

	public function testCursor() {
		$cursor = Animal::cursor();
		$this->assertInstanceOf(LazyCollection::class, $cursor);
	}

	public function testOrderByDesc() {
		$builder = Animal::orderByDesc('name');
		$order = $builder->getQuery()->orders[0];
		$this->assertInstanceOf(Builder::class, $builder);
		$this->assertEquals(['column' => 'animals.name', 'direction' => 'desc'], $order);
	}

	public function testFind() {
		$this->assertInstanceOf(Bird::class, Animal::find(1));
		$this->assertInstanceOf(Bird::class, Bird::find(1));
	}

	public function testFindOrFail() {
		$this->assertInstanceOf(Bird::class, Animal::findOrFail(1));
		$this->assertInstanceOf(Bird::class, Bird::findOrFail(1));
	}

	public function testFindOrFailNotFoundParent() {
		$this->expectException(ModelNotFoundException::class);
		Animal::findOrFail(1000);
	}

	public function testFindOrFailNotFoundChild() {
		$this->expectException(ModelNotFoundException::class);
		Bird::findOrFail(1000);
	}

	public function testCreate() {
		$dog = Dog::create(factory(Dog::class)->make()->toArray());
		$this->assertNotNull($dog->id);
		$this->assertInstanceOf(Dog::class, $dog);
	}

	public function testFirstOrCreateAlreadyExisting() {
		$existingDog = factory(Dog::class)->create();
		$dog = Dog::firstOrCreate(['name' => $existingDog->name]);
		$this->assertEquals($existingDog->toArray(), $dog->toArray());
	}

	public function testFirstOrCreateNotExisting() {
		$dog = Dog::firstOrCreate(['name' => 'Foo']);
		$newDog = Dog::find(1);
		$this->assertEquals($dog->toArray(), $newDog->toArray());
	}

	public function testFirstOrNewAlreadyExisting() {
		$existingDog = factory(Dog::class)->create();
		$dog = Dog::firstOrNew(['name' => $existingDog->name]);
		$this->assertEquals($existingDog->toArray(), $dog->toArray());
	}

	public function testFirstOrNewNotExisting() {
		$dog = Dog::firstOrNew(['name' => 'Foo']);
		$this->assertNull(Dog::find(1));
	}

	public function testUpdateOrCreateNotExisting() {
		$dog = Dog::updateOrCreate(['name' => 'Foo']);
		$newDog = Dog::find(1);
		$this->assertEquals($dog->toArray(), $newDog->toArray());
	}

	public function testUpdateOrCreateAlreadyExisting() {
		$existingDog = factory(Dog::class)->create();
		$dog = Dog::updateOrCreate(['name' => $existingDog->name], ['name' => 'Foo']);
		$newDog = Dog::find(1);
		$this->assertEquals($dog->toArray(), $newDog->toArray());
	}

	public function testWithTrashed() {
		$builder = Cat::withTrashed();
		$this->assertEquals([SoftDeletingScope::class], $builder->removedScopes());
	}

	public function testOnlyTrashed() {
		$builder = Cat::onlyTrashed();
		$where = $builder->getQuery()->wheres[0];
		$this->assertEquals('NotNull', $where['type']);
		$this->assertEquals('cats.deleted_at', $where['column']);
	}

	public function testWithoutGlobalScope() {
		$builder = Cat::withoutGlobalScope(SoftDeletingScope::class);
		$this->assertEquals([SoftDeletingScope::class], $builder->removedScopes());
	}

	public function testHas() {
		$where = Bird::has('trainer')->getQuery()->wheres[0];
		$from = $where['query']->from;
		$this->assertEquals('Exists', $where['type']);
		$this->assertEquals('trainers', $from);
	}

	public function testWhereHas() {
		$where = Bird::whereHas('trainer', function($query) {
			$query->where('age', '<', 50);
		})->getQuery()->wheres[0];
		$from = $where['query']->from;
		$this->assertEquals('Exists', $where['type']);
		$this->assertEquals('trainers', $from);
		$this->assertEquals('age', $where['query']->wheres[1]['column']);
		$this->assertEquals(50, $where['query']->wheres[1]['value']);
		$this->assertEquals('<', $where['query']->wheres[1]['operator']);
	}

	public function testWhereNull() {
		$where = Bird::whereNull('wingspan')->getQuery()->wheres[0];
		$this->assertEquals('Null', $where['type']);
		$this->assertEquals('wingspan', $where['column']);
	}

	public function testWhereNotNull() {
		$where = Bird::whereNotNull('wingspan')->getQuery()->wheres[0];
		$this->assertEquals('NotNull', $where['type']);
		$this->assertEquals('wingspan', $where['column']);
	}

	public function testWhereIn() {
		$where = Bird::whereIn('id', [1, 2])->getQuery()->wheres[0];
		$this->assertEquals('In', $where['type']);
		$this->assertEquals('id', $where['column']);
		$this->assertEquals([1, 2], $where['values']);
	}

	public function testDoesntHave() {
		$where = Bird::doesntHave('trainer')->getQuery()->wheres[0];
		$from = $where['query']->from;
		$this->assertEquals('NotExists', $where['type']);
		$this->assertEquals('trainers', $from);
	}

	public function testWhereDoesntHave() {
		$where = Bird::whereDoesntHave('trainer', function($query) {
			$query->where('age', '<', 50);
		})->getQuery()->wheres[0];
		$from = $where['query']->from;
		$this->assertEquals('NotExists', $where['type']);
		$this->assertEquals('trainers', $from);
		$this->assertEquals('age', $where['query']->wheres[1]['column']);
		$this->assertEquals(50, $where['query']->wheres[1]['value']);
		$this->assertEquals('<', $where['query']->wheres[1]['operator']);
	}

	public function testWhereHasMorph() {
		$where = Cat::whereHasMorph('friend', [Dog::class])->getQuery()->wheres[0];
		$this->assertEquals('Nested', $where['type']);
	}

	public function testWhereDoesntHaveMorph() {
		$where = Cat::whereDoesntHaveMorph('friend', [Dog::class])->getQuery()->wheres[0];
		$this->assertEquals('Nested', $where['type']);
	}

	public function testWithCount() {
		$expression = Cat::withCount('friend')->getQuery()->columns[1];
		$this->assertInstanceOf(Expression::class, $expression);
	}

	public function testCount() {
		factory(Cat::class, 3)->create();
		$this->assertEquals(3, Cat::count());
	}

	public function testFirst() {
		$firstCat = factory(Cat::class, 3)->create()->first();
		$cat = Cat::first();
		$this->assertEquals($firstCat->toArray(), $cat->toArray());
	}

	public function testWithout() {
		$eagerLoads = Monkey::without('friend')->getEagerLoads();
		$this->assertCount(0, $eagerLoads);
	}

}
