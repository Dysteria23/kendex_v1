<?php

namespace App\Providers;

use App\Support\Dates;
use App\Support\Money;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Checklist 4: HTTPS obligatorio en producción (RNF-03).
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        Password::defaults(fn () => Password::min(10)->letters()->numbers());

        Blade::directive('money', fn ($expr) => "<?php echo e(\\" . Money::class . "::format($expr)); ?>");
        Blade::directive('dt', fn ($expr) => "<?php echo e(\\" . Dates::class . "::display($expr)); ?>");
        Blade::directive('d', fn ($expr) => "<?php echo e(\\" . Dates::class . "::date($expr)); ?>");
    }
}
