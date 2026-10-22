<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

function adminTranslation(string $status = 'draft'): array
{
    return ['title' => 'Managed project', 'summary' => 'A persisted summary.', 'status' => 'Published status', 'problem' => 'Problem', 'context' => 'Context', 'role' => 'Role', 'architecture' => 'Architecture', 'decisions' => 'Decisions', 'tradeoffs' => 'Tradeoffs', 'testing' => 'Testing', 'challenges' => 'Challenges', 'evidence' => 'Evidence', 'editorial_status' => $status];
}

beforeEach(function (): void {
    config(['portfolio.admin_emails' => ['owner@example.test']]);
    $this->admin = User::factory()->create(['email' => 'owner@example.test', 'password' => Hash::make('correct horse battery staple')]);
    $this->other = User::factory()->create(['email' => 'other@example.test']);
});

it('blocks anonymous and non-owner administrative access', function () {
    $this->get('/admin/projects')->assertRedirect('/admin/login');
    $this->actingAs($this->other)->get('/admin/projects')->assertForbidden();
});

it('authenticates the configured owner and regenerates the session', function () {
    $this->withSession(['marker' => 'old'])->post('/admin/login', ['email' => 'owner@example.test', 'password' => 'correct horse battery staple'])->assertRedirect('/admin/projects');
    expect(auth()->id())->toBe($this->admin->id);
});

it('creates, updates, publishes, previews, and archives a project through the panel', function () {
    $payload = ['slug' => 'managed-project', 'status_key' => 'verified', 'stack' => "PHP\nLaravel", 'repository' => 'https://github.com/example/project', 'demo' => '', 'display_order' => 2, 'featured' => 1, 'translations' => ['pt-BR' => adminTranslation('published'), 'en' => adminTranslation('draft')]];
    $this->actingAs($this->admin)->post('/admin/projects', $payload)->assertRedirect();
    $project = Project::query()->where('slug', 'managed-project')->firstOrFail();
    $this->get('/pt-BR/projects/managed-project')->assertOk()->assertSee('Managed project');
    $this->get('/en/projects/managed-project')->assertNotFound();
    $this->actingAs($this->admin)->get("/admin/projects/{$project->id}/preview/en")->assertOk()->assertSee('Managed project');
    $this->actingAs($this->admin)->post("/admin/projects/{$project->id}/archive")->assertRedirect();
    $this->get('/pt-BR/projects/managed-project')->assertNotFound();
});

it('rejects invalid external links and duplicate slugs', function () {
    $project = Project::query()->firstOrFail();
    $payload = ['slug' => $project->slug, 'display_order' => 0, 'repository' => 'javascript:alert(1)', 'translations' => ['pt-BR' => adminTranslation(), 'en' => adminTranslation()]];
    $this->actingAs($this->admin)->put("/admin/projects/{$project->id}", $payload)->assertSessionHasErrors(['repository']);
});

it('keeps the import idempotent and does not overwrite editorial changes', function () {
    $project = Project::query()->where('slug', 'eventflow')->firstOrFail();
    $project->translations()->where('locale', 'en')->update(['title' => 'Editorial override']);
    $this->artisan('portfolio:import-projects')->assertExitCode(0);
    expect($project->translations()->where('locale', 'en')->value('title'))->toBe('Editorial override');
    expect(Project::query()->where('slug', 'eventflow')->count())->toBe(1);
});
