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

namespace shaqman\addition\db;

use Yii;
use yii\db\Migration;
use yii\rbac\Item;
use yii\rbac\ManagerInterface;
use yii\rbac\Permission;
use yii\rbac\Role;

/**
 * Description of MigrationRbac
 *
 * @author syakur
 */
class MigrationRbac extends Migration {

    private $_manager;

    /**
     *
     * @return ManagerInterface
     */
    private function manager() {
        if (!$this->_manager) {
            $this->_manager = Yii::$app->authManager;
        }

        return $this->_manager;
    }

    /**
     * @return Permission
     */
    public function createPermission($name) {
        $permission = $this->manager()->createPermission($name);
        $this->manager()->add($permission);
        return $permission;
    }

    public function getPermission($name) {
        return $this->manager()->getPermission($name) ?: $this->createPermission($name);
    }

    /**
     *
     * @param string $name
     * @return Role
     */
    public function getRole($name) {
        return $this->manager()->getRole($name);
    }

    /**
     *
     * @param string $name
     * @return Role
     */
    public function createRole($name) {
        return $this->manager()->createRole($name);
    }

    /**
     *
     * @param Item $parent
     * @param Item $child
     * @return bool
     */
    public function assignTo($parent, $child) {
        return $this->manager()->addChild($parent, $child);
    }

    /**
     *
     * @param Item $parent
     * @param Item $child
     * @return bool
     */
    public function assignToItDept($child) {
        return $this->assignTo($this->manager()->getRole('information-technology'), $child);
    }

    /**
     * @return Permission
     */
    public function createPermissionAndAssign($name, $parent = null) {
        $permission = $this->createPermission($name);
        if ($parent) {
            $this->assignTo($parent, $permission);
        } else {
            $this->assignToItDept($permission);
        }

        return $permission;
    }

    public function addChild($parent, $child) {
        return $this->manager()->addChild($parent, $child);
    }

}
