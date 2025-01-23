<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Drafolin\FilamentInverseShield\Console;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class InverseShield extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "inverse-shield {--panel=admin} {--seeder=ShieldSeeder}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Command description";

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $roles = "";
        Role::query()
            ->where("name", "<>", "admin")
            ->get()
            ->each(function (Role $role) use (&$roles) {
                $roles .= <<<PHP
        \$role = new Role;
        \$role->name = '{$role->name}';
        \$role->display_name = '{$role->display_name}';
        \$role->guard_name = '{$role->guard_name}';
        \$role->save();\n
PHP;

                $role
                    ->permissions()
                    ->each(function (Permission $permission) use (&$roles) {
                        $roles .= <<<PHP
            \$role->permissions()->attach(Permission::findByName("$permission->name"));\n
PHP;
                    });
                $roles .= <<<PHP
        \$this->command->info("Role $role->name created.");\n\n
PHP;
                $this->info("Role $role->name dumped.");
            });

        $this->info("Dumped roles and permissions to seeder");

        $panel = $this->option("panel");
        $seeder = $this->option("seeder");
        $buffer = file(
            __DIR__ .
                DIRECTORY_SEPARATOR .
                "stubs" .
                DIRECTORY_SEPARATOR .
                "seed.stub"
        );
        $result = implode(PHP_EOL, $buffer);
        $result = str_replace("{{roles}}", $roles, $result);
        $result = str_replace("{{class}}", $seeder, $result);
        $result = str_replace("{{panel}}", $panel, $result);
        if (
            file_exists(database_path("seeders/$seeder.php")) &&
            !$this->confirm(
                "File database/seeders/$seeder.php already exists. Do you want to overwrite it?"
            )
        ) {
            return;
        }
        file_put_contents(database_path("seeders/$seeder.php"), $result);

        $this->info("File written to database/seeders/$seeder.php");
    }
}
