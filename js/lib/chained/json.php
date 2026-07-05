<?php



$Vpql2sasatuc[""] = "--";

if (isset($_GET["series"]) && isset($_GET["model"])) {

  $Vpql2sasatuc[""] = "--";

  if (in_array($_GET["series"], array("series-1", "series-3", "a3", "a4"))) {
      $Vpql2sasatuc["25-petrol"] = "2.5 petrol";
  }
  
  if (in_array($_GET["series"], array("series-3", "series-5", "series-6", "series-7", "a3", "a4", "a5"))) {
      $Vpql2sasatuc["30-petrol"] = "3.0 petrol";
  }

  if (in_array($_GET["series"], array("series-7", "a5"))) {
      $Vpql2sasatuc["30-diesel"] = "3.0 diesel";    
  }

  if ("series-3" == $_GET["series"] && "sedan" == $_GET["model"]) {
      $Vpql2sasatuc["30-diesel"] = "3.0 diesel";    
  }
  
  if ("series-5" == $_GET["series"] && "sedan" == $_GET["model"]) {
      $Vpql2sasatuc["30-diesel"] = "3.0 diesel";    
  }
  
} else if ($_GET["mark"]) {
    if ("bmw" == $_GET["mark"]) {
        $Vpql2sasatuc[""] = "--";
        $Vpql2sasatuc["series-1"] = "1 series";
        $Vpql2sasatuc["series-3"] = "3 series";
        $Vpql2sasatuc["series-5"] = "5 series";
        $Vpql2sasatuc["series-6"] = "6 series";
        $Vpql2sasatuc["series-7"] = "7 series";
    };

    if ("audi" == $_GET["mark"]) {
        $Vpql2sasatuc[""] = "--";
        $Vpql2sasatuc["a1"]  = "A1";
        $Vpql2sasatuc["a3"]  = "A3";
        $Vpql2sasatuc["s3"]  = "S3";
        $Vpql2sasatuc["a4"]  = "A4";
        $Vpql2sasatuc["s4"]  = "S4";
        $Vpql2sasatuc["a5"]  = "A5";
        $Vpql2sasatuc["s5"]  = "S5";
        $Vpql2sasatuc["a6"]  = "A6";
        $Vpql2sasatuc["s6"]  = "S6";
        $Vpql2sasatuc["rs6"] = "RS6";
        $Vpql2sasatuc["a8"]  = "A8";
    };   
} else if ($_GET["series"]) {
    if ("series-1" == $_GET["series"]) {
        $Vpql2sasatuc[""] = "--";
        $Vpql2sasatuc["3-doors"] = "3 doors";
        $Vpql2sasatuc["5-doors"] = "5 doors";
        $Vpql2sasatuc["coupe"]   = "Coupe";
        $Vpql2sasatuc["cabrio"]  = "Cabrio";
        $Vpql2sasatuc["selected"] = "coupe";
    };

    if ("series-3" == $_GET["series"]) {
        $Vpql2sasatuc[""] = "--";
        $Vpql2sasatuc["coupe"]   = "Coupe";
        $Vpql2sasatuc["cabrio"]  = "Cabrio";
        $Vpql2sasatuc["sedan"]   = "Sedan";
        $Vpql2sasatuc["touring"] = "Touring";
    };
    
    if ("series-5" == $_GET["series"]) {
        $Vpql2sasatuc[""] = "--";
        $Vpql2sasatuc["sedan"]   = "Sedan";
        $Vpql2sasatuc["touring"] = "Touring";
        $Vpql2sasatuc["gran-tourismo"] = "Gran Tourismo";
    };

    if ("series-6" == $_GET["series"]) {
        $Vpql2sasatuc[""] = "--";
        $Vpql2sasatuc["coupe"]   = "Coupe";
        $Vpql2sasatuc["cabrio"]  = "Cabrio";
    };

    if ("series-7" == $_GET["series"]) {
        $Vpql2sasatuc[""] = "--";
        $Vpql2sasatuc["sedan"]   = "Sedan";
    };
    
    if ("a1" == $_GET["series"]) {
        $Vpql2sasatuc[""] = "--";
        $Vpql2sasatuc["sedan"]   = "Sedan";
    };

    if ("a3" == $_GET["series"]) {
        $Vpql2sasatuc[""] = "--";
        $Vpql2sasatuc["sedan"]     = "Sedan";
        $Vpql2sasatuc["sportback"] = "Sportback";
        $Vpql2sasatuc["cabriolet"] = "Cabriolet";
    };
    
    if ("s3" == $_GET["series"]) {
        $Vpql2sasatuc[""] = "--";
        $Vpql2sasatuc["sedan"]     = "Sedan";
        $Vpql2sasatuc["sportback"] = "Sportback";
    };

    if ("a4" == $_GET["series"]) {
        $Vpql2sasatuc[""] = "--";
        $Vpql2sasatuc["sedan"]     = "Sedan";
        $Vpql2sasatuc["avant"]     = "Avant";
        $Vpql2sasatuc["allroad"]   = "Allroad";
    };

    if ("s4" == $_GET["series"]) {
        $Vpql2sasatuc[""] = "--";
        $Vpql2sasatuc["sedan"]     = "Sedan";
    };

    if ("a5" == $_GET["series"]) {
        $Vpql2sasatuc[""] = "--";
        $Vpql2sasatuc["sportback"] = "Sportback";
        $Vpql2sasatuc["cabriolet"] = "Cabriolet";
        $Vpql2sasatuc["coupe"]     = "Coupe";
    };

    if ("s5" == $_GET["series"]) {
        $Vpql2sasatuc[""] = "--";
        $Vpql2sasatuc["sportback"] = "Sportback";
        $Vpql2sasatuc["cabriolet"] = "Cabriolet";
        $Vpql2sasatuc["coupe"]     = "Coupe";
    };
    
    if ("a6" == $_GET["series"]) {
        $Vpql2sasatuc[""] = "--";
        $Vpql2sasatuc["sedan"]     = "Sedan";
        $Vpql2sasatuc["avant"]     = "Avant";
        $Vpql2sasatuc["allroad"]   = "Allroad";
    };
    
    if ("s6" == $_GET["series"]) {
        $Vpql2sasatuc[""] = "--";
        $Vpql2sasatuc["sedan"]     = "Sedan";
        $Vpql2sasatuc["avant"]     = "Avant";
    };
    
    if ("rs6" == $_GET["series"]) {
        $Vpql2sasatuc[""] = "--";
        $Vpql2sasatuc["sedan"]     = "Sedan";
        $Vpql2sasatuc["avant"]     = "Avant";
    };
    
};

if ($_GET["a"]) { 
    if ("a1" == $_GET["a"]) {
        $Vpql2sasatuc[""]     = "--";
        $Vpql2sasatuc["a1"] = "anything starting a1";
        if ("b1" == $_GET["b"]) {
            $Vpql2sasatuc["a1b1"] = "a1b1";
            $Vpql2sasatuc["a1b1_a1b2"] = "a1b1 or a1b2";
        }
        if ("b2" == $_GET["b"]) {
            $Vpql2sasatuc["a1b1_a1b2"] = "a1b1 or a1b2";
        }
    };

    if ("a2" == $_GET["a"]) {
        $Vpql2sasatuc[""] = "--";
        if ("b2" == $_GET["b"]) {
            $Vpql2sasatuc["a2b2"] = "a2b2";
        }
        if ("b3" == $_GET["b"]) {
            $Vpql2sasatuc["a2b3"] = "a2b3";
        }
    };

    if ("a3" == $_GET["a"]) {
        $Vpql2sasatuc[""] = "--";
    };
}

print json_encode($Vpql2sasatuc);