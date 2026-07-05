<?php
session_start();
include_once('../Connections/Conn.php');

$view_status_date = date('Y-m-d');
$view_status_date_param = $view_status_date . '%';
$pharDocChatstmt = $db->prepare("SELECT * FROM `pharm_doctor_notes` 
                                                WHERE reciever = :reciever 
                                                AND date_time LIKE :view_status_date
                                                ");
$pharDocChatstmt->bindParam(':reciever', $_SESSION['fullname']);
$pharDocChatstmt->bindParam(':view_status_date', $view_status_date_param);
$pharDocChatstmt->execute();

$totalMessages =  $pharDocChatstmt->rowCount();
if ($totalMessages > 0) {
?>
    <table class="table table-boredered table-stripped">
        <tbody>

            <?php
            $sn = 1;
            while ($chat = $pharDocChatstmt->fetch(PDO::FETCH_ASSOC)) {
                $rwxx_sn = $chat['sn'];
            ?>
                <tr>
                    <td><?= $sn++; ?> - &nbsp;<?= '<b>' . $chat['patient_name'] . '</b> -  (' . $chat['drug_name'] . ')'; ?>
                        <br><?= $chat['notes']; ?><br>
                        <textarea cols='4' rows='2' class='form-control ' name='reply_text' id='reply_text' maxlength="100" required></textarea>
                        <button type="button" class="btn btn-xs btn-success" id="add" onclick="reply_sn_notes('<?= $rwxx_sn; ?>')"><i class="fa fa-arrow"></i>&nbsp;Reply</button>
                    </td>

                </tr>
            <?php
            }
            ?>
        </tbody>
    </table>
<?php
}


?>