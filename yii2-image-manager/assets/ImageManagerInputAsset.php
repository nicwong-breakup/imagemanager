<?php
namespace noam148\imagemanager\assets;
use yii\web\AssetBundle;

/**
 * ImageManagerInputAsset.
 */
class ImageManagerInputAsset extends AssetBundle
{
    public $sourcePath = '@vendor/noam148/yii2-image-manager/assets/source';
    public $css = [
        'css/imagemanager.input.css',
    ];
    public $js = [
        'js/script.imagemanager.input.js',
    ];
    public $depends = [
		'yii\web\JqueryAsset',
    ];
    public function init()
    {
        parent::init();
        $this->publishOptions['forceCopy'] = true;
    }
}
