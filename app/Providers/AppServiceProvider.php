<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // @idr($amount)        → "Rp 25.000"     (no decimals, dot thousands)
        // @idrLong($amount)    → "Rp 25.000,50"  (with cents — rarely used in IDR)
        Blade::directive('idr', function ($expression) {
            return "<?php echo 'Rp ' . number_format((float) ($expression), 0, ',', '.'); ?>";
        });

        Blade::directive('idrLong', function ($expression) {
            return "<?php echo 'Rp ' . number_format((float) ($expression), 2, ',', '.'); ?>";
        });
    }
}
