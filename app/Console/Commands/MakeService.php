<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeService extends Command
{
    protected $signature = 'make:service {name : The name of the service class} {--force : Overwrite if file exists}';
    protected $description = 'Create a new service class in app/Services';

    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $path = app_path('Services/' . str_replace('\\', '/', $name) . '.php');
        $namespace = 'App\\Services' . '\\' . trim(Str::replaceLast('.php', '', str_replace('/', '\\', Str::beforeLast($name, '/'))), '\\');

        // Ensure directory exists
        File::ensureDirectoryExists(dirname($path));

        if (File::exists($path) && !$this->option('force')) {
            $this->error("Service already exists at: {$path}");
            return;
        }

        $className = class_basename($name);

        $stub = <<<PHP
<?php

namespace {$namespace};

class {$className}
{
    //
}

PHP;

        File::put($path, $stub);
        $this->info("Service class [{$className}] created at: {$path}");
    }
}
