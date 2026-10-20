<?php

namespace App\Http\Controllers;

/** sitemap.xml y robots.txt dinámicos con el dominio real (checklist 9). */
class SeoController extends Controller
{
    public function sitemap()
    {
        $routes = ['home', 'privacy', 'terms', 'login'];
        if (config('kendex.registration_enabled')) {
            $routes[] = 'register';
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($routes as $name) {
            $xml .= '  <url><loc>' . e(route($name)) . '</loc><changefreq>monthly</changefreq></url>' . "\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function robots()
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /panel',
            'Disallow: /gastos',
            'Disallow: /admin',
            'Disallow: /perfil',
            'Disallow: /reportes',
            'Disallow: /comprobantes',
            '',
            'Sitemap: ' . route('sitemap'),
        ];

        return response(implode("\n", $lines) . "\n", 200, ['Content-Type' => 'text/plain']);
    }
}
