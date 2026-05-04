<?php

// Define environment constants
define('YII_ENV_DEV', true);
define('YII_ENV', 'dev');

// Use web entry point
require __DIR__ . '/web/index.php';

// Create dummy approval data
$db = Yii::$app->db;

// Get existing users for approval chain
$users = $db->createCommand('SELECT id, username FROM user WHERE username IN ("sarno", "budi", "chief1", "manager1")')->queryAll();
$userMap = [];
foreach ($users as $user) {
    $userMap[$user['username']] = $user['id'];
}

echo "Found users:\n";
print_r($userMap);

// Create dummy form results with different approval stages
$dummyData = [
    [
        'no_mesin' => 'MCH-DUMMY-1',
        'operator' => 'Operator Dummy 1',
        'tanggal' => date('Y-m-d'),
        'shift' => 'Pagi',
        'approval_status' => 'leader_approved',
        'leader_id' => $userMap['sarno'] ?? null,
        'leader_approved_at' => time() - 86400, // 1 day ago
        'created_at' => time() - 86400 * 2,
        'updated_at' => time() - 86400,
    ],
    [
        'no_mesin' => 'MCH-DUMMY-2',
        'operator' => 'Operator Dummy 2',
        'tanggal' => date('Y-m-d'),
        'shift' => 'Siang',
        'approval_status' => 'supervisor_approved',
        'leader_id' => $userMap['sarno'] ?? null,
        'leader_approved_at' => time() - 86400 * 2,
        'supervisor_id' => $userMap['budi'] ?? null,
        'supervisor_approved_at' => time() - 86400,
        'created_at' => time() - 86400 * 3,
        'updated_at' => time() - 86400,
    ],
];

foreach ($dummyData as $data) {
    $db->createCommand()->insert('form_result', $data)->execute();
    echo "Inserted dummy form: {$data['no_mesin']} with status: {$data['approval_status']}\n";
}

echo "Dummy data creation completed!\n";

// Create dummy approval data
$db = Yii::$app->db;

// Get existing users for approval chain
$users = $db->createCommand('SELECT id, username FROM user WHERE username IN ("sarno", "budi", "chief1", "manager1")')->queryAll();
$userMap = [];
foreach ($users as $user) {
    $userMap[$user['username']] = $user['id'];
}

echo "Found users:\n";
print_r($userMap);

// Create dummy form results with different approval stages
$dummyData = [
    [
        'no_mesin' => 'MCH-DUMMY-1',
        'operator' => 'Operator Dummy 1',
        'tanggal' => date('Y-m-d'),
        'shift' => 'Pagi',
        'approval_status' => 'leader_approved',
        'leader_id' => $userMap['sarno'] ?? null,
        'leader_approved_at' => time() - 86400, // 1 day ago
        'created_at' => time() - 86400 * 2,
        'updated_at' => time() - 86400,
    ],
    [
        'no_mesin' => 'MCH-DUMMY-2',
        'operator' => 'Operator Dummy 2',
        'tanggal' => date('Y-m-d'),
        'shift' => 'Siang',
        'approval_status' => 'supervisor_approved',
        'leader_id' => $userMap['sarno'] ?? null,
        'leader_approved_at' => time() - 86400 * 2,
        'supervisor_id' => $userMap['budi'] ?? null,
        'supervisor_approved_at' => time() - 86400,
        'created_at' => time() - 86400 * 3,
        'updated_at' => time() - 86400,
    ],
];

foreach ($dummyData as $data) {
    $db->createCommand()->insert('form_result', $data)->execute();
    echo "Inserted dummy form: {$data['no_mesin']} with status: {$data['approval_status']}\n";
}

echo "Dummy data creation completed!\n";