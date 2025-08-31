<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {



        return Inertia("dashboard/Index");
    }


    public function chat()
    {
        return Inertia("dashboard/Index");
    }
}
