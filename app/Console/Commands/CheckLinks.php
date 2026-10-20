<?php

namespace App\Console\Commands;

use DOMDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CheckLinks extends Command
{
    protected $signature = 'kendex:check-links {--base= : URL base (por defecto APP_URL)}';
    protected $description = 'enlace interno de las páginas públicas esté roto (checklist 16)';

    public function handle(): int
    {
        $base = rtrim($this->option('base') ?: config('app.url'), '/');
        $host = parse_url($base, PHP_URL_HOST);

        $queue = [[$base . '/', '(inicio)']];
        $seen = [];
        $broken = [];

        while ($queue) {
            [$url, $from] = array_shift($queue);
            $url = strtok($url, '#');

            if (isset($seen[$url])) {
                continue;
            }
            $seen[$url] = true;

            $response = Http::timeout(10)->withoutRedirecting()->get($url);
            $status = $response->status();

            if ($status >= 400 || $status === 0) {
                $broken[] = [$url, $status, $from];
                continue;
            }

            if ($status >= 300 || ! str_contains((string) $response->header('Content-Type'), 'text/html')) {
                continue;
            }

            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML($response->body());
            libxml_clear_errors();

            foreach ($dom->getElementsByTagName('a') as $a) {
                $href = trim($a->getAttribute('href'));
                if ($href === '' || preg_match('/^(mailto:|tel:|javascript:|#)/i', $href)) {
                    continue;
                }

                $absolute = str_starts_with($href, '/') ? $base . $href : $href;
                if (parse_url($absolute, PHP_URL_HOST) !== $host) {
                    continue; // enlaces externos no se rastrean
                }

                $queue[] = [$absolute, $url];
            }
        }

        $this->info('Páginas revisadas: ' . count($seen));

        if ($broken) {
            $this->table(['URL', 'Estado', 'Enlazada desde'], $broken);

            return self::FAILURE;
        }

        $this->info('Sin enlaces rotos.');

        return self::SUCCESS;
    }
}
