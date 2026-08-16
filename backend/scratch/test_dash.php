<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ctrl = new \App\Http\Controllers\Api\DashboardController();
auth()->login(\App\Models\User::first());

foreach ([1, 2, 3, 4, 5, 6, 7] as $locId) {
    $req = new \Illuminate\Http\Request(['location_id' => $locId, 'outlet_id' => 1]);
    try {
        $res = $ctrl->index($req);
        echo "Location $locId: STATUS " . $res->getStatusCode() . " OK\n";
    } catch (\Throwable $e) {
        echo "Location $locId: ERROR " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
}
