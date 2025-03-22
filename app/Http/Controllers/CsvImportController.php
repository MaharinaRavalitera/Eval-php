<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CsvImport\CsvImportService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CsvImportController extends Controller
{
    protected $csvImportService;

    /**
     * CsvImportController constructor.
     */
    public function __construct(CsvImportService $csvImportService)
    {
        $this->middleware('user.is.admin');
        $this->csvImportService = $csvImportService;
    }

    /**
     * Show the form for importing CSV data
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        // Récupérer la liste des tables disponibles dans la base de données
        $tables = $this->getImportableTables();
        
        return view('csv_import.index', compact('tables'));
    }

    /**
     * Upload CSV file and prepare for import
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            'table' => 'required|string',
            'delimiter' => 'required|string',
            'has_header' => 'boolean'
        ]);

        try {
            // Vérifier que la table existe
            if (!Schema::hasTable($request->table)) {
                return redirect()->back()->with('flash_message_warning', 'La table sélectionnée n\'existe pas');
            }

            // S'assurer que le répertoire de stockage existe
            $storagePath = storage_path('app/csv_imports');
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
            }

            // Stocker le fichier temporairement
            $file = $request->file('csv_file');
            $filename = $file->getClientOriginalName();
            $path = $file->storeAs('csv_imports', $filename);
            $fullPath = storage_path('app/' . $path);
            
            // Vérifier que le fichier a bien été stocké
            if (!file_exists($fullPath)) {
                // Si le stockage a échoué, copier directement le fichier
                copy($file->getRealPath(), $fullPath);
            }
            
            // Traiter le délimiteur spécial pour la tabulation
            $delimiter = $request->delimiter;
            if ($delimiter === '\t') {
                $delimiter = "\t";
            }

            // Lire le fichier CSV pour obtenir les en-têtes
            $hasHeader = $request->has('has_header');
            $headers = [];
            
            if (($handle = fopen($fullPath, 'r')) !== false) {
                // Lire la première ligne pour obtenir les en-têtes
                if (($data = fgetcsv($handle, 1000, $delimiter)) !== false) {
                    if ($hasHeader) {
                        $headers = $data;
                    } else {
                        // Si pas d'en-tête, générer des noms de colonnes
                        for ($i = 0; $i < count($data); $i++) {
                            $headers[] = 'column_' . ($i + 1);
                        }
                    }
                }
                fclose($handle);
            } else {
                return redirect()->back()->with('flash_message_warning', 'Impossible d\'ouvrir le fichier CSV. Vérifiez les permissions.');
            }

            // Obtenir les colonnes de la table cible
            $tableColumns = Schema::getColumnListing($request->table);

            // Stocker les informations en session pour l'étape suivante
            session([
                'csv_import' => [
                    'table' => $request->table,
                    'file_path' => $fullPath,
                    'delimiter' => $delimiter,
                    'has_header' => $hasHeader,
                    'headers' => $headers,
                    'table_columns' => $tableColumns
                ]
            ]);

            return redirect()->route('csv_import.map');
        } catch (\Exception $e) {
            // Log l'erreur pour le débogage
            \Log::error('CSV Import Error: ' . $e->getMessage());
            return redirect()->back()->with('flash_message_warning', 'Erreur lors du téléchargement: ' . $e->getMessage());
        }
    }

    /**
     * Show mapping interface for CSV columns to table fields
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function map(Request $request)
    {
        $importData = session('csv_import');
        
        if (!$importData) {
            return redirect()->route('csv_import.index')->with('flash_message_warning', 'Les informations d\'importation ont expiré. Veuillez recommencer.');
        }
        
        // Lire les premières lignes pour l'aperçu
        $preview = [];
        $filePath = $importData['file_path'];
        $delimiter = $importData['delimiter'];
        $hasHeader = $importData['has_header'];
        
        if (($handle = fopen($filePath, 'r')) !== false) {
            // Lire les 5 premières lignes pour l'aperçu
            $rowCount = 0;
            while (($data = fgetcsv($handle, 1000, $delimiter)) !== false && $rowCount < 5) {
                $preview[] = $data;
                $rowCount++;
            }
            fclose($handle);
        }
        
        return view('csv_import.map', [
            'preview' => $preview,
            'has_header' => $hasHeader,
            'csv_headers' => $importData['headers'],
            'table_columns' => $importData['table_columns'],
            'table' => $importData['table']
        ]);
    }

    /**
     * Process the CSV import with column mapping
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function process(Request $request)
    {
        $request->validate([
            'column_mapping' => 'required|array'
        ]);

        try {
            // Récupérer les informations de la session
            $importData = $request->session()->get('csv_import');
            
            if (!$importData) {
                return redirect()->route('csv_import.index')->with('flash_message_warning', 'Les informations d\'importation ont expiré. Veuillez recommencer.');
            }
            
            $filePath = $importData['file_path'];
            $tableName = $importData['table'];
            $delimiter = $importData['delimiter'];
            
            // Traiter le délimiteur spécial pour la tabulation
            if ($delimiter === '\t') {
                $delimiter = "\t";
            }
            
            $hasHeader = $importData['has_header'];
            
            // Préparer les tableaux de colonnes pour l'importation
            $csvColumns = [];
            $tableColumns = [];
            
            foreach ($request->column_mapping as $csvIndex => $tableColumn) {
                if (!empty($tableColumn)) {
                    $csvColumns[] = $csvIndex;
                    $tableColumns[] = $tableColumn;
                }
            }
            
            // Effectuer l'importation
            $result = $this->csvImportService->importCsv(
                $tableName,
                $filePath,
                $csvColumns,
                $tableColumns,
                $delimiter,
                $hasHeader
            );
            
            // Supprimer le fichier temporaire
            unlink($filePath);
            $request->session()->forget('csv_import');
            
            if ($result['success']) {
                return redirect()->route('csv_import.index')->with('flash_message', $result['message']);
            } else {
                return redirect()->route('csv_import.index')->with('flash_message_warning', $result['message']);
            }
            
        } catch (\Exception $e) {
            return redirect()->route('csv_import.index')->with('flash_message_warning', 'Erreur lors de l\'importation: ' . $e->getMessage());
        }
    }

    /**
     * Get list of importable tables from the database
     * 
     * @return array
     */
    protected function getImportableTables()
    {
        // Exclure certaines tables système
        $excludedTables = [
            'migrations', 'password_resets', 'failed_jobs', 'personal_access_tokens',
            'jobs', 'sessions', 'cache', 'oauth_access_tokens', 'oauth_auth_codes',
            'oauth_clients', 'oauth_personal_access_clients', 'oauth_refresh_tokens'
        ];
        
        $tables = [];
        
        try {
            // Méthode compatible avec MySQL
            $allTables = \DB::select('SHOW TABLES');
            
            foreach ($allTables as $table) {
                // Récupérer le nom de la table depuis l'objet stdClass
                $tableName = reset($table);
                
                if (!in_array($tableName, $excludedTables)) {
                    $tables[] = $tableName;
                }
            }
        } catch (\Exception $e) {
            // En cas d'erreur, retourner quelques tables communes
            $tables = ['clients', 'users', 'tasks', 'projects', 'leads'];
        }
        
        return $tables;
    }
}
