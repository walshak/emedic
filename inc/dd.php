<?php

function dateDiff($date)
{
  $mydate= date("Y-m-d H:i:s");
  $theDiff="";
  //echo $mydate;//2014-06-06 21:35:55
  $datetime1 = date_create($date);
  $datetime2 = date_create($mydate);
  $interval = date_diff($datetime1, $datetime2);
  //echo $interval->format('%s Seconds %i Minutes %h Hours %d days %m Months %y Year    Ago')."<br>";
  $min=$interval->format('%i');
  $sec=$interval->format('%s');
  $hour=$interval->format('%h');
  $mon=$interval->format('%m');
  $day=$interval->format('%d');
  $year=$interval->format('%y');
  if($interval->format('%i%h%d%m%y')=="00000")
  {
    //echo $interval->format('%i%h%d%m%y')."<br>";
    return $sec." Seconds";

  } 

else if($interval->format('%h%d%m%y')=="0000"){
   return $min." Minutes";
   }


else if($interval->format('%d%m%y')=="000"){
   return $hour." Hours";
   }


else if($interval->format('%m%y')=="00"){
   return $day." Days";
   }

else if($interval->format('%y')=="0"){
   return $mon." Months";
   }

else{
   return $year." Years";
   }

}


function getTheDay($date)
                {
                    $curr_date=strtotime(date("Y-m-d H:i:s"));
                    $the_date=strtotime($date);
                    $diff=floor(($curr_date-$the_date)/(60*60*24));
                    switch($diff)
                    {
                        case 0:
                        return "Today" . "  " . date("H:i:s a", strtotime($date)) . ' - ' . date("d-m-Y", strtotime($date));
                            break;
							
                        case 1:
                        return "Yesterday"  . "  " . date("H:i:s a", strtotime($date)) . ' - ' . date("d-m-Y", strtotime($date));
                            break;
							
                        default:
                        return date("H:i:s a", strtotime($date)) . ' - ' . date("d-m-Y", strtotime($date));
						
						
						//if ($diff > 0) {
							
							//return 'In '.$diff.' days';
					//	} else {
						//	return ($diff*-1).' days ago';
						//}
						break;
                           // return $diff." Days ago";
                    }
                }
	
	
	function getTheDay2($date)
                {
                    $curr_date=strtotime(date("Y-m-d H:i:s"));
                    $the_date=strtotime($date);
                    $diff=floor(($curr_date-$the_date)/(60*60*24));
                    switch($diff)
                    {
                        case 0:
                        return "Today at " . "  " . date("H:i:s a", strtotime($date)) . ' - ' . date("d-m-Y", strtotime($date));
                            break;
							
                        case 1:
                        return "Yesterday at "  . "  " . date("H:i:s a", strtotime($date)) . ' - ' . date("d-m-Y", strtotime($date));
                            break;
							
                        default:
                        //return date("H:i:s a", strtotime($date)) . ' - ' . date("d-m-Y", strtotime($date));
						
						
						//if ($diff > 0) {
					return $diff.' days ago at '. date("H:i:s a", strtotime($date)) . ' - ' . date("d-m-Y", strtotime($date));
							//}
							// else {
						//	return ($diff*-1).' days ago';
						//}
						
						//3 days ago at 7:58 pm - 10.06.2014
						break;
                           // return $diff." Days ago";
                    }
                }

	
	
	function getTheDay3($date)
                {
                    $curr_date=strtotime(date("Y-m-d H:i:s"));
                    $the_date=strtotime($date);
                    $diff=floor(($curr_date-$the_date)/(60*60*24));
                    switch($diff)
                    {
                        case 0:
                        return date("h:m A", strtotime($date));
                        break;
    
                        default:
						return date("h:m A", strtotime($date)) . ' - ' . date("d-m-Y", strtotime($date));

						break;
                    }
                }

?>