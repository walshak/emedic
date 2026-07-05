<?php



if (!isset($_GET["chn_year"])) {
    $Vpql2sasatuc[""] = "- Day -";
}

if (!isset($_GET["chn_month"])) {
    $Vpql2sasatuc[""] = "- Month -";
}

if ($_GET["chn_year"]) {
    $Vpql2sasatuc[""] = "--";
    if (in_array($_GET["chn_year"], array("2006", "2007", "2009", "2010", "2011", "2013"))) {
        $Vpql2sasatuc[""] = "- Month -";
        $Vpql2sasatuc["January"] = "January";
        $Vpql2sasatuc["February"] = "February";
        $Vpql2sasatuc["March"] = "March";
        $Vpql2sasatuc["April"] = "April";
        $Vpql2sasatuc["May"] = "May";
        $Vpql2sasatuc["June"] = "June";
        $Vpql2sasatuc["July"] = "July";
        $Vpql2sasatuc["August"] = "August";
        $Vpql2sasatuc["September"] = "September";
        $Vpql2sasatuc["October"] = "October";
        $Vpql2sasatuc["November"] = "November";
        $Vpql2sasatuc["December"] = "December";
    };
    if (in_array($_GET["chn_year"], array("2008", "2012"))) {
        $Vpql2sasatuc[""] = "- Month -";
        $Vpql2sasatuc["January"] = "January";
        $Vpql2sasatuc["February_leap"] = "February";
        $Vpql2sasatuc["March"] = "March";
        $Vpql2sasatuc["April"] = "April";
        $Vpql2sasatuc["May"] = "May";
        $Vpql2sasatuc["June"] = "June";
        $Vpql2sasatuc["July"] = "July";
        $Vpql2sasatuc["August"] = "August";
        $Vpql2sasatuc["September"] = "September";
        $Vpql2sasatuc["October"] = "October";
        $Vpql2sasatuc["November"] = "November";
        $Vpql2sasatuc["December"] = "December";
    };
}
if ($_GET["chn_month"]) {
    if ($_GET["chn_month"] == "February") {
        $Vpql2sasatuc[""] = "- Day -";
        $Vpql2sasatuc["day1"] = "01";
        $Vpql2sasatuc["day2"] = "02";
        $Vpql2sasatuc["day3"] = "03";
        $Vpql2sasatuc["day4"] = "04";
        $Vpql2sasatuc["day5"] = "05";
        $Vpql2sasatuc["day6"] = "06";
        $Vpql2sasatuc["day7"] = "07";
        $Vpql2sasatuc["day8"] = "08";
        $Vpql2sasatuc["day9"] = "09";
        $Vpql2sasatuc["day10"] = "10";
        $Vpql2sasatuc["day11"] = "11";
        $Vpql2sasatuc["day12"] = "12";
        $Vpql2sasatuc["day13"] = "13";
        $Vpql2sasatuc["day14"] = "14";
        $Vpql2sasatuc["day15"] = "15";
        $Vpql2sasatuc["day16"] = "16";
        $Vpql2sasatuc["day17"] = "17";
        $Vpql2sasatuc["day18"] = "18";
        $Vpql2sasatuc["day19"] = "19";
        $Vpql2sasatuc["day20"] = "20";
        $Vpql2sasatuc["day21"] = "21";
        $Vpql2sasatuc["day22"] = "22";
        $Vpql2sasatuc["day23"] = "23";
        $Vpql2sasatuc["day24"] = "24";
        $Vpql2sasatuc["day25"] = "25";
        $Vpql2sasatuc["day26"] = "26";
        $Vpql2sasatuc["day27"] = "27";
        $Vpql2sasatuc["day28"] = "28";
    } else if ($_GET["chn_month"] == "February_leap") {
        $Vpql2sasatuc[""] = "- Day -";
        $Vpql2sasatuc["day1"] = "01";
        $Vpql2sasatuc["day2"] = "02";
        $Vpql2sasatuc["day3"] = "03";
        $Vpql2sasatuc["day4"] = "04";
        $Vpql2sasatuc["day5"] = "05";
        $Vpql2sasatuc["day6"] = "06";
        $Vpql2sasatuc["day7"] = "07";
        $Vpql2sasatuc["day8"] = "08";
        $Vpql2sasatuc["day9"] = "09";
        $Vpql2sasatuc["day10"] = "10";
        $Vpql2sasatuc["day11"] = "11";
        $Vpql2sasatuc["day12"] = "12";
        $Vpql2sasatuc["day13"] = "13";
        $Vpql2sasatuc["day14"] = "14";
        $Vpql2sasatuc["day15"] = "15";
        $Vpql2sasatuc["day16"] = "16";
        $Vpql2sasatuc["day17"] = "17";
        $Vpql2sasatuc["day18"] = "18";
        $Vpql2sasatuc["day19"] = "19";
        $Vpql2sasatuc["day20"] = "20";
        $Vpql2sasatuc["day21"] = "21";
        $Vpql2sasatuc["day22"] = "22";
        $Vpql2sasatuc["day23"] = "23";
        $Vpql2sasatuc["day24"] = "24";
        $Vpql2sasatuc["day25"] = "25";
        $Vpql2sasatuc["day26"] = "26";
        $Vpql2sasatuc["day27"] = "27";
        $Vpql2sasatuc["day28"] = "28";
        $Vpql2sasatuc["day29"] = "29";
    } else if (in_array($_GET["chn_month"], array("April", "June", "September", "November"))) {
        $Vpql2sasatuc[""] = "- Day -";
        $Vpql2sasatuc["day1"] = "01";
        $Vpql2sasatuc["day2"] = "02";
        $Vpql2sasatuc["day3"] = "03";
        $Vpql2sasatuc["day4"] = "04";
        $Vpql2sasatuc["day5"] = "05";
        $Vpql2sasatuc["day6"] = "06";
        $Vpql2sasatuc["day7"] = "07";
        $Vpql2sasatuc["day8"] = "08";
        $Vpql2sasatuc["day9"] = "09";
        $Vpql2sasatuc["day10"] = "10";
        $Vpql2sasatuc["day11"] = "11";
        $Vpql2sasatuc["day12"] = "12";
        $Vpql2sasatuc["day13"] = "13";
        $Vpql2sasatuc["day14"] = "14";
        $Vpql2sasatuc["day15"] = "15";
        $Vpql2sasatuc["day16"] = "16";
        $Vpql2sasatuc["day17"] = "17";
        $Vpql2sasatuc["day18"] = "18";
        $Vpql2sasatuc["day19"] = "19";
        $Vpql2sasatuc["day20"] = "20";
        $Vpql2sasatuc["day21"] = "21";
        $Vpql2sasatuc["day22"] = "22";
        $Vpql2sasatuc["day23"] = "23";
        $Vpql2sasatuc["day24"] = "24";
        $Vpql2sasatuc["day25"] = "25";
        $Vpql2sasatuc["day26"] = "26";
        $Vpql2sasatuc["day27"] = "27";
        $Vpql2sasatuc["day28"] = "28";
        $Vpql2sasatuc["day29"] = "29";
        $Vpql2sasatuc["day30"] = "30";
    } else if (in_array($_GET["chn_month"], array("January", "March", "April", "May", "July", "August", "October", "December"))) {
        $Vpql2sasatuc[""] = "- Day -";
        $Vpql2sasatuc["day1"] = "01";
        $Vpql2sasatuc["day2"] = "02";
        $Vpql2sasatuc["day3"] = "03";
        $Vpql2sasatuc["day4"] = "04";
        $Vpql2sasatuc["day5"] = "05";
        $Vpql2sasatuc["day6"] = "06";
        $Vpql2sasatuc["day7"] = "07";
        $Vpql2sasatuc["day8"] = "08";
        $Vpql2sasatuc["day9"] = "09";
        $Vpql2sasatuc["day10"] = "10";
        $Vpql2sasatuc["day11"] = "11";
        $Vpql2sasatuc["day12"] = "12";
        $Vpql2sasatuc["day13"] = "13";
        $Vpql2sasatuc["day14"] = "14";
        $Vpql2sasatuc["day15"] = "15";
        $Vpql2sasatuc["day16"] = "16";
        $Vpql2sasatuc["day17"] = "17";
        $Vpql2sasatuc["day18"] = "18";
        $Vpql2sasatuc["day19"] = "19";
        $Vpql2sasatuc["day20"] = "20";
        $Vpql2sasatuc["day21"] = "21";
        $Vpql2sasatuc["day22"] = "22";
        $Vpql2sasatuc["day23"] = "23";
        $Vpql2sasatuc["day24"] = "24";
        $Vpql2sasatuc["day25"] = "25";
        $Vpql2sasatuc["day26"] = "26";
        $Vpql2sasatuc["day27"] = "27";
        $Vpql2sasatuc["day28"] = "28";
        $Vpql2sasatuc["day29"] = "29";
        $Vpql2sasatuc["day30"] = "30";
        $Vpql2sasatuc["day31"] = "31";
    }
}

if($Vpql2sasatuc != '') {
    print json_encode($Vpql2sasatuc);
}

?>