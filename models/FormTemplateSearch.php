<?php

namespace app\models;

use yii\data\ActiveDataProvider;

class FormTemplateSearch extends FormTemplate
{
    public $mapping_validation;

    public function rules()
    {
        return [
            [['name', 'source_file', 'status', 'mapping_validation'], 'safe'],
        ];
    }

    public function search($params)
    {
        $query = FormTemplate::find()
        ->andWhere(['in', 'status', ['active', 'draft']])
        ->orderBy(['id' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'name', $this->name])
              ->andFilterWhere(['like', 'source_file', $this->source_file])
              ->andFilterWhere(['status' => $this->status]);

        if ($this->mapping_validation !== null && $this->mapping_validation !== '') {
            $candidates = (clone $query)->all();
            $wantValid = $this->mapping_validation === 'valid';
            $matchedIds = [];

            foreach ($candidates as $template) {
                $isValid = $template->getSchemaValidationSummary()['is_valid'];
                if ($isValid === $wantValid) {
                    $matchedIds[] = $template->id;
                }
            }

            if (empty($matchedIds)) {
                $query->andWhere('0=1');
            } else {
                $query->andWhere(['id' => $matchedIds]);
            }
        }

        return $dataProvider;
    }
}
