<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    /**
     * Display the activities page with department data
     */
    public function index(): View
    {
        // Get all active departments for the filter dropdown
        $departments = Department::active()->orderBy('department_name')->get();
        
        return view('activities', compact('departments'));
    }
}
