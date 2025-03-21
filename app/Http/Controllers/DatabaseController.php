<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Session;

class DatabaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('user.is.admin');
        $this->middleware('is.demo', ['except' => ['index']]);
    }

    public function index()
    {
        return view('settings.database');
    }

    public function truncate(Request $request)
    {
        try {
            $options = [
                '--seed' => true,  // Toujours réinsérer les données initiales
                '--preserve-admin' => true // Toujours préserver l'utilisateur administrateur
            ];

            Artisan::call('db:truncate', $options);
            $output = Artisan::output();

            if (strpos($output, 'erreur') !== false) {
                Session::flash('flash_message_warning', __('Une erreur est survenue lors de la réinitialisation'));
                return redirect()->back()->withErrors(['error' => $output]);
            }

            Session::flash('flash_message', __('Base de données réinitialisée avec succès'));
            return redirect('/');

        } catch (\Exception $e) {
            Session::flash('flash_message_warning', __('Une erreur est survenue: ') . $e->getMessage());
            return redirect()->back();
        }
    }
}
