# Migrations Laravel — Pipeline génération d’articles (Vivat)

Référence des 12 migrations pour la fonctionnalité de génération d’articles. Base : **MySQL 8**, **UUID** pour les clés primaires (générées en application, pas `gen_random_uuid`).

---

## Résumé

| # | Fichier | Table | Rôle |
|---|---------|--------|------|
| 1 | `2024_01_01_000001_create_sources_table.php` | sources | Base des médias |
| 2 | `2024_01_01_000002_create_categories_table.php` | categories | Catégorisation |
| 3 | `2024_01_01_000003_create_rss_feeds_table.php` | rss_feeds | Flux RSS |
| 4 | `2024_01_01_000004_create_rss_items_table.php` | rss_items | Items collectés |
| 5 | `2024_01_01_000005_create_enriched_items_table.php` | enriched_items | Données enrichies |
| 6 | `2024_01_01_000006_create_clusters_table.php` | clusters | Groupements thématiques |
| 7 | `2024_01_01_000007_create_cluster_items_table.php` | cluster_items | Pivot cluster ↔ items |
| 8 | `2024_01_01_000008_create_articles_table.php` | articles | Articles générés |
| 9 | `2024_01_01_000009_create_article_sources_table.php` | article_sources | Traçabilité sources |
| 10 | `2024_01_01_000010_create_category_templates_table.php` | category_templates | Config génération par catégorie |
| 11 | `2024_01_01_000011_create_pipeline_jobs_table.php` | pipeline_jobs | Suivi des jobs |
| 12 | `2024_01_01_000012_create_updated_at_triggers.php` | (triggers) | Auto-update timestamps |

---

## 1. sources

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('base_url');
            $table->string('language', 10)->default('fr');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('is_active');
            $table->index('language');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sources');
    }
};
```

---

## 2. categories

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#3B82F6');
            $table->timestamp('created_at')->useCurrent();
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
```

---

## 3. rss_feeds

*(Version MySQL : pas de `gen_random_uuid`, `foreignUuid`.)*

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rss_feeds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('source_id')->nullable()->constrained('sources')->nullOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->text('feed_url');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_fetched_at')->nullable();
            $table->unsignedSmallInteger('fetch_interval_minutes')->default(30);
            $table->timestamp('created_at')->useCurrent();
            $table->index('is_active');
            $table->index('last_fetched_at');
            $table->index(['is_active', 'last_fetched_at'], 'idx_feeds_due_fetch');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rss_feeds');
    }
};
```

---

## 4. rss_items

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rss_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('rss_feed_id')->nullable()->constrained('rss_feeds')->nullOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('guid')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('url');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('fetched_at')->useCurrent();
            $table->enum('status', ['new', 'enriching', 'enriched', 'failed', 'ignored', 'used'])->default('new');
            $table->string('dedup_hash', 64)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unique('dedup_hash');
            $table->index('status');
            $table->index('published_at');
            $table->index('fetched_at');
            $table->index(['status', 'fetched_at'], 'idx_items_processing');
            $table->fullText(['title', 'description'], 'ft_items_content');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rss_items');
    }
};
```

---

## 5. enriched_items

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enriched_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('rss_item_id')->nullable()->unique()->constrained('rss_items')->cascadeOnDelete();
            $table->text('lead')->nullable();
            $table->json('headings')->nullable();
            $table->json('key_points')->nullable();
            $table->longText('extracted_text')->nullable();
            $table->string('extraction_method', 50)->default('readability');
            $table->unsignedTinyInteger('quality_score')->default(0);
            $table->timestamp('enriched_at')->useCurrent();
            $table->index('quality_score');
            $table->index('extraction_method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enriched_items');
    }
};
```

---

## 6. clusters

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clusters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('label');
            $table->json('keywords')->nullable();
            $table->enum('status', ['pending', 'processing', 'generated', 'failed'])->default('pending');
            $table->timestamp('created_at')->useCurrent();
            $table->index('status');
            $table->index(['category_id', 'status'], 'idx_clusters_category_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clusters');
    }
};
```

---

## 7. cluster_items

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cluster_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_id')->nullable()->constrained('clusters')->cascadeOnDelete();
            $table->foreignUuid('rss_item_id')->nullable()->constrained('rss_items')->cascadeOnDelete();
            $table->unique(['cluster_id', 'rss_item_id'], 'uk_cluster_item');
            $table->index('rss_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cluster_items');
    }
};
```

---

## 8. articles

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->json('keywords')->nullable();
            $table->foreignUuid('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignUuid('cluster_id')->nullable()->constrained('clusters')->nullOnDelete();
            $table->unsignedSmallInteger('reading_time')->default(5);
            $table->enum('status', ['draft', 'review', 'published', 'archived', 'rejected'])->default('draft');
            $table->unsignedTinyInteger('quality_score')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index('status');
            $table->index('published_at');
            $table->index('quality_score');
            $table->index(['status', 'published_at'], 'idx_articles_published');
            $table->index(['category_id', 'status'], 'idx_articles_category_status');
            $table->fullText(['title', 'excerpt'], 'ft_articles_search');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
```

---

## 9. article_sources

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_sources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('article_id')->nullable()->constrained('articles')->cascadeOnDelete();
            $table->foreignUuid('rss_item_id')->nullable()->constrained('rss_items')->nullOnDelete();
            $table->foreignUuid('source_id')->nullable()->constrained('sources')->nullOnDelete();
            $table->text('url');
            $table->timestamp('used_at')->useCurrent();
            $table->index('article_id');
            $table->index('rss_item_id');
            $table->unique(['article_id', 'rss_item_id'], 'uk_article_rss_item');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_sources');
    }
};
```

---

## 10. category_templates

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_id')->nullable()->unique()->constrained('categories')->cascadeOnDelete();
            $table->string('tone', 50)->default('professional');
            $table->string('structure', 50)->default('standard');
            $table->unsignedSmallInteger('min_word_count')->default(900);
            $table->unsignedSmallInteger('max_word_count')->default(2000);
            $table->text('style_notes')->nullable();
            $table->text('seo_rules')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_templates');
    }
};
```

---

## 11. pipeline_jobs

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pipeline_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('job_type', ['fetch_rss', 'enrich', 'cluster', 'generate', 'publish', 'cleanup']);
            $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->index('job_type');
            $table->index('status');
            $table->index(['status', 'created_at'], 'idx_jobs_pending');
            $table->index(['job_type', 'status'], 'idx_jobs_type_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pipeline_jobs');
    }
};
```

---

## 12. updated_at triggers (MySQL)

*(À exécuter après les tables `sources` et `articles` ; certaines tables n’ont que `created_at` dans la migration, les triggers ajoutent la mise à jour de `updated_at` si la colonne existe. Ici sources et articles ont `timestamps()` donc `updated_at` existe.)*

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('
            CREATE TRIGGER sources_updated_at_trigger
            BEFORE UPDATE ON sources
            FOR EACH ROW
            SET NEW.updated_at = NOW()
        ');
        DB::unprepared('
            CREATE TRIGGER articles_updated_at_trigger
            BEFORE UPDATE ON articles
            FOR EACH ROW
            SET NEW.updated_at = NOW()
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS sources_updated_at_trigger');
        DB::unprepared('DROP TRIGGER IF EXISTS articles_updated_at_trigger');
    }
};
```

---

*Référence : CONTEXTE_PROJET.md section 9. Fonctionnalité : génération d’articles.*
