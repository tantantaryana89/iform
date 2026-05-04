<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;

use app\models\ChecksheetTemplate;
use app\models\ChecksheetSection;
use app\models\ChecksheetItem;
use app\models\ChecksheetSymbol;
use app\models\DaftarMesin;
use yii\db\Transaction;

class ChecksheetTemplateController extends Controller
{
    /* =====================================================
     * INDEX
     * ===================================================== */
    public function actionIndex()
    {
        $templates = ChecksheetTemplate::find()
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        return $this->render('index', compact('templates'));
    }

    /* =====================================================
     * VIEW
     * ===================================================== */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $sections = ChecksheetSection::find()
            ->where(['template_id' => $model->id])
            ->orderBy(['sort_order' => SORT_ASC])
            ->with('items')
            ->all();

        return $this->render('view', compact('model', 'sections'));
    }

    /* =====================================================
     * CREATE TEMPLATE
     * ===================================================== */
    public function actionCreate()
    {
        $model = new ChecksheetTemplate();

        $mesinList = $this->getMesinList();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }

        return $this->render('create', compact('model', 'mesinList'));
    }

    /* =====================================================
     * UPDATE TEMPLATE
     * ===================================================== */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $mesinList = $this->getMesinList();

        $sections = ChecksheetSection::find()
            ->where(['template_id' => $model->id])
            ->orderBy(['sort_order' => SORT_ASC])
            ->with('items')
            ->all();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['update', 'id' => $model->id]);
        }

        return $this->render('update', compact(
            'model',
            'mesinList',
            'sections'
        ));
    }

    /* =====================================================
     * ADD SECTION
     * ===================================================== */
    public function actionAddSection($template_id)
    {
        $section = new ChecksheetSection();
        $section->template_id = $template_id;
        $section->title = Yii::$app->request->post('title', 'Section Baru');
        $section->sort_order = ChecksheetSection::find()
            ->where(['template_id' => $template_id])
            ->max('sort_order') + 1;

        $section->save(false);

        return $this->redirect(['update', 'id' => $template_id]);
    }

    /* =====================================================
     * ADD ITEM
     * ===================================================== */
    public function actionAddItem($template_id, $section_id)
    {
        $item = new ChecksheetItem();
        $item->template_id = $template_id;
        $item->section_id  = $section_id;
        $item->label       = Yii::$app->request->post('label', '');
        $item->type        = 'checklist';

        $item->sort_order = ChecksheetItem::find()
            ->where(['section_id' => $section_id])
            ->max('sort_order') + 1;

        $item->excel_row_base = (int)$item->sort_order;
        if ($item->excel_row_base <= 0) {
            $item->excel_row_base = 1;
        }

        // ✅ WAJIB
        $item->item_code = 'ITEM-' . $template_id . '-' . $section_id . '-' . time();

        // default JSON
        $item->shift_json = json_encode(['1']);
        $item->instruction_json = json_encode([
            'standard'   => [],
            'cara'       => [],
            'frekuensi'  => [],
            'note'       => [],
            'conditions' => [],
        ]);

        $item->save(false);

        return $this->redirect(['update', 'id' => $template_id]);
    }

    /* =====================================================
     * EDIT ITEM (INI KUNCI UTAMA)
     * ===================================================== */
    public function actionEditItem($id)
    {
        $item = ChecksheetItem::findOne($id);
        if (!$item) {
            throw new NotFoundHttpException('Item tidak ditemukan');
        }

        $symbols = ChecksheetSymbol::find()
            ->where(['is_active' => 1])
            ->orderBy(['name' => SORT_ASC])
            ->all();

        if (Yii::$app->request->isPost) {

            $post = Yii::$app->request->post();

            // LOAD FORM
            $item->load($post);

            // 🔥 PAKSA SIMPAN SYMBOL (INTI MASALAH)
            $item->symbol_id   = $post['ChecksheetItem']['symbol_id']   ?? null;
            $item->symbol_id_2 = $post['ChecksheetItem']['symbol_id_2'] ?? null;

            // SHIFT → shift_json
            $item->shift_json = json_encode($post['shift'] ?? []);

            // INSTRUCTION → instruction_json
            $item->setInstruction([
                'conditions' => $this->buildConditionRows($post['conditions'] ?? []),
                'standard'  => array_values(array_filter($post['standard'] ?? [])),
                'cara'      => array_values(array_filter($post['cara'] ?? [])),
                'frekuensi' => array_values(array_filter($post['frekuensi'] ?? [])),
                'note'      => array_values(array_filter($post['note'] ?? [])),
            ]);

            if (!$item->save()) {
                // DEBUG FINAL – kalau masih gagal
                dd($item->errors);
            }

            return $this->redirect(['update', 'id' => $item->template_id]);
        }

        return $this->render('edit-item', compact('item', 'symbols'));
    }

    /* =====================================================
     * DELETE TEMPLATE
     * ===================================================== */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $transaction = Yii::$app->db->beginTransaction(Transaction::SERIALIZABLE);

        try {
            foreach ($model->sections as $section) {
                ChecksheetItem::deleteAll(['section_id' => $section->id]);
            }

            ChecksheetSection::deleteAll(['template_id' => $model->id]);
            $model->delete();

            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw $exception;
        }

        return $this->redirect(['index']);
    }

    public function actionDeleteItem($id)
    {
        $item = ChecksheetItem::findOne($id);
        if ($item === null) {
            throw new NotFoundHttpException('Item tidak ditemukan');
        }

        $templateId = (int)$item->template_id;
        $item->delete();

        return $this->redirect(['update', 'id' => $templateId]);
    }

    public function actionDeleteSection($id)
    {
        $section = ChecksheetSection::findOne($id);
        if ($section === null) {
            throw new NotFoundHttpException('Section tidak ditemukan');
        }

        $templateId = (int)$section->template_id;
        $transaction = Yii::$app->db->beginTransaction(Transaction::SERIALIZABLE);

        try {
            ChecksheetItem::deleteAll(['section_id' => $section->id]);
            $section->delete();
            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw $exception;
        }

        return $this->redirect(['update', 'id' => $templateId]);
    }

    /* =====================================================
     * HELPERS
     * ===================================================== */
    protected function findModel($id)
    {
        if (($model = ChecksheetTemplate::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Data tidak ditemukan.');
    }

    protected function getMesinList()
    {
        return ArrayHelper::map(
            DaftarMesin::find()->orderBy(['nama_mesin' => SORT_ASC])->all(),
            'id',
            fn ($m) => $m->no_mesin . ' - ' . $m->nama_mesin
        );
    }

    private function buildConditionRows(array $rows): array
    {
        $conditions = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $condition = [
                'standard' => trim((string)($row['standard'] ?? '')),
                'cara' => trim((string)($row['cara'] ?? '')),
                'frekuensi' => trim((string)($row['frekuensi'] ?? '')),
                'note' => trim((string)($row['note'] ?? '')),
            ];

            if (implode('', $condition) === '') {
                continue;
            }

            $conditions[] = $condition;
        }

        return $conditions;
    }
}
