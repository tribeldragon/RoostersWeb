<?php

class Period {

    private $subject = "";
    private $teacher = "";
    private $room = "";
    
    public function setSubject($subject) {
        $this->subject = $subject;
    }
    
    public function getSubject() {
        return $this->subject;
    }
    
    public function setTeacher($teacher) {
        $this->teacher = $teacher;
    }
    
    public function getTeacher() {
        return $this->teacher;
    }
    
    public function setRoom($room) {
        $this->room = $room;
    }
    
    public function getRoom() {
        return $this->room;
    }

}
