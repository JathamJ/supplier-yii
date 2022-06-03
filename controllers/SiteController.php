<?php

namespace app\controllers;

use Yii;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\Supplier;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new Supplier();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'dataProvider'  => $dataProvider,
            'searchModel'   => $searchModel
        ]);
    }

    protected function getExportHeader($exportFields)
    {
        $header = [];
        foreach ($exportFields as $val) {
            switch ($val) {
                case 'id':
                    $header[] = 'ID';
                    break;
                case 'name':
                    $header[] = 'Name';
                    break;
                case 'code':
                    $header[] = 'Code';
                    break;
                case 't_status':
                    $header[] = 'Status';
                    break;
            }
        }
        return $header;
    }

    /**
     * format export row array
     * @param $exportFields
     * @param $model
     * @return array
     */
    protected function getExportRow($exportFields, $model)
    {
        $ret = [];
        foreach ($exportFields as $val) {
            switch ($val) {
                case 'id':
                    $ret[] = $model->id;
                    break;
                case 'name':
                    $ret[] = $model->name;
                    break;
                case 'code':
                    $ret[] = $model->code;
                    break;
                case 't_status':
                    $ret[] = $model->t_status;
                    break;
            }
        }
        return $ret;
    }

    public function actionExport()
    {
        $searchModel = new Supplier();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->setPagination(false);
        $ret = $dataProvider->getModels();
        $exportFields = $this->request->get('exportFields', ['id']);
        $header = $this->getExportHeader($exportFields);
        $rows = [];
        foreach ($ret as $val) {
            $rows[] = $this->getExportRow($exportFields, $val);
        }
        $filename = 'suppliers.csv';
        ob_clean();
        ob_end_clean();

        header("Content-Type:application/vnd.ms-excel;charset=utf-8");
        header("Content-Disposition:attachment;filename=\"" . urlencode($filename) . "\"");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header("Content-Transfer-Encoding:binary");
        header('Cache-Control: max-age=0');
        header('Expires:0');
        header('Pragma:public');
        $output = fopen('php://output','w+');
        fwrite($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, $header);
        foreach($rows as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }


}
