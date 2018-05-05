<?php

namespace common\components;

use Yii;
use yii\base\Action;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use budyaga\cropper\Widget;
use yii\imagine\Image;

class UploadAction extends Action
{
    public $modelClass;
    public $uploadParam = 'file';
    public $maxSize = 2097152;
    public $extensions = 'jpeg, jpg, png, gif';
    public $width = 300;
    public $height = 300;
    public $outputFormat = 'medium';
 
    /**
     * Returns resize parameters
     * @param string $className model class name
     * @return array|false raw configuration or false if
     * it is unable to resolve
     */
    public function getResizeConfigByModelClassName($className)
    {
        $model = new $className;
        foreach ($model->getBehaviors() as $name => $behavior) {
            if ($behavior instanceOf PhotoBehavior) {
                return [
                    'storageBaseUrl' => $behavior->storageBaseUrl,
                    'storageBasePath' => $behavior->storageBasePath,
                    'formats' => $behavior->formats
                ];
            }
        }
        return false;
    }
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        Widget::registerTranslations();
        if (empty($this->modelClass)) {
            throw new InvalidConfigException(Yii::t('cropper', 'MISSING_MODEL_CLASS'));
        }
    }
    
    /**
     * @inheritdoc
     */
    public function run()
    {
        if (Yii::$app->request->isPost) {
            $file = UploadedFile::getInstanceByName($this->uploadParam);
            $model = new DynamicModel(compact($this->uploadParam));
            $model->addRule($this->uploadParam, 'image', [
                'maxSize' => $this->maxSize,
                'tooBig' => Yii::t('cropper', 'TOO_BIG_ERROR', ['size' => $this->maxSize / (1024 * 1024)]),
                'extensions' => explode(', ', $this->extensions),
                'wrongExtension' => Yii::t('cropper', 'EXTENSION_ERROR', ['formats' => $this->extensions])
            ])->validate();
            
            if ($model->hasErrors()) {
                $result = [
                    'error' => $model->getFirstError($this->uploadParam)
                ];
            } else {
                $model->{$this->uploadParam}->name = uniqid() . '.' . $model->{$this->uploadParam}->extension;
                $request = Yii::$app->request;
                
                try {
                    $sourceImg = Yii::$app->image->load($file->tempName);
                    $xScale = $sourceImg->width / $width;
                    $yScale = $sourceImg->height / $height;
                    $w = intval($request->post('w')) * $xScale;
                    $h = intval($request->post('h')) * $yScale;
                    $x = $request->post('x') * $xScale;
                    $y = $request->post('y') * $yScale;
                } catch (\Exception $e) {
                    Yii::error('Unable to resolve crop attributes because of error: ' . $e->getMessage());
                    return;
                }
                
                $image = Image::crop(
                    $file->tempName . $request->post('filename'),
                    $w, $h, [$x, $y]
                );
                
                $resizeConfig = $this->getResizeConfigByModelClassName($this->modelClass);
                if ($resizeConfig === false) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'error' => Yii::t('app', 'It is unable to resize source image.')
                    ];
                }
                
                $saveFileName = $this->resolveSaveFileName($file);
                $saveFileExtension = $this->resolveSaveFileExtension($file);
                $savePath = rtrim($resizeConfig['storageBasePath'], DIRECTORY_SEPARATOR) .
                    DIRECTORY_SEPARATOR . $saveFileName . '.' . $saveFileExtension;
                if ($image->save($savePath)) {
                    $outputFormats = [];
                    foreach ($resizeConfig['formats'] as $format => $config) {
                        list($width, $height) = $this->resolveThumbnailSize($image, $format, $config);
                        $img = Image::thumbnail($savePath, $width, $height);
                        $fileInfo = pathinfo($savePath);
                        $filePath = $fileInfo['dirname'] . DIRECTORY_SEPARATOR .
                            $saveFileName . '_' . $format . '.' . $saveFileExtension;
                        
                        $relPath = str_replace(
                            rtrim($resizeConfig['storageBasePath']) . '/', '', $filePath
                        );
                        if ($img->save($filePath)) {
                            $outputFormats[$format] = [
                                'path' => $relPath,
                                'size' => filesize($filePath)
                            ];
                        }
                    }
                    $resize = [
                        'name' => $file->name,
                        'path' => str_replace(
                            rtrim($resizeConfig['storageBasePath']) . '/', '', $savePath
                        ),
                        'size' => filesize($savePath),
                        'created_at' => time(),
                        'formats' => $outputFormats
                    ];
                    $result = [
                        'filelink' => rtrim($resizeConfig['storageBaseUrl'], '/') .
                            '/' . $saveFileName . '_' . $this->outputFormat .
                            '.' . $saveFileExtension,
                        'result' => $resize
                    ];
                } else {
                    $result = [
                        'error' => Yii::t('cropper', 'ERROR_CAN_NOT_UPLOAD_FILE')
                    ];
                }
            }
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $result;
        } else {
            throw new BadRequestHttpException(Yii::t('cropper', 'ONLY_POST_REQUEST'));
        }
    }
    
    /**
     * Resolves output file name by given uploaded file
     * @param UploadedFile $file uploaded file instance
     * @return string
     */
    protected function resolveSaveFileName(UploadedFile $file)
    {
        return md5(time() . '_' . $file->name);
    }
    
    /**
     * Resolves output file extension by given uploaded file
     * @param UploadedFile $file uploaded file instance
     * @return string
     */
    protected function resolveSaveFileExtension(UploadedFile $file)
    {
        return $file->extension;
    }
    
    /**
     * Resolves appropriate size of output thumbnail.
     * Proportions of original image should be saved.
     * @param Component $image  original image instance
     * @param string   $format the format name
     * @param array    $config configuration of the format
     * @return array recommended thumbnail size (width, height)
     */
    protected function resolveThumbnailSize($image, $format, $config)
    {
        $size = $image->getSize();
        $origWidth = $size->getWidth();
        $origHeight = $size->getHeight();
        if (isset($config['width']) && !isset($config['height'])) {
            if ($config['width'] < $origWidth) {
                $destWidth = $config['width'];
                $destHeight = $destWidth / ($origWidth / $origHeight);
            } else {
                $destWidth = $origWidth;
                $destHeight = $origHeight;
            }
        } else if (!isset($config['width']) && isset($config['height'])) {
            if ($config['height'] < $origHeight) {
                $destHeight = $config['height'];
                $destWidth = $destHeight / ($origHeight / $origWidth);
            } else {
                $destHeight = $origHeight;
                $destWidth = $origWidth;
            }
        } else if (isset($config['width']) && isset($config['height'])) {
            if ($config['width'] < $origWidth) {
                $destWidth = $config['width'];
            } else {
                $destWidth = $origWidth;
            }
            if ($config['height'] < $origHeight) {
                $destHeight = $config['height'];
            } else {
                $destHeight = $origHeight;
            }
        }
        return [$destWidth, $destHeight];
    }
}
