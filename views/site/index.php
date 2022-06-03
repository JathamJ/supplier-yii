<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\grid\CheckboxColumn;
use yii\bootstrap4\Alert;
use yii\bootstrap4\Button;
use yii\bootstrap4\Modal;

/**
 * @var yii\web\View $this
 * @var app\models\Supplier $dataProvider
 * @var yii\data\ActiveDataProvider $searchModel
 */

$this->title = 'Supplier List';
?>

<?php
$a1 = Alert::begin([
 'options' => [
     'class'    => 'alert-warning alert-selected-page',
     'style'    => [
         'display'  => 'none',
     ],
  ],
]);
$a1->closeButton = false;

 echo '<b class="alert-selected-text">All 10</b> conversations on this page have been selected. ' . Html::a('Select all conversations that match this search', '#', ['class' => 'selected-all-btn']);

Alert::end();

$a2 = Alert::begin([
    'options' => [
        'class' => 'alert-warning alert-selected-all',
        'style'    => [
            'display'  => 'none',
        ],
    ],
]);
$a2->closeButton = false;

echo 'All conversations in this search have been selected. ' . Html::a('clear selection', '#', ['class' => 'clear-selected-btn']);

Alert::end();
?>
<?=
    Button::widget([
        'label'     => 'Export',
        'options' => ['class' => 'btn-primary export'],
    ])
?>
<?= GridView::widget([
    'tableOptions'  => ['class' => 'table table-hover'],
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'pager'         =>  [
        'pageCssClass'      => 'page-item',
        'firstPageCssClass' => 'page-item',
        'lastPageCssClass'  => 'page-item',
        'prevPageCssClass'  => 'page-item',
        'nextPageCssClass'  => 'page-item',
        'linkOptions'       => [
                'class'     => 'page-link',
        ],
        'disabledListItemSubTagOptions' => [
            'tag' => 'div',
            'class' => 'page-link'
        ],
        'firstPageLabel'    => 'First',
        'nextPageLabel'     => 'Next',
        'prevPageLabel'     => 'Prev',
        'lastPageLabel'     => 'Last',
    ],
    'columns'       => [
        [
            'class'     => CheckboxColumn::class,
            'name'      => 'id',
        ],
        [
            'attribute' => 'id',
            'label'     => 'ID',
            'filter'    => Html::dropDownList('params[opParam]', $searchModel->opParam, [
                    ''      => 'All',
                    '<'     => '<',
                    '<='    => '<=',
                    '='     => '=',
                    '>'     => '>',
                    '>='    => '>=',
                    '!='    => '!=',
            ]) .' '. Html::input('number', 'params[idParam]', $searchModel->idParam, ['style'   => ['max-width' => '50px']]),
        ],
        [
            'attribute' => 'name',
            'filter'    => Html::textInput('params[nameParam]', $searchModel->nameParam),
        ],
        [
            'attribute' => 'code',
            'filter'    => Html::textInput('params[codeParam]', $searchModel->codeParam),
        ],
        [
            'attribute' => 't_status',
            'label'     => 'Status',
            'filter'    => Html::dropDownList('params[statusParam]', $searchModel->statusParam, [
                ''      => 'All',
                'ok'    => 'ok',
                'hold'  => 'hold',
            ]),

        ],
    ],

])

?>

<?php     // no selected Modal
    Modal::begin([
        'title'         => 'Warning!',
        'options'       => [
            'class'     => 'no-selected-modal',
        ]
    ]);

    echo 'Please select rows which you want to export.';

    Modal::end();
?>

<?php     // select columns Modal
$colModal = Modal::begin([
    'title'         => 'Which column(s) to be included in the CSV?',
    'options'       => [
        'class'     => 'column-modal',
    ],
    'footer' => '<a href="#" class="btn btn-primary confirm" data-dismiss="modal">Confirm</a>',
]);
$colModal->closeButton = false;

$checkboxList = [
    'id'        => 'ID ',
    'name'      => 'Name ',
    'code'      => 'Code ',
    't_status'  => 'Status ',
];

foreach ($checkboxList as $k => $v) {
    echo Html::checkbox('exportFields[]', true, [
        'value'     => $k,
        'label'     => $v,
        'disabled'  => $k == 'id',
        'style' => ['margin-left' => '20px']
    ]);
}


Modal::end();
?>

<?= Html::jsFile('@web/assets/c25e3a5/jquery.js') ?>
<script type="text/javascript">
    const NoSelected    = 0
    const PageSelected  = 1
    const AllSelected   = 2
    // selectedType record selected status
    let selectedType = NoSelected

    let pageAlert = $(".alert-selected-page")
    let allAlert = $(".alert-selected-all")
    let selectedAllBtn = $(".selected-all-btn")
    let clearBtn = $(".clear-selected-btn")

    // all checkbox click
    $('.select-on-check-all').change(function () {
        if (!$(this).is(':checked')) {  // clear all selected
            selectedType = NoSelected
            pageAlert.hide()
            allAlert.hide()
            return
        }
        $(".alert-selected-text").text('All ' + $("input[name='id[]']:checked").length)
        pageAlert.show()
        selectedType = PageSelected
    })

    // checkbox change
    $("input[name='id[]']").change(function () {
        if (!$(this).is(':checked')) {  // clear all selected
            selectedType = NoSelected
            pageAlert.hide()
            allAlert.hide()
            return
        }
        if ($('.select-on-check-all').is(':checked')) {
            $(".alert-selected-text").text('All ' + $("input[name='id[]']:checked").length)
            pageAlert.show()
            selectedType = PageSelected
        }
    })

    // selected all button click
    selectedAllBtn.click(function () {
        pageAlert.hide()
        allAlert.show()
        selectedType = AllSelected
    })

    // clear selected button click
    clearBtn.click(function () {
        $('.select-on-check-all').click()
    })

    // export button click
    $(".export").click(function () {
        let selectedRowsNum = $("input[name='id[]']:checked").length
        if (selectedRowsNum <= 0 && selectedType == NoSelected) {
            $(".no-selected-modal").modal('show')
            return
        }

        $(".column-modal").modal('show')
    })

    // confirm button click
    $(".confirm").click(function () {
        let exportFields = []
        let selectIds = []
        $("input[name='exportFields[]']:checked").each(function (i) {
            exportFields[i] = $(this).val()
        })
        $("input[name='id[]']:checked").each(function (i) {
            selectIds[i] = $(this).val()
        })
        let params = {
            r:              'site/export',
            selectedType:   selectedType,
            exportFields:   exportFields,
            selectIds:      selectIds,
            params: {
                opParam: $("select[name='params[opParam]']").val(),
                idParam: $("input[name='params[idParam]']").val(),
                nameParam: $("input[name='params[nameParam]']").val(),
                codeParam: $("input[name='params[codeParam]']").val(),
                statusParam: $("select[name='params[statusParam]']").val(),
            }
        }

        window.open('/index.php?' + $.param(params))

    })

</script>

