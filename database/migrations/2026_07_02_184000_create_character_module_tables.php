<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharacterModuleTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Tablas de referencia externas (mínimas para integridad referencial)
        if (!Schema::hasTable('ref_original_universes')) {
            Schema::create('ref_original_universes', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('evt_events')) {
            Schema::create('evt_events', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->timestamps();
            });
        }

        // Tablas del módulo Character
        Schema::create('chr_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 64)->unique();
            $table->timestamps();
        });

        Schema::create('chr_subcategory_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_id')->constrained('chr_categories');
            $table->string('name', 64);
            $table->timestamps();
        });

        Schema::create('chr_subcategory_definitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('subcategory_id')->constrained('chr_subcategory_types');
            $table->string('value', 128);
            $table->timestamps();
        });

        Schema::create('chr_category_undead_suf', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('value', 64)->unique();
            $table->timestamps();
        });

        Schema::create('chr_category_clone_pre', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('value', 64)->unique();
            $table->timestamps();
        });

        Schema::create('chr_roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 64)->unique();
            $table->timestamps();
        });

        Schema::create('chr_hierarchies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 128)->unique();
            $table->timestamps();
        });

        Schema::create('chr_ranks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 64)->unique();
            $table->timestamps();
        });

        Schema::create('chr_archetypes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 64)->unique();
            $table->timestamps();
        });

        Schema::create('chr_characters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->foreignUuid('original_universe_id')->constrained('ref_original_universes');
            $table->foreignUuid('category_id')->constrained('chr_categories');
            $table->foreignUuid('subcategory_type_id')->constrained('chr_subcategory_types');
            $table->foreignUuid('subcategory_definition_id')->constrained('chr_subcategory_definitions');

            $table->boolean('is_undead')->default(false);
            $table->foreignUuid('undead_suffix_id')->nullable()->constrained('chr_category_undead_suf');
            $table->foreignUuid('clone_prefix_id')->nullable()->constrained('chr_category_clone_pre');

            $table->foreignUuid('role_id')->constrained('chr_roles');
            $table->foreignUuid('hierarchy_id')->constrained('chr_hierarchies');
            $table->foreignUuid('rank_id')->constrained('chr_ranks');
            $table->foreignUuid('archetype_id')->constrained('chr_archetypes');

            $table->boolean('caste');
            $table->enum('gender', ['Masculino', 'Femenino', 'Indeterminado']);

            $table->string('age_text', 128)->nullable();
            $table->integer('age_years')->nullable();

            $table->text('short_description');
            $table->text('description');
            $table->text('large_description');
            $table->text('personality');
            $table->text('backstory');

            $table->text('cone_notes')->nullable();

            $table->timestamps();

            $table->index('name');
            $table->index('category_id');
        });

        Schema::create('chr_media_character', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('character_id')->constrained('chr_characters');
            $table->enum('media_type', ['portrait', 'cover', 'tarot_front', 'tarot_back', 'other']);
            $table->text('url');
            $table->string('alt_text', 255)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['character_id', 'media_type']);
        });

        Schema::create('chr_evt_character_event', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('character_id')->constrained('chr_characters');
            $table->foreignUuid('event_id')->nullable()->constrained('evt_events');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->date('happened_at')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('chr_expressions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('character_id')->constrained('chr_characters');
            $table->text('expression');
            $table->enum('kind', ['frase', 'gesto', 'silencio', 'tic', 'otro'])->nullable();
            $table->timestamps();
        });

        Schema::create('chr_stats', function (Blueprint $table) {
            $table->uuid('character_id')->primary();
            $table->foreign('character_id')->references('id')->on('chr_characters');
            $table->integer('hp');
            $table->integer('ad');
            $table->integer('ap');
            $table->integer('def');
            $table->integer('mr');
            $table->integer('stat_cap');
            $table->boolean('uses_magic')->default(true);
            $table->timestamps();
        });

        Schema::create('chr_abilities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('character_id')->constrained('chr_characters');
            $table->string('name', 255);
            $table->integer('affinity_base');
            $table->enum('aptitude_type', ['Fisica', 'Magica', 'Cuantica']);
            $table->enum('cone_hint', ['Nucleo', 'ZonaCercana', 'ZonaMedia', 'Periferia'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('character_id');
        });

        Schema::create('chr_category_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('character_id')->constrained('chr_characters');
            $table->string('key', 64);
            $table->text('value');
            $table->timestamps();

            $table->unique(['character_id', 'key']);
        });

        Schema::create('chr_action_results', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('label', 64);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chr_action_results');
        Schema::dropIfExists('chr_category_fields');
        Schema::dropIfExists('chr_abilities');
        Schema::dropIfExists('chr_stats');
        Schema::dropIfExists('chr_expressions');
        Schema::dropIfExists('chr_evt_character_event');
        Schema::dropIfExists('chr_media_character');
        Schema::dropIfExists('chr_characters');
        Schema::dropIfExists('chr_archetypes');
        Schema::dropIfExists('chr_ranks');
        Schema::dropIfExists('chr_hierarchies');
        Schema::dropIfExists('chr_roles');
        Schema::dropIfExists('chr_category_clone_pre');
        Schema::dropIfExists('chr_category_undead_suf');
        Schema::dropIfExists('chr_subcategory_definitions');
        Schema::dropIfExists('chr_subcategory_types');
        Schema::dropIfExists('chr_categories');
        // No borramos ref_original_universes ni evt_events por si acaso son compartidas
    }
}
