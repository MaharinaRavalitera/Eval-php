<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TruncateDatabase extends Command
{
    protected $signature = 'db:truncate {--seed : Seed the database after truncating} {--preserve-admin : Preserve the admin user}';
    protected $description = 'Truncate all tables in the database and optionally seed them';

    public function handle()
    {
        if (!app()->environment('production') || 
            $this->confirm('ATTENTION: Vous êtes sur le point de vider toutes les tables en PRODUCTION. Voulez-vous continuer?')) {
            
            try {
                DB::beginTransaction();

                // Sauvegarder l'utilisateur admin si l'option est activée
                $adminUser = null;
                if ($this->option('preserve-admin') || true) { // Toujours préserver l'admin
                    $this->info('Sauvegarde de l\'utilisateur administrateur...');
                    $adminUser = DB::table('users')->where('email', 'admin@admin.com')->first();
                    if (!$adminUser) {
                        $this->warn('Aucun utilisateur administrateur trouvé à préserver.');
                    }
                }

                // Désactiver les contraintes de clés étrangères
                $this->info('Désactivation des contraintes de clés étrangères...');
                DB::statement('SET FOREIGN_KEY_CHECKS = 0');

                // Récupérer toutes les tables
                $tables = DB::select('SHOW TABLES');
                $dbName = DB::getDatabaseName();
                $tableKey = 'Tables_in_' . $dbName;

                // Tronquer chaque table
                foreach ($tables as $table) {
                    $tableName = $table->$tableKey;
                    $this->info("Troncature de la table: {$tableName}");
                    DB::table($tableName)->truncate();
                }

                // Réactiver les contraintes de clés étrangères
                $this->info('Réactivation des contraintes de clés étrangères...');
                DB::statement('SET FOREIGN_KEY_CHECKS = 1');

                // Seeding si l'option est spécifiée
                if ($this->option('seed')) {
                    $this->info('Insertion des données initiales...');
                    $this->call('db:seed', ['--force' => true]);
                }

                // Restaurer l'utilisateur admin si sauvegardé
                if ($adminUser) {
                    $this->info('Restauration de l\'utilisateur administrateur...');
                    
                    // Vérifier si l'utilisateur existe déjà après le seeding
                    $existingAdmin = DB::table('users')->where('email', $adminUser->email)->first();
                    
                    if ($existingAdmin) {
                        $this->info('L\'utilisateur administrateur existe déjà après le seeding.');
                    } else {
                        // Insérer l'utilisateur admin
                        DB::table('users')->insert((array)$adminUser);
                        
                        // Assurer que l'utilisateur a le rôle d'administrateur
                        $adminRoleId = DB::table('roles')->where('name', 'administrator')->value('id');
                        if ($adminRoleId) {
                            DB::table('role_user')->insert([
                                'user_id' => $adminUser->id,
                                'role_id' => $adminRoleId
                            ]);
                        }
                    }
                }

                DB::commit();
                $this->info('Base de données réinitialisée avec succès!');

            } catch (\Exception $e) {
                DB::rollBack();
                $this->error('Une erreur est survenue: ' . $e->getMessage());
                return 1;
            }
        } else {
            $this->info('Opération annulée.');
        }

        return 0;
    }
}
