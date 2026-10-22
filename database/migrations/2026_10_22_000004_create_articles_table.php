<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('archived_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('article_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 35);
            $table->string('title', 255);
            $table->text('description');
            $table->longText('body_markdown');
            $table->string('seo_title', 255);
            $table->text('seo_description');
            $table->string('cover_alt', 255)->nullable();
            $table->date('published_on')->nullable();
            $table->string('editorial_status', 20)->default('draft')->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['article_id', 'locale']);
            $table->index(['locale', 'editorial_status', 'published_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_translations');
        Schema::dropIfExists('articles');
    }
};
