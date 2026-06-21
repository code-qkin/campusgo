<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Campus;

class CampusController extends Controller
{
    public function index()
    {
        $campuses = Campus::where('is_active', true)->get();
        return response()->json($campuses);
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:campuses,slug',
            'email_domain' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $campus = Campus::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'email_domain' => $request->email_domain,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json($campus, 201);
    }
}
