<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PortfolioController extends Controller
{
    public function home(): View
    {
        return view('pages.home', ['projects' => collect(config('portfolio.projects'))->where('featured', true)->take(3)]);
    }

    public function about(): View
    {
        return view('pages.about');
    }

    public function projects(): View
    {
        return view('pages.projects', ['projects' => config('portfolio.projects')]);
    }

    public function project(string $slug): View
    {
        $project = collect(config('portfolio.projects'))->firstWhere('slug', $slug);
        abort_if($project === null, 404);

        return view('pages.project', [
            'project' => $project,
            'title' => $project['title'].' — Project case study',
            'description' => $project['summary'],
        ]);
    }

    public function openSource(): View
    {
        return view('pages.open-source', ['contributions' => config('portfolio.open_source')]);
    }

    public function articles(): View
    {
        return view('pages.articles', ['articles' => $this->publishedArticles()]);
    }

    public function article(string $slug): View
    {
        $article = collect($this->publishedArticles())->firstWhere('slug', $slug);
        abort_if($article === null, 404);
        $article['html'] = Str::markdown($article['body'], [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return view('pages.article', compact('article'));
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function sitemap(): Response
    {
        $urls = collect(['/', '/about', '/projects', '/open-source', '/writing', '/contact'])
            ->concat(collect(config('portfolio.projects'))->map(fn (array $project) => '/projects/'.$project['slug']))
            ->concat(collect($this->publishedArticles())->map(fn (array $article) => '/writing/'.$article['slug']));

        return response()->view('sitemap', ['urls' => $urls])->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        return response("User-agent: *\nAllow: /\nSitemap: ".url('/sitemap.xml')."\n", 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    /** @return list<array{slug:string,title:string,description:string,date:string,body:string}> */
    private function publishedArticles(): array
    {
        return collect(File::files(resource_path('content/articles')))
            ->filter(fn ($file) => $file->getExtension() === 'md')
            ->map(function ($file): array {
                $content = $file->getContents();
                preg_match('/\A---\R(.*?)\R---\R(.*)\z/s', $content, $parts);
                $metadata = [];
                foreach (preg_split('/\R/', $parts[1] ?? '') as $line) {
                    if (preg_match('/^(slug|title|description|date|status):\s*(.+)$/', $line, $match)) {
                        $metadata[$match[1]] = trim($match[2], " \t\"'");
                    }
                }
                $date = $metadata['date'] ?? '';
                $parsedDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
                $validDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1
                    && $parsedDate instanceof \DateTimeImmutable
                    && $parsedDate->format('Y-m-d') === $date;

                if (($metadata['status'] ?? '') !== 'published'
                    || preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $metadata['slug'] ?? '') !== 1
                    || ! $validDate) {
                    return [];
                }

                return [...$metadata, 'body' => trim($parts[2] ?? '')];
            })
            ->filter(fn (array $article) => isset($article['slug'], $article['title'], $article['description'], $article['date'], $article['body']))
            ->sortByDesc('date')->values()->all();
    }
}
