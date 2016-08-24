<?php

namespace app\helpers;

use yii\helpers\Html;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

class Util
{
    /**
     * Convert TZ
     *
     * @param string $date
     * @param string $fromTimeZone
     * @param string $toTimeZone
     * @param string $format
     * @return string
     */
    public static function convertTz($date, $fromTimeZone, $toTimeZone, $format = 'Y-m-d H:i:s')
    {
        $date = new \DateTime($date, new \DateTimeZone($fromTimeZone));
        $date->setTimeZone(new \DateTimeZone($toTimeZone));

        return $date->format($format);
    }

    /**
     * Clear text
     * Can use for meta tags
     *
     * @param string $text
     * @return string
     */
    public static function clearText($text)
    {
        $text = str_replace('"', '“', $text);
        return Html::encode(html_entity_decode(strip_tags($text)));
    }

    /**
     * Make page title
     *
     * @param string $title
     * @param string $appendToEnd
     * @return string
     */
    public static function makePageTitle($title = '', $appendToEnd = '')
    {
        $title = $title ? self::clearText($title) . ' / ' : '';
        return $title . $appendToEnd;
    }

   /**
    * Collect model errors
    *
    * @param Model $model the model to be validated
    * @return array the error message array indexed by the attribute IDs.
    */
    public static function collectModelErrors($model)
    {
        $result = [];
        /* @var $model Model */
        $models = [$model];
        foreach ($models as $model) {
            foreach ($model->getErrors() as $attribute => $errors) {
                $result[Html::getInputId($model, $attribute)] = $errors;
            }
        }
        return $result;
    }

    /**
     * Create manually UploadedFile instance by file path
     *
     * @param string $name the original name of the file being uploaded
     * @param string $file file path
     * @return UploadedFile
     */
    public static function makeUploadedFile($name, $file)
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'app');
        file_put_contents($tmpFile, file_get_contents($file));

        $_FILES[$name] = [
            'name' => pathinfo($file, PATHINFO_BASENAME),
            'tmp_name' => $tmpFile,
            'type' => FileHelper::getMimeType($tmpFile),
            'size' => filesize($tmpFile),
            'error' => 0,
        ];

        return UploadedFile::getInstanceByName($name);
    }
}
