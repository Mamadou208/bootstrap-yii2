<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\CheckboxColumn;
use yii\grid\GridView;
use yii\web\JsExpression;
use kartik\select2\Select2;

$this->title = Yii::t('app', 'Regions');
?>
<?= Html::a(Yii::t('app', 'Add'), ['edit'], ['class' => 'btn btn-default']) ?>

<?= Html::beginForm(['operations'], 'post') ?>
  <?php \yii\widgets\Pjax::begin(); ?>

  <?= GridView::widget([
      'dataProvider' => $dataProvider,
      'filterModel'  => $regionSearch,
      'options' => ['class' => 'gridview'],
      'layout' =>
      '<div class="panel panel-default">
         <div class="panel-body panel-table">
           <div class="table-responsive">{items}</div>
         </div>
         <div class="panel-footer">{summary}</div>
       </div>
       <div class="operations">
         ' . Html::submitButton(Yii::t('app', 'delete'), [
             'name' => 'operation',
             'value' => 'delete',
             'data-confirmation' => Yii::t('app', 'Are you sure you want to delete this records?'),
             'data-loading-text' => Yii::t('app', 'Please wait…'),
             'class' => 'submit disabled confirmation btn btn-danger btn-xs'
         ]) . '
       </div>
      {pager}
      ',
      'tableOptions' => ['class' => 'table ' . ($dataProvider->count ? 'table-hover' : '')],
      'columns' => [
          /**
           * @var id
           */
          [
              'class' => CheckboxColumn::classname(),
              'headerOptions' => ['style' => 'width: 30px']
          ],
          /**
           * @var title
           */
          [
              'attribute' => 'title',
              'format' => 'raw',
              'value' => function ($model) {
                  return Html::a(Html::encode($model['title']), ['edit', 'id' => $model['region_id']], ['data-pjax' => 0]);
              }
          ],
          /**
           * @var country
           */
          [
              'attribute' => 'country_id',
              'value' => 'country.title',
              'filter' => Select2::widget([
                  'model' => $regionSearch,
                  'attribute' => 'country_id',
                  'options' => ['placeholder' => ' '],
                  'pluginOptions' => [
                      'width' => '100%',
                      'multiple' => false,
                      'allowClear' => true,
                      'minimumInputLength' => 2,
                      'maximumSelectionSize' => 30,
                      'ajax' => [
                          'url'      => Url::toRoute('countries/autocomplete'),
                          'dataType' => 'json',
                          'type'     => 'POST',
                          'data'     => new JsExpression('function (term) { return {term: term}; }'),
                          'results'  => new JsExpression('function (data) { return {results: data}; }')
                      ],
                      'initSelection' => new JsExpression('function (element, callback) {
                          var data = {id: element.val(), text: "'.@Html::encode($regionSearch->country->title).'"};
                          callback(data);
                      }')
                  ]
              ])
          ],
          // action buttons
          [
              'class' => 'yii\grid\ActionColumn',
              'headerOptions' => ['class' => 'text-right', 'style' => 'width: 40px'],
              'template' => '{delete}',
              'buttons' => [
                  'delete' => function ($url, $model) {
                      return Html::a(
                          '<span class="glyphicon glyphicon-remove"></span>',
                          ['delete', 'id' => $model->primaryKey],
                          [
                              'title' => Yii::t('app', 'Delete'),
                              'class' => 'confirmation submit btn btn-xs btn-danger',
                              'data-pjax' => 0,
                              'data-confirmation' => Yii::t('app', 'Are you sure you want to delete this record?')
                          ]
                      );
                  }
              ]
          ],
      ],
  ]) ?>

  <?php \yii\widgets\Pjax::end(); ?>
<?= Html::endForm(); ?>
