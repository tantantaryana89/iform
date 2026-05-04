<?php

use yii\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

$this->title = 'Pending Approval Foreman';

$dataProvider = new ActiveDataProvider([
    'query' => \app\models\ChecksheetResult::find()
        ->where(['id' => array_column($results, 'id')])
        ->orderBy(['submitted_at' => SORT_DESC]),
    'pagination' => [
        'pageSize' => 20,
    ],
]);
?>

<div class="container-fluid">
    <h3 class="mb-3"><?= Html::encode($this->title) ?></h3>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-bordered table-striped'],
        'columns' => [
            'id',
            'mesin',
            'shift',
            'submitted_at:datetime',

            [
                'attribute' => 'approval_status',
                'label' => 'Status'
            ],

            [
                'label' => 'Aksi',
                'format' => 'raw',
                'value' => function ($model) {
                  $user = Yii::$app->user->identity;

                  $approveRoute = ['approve-foreman', 'id' => $model->id];

                  if ($user->role === 'chief') {
                      $approveRoute = ['approve-chief', 'id' => $model->id];
                  }

                  if ($user->role === 'manager') {
                      $approveRoute = ['approve-manager', 'id' => $model->id];
                  }

                  return
                      Html::a(
                          'Review',
                          ['view', 'id' => $model->id],
                          ['class' => 'btn btn-primary btn-sm me-1']
                      )
                      .
                      Html::a(
                          'Approve',
                          $approveRoute,
                          [
                              'class' => 'btn btn-success btn-sm',
                              'data-confirm' => 'Approve checksheet ini?',
                              'data-method' => 'post'
                          ]
                      );
                }
            ]
        ]
    ]); ?>
</div>