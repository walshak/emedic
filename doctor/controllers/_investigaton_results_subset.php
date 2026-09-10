<?php
$req_no = $investigation_['labrequest_no'] ?? '';
$t_name = $test_name ?? $investigation_['test_name'] ?? 'Test';

$h_theme_color = '';
if (!empty($_SESSION['h_color_code_hex'])) {
    $h_theme_color = $_SESSION['h_color_code_hex'];
} elseif (isset($db)) {
    $h_stmt = $db->query("SELECT color_code_hex FROM hospital_details LIMIT 1");
    if ($h_stmt && $h_row = $h_stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($h_row['color_code_hex'])) {
            $h_theme_color = $h_row['color_code_hex'];
        }
    }
}
if (empty($h_theme_color)) {
    $h_theme_color = '#1ab394';
}

// 1. Query lab_result for this labrequest_no
$res_stmt = $db->prepare("SELECT * FROM lab_result WHERE lab_no = ? ORDER BY sn ASC");
$res_stmt->execute(array($req_no));
$lab_results_sub = $res_stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Query lab_scan_input_results
$inp_stmt = $db->prepare("SELECT * FROM lab_scan_input_results WHERE lab_request_no = ? ORDER BY sn ASC");
$inp_stmt->execute(array($req_no));
$input_results_sub = $inp_stmt->fetchAll(PDO::FETCH_ASSOC);

$entered_by_user = '';
$result_date_val = '';

if (!empty($lab_results_sub)) {
    $entered_by_user = $lab_results_sub[0]['entered_by'] ?? '';
    $result_date_val = $lab_results_sub[0]['result_date'] ?? '';
}

$is_html_report_sub = false;
$report_html_sub = '';

if (!empty($lab_results_sub)) {
    $single_row_sub = (count($lab_results_sub) === 1);
    $first_val_sub = $lab_results_sub[0]['field_value'] ?? '';
    $first_name_sub = trim($lab_results_sub[0]['field_name'] ?? '');
    $first_ref_sub = trim($lab_results_sub[0]['field_ref'] ?? '');
    $has_html_sub = ($first_val_sub !== strip_tags($first_val_sub));

    if ($single_row_sub && ($has_html_sub || (empty($first_name_sub) && empty($first_ref_sub)))) {
        $is_html_report_sub = true;
        $report_html_sub = $first_val_sub;
    }
} else {
    $raw_sub_note = trim($investigation_['result_note'] ?? '');
    if (!empty($raw_sub_note) && $raw_sub_note !== strip_tags($raw_sub_note)) {
        $is_html_report_sub = true;
        $report_html_sub = $raw_sub_note;
    }
}
?>
<div style="width: 100%; max-width: 750px; padding: 15px; border: 1px solid #e7eaec; border-radius: 4px; background: #fff; margin-bottom: 15px;">
    <h3 class="text-navy" style="margin-top:0; border-bottom: 2px solid <?php echo $h_theme_color; ?>; padding-bottom: 8px;">
        <i class="fa fa-flask"></i> <?php echo htmlspecialchars($t_name); ?>
        <small class="pull-right text-muted" style="font-size:12px;">Req #: <?php echo htmlspecialchars($req_no); ?></small>
    </h3>

    <?php if ($is_html_report_sub): ?>
        <style>
            .native-html-sub table {
                width: 100% !important;
                max-width: 100% !important;
                float: none !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                margin-bottom: 15px !important;
                border-collapse: collapse !important;
            }
            .native-html-sub table td, .native-html-sub table th {
                border: 1px solid #ccc !important;
                padding: 8px 12px !important;
                word-wrap: break-word !important;
            }
            .native-html-sub table tr:first-child td, .native-html-sub table tr:first-child th {
                background-color: <?php echo $h_theme_color; ?> !important;
                color: #ffffff !important;
                font-weight: bold !important;
            }
            .native-html-sub table tr:first-child p, .native-html-sub table tr:first-child span {
                color: #ffffff !important;
            }
            .native-html-sub::after {
                content: "";
                display: block;
                clear: both;
            }
        </style>
        <div class="native-html-sub" style="border: 1px solid #ddd; border-radius: 4px; padding: 15px; background-color: #fff; font-size: 14px; line-height: 1.6; color: #222; margin-top: 10px; overflow: hidden; clear: both;">
            <?php echo $report_html_sub; ?>
        </div>
    <?php elseif (!empty($lab_results_sub)): ?>
        <table class="table table-bordered table-striped" style="margin-top: 10px; font-size: 13px;">
            <thead>
                <tr style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important;">
                    <th width="35%" style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important; padding: 8px; font-weight: bold;">Test Parameter</th>
                    <th width="25%" style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important; padding: 8px; font-weight: bold;">Result Value</th>
                    <th width="25%" style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important; padding: 8px; font-weight: bold;">Reference Range</th>
                    <th width="15%" style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important; padding: 8px; font-weight: bold;" class="text-center">Flag</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lab_results_sub as $r_row): 
                    $p_name = !empty($r_row['field_name']) ? $r_row['field_name'] : $t_name;
                    $p_val = htmlspecialchars($r_row['field_value'] ?? '');
                    $p_ref = htmlspecialchars($r_row['field_ref'] ?? '');
                    $comment = trim($r_row['comment'] ?? '');

                    $flag_badge = '-';
                    if (!empty($comment)) {
                        if (preg_match('/\b(H|High)\b/i', $comment)) {
                            $flag_badge = '<span class="label label-danger">High</span>';
                        } elseif (preg_match('/\b(L|Low)\b/i', $comment)) {
                            $flag_badge = '<span class="label label-warning">Low</span>';
                        } elseif (preg_match('/\b(N|Normal)\b/i', $comment)) {
                            $flag_badge = '<span class="label label-primary">Normal</span>';
                        } else {
                            $flag_badge = '<span class="label label-info">' . htmlspecialchars($comment) . '</span>';
                        }
                    }
                ?>
                    <tr>
                        <td style="color: #222;"><strong><?php echo htmlspecialchars($p_name); ?></strong></td>
                        <td style="color: #222;"><?php echo $p_val !== '' ? $p_val : '-'; ?></td>
                        <td style="color: #222;"><?php echo $p_ref !== '' ? $p_ref : '-'; ?></td>
                        <td class="text-center"><?php echo $flag_badge; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php elseif (!empty($input_results_sub)): ?>
        <table class="table table-bordered table-striped" style="margin-top: 10px; font-size: 13px;">
            <thead>
                <tr style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important;">
                    <th width="40%" style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important; padding: 8px; font-weight: bold;">Test Parameter</th>
                    <th width="30%" style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important; padding: 8px; font-weight: bold;">Result Value</th>
                    <th width="30%" style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important; padding: 8px; font-weight: bold;">Expected Value / Ref</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($input_results_sub as $i_row): ?>
                    <tr>
                        <td style="color: #222;"><strong><?php echo htmlspecialchars($i_row['value_title']); ?></strong></td>
                        <td style="color: #222;"><?php echo htmlspecialchars($i_row['result']); ?></td>
                        <td style="color: #222;"><?php echo htmlspecialchars($i_row['value_ref'] ?? '-'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php else: 
        $raw_note = trim($investigation_['result_note'] ?? '');
        if (!empty($raw_note)):
            $lines = array_filter(array_map('trim', explode("\n", $raw_note)));
            $parsed_rows = [];
            $is_structured = true;
            foreach ($lines as $line) {
                if (preg_match('/^([^:]+):\s*([^(]+?)(?:\s*\(Ref:\s*([^)]+)\))?(?:\s*\[([^\]]+)\])?$/i', $line, $m)) {
                    $parsed_rows[] = [
                        'name' => trim($m[1]),
                        'value' => trim($m[2]),
                        'ref' => isset($m[3]) ? trim($m[3]) : '',
                        'flag' => isset($m[4]) ? trim($m[4]) : ''
                    ];
                } else {
                    $is_structured = false;
                    break;
                }
            }
            if ($is_structured && !empty($parsed_rows)): ?>
                <table class="table table-bordered table-striped" style="margin-top: 10px; font-size: 13px;">
                    <thead>
                        <tr style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important;">
                            <th width="35%" style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important; padding: 8px; font-weight: bold;">Test Parameter</th>
                            <th width="25%" style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important; padding: 8px; font-weight: bold;">Result Value</th>
                            <th width="25%" style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important; padding: 8px; font-weight: bold;">Reference Range</th>
                            <th width="15%" style="background-color: <?php echo $h_theme_color; ?> !important; color: #ffffff !important; padding: 8px; font-weight: bold;" class="text-center">Flag</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($parsed_rows as $p_row): 
                            $flag_badge = '-';
                            if (!empty($p_row['flag'])) {
                                if (preg_match('/\b(H|High)\b/i', $p_row['flag'])) {
                                    $flag_badge = '<span class="label label-danger">High</span>';
                                } elseif (preg_match('/\b(L|Low)\b/i', $p_row['flag'])) {
                                    $flag_badge = '<span class="label label-warning">Low</span>';
                                } else {
                                    $flag_badge = '<span class="label label-info">' . htmlspecialchars($p_row['flag']) . '</span>';
                                }
                            }
                        ?>
                            <tr>
                                <td style="color: #222;"><strong><?php echo htmlspecialchars($p_row['name']); ?></strong></td>
                                <td style="color: #222;"><?php echo htmlspecialchars($p_row['value']); ?></td>
                                <td style="color: #222;"><?php echo !empty($p_row['ref']) ? htmlspecialchars($p_row['ref']) : '-'; ?></td>
                                <td class="text-center"><?php echo $flag_badge; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="border: 1px solid #1ab394; border-radius: 4px; padding: 12px; background-color: #f9f9f9; font-size: 13px; margin-top: 10px;">
                    <?php echo nl2br(htmlspecialchars($raw_note)); ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-warning" style="margin-top:10px;">No result recorded yet for this investigation.</div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="well well-sm" style="clear: both; display: block; overflow: hidden; margin-top: 15px; margin-bottom: 0; font-size: 12px; background-color: #f5f5f5;">
        <div class="row">
            <div class="col-xs-6">
                <b>Requested by:</b> <?php echo htmlspecialchars($investigation_['request_by'] ?? ''); ?><br>
                <b>Requested On:</b> <?php echo !empty($investigation_['request_date']) ? date('d-M-Y h:i A', strtotime($investigation_['request_date'])) : '-'; ?><br>
                <b>Status:</b> <span class="label label-primary"><?php echo htmlspecialchars(strtoupper($investigation_['data_capture_status'] ?? $lab_status ?? '')); ?></span>
                <?php if (!empty($investigation_['abnormal_results'])): ?>
                    <br><b>Outcome:</b> <span class="label <?php echo (in_array(strtolower($investigation_['abnormal_results']), ['high', 'abnormal', 'critical']) ? 'label-danger' : (strtolower($investigation_['abnormal_results']) == 'low' ? 'label-warning' : 'label-success')); ?>"><?php echo htmlspecialchars($investigation_['abnormal_results']); ?></span>
                <?php endif; ?>
            </div>
            <div class="col-xs-6">
                <b>Entered By:</b> <?php echo htmlspecialchars($entered_by_user ?: ($investigation_['entered_by'] ?? '-')); ?><br>
                <b>Result Date:</b> <?php echo !empty($result_date_val) ? date('d-M-Y', strtotime($result_date_val)) : (!empty($investigation_['result_date']) ? date('d-M-Y', strtotime($investigation_['result_date'])) : '-'); ?><br>
                <?php if (!empty($investigation_['approved_by'])): ?>
                    <b>Approved By:</b> <?php echo htmlspecialchars($investigation_['approved_by']); ?><br>
                <?php endif; ?>
                <?php if (!empty($investigation_['attachment'])): ?>
                    <b>Attachment:</b> <i class="fa fa-paperclip"></i> <a href="../investigations/uploads/<?php echo htmlspecialchars($req_no . '.' . $investigation_['attachment']); ?>" target="_blank">View/Download File (.<?php echo htmlspecialchars($investigation_['attachment']); ?>)</a><br>
                <?php endif; ?>
                <?php if (!empty($investigation_['result_comment'])): ?>
                    <b>Comment:</b> <?php echo htmlspecialchars($investigation_['result_comment']); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
