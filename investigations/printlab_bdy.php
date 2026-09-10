                      <div class="table-responsive m-t">
                          <?php if (empty($result_date)) {
                                $result_date = $result_date2 ?? date('Y-m-d');
                            }

                            if (empty($h_theme_color)) {
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
                            }

                            // Fetch all lab_result entries for this request
                            $stmt_pr_all = $db->prepare("SELECT * FROM lab_result WHERE lab_no = :lab_no ORDER BY sn ASC");
                            $stmt_pr_all->execute([':lab_no' => $labrequest_no]);
                            $bdy_lab_results = $stmt_pr_all->fetchAll(PDO::FETCH_ASSOC);

                            $is_html_report_bdy = false;
                            $report_html_bdy = '';

                            if (!empty($bdy_lab_results)) {
                                $single_row_bdy = (count($bdy_lab_results) === 1);
                                $first_val_bdy = $bdy_lab_results[0]['field_value'] ?? '';
                                $first_name_bdy = trim($bdy_lab_results[0]['field_name'] ?? '');
                                $first_ref_bdy = trim($bdy_lab_results[0]['field_ref'] ?? '');
                                $has_html_bdy = ($first_val_bdy !== strip_tags($first_val_bdy));

                                if ($single_row_bdy && ($has_html_bdy || (empty($first_name_bdy) && empty($first_ref_bdy)))) {
                                    $is_html_report_bdy = true;
                                    $report_html_bdy = $first_val_bdy;
                                }
                            } else {
                                $raw_n = trim($test_result ?? $result_note ?? '');
                                if (!empty($raw_n) && $raw_n !== strip_tags($raw_n)) {
                                    $is_html_report_bdy = true;
                                    $report_html_bdy = $raw_n;
                                }
                            }
                            ?>

                          <body oncopy="return false" oncut="return false" onpaste="return false">
                              <style>
                                  .invoice-table td, .invoice-table th {
                                      border: 1px solid #ccc;
                                  }

                                  .invoice-table table {
                                      width: 100%;
                                      border-collapse: collapse;
                                  }

                                  .invoice-table td {
                                      max-width: 0;
                                      overflow: hidden;
                                  }

                                  .invoice-table td table {
                                      width: 100% !important;
                                      table-layout: fixed !important;
                                      font-size: inherit;
                                      margin: 0 !important;
                                      padding: 0 !important;
                                  }

                                  .invoice-table td table td {
                                      word-wrap: break-word;
                                      overflow-wrap: break-word;
                                      white-space: normal;
                                      min-width: 0;
                                      max-width: none;
                                  }

                                  .invoice-table td img {
                                      max-width: 100% !important;
                                      height: auto !important;
                                  }
                              </style>

                              <table class="table invoice-table" width="100%" cellpadding="5" cellspacing="5" style="border:1px solid #000; border-collapse:collapse; font-size:12px; font-family:Arial, Helvetica, sans-serif; margin-bottom: 15px;">
                                  <tbody>

                                      <tr style="background-color: <?php echo $h_theme_color; ?> !important; color:#ffffff !important; font-weight:bold;">
                                          <td width="33%" style="border-bottom:1px solid #000; border-top:1px solid #000; padding:6px; color:#ffffff !important;"><?php echo '<strong>' . htmlspecialchars($test_name) . '</strong>'; ?> | LAB NO: LB<?php echo htmlspecialchars($lab_no_bill ?? '') ?></td>
                                          <td width="33%" style="border-bottom: 1px solid #000; border-top: 1px solid #000; text-align:left; padding:6px; color:#ffffff !important;">
                                              <?php if (($_SESSION['section'] ?? '') == 'Laboratory' || !empty($spm)) {
                                                    echo 'Specimen: ' . htmlspecialchars($spm ?? '');
                                                } ?></td>
                                          <td width="33%" style="border-bottom: 1px solid #000; border-top: 1px solid #000; text-align:left; padding:6px; color:#ffffff !important;">Requested By: <?php echo htmlspecialchars($requesting_physician ?? '') ?></td>
                                      </tr>

                                      <?php if ($is_html_report_bdy): ?>
                                          <tr>
                                              <td colspan="3" style="font-size:12px; font-family:Arial, Helvetica, sans-serif; text-align:left; padding:10px; line-height:1.6; color:#222;">
                                                  <?php echo $report_html_bdy; ?>
                                              </td>
                                          </tr>
                                      <?php elseif (!empty($bdy_lab_results)): ?>
                                          <tr>
                                              <td colspan="3" style="padding:0;">
                                                  <table width="100%" cellpadding="6" cellspacing="0" style="border-collapse:collapse; font-size:12px;">
                                                      <thead>
                                                          <tr style="background-color:<?php echo $h_theme_color; ?> !important; color:#ffffff !important; font-weight:bold;">
                                                              <td width="35%" style="background-color:<?php echo $h_theme_color; ?> !important; color:#ffffff !important; border-bottom:1px solid #ccc; padding:6px; font-weight:bold;">Test Component</td>
                                                              <td width="25%" style="background-color:<?php echo $h_theme_color; ?> !important; color:#ffffff !important; border-bottom:1px solid #ccc; padding:6px; font-weight:bold;">Result Value</td>
                                                              <td width="25%" style="background-color:<?php echo $h_theme_color; ?> !important; color:#ffffff !important; border-bottom:1px solid #ccc; padding:6px; font-weight:bold;">Reference Range</td>
                                                              <td width="15%" style="background-color:<?php echo $h_theme_color; ?> !important; color:#ffffff !important; border-bottom:1px solid #ccc; text-align:center; padding:6px; font-weight:bold;">Flag</td>
                                                          </tr>
                                                      </thead>
                                                      <tbody>
                                                          <?php foreach ($bdy_lab_results as $b_row): 
                                                              $comp_name = !empty($b_row['field_name']) ? $b_row['field_name'] : $test_name;
                                                              $comp_val = htmlspecialchars($b_row['field_value'] ?? '');
                                                              $comp_ref = htmlspecialchars($b_row['field_ref'] ?? '');
                                                              $comp_cm = trim($b_row['comment'] ?? '');

                                                              $f_tag = '-';
                                                              if (!empty($comp_cm)) {
                                                                  if (preg_match('/\b(H|High)\b/i', $comp_cm)) {
                                                                      $f_tag = '<strong style="color:red;">High [H]</strong>';
                                                                  } elseif (preg_match('/\b(L|Low)\b/i', $comp_cm)) {
                                                                      $f_tag = '<strong style="color:orange;">Low [L]</strong>';
                                                                  } else {
                                                                      $f_tag = htmlspecialchars($comp_cm);
                                                                  }
                                                              }
                                                          ?>
                                                              <tr>
                                                                  <td style="border-bottom:1px solid #eee; padding:6px; font-weight:bold; color:#222;"><?php echo htmlspecialchars($comp_name); ?></td>
                                                                  <td style="border-bottom:1px solid #eee; padding:6px; color:#222;"><?php echo $comp_val !== '' ? $comp_val : '-'; ?></td>
                                                                  <td style="border-bottom:1px solid #eee; padding:6px; color:#222;"><?php echo $comp_ref !== '' ? $comp_ref : '-'; ?></td>
                                                                  <td style="border-bottom:1px solid #eee; padding:6px; text-align:center;"><?php echo $f_tag; ?></td>
                                                              </tr>
                                                          <?php endforeach; ?>
                                                      </tbody>
                                                  </table>
                                              </td>
                                          </tr>

                                      <?php elseif ($field_type == 'values'): ?>
                                          <?php
                                            $stmt5 = $db->prepare("SELECT * FROM lab_scan_input_results WHERE lab_request_no = :labrequest_no AND test_no = :test_id");
                                            $stmt5->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
                                            $stmt5->bindParam(':test_id', $test_id, PDO::PARAM_STR);
                                            $stmt5->execute();

                                            while ($rowx = $stmt5->fetch(PDO::FETCH_ASSOC)) {
                                            ?>
                                              <tr>
                                                  <td width="33%" style="border-bottom: 1px solid #000; text-align:left; padding:6px; color:#222;"><?php echo htmlspecialchars($rowx['value_title']); ?></td>
                                                  <td width="33%" style="border-bottom: 1px solid #000; text-align:left; padding:6px; color:#222;"><?php echo '<strong>Result:</strong> ' . htmlspecialchars($rowx['result']); ?></td>
                                                  <td width="33%" style="border-bottom: 1px solid #000; text-align:left; padding:6px; color:#222;"><?php echo 'Ref.: ' . htmlspecialchars($rowx['value_ref']); ?></td>
                                              </tr>
                                          <?php } ?>

                                      <?php else: 
                                          $raw_n = trim($test_result ?? $result_note ?? '');
                                          $lines = array_filter(array_map('trim', explode("\n", $raw_n)));
                                          $parsed_bdy = [];
                                          $is_struct = true;
                                          foreach ($lines as $line) {
                                              if (preg_match('/^([^:]+):\s*([^(]+?)(?:\s*\(Ref:\s*([^)]+)\))?(?:\s*\[([^\]]+)\])?$/i', $line, $m)) {
                                                  $parsed_bdy[] = [
                                                      'name' => trim($m[1]),
                                                      'value' => trim($m[2]),
                                                      'ref' => isset($m[3]) ? trim($m[3]) : '',
                                                      'flag' => isset($m[4]) ? trim($m[4]) : ''
                                                  ];
                                              } else {
                                                  $is_struct = false;
                                                  break;
                                              }
                                          }
                                          if ($is_struct && !empty($parsed_bdy)): ?>
                                              <tr>
                                                  <td colspan="3" style="padding:0;">
                                                      <table width="100%" cellpadding="6" cellspacing="0" style="border-collapse:collapse; font-size:12px;">
                                                          <thead>
                                                              <tr style="background-color:<?php echo $h_theme_color; ?> !important; color:#ffffff !important; font-weight:bold;">
                                                                  <td width="35%" style="background-color:<?php echo $h_theme_color; ?> !important; color:#ffffff !important; border-bottom:1px solid #ccc; padding:6px; font-weight:bold;">Test Component</td>
                                                                  <td width="25%" style="background-color:<?php echo $h_theme_color; ?> !important; color:#ffffff !important; border-bottom:1px solid #ccc; padding:6px; font-weight:bold;">Result Value</td>
                                                                  <td width="25%" style="background-color:<?php echo $h_theme_color; ?> !important; color:#ffffff !important; border-bottom:1px solid #ccc; padding:6px; font-weight:bold;">Reference Range</td>
                                                                  <td width="15%" style="background-color:<?php echo $h_theme_color; ?> !important; color:#ffffff !important; border-bottom:1px solid #ccc; text-align:center; padding:6px; font-weight:bold;">Flag</td>
                                                              </tr>
                                                          </thead>
                                                          <tbody>
                                                              <?php foreach ($parsed_bdy as $pb_row): 
                                                                  $f_tag = '-';
                                                                  if (!empty($pb_row['flag'])) {
                                                                      if (preg_match('/\b(H|High)\b/i', $pb_row['flag'])) {
                                                                          $f_tag = '<strong style="color:red;">High [H]</strong>';
                                                                      } elseif (preg_match('/\b(L|Low)\b/i', $pb_row['flag'])) {
                                                                          $f_tag = '<strong style="color:orange;">Low [L]</strong>';
                                                                      } else {
                                                                          $f_tag = htmlspecialchars($pb_row['flag']);
                                                                      }
                                                                  }
                                                              ?>
                                                                  <tr>
                                                                      <td style="border-bottom:1px solid #eee; padding:6px; font-weight:bold;"><?php echo htmlspecialchars($pb_row['name']); ?></td>
                                                                      <td style="border-bottom:1px solid #eee; padding:6px;"><?php echo htmlspecialchars($pb_row['value']); ?></td>
                                                                      <td style="border-bottom:1px solid #eee; padding:6px;"><?php echo !empty($pb_row['ref']) ? htmlspecialchars($pb_row['ref']) : '-'; ?></td>
                                                                      <td style="border-bottom:1px solid #eee; padding:6px; text-align:center;"><?php echo $f_tag; ?></td>
                                                                  </tr>
                                                              <?php endforeach; ?>
                                                          </tbody>
                                                      </table>
                                                  </td>
                                              </tr>
                                          <?php else: ?>
                                              <?php if (!empty($field_ref)): ?>
                                                  <tr>
                                                      <td><strong>Reference</strong></td>
                                                      <td colspan="2" style="border-bottom: 1px solid #000; text-align:left; padding:6px;"><?php echo htmlspecialchars($field_ref); ?></td>
                                                  </tr>
                                              <?php endif; ?>
                                              <tr>
                                                  <td colspan="3" style="font-size:12px; font-family:Arial, Helvetica, sans-serif; text-align:left; padding:6px;"><?php echo nl2br($raw_n); ?></td>
                                              </tr>
                                          <?php endif; ?>
                                      <?php endif; ?>

                                       <?php 
                                       $cm_to_show = !empty($comment) ? $comment : (!empty($result_comment) ? $result_comment : (!empty($roww['result_comment']) ? $roww['result_comment'] : ''));
                                       $ab_to_show = !empty($abnormal_results) ? $abnormal_results : (!empty($roww['abnormal_results']) ? $roww['abnormal_results'] : '');
                                       $att_to_show = !empty($attachment) ? $attachment : (!empty($roww['attachment']) ? $roww['attachment'] : '');
                                       ?>

                                       <?php if (!empty($ab_to_show)): ?>
                                           <tr>
                                               <td style="border-bottom: 1px solid #000; text-align:left; padding:6px;"><strong>Outcome</strong></td>
                                               <td colspan="2" style="border-bottom: 1px solid #000; text-align:left; padding:6px;">
                                                   <strong style="color: <?php echo (in_array(strtolower($ab_to_show), ['high', 'abnormal', 'critical']) ? 'red' : (strtolower($ab_to_show) == 'low' ? 'orange' : 'green')); ?>;"><?php echo htmlspecialchars($ab_to_show); ?></strong>
                                               </td>
                                           </tr>
                                       <?php endif; ?>

                                       <?php if (!empty($cm_to_show)): ?>
                                           <tr>
                                               <td style="border-bottom: 1px solid #000; text-align:left; padding:6px;"><strong>Comment</strong></td>
                                               <td colspan="2" style="border-bottom: 1px solid #000; text-align:left; padding:6px;"><?php echo nl2br(htmlspecialchars($cm_to_show)); ?></td>
                                           </tr>
                                       <?php endif; ?>

                                       <?php if (!empty($att_to_show)): ?>
                                           <tr>
                                               <td style="border-bottom: 1px solid #000; text-align:left; padding:6px;"><strong>Attachment</strong></td>
                                               <td colspan="2" style="border-bottom: 1px solid #000; text-align:left; padding:6px;">
                                                   <a href="uploads/<?php echo htmlspecialchars($labrequest_no . '.' . $att_to_show); ?>" target="_blank">View Attached Document (.<?php echo htmlspecialchars($att_to_show); ?>)</a>
                                               </td>
                                           </tr>
                                       <?php endif; ?>

                                      <tr>
                                          <td colspan="2" style="border-bottom: 1px solid #000; text-align:left; padding:6px;"><?php echo '<strong>Prepared By</strong>: ' . htmlspecialchars($lab_sci_name ?? '') . ' [ ' . htmlspecialchars($lab_sci_speciality ?? '') . ' ]'; ?></td>
                                          <td style="padding:6px;">
                                              Result Date:&nbsp; <?php echo !empty($result_date) ? date("d-m-Y", strtotime($result_date)) : date("d-m-Y"); ?></td>
                                      </tr>

                                  </tbody>
                              </table>
                          </body>