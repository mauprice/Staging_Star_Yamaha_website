<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use Illuminate\Contracts\View\View;

class QuotePrintController extends Controller
{
    public function show(Quote $quote): View
    {
        abort_unless(auth()->user()?->hasAnyRole(['Admin', 'Manager', 'Sales']) ?? false, 404);

        $quote->load('lines');

        return view('quotes.print', ['quote' => $quote]);
    }
}
