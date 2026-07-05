<!--select Employee-->
function employee(str)
{
var xmlhttp;    
if (str=="")
  {
  document.getElementById("getemployee").innerHTML="";
  return;
  }
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    document.getElementById("getemployee").innerHTML=xmlhttp.responseText;
    }
  }
xmlhttp.open("GET","inc/get_employee.php?id="+str,true);
xmlhttp.send();
} 
<!--select Employee-->