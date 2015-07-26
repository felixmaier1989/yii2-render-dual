Yii2-RenderDual
==========
Make your actions compatible for Ajax requests

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist felixmaier1989/yii2-render-dual "*"
```

or add

```
"felixmaier1989/yii2-renderdual": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
<?php

...
use yii2renderdual\RenderDual;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            ...
            \yii2renderdual\RenderDual::className()
        ];
    }

    public function actionIndex()
    {
        \Yii::$app->session->setFlash('success', 'Welcome on this page');

        return $this->renderDual('index', [
            'foo' => 'bar'
        ]);
    }
```
