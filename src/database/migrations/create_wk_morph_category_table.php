<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateWkMorphCategoryTable extends Migration
{
    public function up()
    {
        Schema::create(config('wk-core.table.morph-category.categories'), function (Blueprint $table) {
            $table->uuid('id');
            $table->nullableUuidMorphs('host');
            $table->uuid('ref_id')->nullable();
            $table->string('type')->nullable();
            $table->string('attribute_set')->nullable();
            $table->string('serial')->nullable();
            $table->string('identifier');
            $table->string('url')->nullable();
            $table->string('target')->default('_self');
            $table->string('cover')->nullable();
            $table->unsignedBigInteger('order')->nullable();
            $table->json('images')->nullable();
            $table->boolean('is_enabled')->default(0);

            $table->timestampsTz();
            $table->softDeletes();

            $table->primary('id');
            $table->index('type');
            $table->index('attribute_set');
            $table->index('serial');
            $table->index('identifier');
            $table->index('is_enabled');
            $table->index(['host_type', 'host_id', 'type']);
            $table->index(['host_type', 'host_id', 'type', 'is_enabled']);
        });
        if (!config('wk-morph-category.onoff.core-lang_core')) {
            Schema::create(config('wk-core.table.morph-category.categories_lang'), function (Blueprint $table) {
                $table->uuid('id');
                $table->uuidMorphs('morph');
                $table->uuid('user_id')->nullable();
                $table->string('code');
                $table->string('key');
                $table->longText('value')->nullable();
                $table->boolean('is_current')->default(1);

                $table->timestampsTz();
                $table->softDeletes();

                $table->foreign('user_id')->references('id')
                    ->on(config('wk-core.table.user'))
                    ->onDelete('set null')
                    ->onUpdate('cascade');

                $table->primary('id');
            });
        }
        Schema::create(config('wk-core.table.morph-category.categories_morphs'), function (Blueprint $table) {
            $table->uuid('category_id')->nullable();
            $table->uuidMorphs('morph');

            $table->foreign('category_id')->references('id')
                  ->on(config('wk-core.table.morph-category.categories'))
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->index(['category_id', 'morph_type', 'morph_id']);
        });
    }

    public function down() {
        Schema::dropIfExists(config('wk-core.table.morph-category.categories_morphs'));
        Schema::dropIfExists(config('wk-core.table.morph-category.categories_lang'));
        Schema::dropIfExists(config('wk-core.table.morph-category.categories'));
    }
}
