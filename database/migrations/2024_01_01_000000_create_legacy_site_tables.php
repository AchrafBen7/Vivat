<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Tables alignées sur la base existante ID93677_vivat (phpMyAdmin dump).
 * Structure identique pour permettre l'import des données (articles, catégories, utilisateurs).
 * À ne pas confondre avec le pipeline (sources, rss_*, enriched_items, clusters, articles pipeline).
 */
return new class extends Migration
{
    public function up(): void
    {
        // --- cloaked_ip (dump: latin1) ---
        Schema::create('cloaked_ip', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address_v4', 15);
            $table->string('dns_name', 255);
            $table->dateTime('date_discover');
            $table->timestamp('date_update')->useCurrent();
            $table->dateTime('last_check');
            $table->tinyInteger('status')->default(0);
            $table->string('bot', 30);
            $table->string('origin', 30);
        });

        // --- logs (dump: MyISAM, latin1) ---
        Schema::create('logs', function (Blueprint $table) {
            $table->integer('id');
            $table->tinyInteger('user');
            $table->string('action', 6);
            $table->string('module', 12);
            $table->string('detail', 255);
            $table->dateTime('when');
        });
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            \DB::statement('ALTER TABLE logs ENGINE=MyISAM');
        }

        // --- tbl_ref : catégories / références éditoriales (dump: utf8) ---
        Schema::create('tbl_ref', function (Blueprint $table) {
            $table->id();
            $table->integer('refID')->nullable();
            $table->string('refTitle', 255)->nullable();
            $table->string('refLang', 2)->nullable();
            $table->string('refType', 255)->nullable();
            $table->string('refUrl', 255)->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->string('meta_desc', 255)->nullable();
            $table->string('meta_kw', 255)->nullable();
            $table->string('top_desc', 255)->nullable();
        });

        // --- tbl_usr : utilisateurs site (dump: utf8) ---
        $driver = Schema::getConnection()->getDriverName();
        Schema::create('tbl_usr', function (Blueprint $table) use ($driver) {
            if ($driver === 'sqlite') {
                $table->integer('usrID')->primary();
            } else {
                $table->integer('usrID')->autoIncrement()->primary();
            }
            $table->string('usrNickName', 255)->nullable();
            $table->string('usrPw', 255)->nullable();
            $table->string('usrRealLastName', 255)->nullable();
            $table->string('usrRealFirstName', 255)->nullable();
            $table->string('usrEmail', 255)->nullable();
            $table->tinyInteger('usrType')->nullable();
        });

        // --- tbl_cont_pg : articles / pages de contenu site (dump: utf8) ---
        Schema::create('tbl_cont_pg', function (Blueprint $table) use ($driver) {
            if ($driver === 'sqlite') {
                $table->integer('contID')->primary();
            } else {
                $table->integer('contID')->autoIncrement()->primary();
            }
            $table->string('contTitle', 250)->nullable();
            $table->text('contDesc')->nullable();
            $table->longText('contContent')->nullable();
            $table->text('contKeywords')->nullable();
            $table->string('contImgs', 255)->nullable();
            $table->text('contImgsAlt')->nullable();
            $table->char('contLang', 10)->nullable();
            $table->string('contRef1', 200)->nullable();
            $table->string('contRef2', 200)->nullable();
            $table->string('contRef3', 200)->nullable();
            $table->integer('online')->nullable();
            $table->date('contDate')->nullable();
            $table->integer('contPgs')->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->string('meta_desc', 255)->nullable();
            $table->dateTime('creation')->nullable();
            $table->dateTime('modification')->nullable();
            $table->integer('contPublishDate')->nullable();
        });

        // Index avec préfixes pour rester sous la limite MySQL (3072 bytes en utf8mb4)
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE tbl_cont_pg ADD INDEX TBL_CONT_PG_IDX_PG_CONTENT (contID, contTitle(100), contLang(10), contRef1(50), contRef2(50), contRef3(50), online, contDate, contPgs)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_cont_pg');
        Schema::dropIfExists('tbl_usr');
        Schema::dropIfExists('tbl_ref');
        Schema::dropIfExists('logs');
        Schema::dropIfExists('cloaked_ip');
    }
};
