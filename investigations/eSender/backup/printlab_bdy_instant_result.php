<div class="table-responsive m-t">
    <body oncopy="return false" oncut="return false" onpaste="return false">
        <table class="table invoice-table" width="100%" cellpadding="5" cellspacing="5" style="border:1px solid #000; border-collapse:collapse; font-size:12px; font-family:Arial, Helvetica, sans-serif;">
            <tbody>
                <tr>
                    <td width="33%" style="border-bottom:1px solid #000; border-top:1px solid #000;"><?php echo '<strong>'.$test_name.'</strong>'; ?></td>
                    <td width="33%" style="border-bottom: 1px solid #000; border-top: 1px solid #000; text-align:left">
                        <?php if($_SESSION['section']=='Laboratory'){ echo 'Specimen: ' . $spm; }?>
                    </td>
                    <td width="33%" style="border-bottom: 1px solid #000; border-top: 1px solid #000; text-align:left"></td>
                </tr>

                <?php if($field_type=='values'){ ?>
                    <?php 
                    $stmt5 = $db->prepare("SELECT * FROM lab_scan_input_results WHERE lab_request_no = :labrequest_no AND test_no = :test_id");
                    $stmt5->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
                    $stmt5->bindParam(':test_id', $test_id, PDO::PARAM_STR);
                    $stmt5->execute();

                    while ($rowx = $stmt5->fetch(PDO::FETCH_ASSOC)) {
                        $sn=1;
                    ?>
                        <tr>
                            <td width="33%" style="border-bottom: 1px solid #000; text-align:left"><?php echo $rowx['value_title']?></td>
                            <td width="33%" style="border-bottom: 1px solid #000; text-align:left"><?php echo '<strong>Result:</strong> '. $rowx['result']?></td>
                            <td width="33%" style="border-bottom: 1px solid #000; text-align:left"><?php echo 'Ref.: '. $rowx['value_ref']?></td>
                        </tr>
                    <?php } ?>
                <?php } elseif($field_type=='report' || $field_type==''){ ?>
                    <?php if($field_ref!=''){?>
                        <tr>
                            <td><strong>Reference</strong></td>
                            <td colspan="2" style="border-bottom: 1px solid #000; text-align:left"><?php echo $field_ref; ?></td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td colspan="3" style="font-size:12px; font-family:Arial, Helvetica, sans-serif; text-align:left"><?php echo $test_result; ?></td>
                    </tr>
                <?php } elseif($field_type=='value' || $field_type=='options'){ ?>
                    <tr>
                        <td width="33%" style="border-bottom: 1px solid #000; text-align:left"><?php echo $field_name; ?></td>
                        <td width="33%" style="border-bottom: 1px solid #000; text-align:left"><?php echo '<strong>Result:</strong> '. $test_result; ?></td>
                        <td width="33%" style="border-bottom: 1px solid #000; text-align:left"><?php echo 'Ref.: '. $field_ref; ?></td>
                    </tr>
                <?php } ?>

                <?php if($comment!=''){?>
                    <tr>
                        <td style="border-bottom: 1px solid #000; text-align:left"><strong>Comment</strong></td>
                        <td colspan="2" style="border-bottom: 1px solid #000; text-align:left"><?php echo $comment; ?></td>
                    </tr>
                <?php } ?>

                <tr>
                    <td colspan="2" style="border-bottom: 1px solid #000; text-align:left"><?php echo '<strong>Prepared By</strong>: ' . $lab_sci_name . ' [ ' . $lab_sci_speciality .' ]'; ?></td>
                    <td>Result Date:&nbsp;  <?php echo date("d-m-Y", strtotime($result_date)); ?></td>
               
</tr>
            </tbody>
        </table>
    </body>
</div>
