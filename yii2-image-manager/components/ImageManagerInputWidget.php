<?php

namespace noam148\imagemanager\components;

use Yii;
use yii\widgets\InputWidget;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use noam148\imagemanager\models\ImageManager;
use noam148\imagemanager\assets\ImageManagerInputAsset;

class ImageManagerInputWidget extends InputWidget {

    /**
     * @var null|integer The aspect ratio the image needs to be cropped in (optional)
     */
    public $aspectRatio = null; //option info: https://github.com/fengyuanchen/cropper/#aspectratio

    /**
     * @var int Define the viewMode of the cropper
     */
    public $cropViewMode = 1; //option info: https://github.com/fengyuanchen/cropper/#viewmode

    /**
     * @var bool Show a preview of the image under the input
     */
    public $showPreview = true;

    /**
     * @var bool Show a confirmation message when de-linking a image from the input
     */
    public $showDeletePickedImageConfirm = false;

    /**
     * @inheritdoc
     */
    public function init() {
        parent::init();
        //set language
        if (!isset(Yii::$app->i18n->translations['imagemanager'])) {
            Yii::$app->i18n->translations['imagemanager'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'zh-HK',
                'basePath' => '@vendor/noam148/yii2-image-manager/messages',
                'fileMap' => [
                    'imagemanager' => 'imagemanager.php',
                ]
            ];
        }
    }

    /**
     * @inheritdoc
     */
    public function run() {
        //default
        $ImageManager_id = null;
        $mImageManager = null;
        $sFieldId = null;
        //start input group
        $field = "<div class='image-manager-input'>";
        $field .= "<div class='input-group file-group'>";
        $multi = isset($this->options['multiple']) && $this->options['multiple'] == '1' ? true : false; 
        //set input fields
        if ($this->hasModel()) {
            $list = [];
            $values = "";
            //get field id
            $sFieldId = Html::getInputId($this->model, $this->attribute);
            $sFieldNameId = $sFieldId . "_name";
            //get attribute name
            $sFieldAttributeName = Html::getAttributeName($this->attribute);
            //get filename from selected file
            $ImageManager_id = $this->model->{$sFieldAttributeName};
            $ImageManager_fileName = "";
            $fileNameArr = [];
            
            if($multi){
                $list = [];
                $mImageManager = [];
                if(isset($this->options['list']) && !empty($this->options['list'][0]))
                {
                    $list = $this->options['list'];
                    $mImageManager = ImageManager::find()->where(['id' => $list])->orderBy([new \yii\db\Expression('FIELD (id, '.implode(',',$list).')')])->all();
                }
                // $mImageManager = count($list) > 0 ? ImageManager::find()->where(['id' => $list])->orderBy([new \yii\db\Expression('FIELD (id, '.implode(',',$list).')')])->all() : [];
                if(count($mImageManager) > 0){
                    $values = implode(',',$list);
                    foreach($mImageManager as $key => $m){
                        $fileNameArr[] = $m->fileName;
                        $ImageManager_fileName .= count($mImageManager) != $key + 1 ? $m->fileName.',' : $m->fileName;
                    }
                }
            }
            else{
                $mImageManager = ImageManager::findOne($ImageManager_id);
                if ($mImageManager !== null) {
                    $values = $mImageManager->id;
                    $ImageManager_fileName = $mImageManager->fileName;
                }
            }
            //create field
            $field .= Html::textInput($this->attribute, $ImageManager_fileName, ['class' => 'form-control', 'id' => $sFieldNameId, 'readonly' => true]);
            $field .= Html::activeHiddenInput($this->model, $this->attribute, ['value' => $values]);
        } else {
            $field .= Html::textInput($this->name . "_name", null, ['readonly' => true]);
            $field .= Html::hiddenInput($this->name, ['value' => $this->value], $this->options);
        }
        //end input group
        $sHideClass = $ImageManager_id === null ? 'hide' : '';
        $field .= "<a class='input-group-addon btn btn-primary open-modal-imagemanager' data-multiple='".$multi."' data-input-id='" . $sFieldId . "'>";
        $field .= "<i class='glyphicon glyphicon-folder-open' aria-hidden='true'></i></a>";
        $field .= "</div>";

        //show preview if is true
        if ($this->showPreview == true) {
            $sHideClass = ($mImageManager == null) ? "hide" : "";
            $classes = $multi ? 'sortable' : '';
            $field .= '<div class="image-wrapper ' . $sHideClass . ' '.$classes.'" >';
            if($multi){
                if(count($mImageManager) > 0){
                    foreach($mImageManager as $key => $l){
                        $sImageSource = $l->url;
                        if(isset($l->type) && $l->type == 'video') 
                        {
                            $html = '<img id="' . $sFieldId . '_image" alt="Thumbnail" class="img-responsive img-preview hide">
                                    <video id="'. $sFieldId .'_video" class="video-preview">
                                        <source type="video/mp4" src="'.$l->url.'" alt="'.$l->fileName.'">
                                    </video>';
                        }
                        else{
                            $html = '<img id="' . $sFieldId . '_image" alt="Thumbnail" class="img-responsive img-preview" src="' . $sImageSource . '">';
                            $html .= '<video id="'. $sFieldId .'_video" class="video-preview hide">
                                        <source type="video/mp4" alt="'.$l->fileName.'">
                                    </video>';
                        }
                        $field .= '<div class="image-child">'.$html;
                        $field .= "<a class='btn btn-primary delete-selected-image " . $sHideClass . "' data-image-id='".$l->id."' data-image-name='".$fileNameArr[$key]."' data-input-id='" . $sFieldId . "' data-show-delete-confirm='" . ($this->showDeletePickedImageConfirm ? "true" : "false") . "'><i class='glyphicon glyphicon-remove' aria-hidden='true'></i></a>";
                        $field .= '</div>';
                    }
                }
                else{
                    $field .= '<div class="image-child"><img id="' . $sFieldId . '_image" alt="Thumbnail" class="img-responsive img-preview" src=""><video id="'.$sFieldId.'_video" class="video-preview"><source type="video/mp4"></video>';
                    $field .= "<a class='btn btn-primary delete-selected-image " . $sHideClass . "' data-image-id='' data-image-name='' data-input-id='" . $sFieldId . "' data-show-delete-confirm='" . ($this->showDeletePickedImageConfirm ? "true" : "false") . "'><i class='glyphicon glyphicon-remove' aria-hidden='true'></i></a>";
                    $field .= '</div>';
                }
            }
            else{                
                $sImageSource = isset($mImageManager->id) ? $mImageManager->url : "";
                
                if(isset($mImageManager->type) && $mImageManager->type == 'video')
                $html = '<img id"'.$sFieldId.'_image" alt="Thumbnail" class="img-responsive img-preview hide"><video id="'.$sFieldId.'_video" class="video-preview"><source type="video/mp4" src="'.$sImageSource.'"></video>';
                else
                $html = '<img id="' . $sFieldId . '_image" alt="Thumbnail" class="img-responsive img-preview" src="' . $sImageSource . '"><video id="'.$sFieldId.'_video" class="video-preview hide"><source type="video/mp4"></video>';
                
                $field .= '<div class="image-child">'.$html;
                $field .= "<a class='btn btn-primary delete-selected-image " . $sHideClass . "' data-image-id='".$ImageManager_id."' data-input-id='" . $sFieldId . "' data-show-delete-confirm='" . ($this->showDeletePickedImageConfirm ? "true" : "false") . "'><i class='glyphicon glyphicon-remove' aria-hidden='true'></i></a>";
                $field .= '</div>';
            }

            $field .= '</div>';
        }

        //close image-manager-input div
        $field .= "</div>";

        echo $field;

        $this->registerClientScript();
    }

    /**
     * Registers js Input
     */
    public function registerClientScript() {
        $view = $this->getView();
        ImageManagerInputAsset::register($view);
        $manageMode = isset($this->options['manageMode']) ? $this->options['manageMode'] : 'image'; 

        //set baseUrl from image manager
        $sBaseUrl = Url::to(['/imagemanager/manager']);
        //set base url
        $view->registerJs("imageManagerInput.baseUrl = '" . $sBaseUrl . "';");
        $view->registerJs("imageManagerInput.manageMode = '" . $manageMode . "';");
        $view->registerJs("imageManagerInput.message = " . Json::encode([
                    'imageManager' => Yii::t('imagemanager','Media'),
                    'detachWarningMessage' => Yii::t('imagemanager', 'Are you sure you want to detach the image?'),
                ]) . ";");
    }

}
