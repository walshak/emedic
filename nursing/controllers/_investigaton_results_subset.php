<?php

$investigation_field_stmt = $db->prepare('SELECT * FROM lab_scan_fields WHERE test_no = ? LIMIT 1');
$investigation_field_stmt->execute(array($test_id));

if ($investigation_field_stmt->rowCount() > 0) {

    $investigation_field_info  = $investigation_field_stmt->fetch(PDO::FETCH_ASSOC);
    $field_type = $investigation_field_info['field_type'];
    $field_ = $investigation_field_info['field'];

    ///// Single value /////////////
    if ($field_type == 'value') {
        $investigation_result_stmt = $db->prepare('SELECT * FROM lab_result WHERE test_no = ? AND lab_no = ? LIMIT 1');
        $investigation_result_stmt->execute(array($test_id, $investigation_['labrequest_no']));
        if ($investigation_result_stmt->rowCount() > 0) {
            $investigation_result_info  = $investigation_result_stmt->fetch(PDO::FETCH_ASSOC);
            $result = $investigation_result_info['field_value'];
            $field_name = $investigation_result_info['field_name'];
            echo '<div style="max-width: 500px; padding:20px;border:2px solid #000; display:inline-block"> 
                                    <h2 class="text-center">' . ++$numbering . '</h2>
                                                    <table class="table" border="2">
                                                            <tr><th> Result</th><th> Value</th> </tr>
                                                            <tr>
                                                            <td> ' . $field_name . '</td> 
                                                            <td> ' . $result . '</td> 
                                                            </tr>
                                                    </table>
                                                    <br>
                                    <small>
									<b>Requested by:  ' . $investigation_['request_by'] . ' <br>
									<b>Requested On:  ' . dateFormat_($investigation_['request_date']) . ' <br>
									<b>Status:  ' . $lab_status . ' <br>
									<b>Comment:  ' . $investigation_result_info['comment'] . ' <br> 
									<b>Entered by:  ' . $investigation_result_info['entered_by'] . ' <br> 
									Date:  ' . dateFormat_($investigation_result_info['result_date']) . '</b></small>
                                                </div>';
        }
    }

    ///// options  values /////////////
    if ($field_type == 'options') {
        $investigation_result_stmt = $db->prepare('SELECT * FROM lab_result WHERE test_no = ? AND lab_no = ? LIMIT 1');
        $investigation_result_stmt->execute(array($test_id, $investigation_['labrequest_no']));
        if ($investigation_result_stmt->rowCount() > 0) {
            $investigation_result_info  = $investigation_result_stmt->fetch(PDO::FETCH_ASSOC);
            $result = $investigation_result_info['field_value'];
            $field_name = $investigation_result_info['field_name'];
            echo '<div class="col-md-4" style="border:2px solid #000;"> 
                                    <h2 class="text-center">' . ++$numbering . '</h2>
                                                    <table class="table" border="2">
                                                            <tr><th> Result</th><th> Value</th> </tr>
                                                            <tr>
                                                            <td> ' . $field_name . '</td> 
                                                            <td> ' . $result . '</td> 
                                                            </tr>
                                                    </table>
                                                    <br>
                                    <small>
									<b>Requested by:  ' . $investigation_['request_by'] . ' <br>
									<b>Requested On:  ' . dateFormat_($investigation_['request_date']) . ' <br>
									<b>Status:  ' . $lab_status . ' <br>
									<b>Comment:  ' . $investigation_result_info['comment'] . ' <br> 
									<b>Entered by:  ' . $investigation_result_info['entered_by'] . ' <br> 
									Date:  ' . dateFormat_($investigation_result_info['result_date']) . '</b></small>
                                                </div>';
        }
    }

    ///// Multile  values /////////////
    if ($field_type == 'values') {
        $investigation_result_stmt = $db->prepare('SELECT * FROM lab_scan_input_results WHERE test_no = ? AND lab_request_no = ? ');
        $investigation_result_stmt->execute(array($test_id, $investigation_['labrequest_no']));

        if ($investigation_result_stmt->rowCount() > 0) {
            $investigation_result_info_list  = $investigation_result_stmt->fetchAll(PDO::FETCH_ASSOC);
            echo '<div class="col-md-4" style="border:2px solid #000;">   
                                        <h2 class="text-center">' . ++$numbering . '</h2>
                                     `<table class="table" border="2">';
            echo ' <tr><th class="text-center"> Result</th> <th class="text-center"> Value</th> <th class="text-center">Expected Value/Ref</th> </tr> ';
            foreach ($investigation_result_info_list as $key => $investigation_result_info_) {
                $result = $investigation_result_info_['result'];
                $value_title = $investigation_result_info_['value_title'];
                $value_ref = $investigation_result_info_['value_ref'];
                echo ' <tr>
                                                <td> ' . $value_title . '</td> 
                                                <td class="text-center"> ' . $result . '</td> 
                                                <td class="text-center"> ' . $value_ref . '</td> 
                                                </tr> ';
            }

            echo '</table> 
                                   <br>
                                    <small>
									<b>Requested by:  ' . $investigation_['request_by'] . ' <br>
									<b>Requested On:  ' . dateFormat_($investigation_['request_date']) . ' <br>
									<b>Status:  ' . $lab_status . ' <br>
									<b>Comment:  ' . $investigation_result_info['comment'] . ' <br> 
									<b>Entered by:  ' . $investigation_result_info['entered_by'] . ' <br> 
									Date:  ' . dateFormat_($investigation_result_info['result_date']) . '</b></small>
                                    </div>
                                    ';
        }
    }

    ///// Report Format /////////////
    if ($field_type == 'report') {
        $investigation_result_stmt = $db->prepare('SELECT * FROM lab_result WHERE test_no = ? AND lab_no = ? LIMIT 1');
        $investigation_result_stmt->execute(array($test_id, $investigation_['labrequest_no']));
        if ($investigation_result_stmt->rowCount() > 0) {
            $investigation_result_info  = $investigation_result_stmt->fetch(PDO::FETCH_ASSOC);
            $result = $investigation_result_info['field_value'];
            $field_name = $investigation_result_info['field_name'];
            echo '<div style="max-width: 500px; padding:20px;border:2px solid #000; display:inline-block">  ' . $result . '
                                    <h2 class="text-center">' . ++$numbering . '</h2>
                                    <br>
                                    <small>
									<b>Requested by:  ' . $investigation_['request_by'] . ' <br>
									<b>Requested On:  ' . dateFormat_($investigation_['request_date']) . ' <br>
									<b>Status:  ' . $lab_status . ' <br>
									<b>Comment:  ' . $investigation_result_info['comment'] . ' <br> 
									<b>Entered by:  ' . $investigation_result_info['entered_by'] . ' <br> 
									Date:  ' . dateFormat_($investigation_result_info['result_date']) . '</b></small>
                                    </div>';
        } else {
            echo '';
        }
    }
} else {
    ///echo 'Test result format not defined...';
}
