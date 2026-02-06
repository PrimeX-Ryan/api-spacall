<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Provider;

$t = Provider::with(['user', 'services'])->find(2);

$data = [
    'provider_id' => $t->id,
    'name' => $t->user->first_name . ' ' . $t->user->last_name,
    'services' => $t->services->map(fn($s) => [
        'service_id' => $s->id,
        'name' => $s->name
    ])
];

echo json_encode($data, JSON_PRETTY_PRINT);
