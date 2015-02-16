<?php

class Roster {

    public $group = "";
    public $days = array();

    public function setGroup($groupName) {
        $this->group = $groupName;
    }

    public function getGroup() {
        return $this->group;
    }

    public function addDay(Day $day) {
        array_push($this->days, $day);
    }

}
