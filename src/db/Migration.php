<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace shaqman\addition\db;

use shaqman\addition\helpers\VarDumper;
use shaqman\addition\traits\ConfigHelper;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use yii\base\InvalidValueException;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\db\Migration as YiiMigration;
use yii\helpers\ArrayHelper;
use const YII_ENV;

/**
 * Description of Migration
 *
 * @author syakur
 */
class Migration extends YiiMigration {

    use ConfigHelper;

    private $includeAuditableColumns = true;
    private $includeSoftDeleteColumns = true;

    private function generateShortName($name) {
        return strlen($name) > 60 ? substr($name, 0, 60) . substr(md5($name), 0, 3) : $name;
    }

    private function generateFkName($table, $column, $refTable) {
        return $this->generateShortName("fk-$table-$refTable-$column-id");
    }

    private function generateIdxName($table, $column, $refTable) {
        return $this->generateShortName("idx-$table-$column");
    }

    protected function auditableColumns() {
        return [
            'created_by' => $this->integer(11)->unsigned()->notNull(),
            'created_at' => $this->integer(11)->notNull(),
            'updated_by' => $this->integer(11)->unsigned(),
            'updated_at' => $this->integer(11),
        ];
    }

    protected function softDeleteColumns() {
        return [
            'row_status' => $this->rowStatusColumnType()
        ];
    }

    public function executeYiiCommand(string $cmd) {
        $cmd = 'php ' . (YII_ENV == 'test' ? 'yii_test' : 'yii') . ' ' . $cmd;

        $process = new Process($cmd);
        $process->setTimeout(60 * 1); //1 minutes
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        echo $process->getOutput();
    }

    public function createSimpleRelationship($table, $column, $refTable) {
        $this->createIndex($this->generateIdxName($table, $column, $refTable), $table, $column);
        $this->addForeignKey($this->generateFkName($table, $column, $refTable), $table, $column, $refTable, "id");
    }

    public function primaryKey($length = null) {
        return parent::primaryKey($length)->unsigned();
    }

    public function dropSimpleRelationship($table, $column, $refTable) {
        $this->dropForeignKey($this->generateFkName($table, $column, $refTable), $table);
        $this->dropIndex($this->generateIdxName($table, $column, $refTable), $table);
    }

    public function createTable($table, $columns, $options = null) {
        if ($this->includeAuditableColumns) {
            $columns = ArrayHelper::merge($columns, $this->auditableColumns());
        }

        if ($this->includeSoftDeleteColumns) {
            $columns = ArrayHelper::merge($columns, $this->softDeleteColumns());
        }

        parent::createTable($table, $columns, $options);
    }

    public function addAuditableColumns($table) {
        foreach ($this->auditableColumns() as $column=>$type) {
            $this->addColumn($table, $column,$type);
        }
    }

    public function addSoftDeleteColumns($table) {
        foreach ($this->softDeleteColumns() as $column=>$type) {
            $this->addColumn($table, $column,$type);
        }
    }

    public function auditableColumn($include = true) {
        $this->includeAuditableColumns = $include;
        return $this;
    }

    public function softDeleteColumn($include = true) {
        $this->includeSoftDeleteColumns = $include;
        return $this;
    }

    public function rowStatusColumnType() {
        return $this->smallInteger()->notNull()->defaultValue(0)->unsigned();
    }

    public function currency() {
        return $this->decimal(30, 4);
    }

    public function insertAfterPosition($position, ActiveRecord $record) {
        if (!in_array('position', $record->attributes())) {
            throw new InvalidValueException('Only activerecord with a position attribute could be used.');
        }

        if (is_array($position)) {
            reset($position);
            $position = $record->findOne([key($position) => current($position)])->position;
        }

        if (!is_int($position)) {
            throw new InvalidValueException('Invalid position value. Only array and integer values are allowed');
        }

        $currentRecords = $record->find()->where([">", "position", $position])->all();
        foreach ($currentRecords as $currentRecord) {
            $currentRecord->position = ++$currentRecord->position;
            if (!$currentRecord->save()) {
                throw new Exception(VarDumper::dumpAsString($currentRecord->getErrors()));
            }
        }

        $record->position = $position + 1;
        if (!$record->save()) {
            throw new Exception(VarDumper::dumpAsString($record->getErrors()));
        }

        return $record;
    }

}
