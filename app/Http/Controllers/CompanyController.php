<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::all();

        return view('companies.index', compact('companies'));
    }

    public function create()
    {
        return view('companies.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'subdistrict' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'zipcode' => 'nullable|string|max:20',
            'google_maps_url' => 'nullable|url|max:2048',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'nullable|string|in:active,pending',
        ]);

        Company::create($validated);

        return redirect()->route('officer.companies.index')->with('success', 'เพิ่มข้อมูลสถานประกอบการเรียบร้อยแล้ว');
    }

    public function show(Company $company)
    {
        return view('companies.show', compact('company'));
    }

    public function edit(Company $company)
    {
        return view('companies.form', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'subdistrict' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'zipcode' => 'nullable|string|max:20',
            'google_maps_url' => 'nullable|url|max:2048',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'nullable|string|in:active,pending',
        ]);

        $company->update($validated);

        return redirect()->route('officer.companies.index')->with('success', 'อัปเดตข้อมูลสถานประกอบการเรียบร้อยแล้ว');
    }

    public function destroy(Company $company)
    {
        $company->delete();

        return redirect()->route('officer.companies.index')->with('success', 'ลบข้อมูลสถานประกอบการเรียบร้อยแล้ว');
    }
}
