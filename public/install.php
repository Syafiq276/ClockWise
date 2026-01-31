<?php
/**
 * ClockWise Web Installer
 * 
 * This script handles installation tasks that normally require terminal access.
 * DELETE THIS FILE AFTER INSTALLATION FOR SECURITY!
 */

// Prevent timeout for long operations
set_time_limit(300);

$steps = [];
$errors = [];

// Check if already installed
if (file_exists(__DIR__ . '/../storage/installed.lock')) {
    die('
        <div style="font-family: sans-serif; max-width: 500px; margin: 50px auto; padding: 20px; border: 2px solid #ef4444; border-radius: 10px; background: #fef2f2;">
            <h2 style="color: #dc2626;">⚠️ Already Installed</h2>
            <p>ClockWise is already installed. If you need to reinstall, delete <code>storage/installed.lock</code> first.</p>
            <p style="color: #dc2626;"><strong>Please delete this install.php file for security!</strong></p>
            <a href="/" style="display: inline-block; margin-top: 10px; padding: 10px 20px; background: #3b82f6; color: white; text-decoration: none; border-radius: 5px;">Go to ClockWise</a>
        </div>
    ');
}

// Security token check
$token = $_GET['token'] ?? '';
$expectedToken = 'clockwise2026'; // Change this before uploading!

if ($token !== $expectedToken) {
    die('
        <div style="font-family: sans-serif; max-width: 500px; margin: 50px auto; padding: 20px; border: 2px solid #f59e0b; border-radius: 10px; background: #fffbeb;">
            <h2 style="color: #d97706;">🔐 Security Token Required</h2>
            <p>Access this installer with the security token:</p>
            <code style="background: #fef3c7; padding: 5px 10px; border-radius: 5px;">install.php?token=clockwise2026</code>
        </div>
    ');
}

// Action handler
$action = $_GET['action'] ?? 'check';

?>
<!DOCTYPE html>
<html>
<head>
    <title>ClockWise Installer</title>
    <style>
        * { box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            background: #f1f5f9; 
            margin: 0; 
            padding: 20px;
        }
        .container { max-width: 600px; margin: 0 auto; }
        .card { 
            background: white; 
            border-radius: 16px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.1); 
            padding: 30px; 
            margin-bottom: 20px;
        }
        h1 { color: #1e293b; margin-top: 0; }
        h2 { color: #334155; font-size: 18px; margin-top: 25px; }
        .step { 
            padding: 12px 15px; 
            border-radius: 8px; 
            margin: 8px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .step.success { background: #dcfce7; color: #166534; }
        .step.error { background: #fef2f2; color: #dc2626; }
        .step.warning { background: #fffbeb; color: #d97706; }
        .step.info { background: #eff6ff; color: #1d4ed8; }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            margin-top: 15px;
            border: none;
            cursor: pointer;
        }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(59,130,246,0.4); }
        .btn-danger { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
        .btn-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        code { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 13px; }
        .alert { padding: 15px; border-radius: 8px; margin: 15px 0; }
        .alert-warning { background: #fffbeb; border: 1px solid #fbbf24; color: #92400e; }
        .alert-danger { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>🕐 ClockWise Installer</h1>
            
            <?php if ($action === 'check'): ?>
                <p>This installer will help you set up ClockWise on your cPanel hosting.</p>
                
                <h2>📋 Pre-Installation Checks</h2>
                
                <?php
                // Check PHP version
                $phpVersion = phpversion();
                $phpOk = version_compare($phpVersion, '8.1', '>=');
                echo '<div class="step ' . ($phpOk ? 'success' : 'error') . '">';
                echo ($phpOk ? '✅' : '❌') . " PHP Version: $phpVersion " . ($phpOk ? '(OK)' : '(Requires 8.1+)');
                echo '</div>';
                
                // Check extensions
                $requiredExtensions = ['pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json'];
                foreach ($requiredExtensions as $ext) {
                    $loaded = extension_loaded($ext);
                    echo '<div class="step ' . ($loaded ? 'success' : 'error') . '">';
                    echo ($loaded ? '✅' : '❌') . " Extension: $ext";
                    echo '</div>';
                }
                
                // Check writable directories
                $writableDirs = ['../storage', '../storage/logs', '../storage/framework', '../bootstrap/cache'];
                foreach ($writableDirs as $dir) {
                    $writable = is_writable($dir);
                    echo '<div class="step ' . ($writable ? 'success' : 'warning') . '">';
                    echo ($writable ? '✅' : '⚠️') . " Writable: $dir";
                    echo '</div>';
                }
                
                // Check .env file
                $envExists = file_exists(__DIR__ . '/../.env');
                echo '<div class="step ' . ($envExists ? 'success' : 'error') . '">';
                echo ($envExists ? '✅' : '❌') . " .env file exists";
                echo '</div>';
                ?>
                
                <h2>📝 Next Steps</h2>
                <ol>
                    <li>Make sure you've uploaded all files</li>
                    <li>Create MySQL database in cPanel</li>
                    <li>Edit <code>.env</code> file with your database credentials</li>
                    <li>Click "Run Installation" below</li>
                </ol>
                
                <a href="?token=<?= $token ?>&action=install" class="btn" onclick="return confirm('Make sure you have configured .env file with database credentials!')">
                    🚀 Run Installation
                </a>
                
            <?php elseif ($action === 'install'): ?>
                <h2>⚙️ Running Installation...</h2>
                
                <?php
                require __DIR__ . '/../vendor/autoload.php';
                
                try {
                    // Boot Laravel
                    $app = require_once __DIR__ . '/../bootstrap/app.php';
                    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
                    $kernel->bootstrap();
                    
                    echo '<div class="step success">✅ Laravel bootstrapped</div>';
                    
                    // Run migrations
                    echo '<div class="step info">⏳ Running migrations...</div>';
                    $exitCode = Artisan::call('migrate', ['--force' => true]);
                    if ($exitCode === 0) {
                        echo '<div class="step success">✅ Migrations completed</div>';
                    } else {
                        echo '<div class="step error">❌ Migration failed: ' . Artisan::output() . '</div>';
                    }
                    
                    // Run seeder
                    echo '<div class="step info">⏳ Seeding database...</div>';
                    $exitCode = Artisan::call('db:seed', ['--force' => true]);
                    if ($exitCode === 0) {
                        echo '<div class="step success">✅ Database seeded</div>';
                    } else {
                        echo '<div class="step warning">⚠️ Seeding skipped or failed</div>';
                    }
                    
                    // Clear and cache config
                    Artisan::call('config:clear');
                    Artisan::call('cache:clear');
                    Artisan::call('view:clear');
                    Artisan::call('route:clear');
                    echo '<div class="step success">✅ Caches cleared</div>';
                    
                    // Create storage link
                    if (!file_exists(__DIR__ . '/storage')) {
                        Artisan::call('storage:link');
                        echo '<div class="step success">✅ Storage linked</div>';
                    }
                    
                    // Generate app key if not set
                    if (empty(env('APP_KEY')) || env('APP_KEY') === 'base64:') {
                        Artisan::call('key:generate', ['--force' => true]);
                        echo '<div class="step success">✅ App key generated</div>';
                    }
                    
                    // Mark as installed
                    file_put_contents(__DIR__ . '/../storage/installed.lock', date('Y-m-d H:i:s'));
                    echo '<div class="step success">✅ Installation completed!</div>';
                    
                    echo '<div class="alert alert-warning" style="margin-top: 20px;">
                        <strong>🔐 IMPORTANT:</strong> Delete this <code>install.php</code> file now for security!
                    </div>';
                    
                    echo '<h2>🎉 Login Credentials</h2>';
                    echo '<div class="step info">📧 Admin: <strong>admin@clockwise.my</strong> / <strong>password123</strong></div>';
                    echo '<div class="step info">📧 Employee: <strong>ali@clockwise.my</strong> / <strong>password123</strong></div>';
                    
                    echo '<a href="/" class="btn btn-success">🚀 Go to ClockWise</a>';
                    
                } catch (Exception $e) {
                    echo '<div class="step error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    echo '<pre style="background: #fef2f2; padding: 10px; border-radius: 8px; overflow: auto; font-size: 12px;">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
                    echo '<a href="?token=' . $token . '&action=check" class="btn btn-danger">← Back to Checks</a>';
                }
                ?>
                
            <?php endif; ?>
        </div>
        
        <p style="text-align: center; color: #94a3b8; font-size: 13px;">
            ClockWise Installer v1.0 | Delete this file after installation!
        </p>
    </div>
</body>
</html>
