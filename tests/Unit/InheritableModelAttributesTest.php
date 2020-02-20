<?php

namespace Tests\Unit;

use Orchestra\Testbench\TestCase;
use Cvsouth\EloquentInheritance\Providers\ServiceProvider;
use Cvsouth\EloquentInheritance\InheritableModel;
use Cvsouth\EloquentInheritance\QueryBuilder;
use Cvsouth\EloquentInheritance\Builder;

use Tests\Stubs\Product;
use Tests\Stubs\UnitProduct;

class InheritableModelAttributesTest extends TestCase {

	protected function getPackageProviders($app) {
		return [ServiceProvider::class];
	}

	protected function setUp(): void {
		parent::setUp();
		$this->loadMigrationsFrom(__DIR__ .'/../database/migrations');
		$this->withFactories(__DIR__ .'/../database/factories');
		$this->artisan('migrate')->run();
	}

	protected function tearDown(): void {
		$this->artisan('migrate:reset')->run();
	}

	public function testCanGetAttributeOnParent() {
		$product = new Product(['price' => 10]);
		$this->assertEquals(10, $product->price);
	}

	public function testCanGetAttributeOnChild() {
		$product = new UnitProduct(['units' => 10]);
		$this->assertEquals(10, $product->units);
	}

	public function testCanGetAttributeFromParent() {
		$product = new UnitProduct(['price' => 10]);
		$this->assertEquals(10, $product->price);
	}

	public function testCanGetMutatedOnParent() {
		$product = new Product(['description' => 'Foo']);
		$this->assertEquals('#Foo', $product->description);
	}

	public function testCanGetMutatedOnChild() {
		$product = new UnitProduct(['color' => 'red']);
		$this->assertEquals('RED', $product->color);
	}

	public function testCanGetMutatedFromParent() {
		$product = new UnitProduct(['description' => 'Foo']);
		$this->assertEquals('#Foo', $product->description);
	}

	public function testVirtualMutatedOnParent() {
		$product = new Product();
		$this->assertEquals('product', $product->type);
	}

	public function testVirtualMutatedOnChild() {
		$product = new UnitProduct();
		$this->assertEquals('unitProduct', $product->name);
	}

	public function testVirtualMutatedFromParent() {
		$product = new UnitProduct();
		$this->assertEquals('product', $product->type);
	}

	public function testVirtualOverridenMutated() {
		$product = new UnitProduct();
		$this->assertEquals(10, $product->serial);
	}

	public function testPluckExpensive() {
		$expensive = collect([new UnitProduct(['price' => 10]), new UnitProduct(['price' => 100])])
			->pluck('is_expensive');
		$this->assertFalse($expensive[0]);
		$this->assertTrue($expensive[1]);
	}

	public function testKeyBy() {
		factory(UnitProduct::class, 2)->create();
		$ids = Product::all()->keyBy('product_id')->keys();
		$this->assertEquals(1, $ids->get(0));
		$this->assertEquals(2, $ids->get(1));
	}

	public function testMapWithKeys() {
		factory(UnitProduct::class, 2)->create();
		$ids = Product::all()->mapWithKeys(function($job) { return [$job->job_id => $job]; });
		$this->assertEquals(1, $ids->get(0));
		$this->assertEquals(2, $ids->get(1));
	}

	public function testPluck() {
		factory(UnitProduct::class, 2)->create();
		$ids = Product::all()->pluck('job_id');
		$this->assertEquals(1, $ids->get(0));
		$this->assertEquals(2, $ids->get(1));
	}

}
