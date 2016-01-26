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