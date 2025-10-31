<?php

namespace App\Filament\Widgets;

use Filament\Widgets\AccountWidget as BaseAccountWidget;
use Illuminate\Contracts\View\View;

class AccountWidget extends BaseAccountWidget
{
    public function render(): View
    {
        return view('filament.widgets.account', [
            'user' => auth()->user()
        ]);
    }
}
