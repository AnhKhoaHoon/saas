<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class BillingController extends Controller
{
    public function pricing(): View
    {
        return view('billing.pricing');
    }

    public function dashboard(Request $request): View
    {
        $subscription = $request->user()->subscriptions()->latest()->first();
        
        return view('billing.dashboard', [
            'subscription' => $subscription
        ]);
    }
}
