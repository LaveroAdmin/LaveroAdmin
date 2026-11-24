<?php

namespace LaveroAdmin\Admin\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use LaveroAdmin\Admin\Admin;

class DiagnosticsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:diagnose {--fix : Attempt to automatically resolve detected issues}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check system health, permissions, assets, and database configuration for Lavero Admin';

    /**
     * Track issues found.
     *
     * @var array
     */
    protected $issues = [];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->line(AdminCommand::$logo);
        $this->info('Lavero Admin Diagnostics Tool');
        $this->line('Version: ' . Admin::getLongVersion());
        $this->line('Timestamp: ' . now()->toDateTimeString());
        $this->line('');

        $this->checkPhpEnvironment();
        $this->checkLaravelAndSecurity();
        $this->checkDatabase();
        $this->checkAssetsAndStorage();
        $this->checkExtensions();

        $this->line('');
        $this->renderSummary();

        if ($this->option('fix') && count($this->issues) > 0) {
            $this->runAutoFixes();
        }

        return count($this->issues) === 0 ? 0 : 1;
    }

    /**
     * Check PHP environment and required extensions.
     */
    protected function checkPhpEnvironment(): void
    {
        $this->comment('--- 1. PHP & Extensions ---');

        $phpVersion = PHP_VERSION;
        $phpOk = version_compare($phpVersion, '8.1.0', '>=');
        $this->printCheck(
            "PHP Version: {$phpVersion} (>= 8.1 required)",
            $phpOk,
            'Please upgrade your PHP version to 8.1 or higher (recommended: 8.2+).'
        );

        $requiredExtensions = [
            'pdo'       => 'Required for database operations',
            'mbstring'  => 'Required for UTF-8 string manipulation',
            'openssl'   => 'Required for encryption and security tokens',
            'fileinfo'  => 'Required for file upload MIME detection',
            'curl'      => 'Required for HTTP requests and APIs',
            'zip'       => 'Required for spreadsheet export and archives',
            'gd'        => 'Required for image resizing and QR code rendering',
        ];

        foreach ($requiredExtensions as $ext => $reason) {
            $loaded = extension_loaded($ext);
            if ($ext === 'gd' && !$loaded && extension_loaded('imagick')) {
                $loaded = true;
                $ext = 'imagick (substituting for gd)';
            }
            $this->printCheck(
                "PHP extension: {$ext}",
                $loaded,
                "Enable php-{$ext} in your php.ini. Reason: {$reason}."
            );
        }

        $memoryLimit = ini_get('memory_limit');
        $this->info("  ℹ Memory limit: {$memoryLimit}");
    }

    /**
     * Check Laravel framework and security configuration.
     */
    protected function checkLaravelAndSecurity(): void
    {
        $this->line('');
        $this->comment('--- 2. Framework & Security ---');

        $laravelVersion = app()->version();
        $this->printCheck("Laravel Framework: {$laravelVersion}", true);

        $appKey = config('app.key');
        $hasKey = !empty($appKey);
        $this->printCheck(
            'Application Key (APP_KEY): ' . ($hasKey ? 'Configured' : 'Missing'),
            $hasKey,
            'Run "php artisan key:generate" to generate an application key.'
        );

        $configExists = file_exists(config_path('admin.php'));
        $this->printCheck(
            'Admin Configuration (config/admin.php): ' . ($configExists ? 'Present' : 'Not found'),
            $configExists,
            'Run "php artisan vendor:publish --provider=\"LaveroAdmin\Admin\AdminServiceProvider\"" to publish config.'
        );

        $env = config('app.env');
        $debug = config('app.debug') ? 'Enabled (true)' : 'Disabled (false)';
        $this->info("  ℹ Environment: [{$env}], Debug mode: [{$debug}]");
    }

    /**
     * Check database connectivity and admin tables.
     */
    protected function checkDatabase(): void
    {
        $this->line('');
        $this->comment('--- 3. Database & Migrations ---');

        try {
            DB::connection()->getPdo();
            $connectionName = DB::getDefaultConnection();
            $driver = config("database.connections.{$connectionName}.driver");
            $database = config("database.connections.{$connectionName}.database");
            $this->printCheck("Database Connection: [{$connectionName}] ({$driver} -> {$database})", true);
        } catch (\Throwable $e) {
            $this->printCheck('Database Connection', false, 'Unable to connect to database: ' . $e->getMessage());
            return;
        }

        $tables = [
            'users_table'            => config('admin.database.users_table', 'admin_users'),
            'roles_table'            => config('admin.database.roles_table', 'admin_roles'),
            'permissions_table'      => config('admin.database.permissions_table', 'admin_permissions'),
            'menu_table'             => config('admin.database.menu_table', 'admin_menu'),
            'operation_log_table'    => config('admin.database.operation_log_table', 'admin_operation_log'),
            'role_users_table'       => config('admin.database.role_users_table', 'admin_role_users'),
            'role_permissions_table' => config('admin.database.role_permissions_table', 'admin_role_permissions'),
            'role_menu_table'        => config('admin.database.role_menu_table', 'admin_role_menu'),
        ];

        foreach ($tables as $key => $table) {
            $exists = Schema::hasTable($table);
            $this->printCheck("Table [{$table}]", $exists, "Table [{$table}] is missing. Run \"php artisan admin:install\" or \"php artisan migrate\".");
        }

        // Check 2FA columns on users table
        $usersTable = config('admin.database.users_table', 'admin_users');
        if (Schema::hasTable($usersTable)) {
            $twoFactorColumns = ['two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at'];
            $missing2FaCols = [];
            foreach ($twoFactorColumns as $col) {
                if (!Schema::hasColumn($usersTable, $col)) {
                    $missing2FaCols[] = $col;
                }
            }
            $twoFactorOk = count($missing2FaCols) === 0;
            $this->printCheck(
                '2FA Authentication Columns in [' . $usersTable . ']',
                $twoFactorOk,
                'Missing 2FA columns (' . implode(', ', $missing2FaCols) . '). Run "php artisan migrate" to add 2FA migration.'
            );
        }
    }

    /**
     * Check assets publication and storage link.
     */
    protected function checkAssetsAndStorage(): void
    {
        $this->line('');
        $this->comment('--- 4. Assets & Storage ---');

        $vendorAssetsPath = public_path('vendor/lavero-admin');
        $assetsExist = is_dir($vendorAssetsPath);
        $cssPath = $vendorAssetsPath . '/lavero-admin/css/styles.css';
        $cssValid = file_exists($cssPath) && filesize($cssPath) > 0;

        $this->printCheck(
            'Published Admin Assets: (public/vendor/lavero-admin)',
            $assetsExist && $cssValid,
            'Assets are not published or styles.css is missing. Run "php artisan admin:publish --force".'
        );

        $storageLink = public_path('storage');
        $storageLinked = is_link($storageLink) || is_dir($storageLink);
        $this->printCheck(
            'Public Storage Symlink: (public/storage)',
            $storageLinked,
            'Public storage link is missing. Run "php artisan storage:link".'
        );

        $uploadDisk = config('admin.upload.disk', 'admin');
        $diskConfigured = array_key_exists($uploadDisk, config('filesystems.disks', []));
        $this->printCheck(
            "Upload Disk [{$uploadDisk}] configured in filesystems.php",
            $diskConfigured,
            "Disk [{$uploadDisk}] is not defined in config/filesystems.php."
        );
    }

    /**
     * Check registered extensions.
     */
    protected function checkExtensions(): void
    {
        $this->line('');
        $this->comment('--- 5. Registered Extensions ---');

        $extensions = Admin::$extensions;
        if (empty($extensions)) {
            $this->info('  ℹ No custom extensions registered yet.');
            return;
        }

        foreach ($extensions as $name => $class) {
            $classExists = class_exists($class);
            $this->printCheck("Extension [{$name}] -> {$class}", $classExists, "Class [{$class}] does not exist.");
        }
    }

    /**
     * Print single check result.
     */
    protected function printCheck(string $title, bool $success, ?string $recommendation = null): void
    {
        if ($success) {
            $this->line("  <info>✔</info> {$title}");
        } else {
            $this->line("  <error>✘</error> {$title}");
            if ($recommendation) {
                $this->line("    <comment>↳ Suggestion: {$recommendation}</comment>");
                $this->issues[] = [
                    'title'          => $title,
                    'recommendation' => $recommendation,
                ];
            }
        }
    }

    /**
     * Render final summary.
     */
    protected function renderSummary(): void
    {
        $count = count($this->issues);
        if ($count === 0) {
            $this->info('=========================================');
            $this->info('  ✔ ALL DIAGNOSTICS CHECKS PASSED!');
            $this->info('  Your Lavero Admin setup is healthy.');
            $this->info('=========================================');
        } else {
            $this->error('=========================================');
            $this->error("  ✘ FOUND {$count} ISSUE(S) THAT REQUIRE ATTENTION.");
            $this->error('=========================================');
            if (!$this->option('fix')) {
                $this->line('Tip: run "php artisan admin:diagnose --fix" to automatically resolve fixable issues.');
            }
        }
    }

    /**
     * Run automatic fixes.
     */
    protected function runAutoFixes(): void
    {
        $this->line('');
        $this->comment('--- Attempting Automatic Fixes ---');

        $storageLink = public_path('storage');
        if (!is_link($storageLink) && !is_dir($storageLink)) {
            $this->info('Creating storage link...');
            Artisan::call('storage:link');
            $this->line(Artisan::output());
        }

        $vendorAssetsPath = public_path('vendor/lavero-admin');
        if (!is_dir($vendorAssetsPath) || !file_exists($vendorAssetsPath . '/lavero-admin/css/styles.css')) {
            $this->info('Publishing admin assets...');
            Artisan::call('admin:publish', ['--force' => true]);
            $this->line(Artisan::output());
        }

        $this->info('Auto-fix completed. Re-running diagnostics:');
        $this->call('admin:diagnose');
    }
}
