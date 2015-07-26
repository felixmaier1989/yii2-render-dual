<?php

namespace yii2renderdual;

use Yii;
use yiiunit\TestCase; // @todo Find a way to fetch this class. Seems like its not included in any package
use yii\web\Request;
use yii\web\Controller;
use yii\db\ActiveRecord;
use RenderDual;

/**
 * Unit test for [[\yii2renderdual\RenderDual]].
 * @see RenderDual
 *
 * @group behaviors
 */
class RenderDualTest extends TestCase
{

    /**
     * Mocks a Yii web application
     * @param boolean $ajax
     */
    protected function _mockApplication($ajax) {
        $this->mockWebApplication([
            'components' => [
                'session',
                'request' => [
                    'class' => $ajax ?
                        '\yii2renderdual\AjaxRequest' :
                        '\yii2renderdual\NonAjaxRequest',
                ],
            ],
        ], '\yii\web\Application');
    }

    /**
     * @test
     */
    public function testRenderDualNotController()
    {
        $controller = new \yii\db\ActiveRecord();
        $controller->attachBehavior('RenderDual', new \yii2renderdual\RenderDual);
        $this->setExpectedException('Exception');
        $render = $controller->renderDual('view', ['foo' => 'bar'], 'foo bar');
    }

    /**
     * @test
     */
    public function testRenderDualAjaxDataException()
    {
        $this->_mockApplication(true);

        $controller = $this->getMock('\yii\web\Controller', ['render'], ['controllerTest', 'moduleTest']);
        $controller->attachBehavior('RenderDual', new \yii2renderdual\RenderDual);
        $controller
            ->expects($this->once())
            ->method('render')
            ->with(
                'view',
                ['foo' => 'bar']
            )
            ->will($this->returnValue('<h1>Output</h1>'));

        $this->setExpectedException('InvalidArgumentException');

        $render = $controller->renderDual('view', ['foo' => 'bar'], 'foo bar');
    }

    /**
     * @test
     */
    public function testRenderDualAjaxDataArray()
    {
        $this->_mockApplication(true);

        $controller = $this->getMock('\yii\web\Controller', ['render'], ['controllerTest', 'moduleTest']);
        $controller->attachBehavior('RenderDual', new \yii2renderdual\RenderDual);
        $controller
            ->expects($this->once())
            ->method('render')
            ->with(
                'view',
                ['foo' => 'bar']
            )
            ->will($this->returnValue('<h1>Output</h1>'));

        $render = $controller->renderDual('view', ['foo' => 'bar'], ['alpha' => 'bravo']);

        $this->assertEquals(['alpha' => 'bravo'], $render['params']);
    }

    /**
     * @test
     */
    public function testRenderDualAjaxDataTrue()
    {
        $this->_mockApplication(true);

        $controller = $this->getMock('\yii\web\Controller', ['render'], ['controllerTest', 'moduleTest']);
        $controller->attachBehavior('RenderDual', new \yii2renderdual\RenderDual);
        $controller
            ->expects($this->once())
            ->method('render')
            ->with(
                'view',
                ['foo' => 'bar']
            )
            ->will($this->returnValue('<h1>Output</h1>'));

        $render = $controller->renderDual('view', ['foo' => 'bar'], true);

        $this->assertEquals(['foo' => 'bar'], $render['params']);
    }

    /**
     * @test
     */
    public function testRenderDualAjaxDataFalse()
    {
        $this->_mockApplication(true);

        $controller = $this->getMock('\yii\web\Controller', ['render'], ['controllerTest', 'moduleTest']);
        $controller->attachBehavior('RenderDual', new \yii2renderdual\RenderDual);
        $controller
            ->expects($this->once())
            ->method('render')
            ->with(
                'view',
                ['foo' => 'bar']
            )
            ->will($this->returnValue('<h1>Output</h1>'));

        $render = $controller->renderDual('view', ['foo' => 'bar'], false);

        $this->assertEmpty($render['params']);
    }

    /**
     * @test
     */
    public function testRenderDualAjax()
    {
        $this->_mockApplication(true);

        $controller = $this->getMock('\yii\web\Controller', ['render'], ['controllerTest', 'moduleTest']);
        $controller->attachBehavior('RenderDual', new \yii2renderdual\RenderDual);
        $controller
            ->expects($this->once())
            ->method('render')
            ->with(
                'view',
                ['foo' => 'bar']
            )
            ->will($this->returnValue('<h1>Output</h1>'));

        \Yii::$app->session->setFlash('error', 'An error has occured');
        \Yii::$app->session->setFlash('success', 'But everything is fine');

        $render = $controller->renderDual('view', ['foo' => 'bar']);

        $this->assertEquals(\yii\web\Response::FORMAT_JSON, \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON);
        $this->assertEquals('<h1>Output</h1>', $render['rendered']);
        $this->assertEquals('An error has occured', $render['flashes']['error']);
        $this->assertEquals('But everything is fine', $render['flashes']['success']);
    }

    /**
     * \yii\web\Controller::findLayoutFile() should return false
     * @test
     */
    public function testRenderDualAjaxLayout()
    {
        $this->_mockApplication(true);

        $view = $this->getMock('\yii\base\View', ['render']);
        $view
            ->expects($this->once())
            ->method('render')
            ->will($this->returnValue('<h1>Output</h1>'));

        $controller = $this->getMock('\yii\web\Controller', ['findLayoutFile', 'getView'], ['controllerTest', 'moduleTest']);
        $controller->attachBehavior('RenderDual', new \yii2renderdual\RenderDual);
        $controller
            ->expects($this->any())
            ->method('getView')
            ->will($this->returnValue($view));
        $controller
            ->expects($this->once())
            ->method('findLayoutFile')
            ->will($this->returnValue(false));

        $render = $controller->renderDual('view', ['foo' => 'bar']);
    }

    /**
     * \yii\web\Controller::findLayoutFile() should return a non empty value
     * @test
     */
    public function testRenderDualNonAjaxLayout()
    {
        $this->_mockApplication(false);

        $view = $this->getMock('\yii\base\View', ['render', 'renderFile']);
        $view
            ->expects($this->once())
            ->method('render')
            ->will($this->returnValue('<h1>Output</h1>'));
        $view
            ->expects($this->once())
            ->method('renderFile')
            ->will($this->returnValue('<h1>Layout</h1>'));

        $controller = $this->getMock('\yii\web\Controller', ['findLayoutFile', 'getView'], ['controllerTest', 'moduleTest']);
        $controller->attachBehavior('RenderDual', new \yii2renderdual\RenderDual);
        $controller
            ->expects($this->any())
            ->method('getView')
            ->will($this->returnValue($view));
        $controller
            ->expects($this->once())
            ->method('findLayoutFile')
            ->will($this->returnCallback(function($e){return (bool) $e;}));

        $render = $controller->renderDual('view', ['foo' => 'bar']);
    }

    /**
     * @test
     */
    public function testRenderDualNonAjax()
    {
        $this->_mockApplication(false);

        $controller = $this->getMock('\yii\web\Controller', ['render'], ['controllerTest', 'moduleTest']);
        $controller->attachBehavior('RenderDual', new \yii2renderdual\RenderDual);
        $controller
            ->expects($this->once())
            ->method('render')
            ->with(
                'view',
                ['foo' => 'bar']
            )
            ->will($this->returnValue('<h1>Output</h1>'));

        $render = $controller->renderDual('view', ['foo' => 'bar']);

        $this->assertEquals('<h1>Output</h1>', $render);
    }
}

class AjaxRequest extends \yii\web\Request {
    public function getIsAjax()
    {
        return true;
    }
}

class NonAjaxRequest extends \yii\web\Request {
    public function getIsAjax()
    {
        return false;
    }
}