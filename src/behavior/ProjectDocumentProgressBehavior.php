<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace shaqman\addition\behavior;

/**
 * Description of ProjectDocumentProgressBehavior
 *
 * @author syakur
 */
class ProjectDocumentProgressBehavior extends \yii\base\Behavior {

    private $populated = false;
    private $groupTotal = 0;
    private $groupDone = 0;
    private $colors = ['progress-bar-success', 'progress-bar-info', 'progress-bar-warning', 'progress-bar-danger'];
    private $bars = [];

    public function init() {
        parent::init();
    }

    public function getColor() {
        $color = next($this->colors);
        if ($color === false) {
            $color = reset($this->colors);
        }

        return $color;
    }

    private function populateData() {
        $this->groupTotal = count($this->owner->getModels());
        foreach ($this->owner->getModels() as $key => $value) {
            $percent = 0;
            if ($value->getLatestProjectRequiredDocumentRevs() !== null) {
                $this->groupDone++;
                $percent = 1 / $this->groupTotal * 100;
            }

            $this->bars[] = ['percent' => $percent, 'label' => $value->projectDocument->name, 'options' => ['class' => $this->getColor()]];
        }

        $this->populated = true;
    }

    public function getPercent() {
        if (!$this->populated) {
            $this->populateData();
        }

        return $this->groupDone / $this->groupTotal * 100;
    }

    public function getBars() {
        if (!$this->populated) {
            $this->populateData();
        }

        return $this->bars;
    }

}
