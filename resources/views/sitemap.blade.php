<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($urls as $path)<url><loc>{{ url($path) }}</loc></url>
@endforeach</urlset>
