
<?php 
if (isset($_GET['CandName'])){
$CandName=$_GET['CandName'];
}

if (isset($_GET['email'])){
$email=$_GET['email'];
}
if (isset($_GET['errmsg3'])){
$errmsg3=$_GET['errmsg3'];
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" type="text/css" href="success/bootstrap-1.4.0.css">
<link rel="stylesheet" type="text/css" href="success/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="success/futmin-base.css">
<link rel="stylesheet" type="text/css" href="success/custom_theme.css">
<script type="text/javascript" src="success/jquery-1.4.3.min.js"></script>
<script type="text/javascript" src="success/bootstrap-dropdown-1.4.0.js"></script>

    <title>Federal University of Technology (ePortal)</title>
    <!--base href="http://localhost:8080/app/@@page"
     tal:attributes="href python: view.url(layout.site)" / --><meta name="robots" content="index, follow">
    <link rel="stylesheet" media="only screen and (max-device-width: 480px)" type="text/css" href="./success_files/@@-waeup.kofa.browser-mobile.css">
    <link rel="alternate" type="application/rss+xml" title="RSS feed of Federal University of Technology Minna" href="http://portal.futminna.edu.ng/feed.rss">
  <style type="text/css">
<!--
.style2 {font-size: 14px}
-->
  </style></head>
  <body>
    <div class="topbar" data-scrollspy="scrollspy">
      <div class="topbar-inner">
       
          <table width="101%" height="72" border="0" background="logincss/topBG-purple.png">
            <tr>
              <td width="13%" scope="col">&nbsp;</td>
              <td width="87%" valign="bottom" scope="col">&nbsp;</td>
            </tr>
          </table>
      
      </div>
    </div>

    <div class="container">
      
      <div class="content">
        <div><div id="html">
          <h1><br />
            Welcome: <?php echo $CandName; ?><br />
            <br>
            <span class="style2"><small>
Your Student ID and Password (Information needed to login to the portal) has been sent to the following:</small></span></h1>
          <p>&nbsp;</p>
          <table width="70" border="0">
            <tr>
              <td width="19%" scope="col">E mail Address</td>
              <td width="81%" scope="col"><?php echo $email ; ?></td>
            </tr>
            <tr>
              <td>Email's Password</td>
              <td><?php echo $CandName ; ?></td>
            </tr>
            <tr>
              <td>Link to the email</td>
              <td><a href="https://www.google.com/a/st.futminna.edu.ng/ServiceLogin?service=mail&amp;passive=true&amp;rm=false&amp;continue=https://mail.google.com/a/st.futminna.edu.ng/&amp;ss=1&amp;ltmpl=default&amp;ltmplcache=2&amp;emr=1">FUT Minna Student's email</a></td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
          </table>
          <p align="center"><a href="login.php">Logout</a></p>

</div>
        </div>
        <div id="footer" class="footer">
<div> <br />
    <table width="70" border="0">
      <tr>
        <td width="83%" scope="col">Powered by ITS, FUT Minna</td>
        <td width="17%" align="right" scope="col">Copyright © 2013 </td>
      </tr>
    </table>
    </div>
<div></div>
          <div></div>
        </div>
      </div>
    </div>
  

</body></html>