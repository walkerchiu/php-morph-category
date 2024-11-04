<?php

namespace WalkerChiu\MorphCategory;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use WalkerChiu\MorphCategory\Models\Entities\Category;
use WalkerChiu\MorphCategory\Models\Entities\CategoryLang;

class CategoryTest extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ .'/../migrations');
        $this->withFactories(__DIR__ .'/../../src/database/factories');
    }

    /**
     * To load your package service provider, override the getPackageProviders.
     *
     * @param \Illuminate\Foundation\Application  $app
     * @return Array
     */
    protected function getPackageProviders($app)
    {
        return [\WalkerChiu\Core\CoreServiceProvider::class,
                \WalkerChiu\MorphCategory\MorphCategoryServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
    }

    /**
     * A basic functional test on Category.
     *
     * For WalkerChiu\MorphCategory\Models\Entities\Category
     * 
     * @return void
     */
    public function testMorphCategory()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-morph-category.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-morph-category.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-morph-category.soft_delete', 1);

        // Give
        $db_morph_1 = factory(Category::class)->create();
        $db_morph_2 = factory(Category::class)->create();
        $db_morph_3 = factory(Category::class)->create(['is_enabled' => 1]);

        // Get records after creation
            // When
            $records = Category::all();
            // Then
            $this->assertCount(3, $records);

        // Delete someone
            // When
            $db_morph_2->delete();
            $records = Category::all();
            // Then
            $this->assertCount(2, $records);

        // Resotre someone
            // When
            Category::withTrashed()
                    ->find($db_morph_2->id)
                    ->restore();
            $record_2 = Category::find($db_morph_2->id);
            $records = Category::all();
            // Then
            $this->assertNotNull($record_2);
            $this->assertCount(3, $records);

        // Return Lang class
            // When
            $class = $record_2->lang();
            // Then
            $this->assertEquals($class, CategoryLang::class);

        // Scope query on enabled records
            // When
            $records = Category::ofEnabled()
                               ->get();
            // Then
            $this->assertCount(1, $records);

        // Scope query on disabled records
            // When
            $records = Category::ofDisabled()
                               ->get();
            // Then
            $this->assertCount(2, $records);
    }
}
