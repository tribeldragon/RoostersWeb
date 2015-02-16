<?php

class Parser {

    private $days = array();
    private $html = "";
    private $dom;

    public function __construct($html) {
        $this->days = array(
            2 => new Day(),
            4 => new Day(),
            6 => new Day(),
            8 => new Day(),
            10 => new Day()
        );

        $this->html = $html;
        $this->html = str_replace("&nbsp;", "", $this->html); // Prevent &nbsp; parsing

        $this->dom = new DOMDocument;
        $this->dom->preserveWhiteSpace = false;
        @$this->dom->loadHTML($this->html);

        //$this->roster = new Roster();
    }

    private function getGroupName() {
        $group = $this->dom->getElementsByTagName("font")->item(1);
        $group = $group->ownerDocument->saveHTML($group);
        $group = strip_tags($group);
        $group = preg_replace('/\s+/', '', $group);

        return $group;
    }

    private function getTable() {
        return $this->dom->getElementsByTagName("table")->item(1);
    }

    private function getRows($table) {
        return $table->childNodes;
    }

    private function getColumns($row) {
        return $row->childNodes;
    }

    private function loopThroughRows($rows) {
        $index = 0;

        foreach ($rows as $rowKey => $rowValue) {
            if ($rowValue->hasChildNodes()) {
                $columns = $this->getColumns($rowValue);
                $this->loopThroughColumns($columns, $rowKey, $index);
                $index++;
            }
        }
    }

    private function loopThroughColumns($columns, $rowKey, $index) {
        foreach ($columns as $columnKey => $columnValue) {
            if ($columnValue->hasChildNodes()) {
                $this->parseColumn($columnKey, $columnValue, $rowKey, $index);
            }
        }
    }

    private function parseColumn($columnKey, $columnValue, $rowKey, $index) {
        if ($rowKey != 0 && $columnKey != 0) {
            $rowspan = $columnValue->attributes->getNamedItem("rowspan");
            $colspan = $columnValue->attributes->getNamedItem("colspan");

            // Column contains 2 columns
            if ($colspan->value == 6) {
                trigger_error("Supporting nested periods needs work!");
//                $colspan = $columnValue->previousSibling->previousSibling->attributes->getNamedItem("colspan");
//
//                if ($colspan->value == 6) {
//                    $data = $columnValue->ownerDocument->saveHTML($columnValue); // Get data from column
//                    $data = strip_tags($data); // Remove html tags
//                    $data = preg_replace('/\s+/', ' ', $data); // Remove whitespace
//                    // Extract data from array, create new array, add extracted and new data to last array
//                    $temp = $tue[$index - 1]; // FIXME: needs to be dynamic
//                    $tue[$index - 1] = array($temp, $data);
//                    return;
//                }
            }

            // Set $currentDay to the appropriate array
            $currentDay = &$this->days[$columnKey];

            $j = 2;
            // If the size of $currentDay is bigger than what it should be go to the next one
            while (sizeof($currentDay->periods) >= $index) {
                if ($columnKey + $j > 10) {
                    die("WTF?!");
                }

                $currentDay = &$this->days[$columnKey + $j];
                $j += 2;
            }

            if ($rowspan) {
                $data = $columnValue->ownerDocument->saveHTML($columnValue); // Get data from column
                $data = strip_tags($data); // Remove html tags
                $data = preg_replace('/\s+/', ' ', $data); // Remove whitespace

                $amount = $rowspan->value / 2; // Amount of rows

                $data2 = $columnValue->getElementsByTagName("td");

                if ($data2->length >= 3) {
                    $subject = strip_tags($data2->item(0)->ownerDocument->saveHTML($data2->item(0)));
                    $teacher = strip_tags($data2->item(1)->ownerDocument->saveHTML($data2->item(1)));
                    $room = strip_tags($data2->item(2)->ownerDocument->saveHTML($data2->item(2)));

                    $subject = preg_replace('/\s+/', '', $subject);
                    $teacher = preg_replace('/\s+/', '', $teacher);
                    $room = preg_replace('/\s+/', '', $room);

                    for ($i = 0; $i < $amount; $i++) {
                        $p = new Period();
                        $p->setSubject($subject);
                        $p->setTeacher($teacher);
                        $p->setRoom($room);

                        $currentDay->addPeriod($p);
                    }
                } else {
                    for ($i = 0; $i < $amount; $i++) {
                        $p = new Period();
                        $currentDay->addPeriod($p);
                    }
                }
            }
        } else {
            
        }
    }

    public function parse() {
        // Get roster table
        $table = $this->getTable();
        $rows = $this->getRows($table);
        $this->loopThroughRows($rows);

        $roster = new Roster();
        $roster->setGroup($this->getGroupName());

        foreach ($this->days as $day) {
            $roster->addDay($day);
        }

        return $roster;
    }

}
