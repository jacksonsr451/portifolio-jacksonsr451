<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LegacyRedirectController extends Controller
{
    public function home(Request $request): RedirectResponse
    {
        return $this->redirect($request, 'localized.home');
    }

    public function about(Request $request): RedirectResponse
    {
        return $this->redirect($request, 'localized.about');
    }

    public function projects(Request $request): RedirectResponse
    {
        return $this->redirect($request, 'localized.projects.index');
    }

    public function project(Request $request, string $slug): RedirectResponse
    {
        return $this->redirect($request, 'localized.projects.show', ['slug' => $slug]);
    }

    public function openSource(Request $request): RedirectResponse
    {
        return $this->redirect($request, 'localized.open-source');
    }

    public function articles(Request $request): RedirectResponse
    {
        return $this->redirect($request, 'localized.articles.index');
    }

    public function article(Request $request, string $slug): RedirectResponse
    {
        return $this->redirect($request, 'localized.articles.show', ['slug' => $slug]);
    }

    public function contact(Request $request): RedirectResponse
    {
        return $this->redirect($request, 'localized.contact');
    }

    /** @param array<string, string> $parameters */
    private function redirect(Request $request, string $routeName, array $parameters = []): RedirectResponse
    {
        $url = route($routeName, ['locale' => config('i18n.default'), ...$parameters]);
        $query = http_build_query($request->query());

        return redirect()->to($query === '' ? $url : $url.'?'.$query, 301);
    }
}
