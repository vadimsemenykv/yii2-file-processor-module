Yii2 file processor module
==========================
Extension to upload and store files

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist vadymsemeniuk/yii2-file-processor-module "*"
```

or add

```
"vadymsemeniuk/yii2-file-processor-module": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
./yii migrate --migrationPath=@vendor/metalguardian/yii2-file-processor-module/src/migrations
```

UploadBehavior
--------------

add this to behaviors of model

```
'upload_image_id' => [
                'class' => \metalguardian\fileProcessor\behaviors\UploadBehavior::className(),
                'attribute' => 'image_id',
                'required' => false,
                'image' => true,
   ```

DeleteBehavior
--------------

add this to behaviors of model

for one attribute

```
'delete_image_id' => [
                'class' => \backend\components\DeleteBehavior::className(),
                'attribute' => 'image_id',
            ],
```

for array of attributes

```
'delete_media_files' => [
                'class' => \backend\components\DeleteBehavior::className(),
                'attribute' => ['image_id', 'video_id'],
            ],
```

UploadDeleteBehavior
--------------

add this to behaviors of model

```
'upload_delete_image_id' => [
                'class' => \metalguardian\fileProcessor\behaviors\UploadDeleteBehavior::className(),
                'attribute' => 'image_id',
                'required' => false,
                'image' => true,
   ```

PNG Compression
------------------

To switch-on compression for png-images install https://pngquant.org.
Then add to console controller action ```ImageCompressor::compressPngThumbs($path);```,
where $path is path to thumbnails folder and setup cron task from user which creates files,
for example www-data(crontab -u www-data -e).


Adding watermarks to thumbnails
-------------------
To add watermark while creating FPM::ACTION_ADAPTIVE_THUMBNAIL, FPM::ACTION_THUMBNAIL or FPM::ACTION_CANVAS_THUMBNAIL config must be like this:
````
'sliderThumb' => [
                        'action' => FPM::ACTION_THUMBNAIL,
                        'width' => 330,
                        'height' => 330,
                        'watermark' => [
                            'fileName' => $wmarkPath,
                            'point' => [
                                'x' => 0,
                                'y' => 0,
                            ],
                            'size' => [
                                'width' => 330,
                                'height' => 330,
                            ]
                        ],
                    ],
````
where 'point' (coordinates of point on originap image where to place watermark) and 'size' (size of watermark's thumbnail if it larger then original image) are optional, only 'fileName' is required.
If watermark size is larger then original image - it will not be pasted into original image. To fix this - module will create thumbnail of watermark with original image size to fit it.