<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    /**
     * Display the dashboard page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // In a real application, we would fetch data from the database
        // For now, we're just returning the view
        return view('dashboard');
    }
}
