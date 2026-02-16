<?php

namespace App\Console\Commands;

use App\Models\User;
use Database\Seeders\DocumentStateSeeder;
use Database\Seeders\DocumentTypeSeeder;
use Database\Seeders\ProviderTypeSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Process;
use Spatie\Permission\Models\Role;

class AppInstallCommand extends Command
{
    protected $signature = 'app:install';

    protected $description = 'Instala la aplicación: migra BD, genera datos base, configura Shield y crea admin';

    public function handle(): int
    {
        $this->components->info('Instalando aplicación...');

        // 1. Migraciones
        $this->components->task('Ejecutando migraciones', function () {
            $this->callSilently('migrate', ['--force' => true]);
        });

        // 2. Seeders de datos base (idempotentes)
        $this->components->task('Sembrando estados de documento', function () {
            $this->callSilently('db:seed', ['--class' => DocumentStateSeeder::class, '--force' => true]);
        });

        $this->components->task('Sembrando tipos de documento', function () {
            $this->callSilently('db:seed', ['--class' => DocumentTypeSeeder::class, '--force' => true]);
        });

        $this->components->task('Sembrando tipos de proveedor', function () {
            $this->callSilently('db:seed', ['--class' => ProviderTypeSeeder::class, '--force' => true]);
        });

        // 3. Shield: generar permisos y políticas
        $this->components->task('Generando permisos de Shield', function () {
            Process::run('php artisan shield:generate --all --panel=admin --no-interaction');
        });

        // 4. Crear usuario admin
        $this->components->task('Creando usuario administrador', function () {
            $admin = User::firstOrCreate(
                ['email' => 'admin@grupocosteno.com'],
                [
                    'name' => 'Administrador',
                    'password' => Hash::make('admin1234'),
                    'active' => true,
                ]
            );

            $superAdmin = Role::firstOrCreate(
                ['name' => 'super_admin', 'guard_name' => 'web']
            );

            if (! $admin->hasRole($superAdmin)) {
                $admin->assignRole($superAdmin);
            }
        });

        // 5. Limpiar caches
        $this->components->task('Limpiando caches', function () {
            $this->callSilently('optimize:clear');
        });

        $this->newLine();
        $this->components->info('Instalación completada.');
        $this->components->bulletList([
            'Admin: admin@grupocosteno.com / admin1234',
            'Panel: /admin',
            'Recuerda cambiar la contraseña del administrador.',
        ]);

        return self::SUCCESS;
    }
}
