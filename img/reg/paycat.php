<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>SELECT EITHER SESSIONAL OR SEMESTERIAL PAYMENT</title>
</head>

<body>
<form id="form1" name="form1" method="post" action="payrequest_pg.php">
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <input name="stdid" type="hidden" id="stdid" value="<?php echo $stdid; ?>" />
  <table width="454" border="1" align="center" cellpadding="0" cellspacing="5">
    <tr>
      <td colspan="2" align="center">SELECT EITHER SESSIONAL OR SEMESTERIAL PAYMENT</td>
    </tr>
    <tr>
      <td width="216" align="right">CATEGORY</td>
      <td width="217"><label for="select"></label>
        <select name="cat" id="cat">
          <option value="SESSIONAL">SESSIONAL</option>
          <option value="SEMETERIAL">SEMETERIAL</option>
      </select></td>
    </tr>
    <tr>
      <td colspan="2" align="center"><input type="submit" name="button" id="button" value="Submit" /></td>
    </tr>
  </table>
</form>
</body>
</html>