<?php

namespace App\Http\Controllers;

use App\Models\AdminAudit;
use App\Models\Project;
use App\Models\ProjectTranslation;
use App\Support\SupportedLocales;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AdminProjectController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Project::class);

        return view('admin.projects.index', ['projects' => Project::with('translations')->orderBy('display_order')->orderBy('id')->get()]);
    }

    public function create(): View
    {
        Gate::authorize('create', Project::class);

        return view('admin.projects.form', ['project' => new Project, 'locales' => app(SupportedLocales::class)->codes()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Project::class);
        $project = DB::transaction(fn () => $this->saveProject(new Project, $request));
        $this->audit('created', $project);

        return redirect()->route('admin.projects.edit', $project)->with('status', __('admin.saved'));
    }

    public function edit(Project $project): View
    {
        Gate::authorize('view', $project);

        return view('admin.projects.form', ['project' => $project->load('translations'), 'locales' => app(SupportedLocales::class)->codes()]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        Gate::authorize('update', $project);
        DB::transaction(fn () => $this->saveProject($project, $request));
        $this->audit('updated', $project);

        return back()->with('status', __('admin.saved'));
    }

    public function archive(Project $project): RedirectResponse
    {
        Gate::authorize('delete', $project);
        $project->update(['archived_at' => now(), 'updated_by' => auth()->id()]);
        $this->audit('archived', $project);

        return back()->with('status', __('admin.archived'));
    }

    public function preview(Request $request, Project $project, string $locale): View
    {
        Gate::authorize('view', $project);
        abort_unless(app(SupportedLocales::class)->supports($locale), 404);
        $translation = ProjectTranslation::query()->where('project_id', $project->id)->where('locale', $locale)->firstOrFail();
        $links = $this->jsonArray($project->getRawOriginal('links'));
        app()->setLocale($locale);

        return view('pages.project', ['project' => [...$project->only(['slug', 'stack', 'featured']), ...$translation->only(['title', 'summary', 'status', 'problem', 'context', 'role', 'architecture', 'decisions', 'tradeoffs', 'testing', 'challenges', 'evidence']), 'repository' => $links['repository'] ?? null, 'demo' => $links['demo'] ?? null, 'sources' => $this->jsonArray($project->getRawOriginal('evidence'))], 'title' => (string) $translation->getAttribute('title'), 'description' => (string) $translation->getAttribute('summary')]);
    }

    private function saveProject(Project $project, Request $request): Project
    {
        $data = $request->validate(['slug' => ['required', 'alpha_dash', 'max:120', 'unique:projects,slug,'.($project->id ?? 'NULL')], 'status_key' => ['nullable', 'string', 'max:120'], 'stack' => ['nullable', 'string', 'max:4000'], 'repository' => ['nullable', 'url:http,https', 'max:2048'], 'demo' => ['nullable', 'url:http,https', 'max:2048'], 'display_order' => ['required', 'integer', 'min:0', 'max:2147483647'], 'featured' => ['nullable', 'boolean'], 'translations' => ['required', 'array'], 'translations.*.title' => ['required', 'string', 'max:255'], 'translations.*.summary' => ['required', 'string', 'max:5000'], 'translations.*.status' => ['required', 'string', 'max:255'], 'translations.*.problem' => ['required', 'string'], 'translations.*.context' => ['required', 'string'], 'translations.*.role' => ['required', 'string'], 'translations.*.architecture' => ['required', 'string'], 'translations.*.decisions' => ['required', 'string'], 'translations.*.tradeoffs' => ['required', 'string'], 'translations.*.testing' => ['required', 'string'], 'translations.*.challenges' => ['required', 'string'], 'translations.*.evidence' => ['required', 'string'], 'translations.*.editorial_status' => ['required', 'in:draft,published']]);
        $project->fill(['slug' => $data['slug'], 'status_key' => $data['status_key'] ?? null, 'stack' => $this->lines($data['stack'] ?? ''), 'links' => ['repository' => $data['repository'] ?? null, 'demo' => $data['demo'] ?? null], 'display_order' => $data['display_order'], 'featured' => (bool) ($data['featured'] ?? false), 'updated_by' => auth()->id(), 'created_by' => $project->created_by ?: auth()->id()])->save();
        foreach ($data['translations'] as $locale => $translation) {
            $project->translations()->updateOrCreate(['locale' => $locale], [...$translation, 'published_at' => $translation['editorial_status'] === 'published' ? now() : null]);
        }

        return $project;
    }

    private function lines(string $value): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', $value) ?: [])));
    }

    /** @return array<int|string, mixed> */
    private function jsonArray(mixed $value): array
    {
        $decoded = is_string($value) ? json_decode($value, true) : null;

        return is_array($decoded) ? $decoded : [];
    }

    private function audit(string $action, Project $project): void
    {
        AdminAudit::create(['user_id' => auth()->id(), 'action' => $action, 'auditable_type' => Project::class, 'auditable_id' => $project->id, 'ip_address' => request()->ip()]);
    }
}
