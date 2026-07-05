<?php
//if (isset($_GET['stdid'])){
//$urlstdid=$_GET['stdid'];
//}
  //encode stdid
  //include('encodeco.php');
 //echo $stdid=decode("$urlstdid","$mykey"); 
 $urlstdid='M1300001';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript">
function altRows(id){
	if(document.getElementsByTagName){  
		
		var table = document.getElementById(id);  
		var rows = table.getElementsByTagName("tr"); 
		 
		for(i = 0; i < rows.length; i++){          
			if(i % 2 == 0){
				rows[i].className = "evenrowcolor";
			}else{
				rows[i].className = "oddrowcolor";
			}      
		}
	}
}
window.onload=function(){
	altRows('alternatecolor');
}
</script>

<link rel="stylesheet" href="css/style.css">
<title>Untitled Document</title>

</head>

<body class="twoColHybRtHdr">         
<div class="register">
  <div id="header">
    <h1>FUT MINNA - eportal</h1>
  <!-- end #header --></div>
    <div id="error">
    <h1>eportal error</h1>
    </div>
  <div id="image">
    <div align="center">
      <!-- end #image -->
      <img src="passport.png" width="120" height="120" /></div>
  </div>
  <div id="sidebar1">
    <h1><?php echo 'Status: Admitted' ?></h1>
    <hr />
    <h1><?php echo 'Activities:' ?></h1>
    <li><?php echo 'Admission Data' ?></li>
    <li><?php echo '<a href="payrequest.php?' . 'stdid=' . $urlstdid .'">Acceptance Fee</a>'; ?></li>
    <li><?php echo 'School Fee' ?></li>
    <li><?php echo 'Accommodation Fee' ?></li>
    <li><?php echo 'Academic Credentials' ?></li>
        <li><?php echo 'Personal Data' ?></li>
            <li><?php echo 'Study Status' ?></li>


    <p>&nbsp;</p>
  <!-- end #sidebar1 --></div>
  <div id="mainContent">
  <?php
  /// determin the student status - here
//include('admdata.php');

  ?>
  </div>
	<!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats -->
	<br class="clearfloat" />
  </div>
<!-- end #container --></div>
	<div class="about">
      <div class="style1"> Powered by <a href="https://www.futminna.edu.ng">ITS-FUTMINNA</a><br />
      Copyright © FUTMINNA 2013      </div>
      <div><!-- end #footer -->
      </div>
</body>
</html>
