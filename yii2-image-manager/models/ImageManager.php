<?php

namespace noam148\imagemanager\models;

use noam148\imagemanager\Module;
use Yii;
use yii\db\Expression;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "ImageManager".
 *
 * @property integer $id
 * @property string $fileName
 * @property string $fileHash
 * @property string $created
 * @property string $modified
 * @property string $createdBy
 * @property string $modifiedBy
 */
class ImageManager extends \yii\db\ActiveRecord { 

	/**
	 * Set Created date to now
	 */
	public function behaviors() {
	    $aBehaviors = [];

	    // Add the time stamp behavior
        $aBehaviors[] = [
            'class' => TimestampBehavior::className(),
			'attributes' => [
				\yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created'],
			],
            'value' => new Expression('NOW()')
        ];

        // Get the imagemanager module from the application
        $moduleImageManager = Yii::$app->getModule('imagemanager');
        /* @var $moduleImageManager Module */
        // if ($moduleImageManager !== null) {
        //     // Module has been loaded
        //     if ($moduleImageManager->setBlameableBehavior) {
        //         // Module has blame able behavior
        //         $aBehaviors[] = [
        //             'class' => BlameableBehavior::className(),
        //             'createdByAttribute' => 'createdBy',
        //         ];
        //     }
        // }

		return $aBehaviors;
	}

	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return 'media';
	}

    /**
     * Get the DB component that the model uses
     * This function will throw error if object could not be found
     * The DB connection defaults to DB
     * @return null|object
     */
	public static function getDb() {
        // Get the image manager object
        $oImageManager = Yii::$app->get('imagemanager', false);

        if($oImageManager === null) {
            // The image manager object has not been set
            // The normal DB object will be returned, error will be thrown if not found
            return Yii::$app->get('db');
        }

        // The image manager component has been loaded, the DB component that has been entered will be loaded
        // By default this is the Yii::$app->db connection, the user can specify any other connection if needed
        return Yii::$app->get($oImageManager->databaseComponent);
    }

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['url'], 'required'],
			[['created'], 'safe'],
            [['id'], 'integer'],
            [['fileName', 'type', 'url'], 'string']
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'id' => Yii::t('imagemanager', 'ID'),
			'fileName' => 'File Name',
			'type' => 'File Type',
			'url' => 'Url',
			'created' => Yii::t('imagemanager', 'Created'),
		];
	}

	// public function afterDelete()
    // {
    //     parent::afterDelete();

    //     // Check if file exists
    //     if (file_exists($this->getImagePathPrivate())) {
    //         unlink($this->getImagePathPrivate());
    //     }
    // }

    /**
	 * Get image path private
	 * @return string|null If image file exists the path to the image, if file does not exists null
	 */
	public function getImagePathPrivate() {
		//set default return
		$return = null;
		//set media path
		$sMediaPath = \Yii::$app->imagemanager->mediaPath;
		$sFileExtension = pathinfo($this->fileName, PATHINFO_EXTENSION);
		//get image file path
		$sImageFilePath = $sMediaPath . '/' . $this->id . '_' . $this->fileHash . '.' . $sFileExtension;
		//check file exists
		if (file_exists($sImageFilePath)) {
			$return = $sImageFilePath;
		}
		return $return;
	}

	/**
	 * Get image data dimension/size
	 * @return array The image sizes
	 */
	public function getImageDetails() {
		//set default return
		$return = ['width' => 0, 'height' => 0, 'size' => 0];
		//set media path
		$sMediaPath = $this->url;
		$sFileExtension = pathinfo($this->fileName, PATHINFO_EXTENSION);
		//get image file path
		$sImageFilePath = $sMediaPath;
		//check file exists
		if ($file = @fopen($sImageFilePath, 'r')) {
			list($width, $height) = getimagesize($sImageFilePath);
			$return['width'] = isset($width) ? $width : 0;
			$return['height'] = isset($height) ? $height : 0;
			$return['size'] = $file;
			// $return['size'] = Yii::$app->formatter->asShortSize(filesize($sImageFilePath), 2);
		}
		return $return;
	}

}
