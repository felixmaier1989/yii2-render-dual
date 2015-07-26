Yii2-RenderDual
==========
Make your actions compatible for Ajax requests

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist felixmaier1989/yii2-renderdual "*"
```

or add

```
"felixmaier1989/yii2-renderdual": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by extending your controller:

```php
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

    public function actionAbout()
    {
        Yii::$app->session->setFlash('success', 'Welcome on my home page');
        $fruits = ['banana', 'apple', 'jackfruit', 'papaya'];
        return $this->renderDual('about', compact('fruits'), true);
    }
...
```

An Ajax call to your `site/about` action would then return

```
Array
(
    [flashes] => Array
        (
            [success] => Welcome on my home page
        )
    [params] => Array
        (
            [fruits] => Array
                (
                    [0] => banana
                    [1] => apple
                    [2] => jackfruit
                    [3] => papaya
                )

        )
    [rendered] => <h1>About</h1><p>I like banana, apple, jackfruit, papaya</p>
)
```
