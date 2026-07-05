<?php
// Dummy username and password
///$validUsername = "user";

require_once 'Connections/Conn.php';

// Get the posted data
$data = json_decode(file_get_contents('php://input'));
$validPassword = $data->password;
$username = $data->username;
$password = md5(strtolower($validPassword));

$stmt = $db->prepare('SELECT id FROM admin_users WHERE password=:password and username=:username');
$stmt->bindValue(':password', $password, PDO::PARAM_STR);
$stmt->bindValue(':username', $username, PDO::PARAM_STR);
$stmt->execute();
if ($stmt->rowCount() > 0) {
    // Check if the username and password match
    ///if ($data->username === $validUsername && $data->password === $validPassword) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error']);
}
