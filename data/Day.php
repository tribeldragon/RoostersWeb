<?php

class Day {

    public $periods = array();
    
    public function addPeriod(Period $period) {
        array_push($this->periods, $period);
    }

}
