<?php

namespace App\Http\Controllers;

use App\Models\AdminAudit;
use App\Models\Article;
use App\Models\ArticleTranslation;
use App\Support\MarkdownRenderer;
use App\Support\SupportedLocales;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminArticleController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Article::class);
        $query = Article::query()->with('translations')->orderByDesc('id');
        if ($request->filled('search')) {
            $query->where('slug', 'like', '%'.addcslashes((string) $request->input('search'), '%_').'%');
        }
        if ($request->filled('locale')) {
            $query->whereHas('translations', fn ($q) => $q->where('locale', $request->string('locale')));
        }
        if ($request->filled('status')) {
            $query->whereHas('translations', fn ($q) => $q->where('editorial_status', $request->string('status')));
        }

        return view('admin.articles.index', ['articles' => $query->get(), 'locales' => app(SupportedLocales::class)->codes()]);
    }

    public function create(): View
    {
        Gate::authorize('create', Article::class);

        return view('admin.articles.form', ['article' => new Article, 'locales' => app(SupportedLocales::class)->codes()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Article::class);
        $article = DB::transaction(fn () => $this->save(new Article, $request));
        $this->audit('article.created', $article);

        return redirect()->route('admin.articles.edit', $article)->with('status', __('admin.article_saved'));
    }

    public function edit(Article $article): View
    {
        Gate::authorize('view', $article);

        return view('admin.articles.form', ['article' => $article->load('translations'), 'locales' => app(SupportedLocales::class)->codes()]);
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        Gate::authorize('update', $article);
        DB::transaction(fn () => $this->save($article, $request));
        $this->audit('article.updated', $article);

        return back()->with('status', __('admin.article_saved'));
    }

    public function archive(Article $article): RedirectResponse
    {
        Gate::authorize('update', $article);
        $article->update(['archived_at' => now(), 'updated_by' => auth()->id()]);
        $this->audit('article.archived', $article);

        return back()->with('status', __('admin.article_archived'));
    }

    public function destroy(Article $article): RedirectResponse
    {
        Gate::authorize('delete', $article);
        $id = $article->id;
        $article->delete();
        AdminAudit::create(['user_id' => auth()->id(), 'action' => 'article.deleted', 'auditable_type' => Article::class, 'auditable_id' => $id, 'ip_address' => request()->ip()]);

        return redirect()->route('admin.articles.index')->with('status', __('admin.article_deleted'));
    }

    public function preview(Article $article, string $locale, MarkdownRenderer $markdown): mixed
    {
        Gate::authorize('view', $article);
        abort_unless(app(SupportedLocales::class)->supports($locale), 404);
        $translation = ArticleTranslation::query()->where('article_id', $article->id)->where('locale', $locale)->firstOrFail();
        app()->setLocale($locale);
        $data = ['slug' => $article->slug, 'title' => $translation->title, 'description' => $translation->description, 'date' => optional($translation->published_on)->format('Y-m-d') ?? now()->format('Y-m-d'), 'body' => $translation->body_markdown, 'html' => $markdown->render($translation->body_markdown)];

        return response()->view('pages.article', ['article' => $data, 'title' => $translation->seo_title, 'description' => $translation->seo_description, 'indexable' => false])->header('X-Robots-Tag', 'noindex, nofollow, noarchive')->header('Cache-Control', 'private, no-store');
    }

    private function save(Article $article, Request $request): Article
    {
        $data = $request->validate(['slug' => ['required', 'alpha_dash', 'max:120', 'unique:articles,slug,'.($article->id ?? 'NULL')], 'translations' => ['nullable', 'array'], 'translations.*.title' => ['nullable', 'string', 'max:255'], 'translations.*.description' => ['nullable', 'string', 'max:5000'], 'translations.*.body_markdown' => ['nullable', 'string', 'max:100000'], 'translations.*.seo_title' => ['nullable', 'string', 'max:255'], 'translations.*.seo_description' => ['nullable', 'string', 'max:5000'], 'translations.*.cover_alt' => ['nullable', 'string', 'max:255'], 'translations.*.published_on' => ['nullable', 'date_format:Y-m-d'], 'translations.*.editorial_status' => ['nullable', 'in:draft,published,archived']]);
        $locales = app(SupportedLocales::class)->codes();
        foreach (array_keys($data['translations'] ?? []) as $locale) {
            if (! in_array($locale, $locales, true)) {
                throw ValidationException::withMessages(["translations.$locale" => __('admin.invalid_locale')]);
            }
        }
        $article->fill(['slug' => $data['slug'], 'updated_by' => auth()->id(), 'created_by' => $article->created_by ?: auth()->id()])->save();
        foreach ($data['translations'] ?? [] as $locale => $translation) {
            $status = $translation['editorial_status'] ?? 'draft';
            $provided = array_filter($translation, fn ($value) => $value !== null && $value !== '');
            if ($provided === []) {
                continue;
            }
            foreach (['title', 'description', 'body_markdown', 'seo_title', 'seo_description'] as $required) {
                if (trim((string) ($translation[$required] ?? '')) === '') {
                    throw ValidationException::withMessages(["translations.$locale.$required" => __('admin.article_required')]);
                }
            }
            if ($status === 'published' && empty($translation['published_on'])) {
                $translation['published_on'] = now()->format('Y-m-d');
            }
            $existing = $article->translations()->where('locale', $locale)->first();
            $publishedAt = $existing?->getAttribute('published_at') ?? now();
            $article->translations()->updateOrCreate(['locale' => $locale], [...$translation, 'published_at' => $status === 'published' ? $publishedAt : null]);
        }

        return $article;
    }

    private function audit(string $action, Article $article): void
    {
        AdminAudit::create(['user_id' => auth()->id(), 'action' => $action, 'auditable_type' => Article::class, 'auditable_id' => $article->id, 'ip_address' => request()->ip()]);
    }
}
