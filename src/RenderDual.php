<?php

namespace yii2renderdual;

use yii\base\Behavior;

/**
 * RenderDual provides a simple way to handle actions which should behave differently if called
 * in an Ajax request or not
 *
 * You only need to call $this->renderDual() instead of $this->render to make your Ajax request returning
 * rendered view, flash messages and additional data in a JSON response.
 * The rest of your action script stays untouched, just like processing a normal server request.
 *
 * ~~~
 * public function behaviors()
 * {
 *     return [
 *         \yii2renderdual\RenderDual::className(),
 *     ];
 * }
 * ~~~
 *
 */
class RenderDual extends Behavior
{
    /**
     * Renders a view for both Ajax and non-Ajax requests
     *
     * If Non-Ajax request, this method will behave just like \yii\web\Controller::render()
     * If Ajax-request, a JSON-serialized array will be returned, containing:
     * - string rendered The rendered view, without layout
     * - array flashes Flash messages previously set
     * - array params additional data
     *
     * @param string $view the view name.
     * @param array $params the parameters (name-value pairs) that should be made available in the view.
     * These parameters will not be available in the layout.
     * @param boolean|array $ajaxResponseData
     * - If true, view params will be in the Ajax response.
     * Be careful not to pass sensitive data.
     * E.g. you pass an ActiveRecord object to the view.
     * Your visitor can see the response of the Ajax request and would have access to the whole model structure
     * - If false, no view params
     * - If you set an array, it will be in the response
     * @return string|array the rendering result.
     * @throws InvalidArgumentException if $ajaxResponseData is not correctly set
     */
    public function renderDual($view, $params = null, $ajaxResponseData = false)
    {
        $this->__attachedToController();

        if (!\Yii::$app->request->getIsAjax())
            return $this->owner->render($view, $params);

        $this->owner->layout = false;
        $rendered = $this->owner->render($view, $params);

        $flashes = \Yii::$app->session->getAllFlashes(true);

        if ($ajaxResponseData === false) {
			$params = [];
        } elseif ($ajaxResponseData === true) {
			$params = $params;
        } elseif (is_array($ajaxResponseData)) {
			$params = $ajaxResponseData;
        } else {
            throw new \InvalidArgumentException('Parameter $ajaxResponseData only accepts values false|true|array');
        }

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return compact('flashes', 'params', 'rendered');
    }

    /**
     * Checks the behavior has been attached to a controller
     * @throws Exception
     */
    private function __attachedToController() {
        if (!is_subclass_of($this->owner, '\yii\web\Controller')) {
            throw new \Exception(sprintf(
                'RenderDual is restricted to controllers. Class %s should extend class %s',
                get_class($this->owner),
                '\yii\web\Controller'
            ));
        }
    }

}