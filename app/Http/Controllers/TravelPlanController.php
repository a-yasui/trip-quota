<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TravelPlanController extends Controller
{
    /**
     * Display a listing of the travel plans.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // In a real application, we would fetch data from the database
        // For now, we're just returning the view
        return view('travel-plans.index');
    }

    /**
     * Show the form for creating a new travel plan.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // This would be a form to create a new travel plan
        return view('welcome'); // Placeholder
    }
}
