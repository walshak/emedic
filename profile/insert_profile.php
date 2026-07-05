<?php include("../Connections/Conn.php"); ?>

<?php
session_start();

$setdate = date('Y-m-d H:i:s');

if ($_POST["MM_update"] == 'add_tips_info') {

  $username = $_POST['username'];
  $tips = $_POST['msg'];
  $stmt = "UPDATE admin_users SET my_tips='$tips' WHERE username='$username'";
  $db->exec($stmt);
}


if ($_POST["MM_update"] == 'change_password') {

  $username = $_POST['username'];
  $password = md5(strtolower($_POST['re_password']));
  $stmt = "UPDATE admin_users SET password='$password' WHERE username='$username'";
  $db->exec($stmt);
}
if ($_POST["MM_update"] == 'edit_profile') {
  $stmt = $db->prepare("UPDATE admin_users SET title=:title, fullname=:fullname, email=:email, phone_number=:phone, about_me=:about_me WHERE username=:username");
  $stmt->bindParam(':title', $_POST['title']);
  $stmt->bindParam(':fullname', $_POST['name']);
  $stmt->bindParam(':email', $_POST['email']);
  $stmt->bindParam(':phone', $_POST['phone']);
  $stmt->bindParam(':about_me', $_POST['about_me']);
  $stmt->bindParam(':username', $_POST['username']);
  $stmt->execute();
}

if ($_POST["MM_update"] == 'what_to_do') {
  $note_time = $_POST['note_time'];
  $note_date = $_POST['note_date'];
  $new_dates = date("Y-m-d H:i:s", strtotime($note_date . '' . $note_time));
  $stmt = $db->prepare("INSERT INTO admin_users_what_todo(note, date_time, username) VALUES (:note, :date_time, :username)");
  $stmt->bindParam(':note', $_POST['note']);
  $stmt->bindParam(':date_time', $new_dates);
  $stmt->bindParam(':username', $_SESSION['username']);
  $stmt->execute();
}

?>
 
 
 