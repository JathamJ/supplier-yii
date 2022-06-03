<?php

namespace app\models;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

/**
 * Class Supplier
 */
class Supplier extends ActiveRecord {

    const noSelected    = 0;
    const pageSelected  = 1;
    const allSelected   = 2;

    public $opParam;
    public $idParam;
    public $nameParam;
    public $codeParam;
    public $statusParam;

    public function rules()
    {
        return [
            [
                ['opParam'],
                'string',
            ],
            [
                ['idParam'],
                'integer',
            ],
            [
                ['nameParam'],
                'string',
            ],
            [
                ['codeParam'],
                'string',
            ],
            [
                ['statusParam'],
                'string',
            ],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Supplier::find()->select('id,name,code,t_status');
        $dataProvider = new ActiveDataProvider([
            'query'         => $query,
            'pagination'    => [
                'pageSize'  => 10,
            ],
        ]);
        if (!($this->load($params, 'params') && $this->validate())) {
            return $dataProvider;
        }

        //where condition
        if ($this->opParam && $this->idParam) {
            $query->where([$this->opParam, 'id', $this->idParam]);
        }
        if (!$this->opParam) {
            $this->idParam = '';
        }
        if ($this->codeParam) {
            $query->where(['like', 'code', $this->codeParam]);
        }
        if ($this->nameParam) {
            $query->where(['like', 'name', $this->nameParam]);
        }
        if ($this->statusParam) {
            $query->where(['t_status' => $this->statusParam]);
        }
        $selectedType = $params['selectedType'] ?? 0;
        $selectIds = $params['selectIds'] ?? [];
        if ($selectedType != self::allSelected && !empty($selectIds)) {
            $query->where(['in', 'id', $selectIds]);
        }

        return $dataProvider;
    }
}