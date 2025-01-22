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
    protected $signature = "inverse-shield {--panel=admin}";

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
        $result = <<<PHP
<?php
/**
 * @noinspection PhpMultipleClassDeclarationsInspection
 * @noinspection DuplicatedCode
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ShieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \$this->command->warn(<<<TXT
        Careful: This will remove all existing user-roles relations.
        It will also remove all existing permissions and roles, restoring them to the moment of the last InverseShield execution.
        Make sure you have a backup of your database before running this command.
        TXT);

        if(!\$this->command->confirm("Do you wish to continue?")) {
            return;
        }

        Role::all()->each(function (Role \$role) {
            \$role->permissions()->detach();
            \$role->delete();
        });
        \$this->command->info('Successfully deleted all roles and permissions');

        Artisan::call('shield:generate --all --ignore-existing-policies -n --panel=$panel');
        \$this->command->info('Successfully generated base permissions');

$roles

    }
}
PHP;
        if (
            file_exists(database_path("seeders/ShieldSeeder.php")) &&
            !$this->confirm(
                "File database/seeders/ShieldSeeder.php already exists. Do you want to overwrite it?"
            )
        ) {
            return;
        }
        file_put_contents(database_path("seeders/ShieldSeeder.php"), $result);

        $this->info("File written to database/seeders/ShieldSeeder.php");
    }
}
