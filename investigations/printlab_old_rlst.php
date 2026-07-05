<?php if ($field_type == 'values') {
    $stmxt5 = $db->query("SELECT * FROM lab_scan_input_results_old WHERE lab_request_no='$labrequest_no' and test_no='$test_id'");
    if ($stmxt5->rowCount() > 0) { ?>
        <table class="table invoice-table" width="100%" cellpadding="5" cellspacing="5" style="border:1px solid #000; border-collapse:collapse; font-size:12px; font-family:Arial, Helvetica, sans-serif;">
            <tbody>
                <tr>
                    <td colspan="3" style="border-bottom: 1px solid #000; text-align:left">
                        <h2 style="color:red;">Previous Reports EDITED (NOT TO BE USED)</h2>
                    </td>
                </tr>
                <?php
                while ($rowxdc = $stmxt5->fetch(PDO::FETCH_ASSOC)) {
                    //	$sn=1;
                ?>
                    <tr>
                        <td width="33%" style="border-bottom: 1px solid #000; text-align:left"><?php echo $rowxdc['value_title'] ?></td>
                        <td width="33%" style="border-bottom: 1px solid #000; text-align:left"><?php echo '<strong>Result:</strong> ' . $rowxdc['result'] ?></td>
                        <td width="33%" style="border-bottom: 1px solid #000; text-align:left"><?php echo 'Ref.: ' . $rowxdc['value_ref'] . ' /By: ' . $rowxdc['entered_by'] . ' /Date: ' . date("d-m-Y", strtotime($rowxdc['date_time'])); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>



    <?php } else {

    $stmtc5 = $db->query("SELECT * FROM lab_result_old WHERE lab_no='$labrequest_no' and test_no='$test_id'");
    if ($stmtc5->rowCount() > 0) { ?>
        <table class="table invoice-table" width="100%" cellpadding="5" cellspacing="5" style="border:1px solid #000; border-collapse:collapse; font-size:12px; font-family:Arial, Helvetica, sans-serif;">
            <tbody>
                <tr>
                    <td colspan="3" style="border-bottom: 1px solid #000; text-align:left">
                        <h2 style="color:red;">Previous Reports EDITED (NOT TO BE USED)</h2>
                    </td>
                </tr>

                <?php while ($rowxdc = $stmtc5->fetch(PDO::FETCH_ASSOC)) {
                    //	$sn=1;
                ?>
                    <tr>
                        <td width="33%" style="border-bottom: 1px solid #000; text-align:left"><?php echo $rowxdc['field_name'] ?></td>
                        <td width="33%" style="border-bottom: 1px solid #000; text-align:left"><?php echo '<strong>Result:</strong> ' . $rowxdc['field_value'] ?></td>
                        <td width="33%" style="border-bottom: 1px solid #000; text-align:left"><?php echo 'Ref.: ' . $rowxdc['field_ref'] . ' /By: ' . $rowxdc['entered_by'] . ' /Date: ' . date("d-m-Y", strtotime($rowxdc['result_date'])); ?></td>
                    </tr>
                <?php
                } ?>
            </tbody>
        </table>
<?php
    }
}
?>