<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class DummyDataController extends Controller
{
    /**
     * DummyDataController constructor.
     */
    public function __construct()
    {
        $this->middleware('user.is.admin');
        $this->middleware('is.demo', ['except' => ['index']]);
    }

    /**
     * Show the form for generating test data
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('dummy_data.index');
    }

    /**
     * Generate test data
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generateTestData()
    {
        try {
            // Générer les données de démonstration
            Artisan::call('db:seed --class=DummyDatabaseSeeder');

            return redirect()->back()->with('flash_message', 'Demo data generated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('flash_message_warning', 'Error generating demo data: ' . $e->getMessage());
        }
    }
}
