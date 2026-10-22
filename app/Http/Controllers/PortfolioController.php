<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Support\LocalizedArticleRepository;
use App\Support\LocalizedPortfolio;
use App\Support\PortfolioContact;
use App\Support\SupportedLocales;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PortfolioController extends Controller
{
    public function home(string $locale): View
    {
        $contact = app(PortfolioContact::class);

        return view('pages.home', [
            'projects' => collect(app(LocalizedPortfolio::class)->projects($locale))->where('featured', true)->take(3),
            'contributions' => collect(app(LocalizedPortfolio::class)->contributions($locale))->where('state', 'merged')->take(3),
            'resumeAvailable' => $contact->resumePath() !== null,
            'title' => __('ui.seo.home_title'),
            'description' => __('ui.seo.home_description'),
        ]);
    }

    public function about(string $locale): View
    {
        return view('pages.about', [
            'title' => __('ui.seo.about_title'),
            'description' => __('ui.about.intro'),
        ]);
    }

    public function projects(string $locale): View
    {
        return view('pages.projects', [
            'projects' => app(LocalizedPortfolio::class)->projects($locale),
            'title' => __('ui.seo.projects_title'),
            'description' => __('ui.projects.intro'),
        ]);
    }

    public function project(string $locale, string $slug): View
    {
        $project = app(LocalizedPortfolio::class)->project($locale, $slug);
        abort_if($project === null, 404);

        return view('pages.project', [
            'project' => $project,
            'title' => $project['title'].__('ui.project.title_suffix'),
            'description' => $project['summary'],
        ]);
    }

    public function openSource(string $locale): View
    {
        return view('pages.open-source', [
            'contributions' => app(LocalizedPortfolio::class)->contributions($locale),
            'title' => __('ui.seo.open_source_title'),
            'description' => __('ui.open_source.intro'),
        ]);
    }

    public function articles(string $locale): View
    {
        return view('pages.articles', [
            'articles' => app(LocalizedArticleRepository::class)->published($locale),
            'title' => __('ui.seo.writing_title'),
            'description' => __('ui.writing.intro'),
        ]);
    }

    public function article(string $locale, string $slug): View
    {
        $article = app(LocalizedArticleRepository::class)->find($locale, $slug);
        abort_if($article === null, 404);
        $article['html'] = Str::markdown($article['body'], [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return view('pages.article', [
            'article' => $article,
            'title' => $article['title'].__('ui.article.title_suffix'),
            'description' => $article['description'],
        ]);
    }

    public function contact(string $locale): View
    {
        $contact = app(PortfolioContact::class);

        return view('pages.contact', [
            'email' => $contact->email(),
            'profileLinks' => $contact->profileLinks(),
            'resumeAvailable' => $contact->resumePath() !== null,
            'title' => __('ui.seo.contact_title'),
            'description' => __('ui.contact.intro'),
        ]);
    }

    public function downloadResume(PortfolioContact $contact): BinaryFileResponse
    {
        $resumePath = $contact->resumePath();
        abort_if($resumePath === null, 404);

        return response()->download(Storage::disk('local')->path($resumePath), basename($resumePath), [
            'Content-Type' => 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function sitemap(): Response
    {
        $portfolio = app(LocalizedPortfolio::class);
        $articles = app(LocalizedArticleRepository::class);
        $localeRegistry = app(SupportedLocales::class);
        $locales = $localeRegistry->codes();
        $groups = [];

        $addGroup = static function (string $name, array $parameters, array $availableLocales) use (&$groups, $localeRegistry): void {
            $alternates = [];
            foreach ($availableLocales as $locale) {
                $alternates[$locale] = route($name, ['locale' => $locale, ...$parameters]);
            }
            if (isset($alternates[$localeRegistry->default()])) {
                $alternates['x-default'] = $alternates[$localeRegistry->default()];
            }
            foreach (array_diff_key($alternates, ['x-default' => true]) as $url) {
                $groups[] = ['loc' => $url, 'alternates' => $alternates];
            }
        };

        foreach (['localized.home', 'localized.about', 'localized.projects.index', 'localized.open-source', 'localized.articles.index', 'localized.contact'] as $name) {
            $addGroup($name, [], $locales);
        }

        foreach (Project::query()->pluck('slug') as $slug) {
            $available = array_values(array_filter($locales, fn (string $locale) => $portfolio->project($locale, $slug) !== null));
            if ($available !== []) {
                $addGroup('localized.projects.show', ['slug' => $slug], $available);
            }
        }

        $articleSlugs = collect($locales)
            ->flatMap(fn (string $locale) => array_column($articles->published($locale), 'slug'))
            ->unique()->values();

        foreach ($articleSlugs as $slug) {
            $available = array_values(array_filter($locales, fn (string $locale) => $articles->find($locale, $slug) !== null));
            if ($available !== []) {
                $addGroup('localized.articles.show', ['slug' => $slug], $available);
            }
        }

        return response()->view('sitemap', ['entries' => $groups])->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        return response("User-agent: *\nAllow: /\nSitemap: ".url('/sitemap.xml')."\n", 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
