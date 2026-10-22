<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

function articleTranslation(string $status = 'draft'): array
{
    return ['title' => 'Managed article', 'description' => 'A persisted description.', 'body_markdown' => "## Safe heading\n\nBody text.", 'seo_title' => 'Managed article SEO', 'seo_description' => 'Managed article SEO description.', 'published_on' => '2026-10-22', 'editorial_status' => $status];
}

beforeEach(function (): void {
    config(['portfolio.admin_emails' => ['owner@example.test']]);
    $this->admin = User::factory()->create(['email' => 'owner@example.test', 'password' => Hash::make('correct horse battery staple')]);
    $this->other = User::factory()->create(['email' => 'other@example.test']);
});

it('reuses the project panel authorization for articles', function () {
    $this->get('/admin/articles')->assertRedirect('/admin/login');
    $this->actingAs($this->other)->get('/admin/articles')->assertForbidden();
});

it('creates, publishes independently, previews privately, archives, and deletes an article', function () {
    $payload = ['slug' => 'managed-article', 'translations' => ['pt-BR' => articleTranslation('published'), 'en' => articleTranslation('draft')]];
    $this->actingAs($this->admin)->post('/admin/articles', $payload)->assertRedirect();
    $article = Article::query()->where('slug', 'managed-article')->firstOrFail();

    $this->get('/pt-BR/writing/managed-article')->assertOk()->assertSee('Managed article');
    $this->get('/en/writing/managed-article')->assertNotFound();
    $this->actingAs($this->admin)->get("/admin/articles/{$article->id}/preview/en")
        ->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')->assertHeader('Cache-Control', 'no-store, private');
    $this->actingAs($this->admin)->post("/admin/articles/{$article->id}/archive")->assertRedirect();
    $this->get('/pt-BR/writing/managed-article')->assertNotFound();
    $this->actingAs($this->admin)->delete("/admin/articles/{$article->id}")->assertRedirect();
    expect(Article::query()->whereKey($article->id)->exists())->toBeFalse();
});

it('renders the admin workflow, filters articles, and supports unpublishing', function () {
    $this->actingAs($this->admin)->get('/admin/articles/create')->assertOk()->assertSee(__('admin.new_article'));

    $this->actingAs($this->admin)->post('/admin/articles', [
        'slug' => 'workflow-article',
        'translations' => ['pt-BR' => articleTranslation('published')],
    ])->assertRedirect();

    $article = Article::query()->where('slug', 'workflow-article')->firstOrFail();
    $this->actingAs($this->admin)->get('/admin/articles?search=workflow&locale=pt-BR&status=published')
        ->assertOk()->assertSee('workflow-article');
    $this->actingAs($this->admin)->get("/admin/articles/{$article->id}/edit")
        ->assertOk()->assertSee(__('admin.edit_article'));
    $this->actingAs($this->admin)->get("/admin/articles/{$article->id}/preview/fr")->assertNotFound();

    $this->actingAs($this->admin)->put("/admin/articles/{$article->id}", [
        'slug' => 'workflow-article',
        'translations' => ['pt-BR' => articleTranslation('draft')],
    ])->assertRedirect();

    $this->get('/pt-BR/writing/workflow-article')->assertNotFound();
    expect($article->fresh()->translations->first()->published_at)->toBeNull();
});

it('requires complete metadata before publishing and rejects unsafe locale input', function () {
    $payload = ['slug' => 'incomplete-article', 'translations' => ['pt-BR' => ['editorial_status' => 'published', 'title' => 'Title'], 'fr' => articleTranslation()]];
    $this->actingAs($this->admin)->post('/admin/articles', $payload)->assertSessionHasErrors(['translations.fr']);
    $this->actingAs($this->admin)->post('/admin/articles', ['slug' => 'incomplete-article', 'translations' => ['pt-BR' => ['editorial_status' => 'published', 'title' => 'Title']]])->assertSessionHasErrors(['translations.pt-BR.description']);
});

it('imports articles idempotently without overwriting administrative edits', function () {
    $article = Article::query()->where('slug', 'idempotent-apis')->firstOrFail();
    $article->translations()->where('locale', 'en')->update(['title' => 'Editorial override']);
    $this->artisan('portfolio:import-articles')->assertExitCode(0);
    expect($article->translations()->where('locale', 'en')->value('title'))->toBe('Editorial override');
    expect(Article::query()->where('slug', 'idempotent-apis')->count())->toBe(1);
});

it('does not expose filesystem-only Markdown after cutover', function () {
    $this->get('/en/writing/not-in-database')->assertNotFound();
});
