<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?string $ogImage = null,
        public string $ogType = 'website',
        public ?string $canonical = null,
        public ?string $jsonLd = null,
    ) {
    }

    public function render(): View
    {
        return view('layouts.app');
    }
}
