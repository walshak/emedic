<div class="text-right">

    <a href="gen_services.php?hosp_no=<?= base64_encode(
                                            base64_encode($hospital_no . '||' . $app_no),
                                        ) ?>">Return (Previous Page)</a> &nbsp; : &nbsp; <button class="btn btn-sm btn-primary" onclick="ClickheretoprintDiv('printable_area')"><i class="fa fa-print"></i> Print</button>

</div>

<div id="printable_area">
    <table class="table active hover" border="2">
        <tr>
            <td colspan="3">
                <?php
                $hospital_info = $Hospital->get([]);
                if (!empty($hospital_info[0])) {
                    echo ' 
                         <p class="text-center"> <img src="img/logo.png" width="100px"> </p>
                        <h4 class="text-center"> ' .
                        $hospital_info[0]->name .
                        ' <br> ' .
                        $hospital_info[0]->address .
                        ' </h4> 
                        <div id="heading">Patient Service </div>
                       ';
                }
                ?>
            </td>
        </tr>
        <tr>
            <td>
                Patient Name:
                <?= $patient_name ?>
            </td>
            <td>
                Patient ID:
                <?= $hospital_no ?>
            </td>
            <td>
                Sex:
                <?= $sex ?>
            </td>
        </tr>
        <tr>
            <td>
                Service:
                <?= $note['service'] ?>
            </td>
            <td>
                Captured By:
                <?= $note['consultant_name'] ?>
            </td>
            <td>

            </td>
        </tr>
        <tr>
            <td colspan="3">
                <h3>Notes:</h3>
                <?= $note['notes'] ?>
            </td>
        </tr>
        <tr>
            <td colspan="3" class="text-center">
                <small style="color:#ccc"> </small>
            </td>
        </tr>
    </table>
</div>
<script>
    function ClickheretoprintDiv(div_id) {
        var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,";
        disp_setting += "scrollbars=yes,width=800, height=400, left=100, top=25";
        var content_vlue = document.getElementById(div_id).innerHTML;

        var docprint = window.open("", "", disp_setting);
        docprint.document.write('<html><head><title>.::Webmedic </title> <link rel="stylesheet" href="css/bootstrap.min.css">');
        docprint.document.write('</head><body onLoad="self.print()" style="width: 100%; height="auto" font-size:16px; font-family:arial;">');
        docprint.document.write(content_vlue);
        docprint.document.write('</body></html>');
        docprint.document.close();
        docprint.focus();
    }
</script>