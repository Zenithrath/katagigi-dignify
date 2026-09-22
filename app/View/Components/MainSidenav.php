<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MainSidenav extends Component
{
    public function __construct()
    {
    }

    public function render(): View
    {
        return view('components.main-sidenav');
    }
}
