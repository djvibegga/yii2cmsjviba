<?php

namespace common\helpers;

use yii\base\Model;

class FormHelper
{
    /**
     * Returns list of model errors which are appropriated to given confition
     * @param Model  $form     source model
     * @param string $prefix   error attribute prefix
     * @param string $langName the attribute language
     * @return array errors list
     */
    public static function getCustomErrors(Model $form, $prefix, $langName)
    {
        $ret = [];
        foreach ($form->getErrors() as $attr => $errors) {
            if (strpos($attr, $prefix . '[' . $langName . ']') !== false) {
                $infoAttr = str_replace($prefix . '[' . $langName . '][', '', $attr);
                $infoAttr = substr($infoAttr, 0, strlen($infoAttr) - 1);
                $ret[$infoAttr] = $errors;
            }
        }
        return $ret;
    }
}