<?php

/*
 * The MIT License
 *
 * Copyright 2017 syakur.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace shaqman\addition\helpers;

use common\models\Employee;
use common\models\options\Department;
use mdm\admin\models\Route;
use shaqman\addition\rules\DepartmentRule;
use shaqman\addition\rules\HeadDepartmentRule;
use Yii;
use yii\base\BaseObject;
use yii\helpers\Inflector;
use yii\web\Application;

/**
 * Description of RbacHelper
 *
 * @author syakur
 */
class RbacHelper extends BaseObject {

    private $routeHelper;
    private $manager;

    public function init() {
        parent::init();

        $this->routeHelper = new Route();
        $this->manager = Yii::$app->authManager;
    }

    public function defaultRouteMapping() {
        return ['information-technology' => ['/*'],
            'sales' => [
                '/action-plan/*',
                '/proposal/*',
                '/prospect/*',
                '/sales-quotation-request/*'
            ],
        ];
    }

    public function applyAllRoutesAsPermission() {
        foreach ($this->getBackendRoutes() as $route) {
            $permission = $this->manager->getPermission($route);

            if (!$permission) {
                $this->manager->add($this->manager->createPermission($route));
            }
        }
    }

    public function setInitData() {
        // Since this is an init, lets start from a clean state
        $this->manager->removeAll();

        $deptRule = new DepartmentRule();
        $this->manager->add($deptRule);

        $headDeptRule = new HeadDepartmentRule();
        $this->manager->add($headDeptRule);


        $departments = Department::find()->all();
        foreach ($departments as $department) {
            $deptRole = $this->manager->createRole(Inflector::slug($department->name));
            $deptRole->ruleName = $deptRule->name;
            $this->manager->add($deptRole);

            $headDeptRole = $this->manager->createRole('head-' . Inflector::slug($department->name));
            $headDeptRole->ruleName = $headDeptRule->name;
            $this->manager->add($headDeptRole);
            $this->manager->addChild($headDeptRole, $deptRole);
        }

        foreach ($this->getBackendRoutes() as $route) {
            $this->manager->add($this->manager->createPermission($route));
        }
        foreach ($this->defaultRouteMapping() as $role => $routeMapping) {
            $parent = $this->manager->getRole($role);
            foreach ($routeMapping as $route) {
                $child = $this->manager->getPermission($route);
                $this->manager->addChild($parent, $child);
            }
        }

        $employees = Employee::find()->all();
        foreach ($employees as $employee) {
            $this->manager->assign($this->manager->getRole(($employee->as_head == 1 ? 'head-' : '') . Inflector::slug($employee->department->name)), $employee->user_id);
        }
    }

    private function getBackendRoutes() {
        $baseAppDir = __DIR__ . '/../../../';
        $originalApp = Yii::$app;
        $config = ArrayHelper::merge(
                        require($baseAppDir . 'common/config/main.php'), require($baseAppDir . 'common/config/main-local.php'), require($baseAppDir . 'frontend/web/backend/config/main.php'), require($baseAppDir . 'frontend/web/backend/config/main-local.php')
        );

        $routes = $this->routeHelper->getAppRoutes(new Application($config));

        Yii::$app = $originalApp;
        return $routes;
    }

}
