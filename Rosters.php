<?php

class Rosters {

    private $loginUrl = "https://studienet.fcroc.nl/";
    private $rostersUrl = "https://studienet.fcroc.nl/bbcswebdav/pid-240621-dt-content-rid-516874_2/courses/ICT_Heerenveen/roosters%20henk/3320klassen.htm";
    private $rosterPrefix = "https://studienet.fcroc.nl/bbcswebdav/pid-240621-dt-content-rid-516874_2/courses/ICT_Heerenveen/roosters%20henk/";
    public $rosterUrls = array();
    public $rosters = array();
    
    public $debug = false;

    // Synchronize roster data from blackboard
    public function synchronize() {
        $this->login();
        $this->getRosterUrls();
        $this->parseRosters();

        if ($this->debug) {
            $this->displayRosters();
        } else {
            $this->storeRosters();
        }
    }

    // Authenticate with blackboard so we can request data
    private function login() {
        $loginData = array(
            "user_id" => urlencode(""),
            "password" => urlencode(""),
            "login" => urlencode("Aanmelden"),
            "action" => urlencode("login"),
            "remote-user" => urlencode(""),
            "new_loc" => urlencode("/webapps/portal/frameset.jsp"),
            "auth_type" => urlencode(""),
            "one_time_token" => urlencode(""),
            "encoded_pw" => urlencode(""),
            "encoded_pw_unicode" => urlencode("")
        );

        return $this->curlPost($this->loginUrl, $loginData);
    }

    // Fetch link to rosters for all groups
    private function getRosterUrls() {
        $rosters = array();

        $result = $this->curlGet($this->rostersUrl);

        if ($result) {
            $dom = new DOMDocument();
            $dom->preserveWhiteSpace = false;
            @$dom->loadHTML($result);

            $elements = $dom->getElementsByTagName("a");

            foreach ($dom->getElementsByTagName("a") as $link) {
                $file = $link->attributes->getNamedItem("href")->value;

                if ($file == 'http://www.school-timetabling.com' || $file == 'http://www.grupet.at') {
                    continue;
                }

                array_push($rosters, $this->rosterPrefix . $file);
            }
        } else {
            // TODO: handle error?
        }

        $this->rosterUrls = $rosters;
    }

    // Parse the rosters
    private function parseRosters() {
        foreach ($this->rosterUrls as $url) {
            $html = $this->curlGet($url);

            $parser = new Parser($html);
            $roster = $parser->parse();

            array_push($this->rosters, $roster);
        }
    }

    // Store roster data to DB
    private function storeRosters() {
        // TODO: Implement storing roster data in DB
        // step 1. store 
    }

    // Print rosters in a table
    private function displayRosters() {
        for ($i = 0; $i < sizeof($this->rosters); $i++) {
            $roster = $this->rosters[$i];
            $url = $this->rosterUrls[$i];

            echo "<a href=\"" . $url . "\"><h1>" . $roster->group . "</h1></a>";
            echo "<table border=\"1\" style=\"table-layout: fixed; width: 875px;\">";

            for ($row = 0; $row < 9; $row++) {
                echo "<tr>";

                for ($column = 0; $column < 5; $column++) {
                    $period = $roster->days[$column]->periods[$row];

                    if ($period->getSubject() == null) {
                        echo "<td>&nbsp;</td>";
                    } else {
                        echo "<td>" . $period->getSubject() . " " . $period->getTeacher() . " " . $period->getRoom() . "</td>";
                    }
                }

                echo "</tr>";
            }

            echo "</table>";
        }
    }

    // Send get request with cURL
    public static function curlGet($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies"); // TODO: store "cookiefile" in a var somewhere
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . "/cacert.pem");
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    // Send post request with cURL
    public static function curlPost($url, array $postData) {
        $postString = "";

        foreach ($postData as $key => $value) {
            $postString .= $key . "=" . $value . "&";
        }

        $postString = rtrim($postString, '&');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($postData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
        curl_setopt($ch, CURLOPT_COOKIEJAR, "cookies"); // TODO: store "cookiefile" in a var somewhere
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . "/cacert.pem");

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

}
