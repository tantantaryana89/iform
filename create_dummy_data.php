<?php
define('YII_DEBUG', true);
define('YII_ENV', 'dev');
define('YII_ENV_DEV', true);

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/web.php';

$app = new yii\web\Application($config);

use app\models\FormResult;

// Dummy data
$dummyData = [
    [
        'no_mesin' => 'DM-APPR-001',
        'operator' => 'Operator Test 1',
        'tanggal' => '2026-04-05',
        'shift' => 'Pagi',
        'template_id' => 1,
        'approval_status' => FormResult::STATUS_LEADER_APPROVED,
        'leader_id' => 7,
        'leader_approved_at' => 1744049400,
    ],
    [
        'no_mesin' => 'DM-APPR-002',
        'operator' => 'Operator Test 2',
        'tanggal' => '2026-04-04',
        'shift' => 'Siang',
        'template_id' => 1,
        'approval_status' => FormResult::STATUS_SUPERVISOR_APPROVED,
        'leader_id' => 7,
        'leader_approved_at' => 1744034400,
        'supervisor_id' => 8,
        'supervisor_approved_at' => 1744053600,
    ],
    [
        'no_mesin' => 'DM-APPR-003',
        'operator' => 'Operator Test 3',
        'tanggal' => '2026-04-03',
        'shift' => 'Malam',
        'template_id' => 1,
        'approval_status' => FormResult::STATUS_CHIEF_APPROVED,
        'leader_id' => 7,
        'leader_approved_at' => 1743962400,
        'supervisor_id' => 8,
        'supervisor_approved_at' => 1743979200,
        'chief_id' => 9,
        'chief_approved_at' => 1744049400,
    ],
    [
        'no_mesin' => 'DM-APPR-004',
        'operator' => 'Operator Test 4',
        'tanggal' => '2026-04-02',
        'shift' => 'Pagi',
        'template_id' => 1,
        'approval_status' => FormResult::STATUS_APPROVED,
        'leader_id' => 7,
        'leader_approved_at' => 1743880000,
        'supervisor_id' => 8,
        'supervisor_approved_at' => 1743893600,
        'chief_id' => 9,
        'chief_approved_at' => 1743967200,
        'manager_id' => 10,
        'manager_approved_at' => 1744054800,
    ],
    [
        'no_mesin' => 'DM-APPR-005',
        'operator' => 'Operator Test 5',
        'tanggal' => '2026-04-01',
        'shift' => 'Siang',
        'template_id' => 1,
        'approval_status' => FormResult::STATUS_APPROVED,
        'leader_id' => 7,
        'leader_approved_at' => 1743793600,
        'supervisor_id' => 8,
        'supervisor_approved_at' => 1743808000,
        'chief_id' => 9,
        'chief_approved_at' => 1743880800,
        'manager_id' => 10,
        'manager_approved_at' => 1743973200,
    ],
];

$count = 0;
foreach ($dummyData as $data) {
    $form = new FormResult();
    $form->attributes = $data;
    $form->created_at = time();
    $form->updated_at = time();
    
    if ($form->save()) {
        $count++;
        echo "✓ Inserted: " . $data['no_mesin'] . "\n";
    } else {
        echo "✗ Failed to insert: " . $data['no_mesin'] . "\n";
        if ($form->errors) {
            print_r($form->errors);
        }
    }
}

echo "\nTotal inserted: $count records\n";

// Verify
$records = FormResult::find()->where(['like', 'no_mesin', 'DM-APPR'])->all();
echo "\nVerification - Total DM-APPR records in database: " . count($records) . "\n";
foreach ($records as $r) {
    echo "  - {$r->no_mesin} ({$r->approval_status})\n";
}
?>
