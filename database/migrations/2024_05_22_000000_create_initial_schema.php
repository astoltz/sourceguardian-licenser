<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Default Laravel Tables
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // Application Tables
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('display_name');
            $table->text('project_id'); // Encrypted
            $table->text('project_key'); // Encrypted
            $table->boolean('enabled')->default(true);
            $table->string('license_filename')->nullable();
            $table->timestamps();
        });

        Schema::create('project_constants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('data');
            $table->timestamps();
        });

        Schema::create('project_time_servers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained()->cascadeOnDelete();
            $table->string('data');
            $table->timestamps();
        });

        Schema::create('project_header_texts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained()->cascadeOnDelete();
            $table->string('data');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained()->cascadeOnDelete();
            $table->string('display_name');
            $table->boolean('enabled')->default(true);
            $table->text('override_project_id')->nullable(); // Encrypted
            $table->text('override_project_key')->nullable(); // Encrypted
            $table->string('override_license_filename')->nullable();
            $table->timestamps();
        });

        Schema::create('version_constants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('version_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('data');
            $table->timestamps();
        });

        Schema::create('version_header_texts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('version_id')->constrained()->cascadeOnDelete();
            $table->string('data');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('variations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained()->cascadeOnDelete();
            $table->string('display_name');
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('variation_constants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('variation_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('data');
            $table->timestamps();
        });

        Schema::create('variation_header_texts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('variation_id')->constrained()->cascadeOnDelete();
            $table->string('data');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('display_name');
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('customer_constants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('data');
            $table->timestamps();
        });

        Schema::create('customer_header_texts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained()->cascadeOnDelete();
            $table->string('data');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('licenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('display_name');
            $table->text('shared_secret'); // Encrypted
            $table->foreignUuid('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('variation_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('version_id')->constrained()->cascadeOnDelete();
            $table->boolean('enabled')->default(true);
            $table->dateTime('expiration_date')->nullable();
            $table->boolean('bind_domain_ignore_cli')->default(false);
            $table->boolean('bind_ip_ignore_cli')->default(false);
            $table->timestamps();
        });

        Schema::create('license_constants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('license_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('data');
            $table->timestamps();
        });

        Schema::create('license_header_texts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('license_id')->constrained()->cascadeOnDelete();
            $table->string('data');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('license_domains', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('license_id')->constrained()->cascadeOnDelete();
            $table->string('domain');
            $table->timestamps();
        });

        Schema::create('license_ips', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('license_id')->constrained()->cascadeOnDelete();
            $table->string('ip');
            $table->timestamps();
        });

        Schema::create('license_macs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('license_id')->constrained()->cascadeOnDelete();
            $table->string('mac');
            $table->timestamps();
        });

        Schema::create('license_machine_ids', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('license_id')->constrained()->cascadeOnDelete();
            $table->string('machine_id');
            $table->timestamps();
        });

        Schema::create('generated_licenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('license_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('version_id')->constrained()->cascadeOnDelete();
            $table->binary('data');
            $table->timestamp('downloaded_at')->nullable();
            $table->ipAddress('downloaded_ip')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('method');
            $table->string('path');
            $table->string('action')->nullable();
            $table->json('payload')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('generated_licenses');
        Schema::dropIfExists('license_machine_ids');
        Schema::dropIfExists('license_macs');
        Schema::dropIfExists('license_ips');
        Schema::dropIfExists('license_domains');
        Schema::dropIfExists('license_header_texts');
        Schema::dropIfExists('license_constants');
        Schema::dropIfExists('licenses');
        Schema::dropIfExists('customer_header_texts');
        Schema::dropIfExists('customer_constants');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('variation_header_texts');
        Schema::dropIfExists('variation_constants');
        Schema::dropIfExists('variations');
        Schema::dropIfExists('version_header_texts');
        Schema::dropIfExists('version_constants');
        Schema::dropIfExists('versions');
        Schema::dropIfExists('project_header_texts');
        Schema::dropIfExists('project_time_servers');
        Schema::dropIfExists('project_constants');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
