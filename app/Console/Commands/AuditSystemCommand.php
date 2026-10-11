<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AuditSystemCommand extends Command
{
    protected $signature = 'app:audit-system';

    protected $description = 'Run a full URU Coop-IS system health and requirement audit';

    private $passed = 0;
    private $warnings = 0;
    private $failed = 0;

    public function handle()
    {
        $this->line('URU Coop-IS Full System Health & Requirement Audit');
        $this->line(str_repeat('=', 58));

        $this->auditDatabase();
        $this->auditAuthenticationAndRoutes();
        $this->auditCwieDocumentsAndPdf();
        $this->auditIntegrations();

        $this->line('');
        $this->line(str_repeat('=', 58));
        $this->info("SUMMARY  PASS: {$this->passed}  WARN: {$this->warnings}  FAIL: {$this->failed}");

        if ($this->failed > 0) {
            $this->error('Audit completed with failures. Resolve FAIL items before release.');
            return self::FAILURE;
        }

        if ($this->warnings > 0) {
            $this->warn('Audit completed with warnings. Review WARN items before production use.');
        } else {
            $this->info('Audit completed successfully.');
        }

        return self::SUCCESS;
    }

    private function auditDatabase()
    {
        $this->section('1. Database & MySQL Connection');
        $this->check('DB_CONNECTION=mysql', config('database.default') === 'mysql', 'default='.config('database.default'));
        $this->check('MySQL driver', config('database.connections.mysql.driver') === 'mysql');
        $this->check('MySQL host', config('database.connections.mysql.host') === '127.0.0.1', config('database.connections.mysql.host'));
        $this->check('MySQL port', (string) config('database.connections.mysql.port') === '3306', (string) config('database.connections.mysql.port'));
        $this->check('Database name', config('database.connections.mysql.database') === 'uru_coop_db', config('database.connections.mysql.database'));
        $this->check('utf8mb4 charset', config('database.connections.mysql.charset') === 'utf8mb4', config('database.connections.mysql.charset'));
        $this->check('Thai collation', config('database.connections.mysql.collation') === 'utf8mb4_unicode_ci', config('database.connections.mysql.collation'));
        $this->check('InnoDB engine', config('database.connections.mysql.engine') === 'InnoDB', config('database.connections.mysql.engine'));

        try {
            DB::connection('mysql')->getPdo();
            $this->check('MySQL connection', true);
        } catch (Throwable $exception) {
            $this->check('MySQL connection', false, $exception->getMessage());
            return;
        }

        foreach (['users', 'cwie_documents', 'companies', 'faculties', 'departments', 'approval_histories', 'notifications'] as $table) {
            $this->check('Table '.$table, Schema::connection('mysql')->hasTable($table));
        }

        foreach (['student@uru.ac.th' => 'student', 'advisor@uru.ac.th' => 'advisor', 'officer@uru.ac.th' => 'officer', 'admin@uru.ac.th' => 'admin'] as $email => $role) {
            $user = User::where('email', $email)->first();
            $this->check('Seed user '.$role, $user && $user->role === $role, $email);
        }
    }

    private function auditAuthenticationAndRoutes()
    {
        $this->section('2. Multi-Role Authentication & Routes');
        $this->check('Default auth guard is web', config('auth.defaults.guard') === 'web');
        $this->check('Web guard uses users provider', config('auth.guards.web.provider') === 'users');
        $this->check('Users provider uses User model', config('auth.providers.users.model') === User::class);

        $routes = [
            'login' => 'login',
            'logout' => 'logout',
            'student.dashboard' => 'student/dashboard',
            'student.cwie.index' => 'student/cwie',
            'student.cwie.create' => 'student/cwie/{type}/create',
            'staff.dashboard' => 'staff/dashboard',
            'staff.approvals.dashboard' => 'staff/approvals',
            'advisor.approvals.dashboard' => 'advisor/approvals',
            'admin.dashboard' => 'admin/dashboard',
            'admin.users.index' => 'admin/users',
        ];

        foreach ($routes as $name => $uri) {
            $route = Route::getRoutes()->getByName($name);
            $this->check('Route '.$name, $route && $route->uri() === $uri, $route ? $route->uri() : 'missing');
        }

        $this->checkMiddleware('Student portal isolation', 'student.dashboard', ['auth', 'portal:student']);
        $this->checkMiddleware('Staff portal isolation', 'staff.dashboard', ['auth', 'role:advisor,officer', 'portal:staff']);
        $this->checkMiddleware('Admin portal isolation', 'admin.dashboard', ['auth', 'role:admin', 'portal:admin']);
        $this->checkMiddleware('Single login guest guard', 'login', ['guest']);
        $this->checkMiddleware('Single logout auth guard', 'logout', ['auth']);
    }

    private function auditCwieDocumentsAndPdf()
    {
        $this->section('3. CWIE 01-06 & PDF');
        $controller = app_path('Http/Controllers/CwieDocumentController.php');
        $model = app_path('Models/CwieDocument.php');
        $definitionSource = file_exists($controller) ? file_get_contents($controller) : '';

        $this->check('CWIE document controller', class_exists('App\\Http\\Controllers\\CwieDocumentController'));
        $this->check('CWIE document model', class_exists('App\\Models\\CwieDocument'));
        foreach (range(1, 6) as $number) {
            $type = sprintf('cwie-%02d', $number);
            $this->check('Definition '.$type, strpos($definitionSource, "'{$type}'") !== false);
        }

        foreach (['cwie/index.blade.php', 'cwie/form.blade.php', 'cwie/show.blade.php', 'cwie/pdf.blade.php'] as $view) {
            $this->check('View '.$view, file_exists(resource_path('views/'.$view)));
        }
        $pdfSource = file_exists(resource_path('views/cwie/pdf.blade.php')) ? file_get_contents(resource_path('views/cwie/pdf.blade.php')) : '';
        $usesA4Format = strpos($definitionSource, "'format' => 'A4'") !== false;
        $usesThaiTemplate = strpos($pdfSource, 'lang="th"') !== false && strpos($pdfSource, "font-family: 'thsarabunnew'") !== false;
        $hasConflictingPageRule = strpos($pdfSource, '@page') !== false;
        $this->check('Thai A4 PDF template', $usesA4Format && $usesThaiTemplate && !$hasConflictingPageRule);

        $dompdfInstalled = class_exists('Barryvdh\\DomPDF\\Facade\\Pdf') || class_exists('Barryvdh\\DomPDF\\ServiceProvider');
        $mpdfInstalled = class_exists('Mpdf\\Mpdf');
        if ($dompdfInstalled) {
            $this->check('barryvdh/laravel-dompdf package', true);
        } elseif ($mpdfInstalled) {
            $this->auditWarn('barryvdh/laravel-dompdf package', 'not installed; project currently has mpdf/mpdf');
        } else {
            $this->auditFail('PDF package', 'neither DomPDF nor mPDF is available');
        }
    }

    private function auditIntegrations()
    {
        $this->section('4. Notifications & Map Integrations');
        $this->check('Workflow Mailable', class_exists('App\\Mail\\CwieWorkflowMail'));
        $this->check('SMTP service config', is_array(config('services.smtp')) && !empty(config('services.smtp.mailer')));
        $this->check('LINE service config', is_array(config('services.line')) && !empty(config('services.line.push_endpoint')));
        if (empty(config('services.line.channel_access_token'))) {
            $this->auditWarn('LINE credentials', 'LINE_CHANNEL_ACCESS_TOKEN is empty');
        } else {
            $this->check('LINE credentials', true);
        }

        $companyViews = [resource_path('views/companies/index.blade.php'), resource_path('views/companies/form.blade.php')];
        $mapSource = '';
        foreach ($companyViews as $view) if (file_exists($view)) $mapSource .= file_get_contents($view);
        $this->check('Leaflet map integration', strpos($mapSource, 'unpkg.com/leaflet') !== false);
        $this->check('OpenStreetMap tiles', strpos($mapSource, 'openstreetmap.org') !== false);
        $this->check('Nominatim geocoding', strpos($mapSource, 'nominatim.openstreetmap.org') !== false);
    }

    private function checkMiddleware($label, $routeName, array $required)
    {
        $route = Route::getRoutes()->getByName($routeName);
        if (!$route) {
            $this->auditFail($label, 'route missing');
            return;
        }

        $middleware = $route->middleware();
        $missing = array_values(array_filter($required, function ($item) use ($middleware) {
            return !in_array($item, $middleware, true);
        }));
        $this->check($label, empty($missing), empty($missing) ? implode(', ', $middleware) : 'missing: '.implode(', ', $missing));
    }

    private function section($title)
    {
        $this->line('');
        $this->info($title);
    }

    private function check($label, $condition, $detail = '')
    {
        if ($condition) {
            $this->passed++;
            $this->line('<fg=green>[PASS]</> '.$label.($detail ? ' ('.$detail.')' : ''));
            return;
        }

        $this->auditFail($label, $detail);
    }

    private function auditWarn($label, $detail = '')
    {
        $this->warnings++;
        $this->line('<fg=yellow>[WARN]</> '.$label.($detail ? ' ('.$detail.')' : ''));
    }

    private function auditFail($label, $detail = '')
    {
        $this->failed++;
        $this->line('<fg=red>[FAIL]</> '.$label.($detail ? ' ('.$detail.')' : ''));
    }
}
