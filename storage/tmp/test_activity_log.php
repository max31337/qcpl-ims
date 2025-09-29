<?php

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\ActivityLog;

// Create a test user (will persist)
$u = User::create([
    'firstname' => 'TTest',
    'lastname' => 'User',
    'name' => 'TTest User',
    'username' => 'ttest_user_' . time(),
    'employee_id' => 'TEST-'.time(),
    'email' => 'ttest'.time().'@example.test',
    'password' => bcrypt('password'),
    'role' => 'staff',
    'branch_id' => 1,
    'division_id' => 1,
    'section_id' => 1,
    'approval_status' => 'pending',
    'is_active' => 0,
]);

$log = ActivityLog::log('created', $u, [], $u->toArray());

echo "Created user id={$u->id}, activity_log id={$log->id}\n";

