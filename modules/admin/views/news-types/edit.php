<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Type of news') . ' / ';
$this->title .= !empty($model->title) ? $model->title : Yii::t('app', 'Create');
?>
<?= Html::a(Yii::t('app', 'List'), Url::toRoute('index'), ['class' => 'btn btn-default']) ?>&nbsp;
<?= Html::a(Yii::t('app', 'Add'), Url::toRoute('edit'), ['class' => 'btn btn-default']) ?><hr>
<?= $this->render('/shared/flash') ?>

<?php $form = ActiveForm::begin(['options' => ['class' => 'ajax-form']]); ?>

  <!-- title -->
  <?= $form->field($model, 'title')->textInput() ?>

  <hr>
  <div class="form-controls">
    <div class="form-group pull-left">
      <?= Html::submitButton(Yii::t('app', 'Save'), [
          'name' => 'submit',
          'class' => 'btn btn-info',
          'data-loading-text' => Yii::t('app', 'Please wait…')
      ]) ?>
      <?php if ($model->primaryKey) : ?>
      <?= Html::a(
          Yii::t('app', 'Delete'),
          ['delete', 'id' => $model->primaryKey, 'reload' => true],
          [
              'title' => Yii::t('app', 'Delete'),
              'class' => 'btn btn-danger',
              'data-pjax' => 1,
              'data-method' => 'post',
              'data-confirm' => Yii::t('app', 'Are you sure you want to delete this record?')
          ]
      ); ?>
      <?php endif?>
    </div>
  </div>

<?php ActiveForm::end(); ?>
