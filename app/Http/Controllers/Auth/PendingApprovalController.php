<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PendingApprovalController extends Controller
{
    /**
     * Display the pending approval view.
     */
    public function __invoke(): View
    {
        return view('auth.pending-approval');
    }
}