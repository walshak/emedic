<?php
session_start();
include("../Connections/Conn.php");
$setdate = date("Y-m-d");

// Handle actions
$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
    case 'create':
        createRoom($db);
        break;
    case 'read':
        readRooms($db);
        break;
    case 'get':
        getRoom($db);
        break;
    case 'update':
        updateRoom($db);
        break;
    case 'delete':
        deleteRoom($db);
        break;
    default:
        echo json_encode(array('success' => false, 'message' => 'Invalid action'));
}

function createRoom($db)
{
    $room = isset($_POST['room']) ? $_POST['room'] : '';

    if (empty($room)) {
        echo json_encode(array('success' => false, 'message' => 'Room is required'));
        return;
    }

    try {
        $stmt = $db->prepare("INSERT INTO consultations_rooms (room) VALUES (:room)");
        $stmt->bindParam(':room', $room);
        if ($stmt->execute()) {
            echo json_encode(array('success' => true, 'message' => 'Room created successfully'));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Error creating room'));
        }
    } catch (PDOException $e) {
        echo json_encode(array('success' => false, 'message' => 'Database error: ' . $e->getMessage()));
    }
}

function readRooms($db)
{
    try {

        $stmt = $db->query("SELECT fullname, Consult_Room, id FROM admin_users WHERE rights IN ('DR','MD','GM') AND status=1 ORDER BY fullname");
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($rooms) {
            echo json_encode(array('success' => true, 'data' => $rooms));
        } else {
            echo json_encode(array('success' => false, 'message' => 'No rooms found'));
        }
    } catch (PDOException $e) {
        echo json_encode(array('success' => false, 'message' => 'Database error: ' . $e->getMessage()));
    }
}

function getRoom($db)
{
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    try {
        $stmt = $db->prepare("SELECT Consult_Room,id FROM admin_users WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $room = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($room) {
            echo json_encode(array('success' => true, 'data' => $room));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Room not found'));
        }
    } catch (PDOException $e) {
        echo json_encode(array('success' => false, 'message' => 'Database error: ' . $e->getMessage()));
    }
}

function updateRoom($db)
{
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $room = isset($_POST['room_2']) ? $_POST['room_2'] : '';
    /// $doctor = isset($_POST['doctor']) ? $_POST['doctor'] : '';

    if (empty($id) || empty($room)) {
        echo json_encode(array('success' => false, 'message' => 'All fields are required'));
        return;
    }

    try {
        $stmt = $db->prepare("UPDATE admin_users SET Consult_Room = :Consult_Room WHERE id = :id");
        $stmt->bindParam(':Consult_Room', $room);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(array('success' => true, 'message' => 'Room updated successfully'));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Error updating room'));
        }
    } catch (PDOException $e) {
        echo json_encode(array('success' => false, 'message' => 'Database error: ' . $e->getMessage()));
    }
}

function deleteRoom($db)
{
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    try {
        $stmt = $db->prepare("DELETE FROM consultations_rooms WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(array('success' => true, 'message' => 'Room deleted successfully'));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Error deleting room'));
        }
    } catch (PDOException $e) {
        echo json_encode(array('success' => false, 'message' => 'Database error: ' . $e->getMessage()));
    }
}
