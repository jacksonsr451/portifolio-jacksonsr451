<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->string('status_key', 120)->nullable();
            $table->json('stack')->nullable();
            $table->json('links')->nullable();
            $table->json('evidence')->nullable();
            $table->unsignedInteger('display_order')->default(0)->index();
            $table->boolean('featured')->default(false)->index();
            $table->timestamp('archived_at')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('project_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 35);
            $table->string('title', 255);
            $table->text('summary');
            $table->string('editorial_status', 20)->default('draft')->index();
            $table->timestamp('published_at')->nullable();
            foreach (['status', 'problem', 'context', 'role', 'architecture', 'decisions', 'tradeoffs', 'testing', 'challenges', 'evidence'] as $field) {
                $table->text($field);
            }
            $table->timestamps();
            $table->unique(['project_id', 'locale']);
            $table->index(['locale', 'editorial_status']);
        });

        Schema::create('admin_audits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 80);
            $table->string('auditable_type', 160);
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('metadata')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_audits');
        Schema::dropIfExists('project_translations');
        Schema::dropIfExists('projects');
    }
};
