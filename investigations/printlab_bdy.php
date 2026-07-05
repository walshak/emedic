                      <div class="table-responsive m-t">
                          <?php if ($result_date == '') {
                                $result_date = $result_date2;
                            }
                            ?>

                          <body oncopy="return false" oncut="return false" onpaste="return false">
                              <style>
                                  .invoice-table td {
                                      border: 1px solid gray;
                                  }

                                  .invoice-table table {
                                      width: 100%;
                                      border-collapse: collapse;
                                  }

                                  .invoice-table td {
                                      max-width: 0;
                                      /* Forces cell to respect width constraints */
                                      overflow: hidden;
                                      /* Prevents content overflow */
                                  }

                                  /* Handle any tables within the field values */
                                  .invoice-table td table {
                                      width: 100% !important;
                                      /* Force any nested tables to 100% of container */
                                      table-layout: fixed !important;
                                      /* Fixed layout for better control */
                                      font-size: inherit;
                                      /* Inherit font size for consistent scaling */
                                      margin: 0 !important;
                                      /* Remove any margins */
                                      padding: 0 !important;
                                      /* Remove any padding */
                                  }

                                  /* Handle cells within nested tables */
                                  .invoice-table td table td {
                                      word-wrap: break-word;
                                      /* Allow long words to break */
                                      overflow-wrap: break-word;
                                      white-space: normal;
                                      /* Allow text to wrap */
                                      min-width: 0;
                                      /* Allow cell to shrink below content size */
                                      max-width: none;
                                      /* Remove max-width constraint for nested cells */
                                  }

                                  /* Force images to be responsive */
                                  .invoice-table td img {
                                      max-width: 100% !important;
                                      height: auto !important;
                                  }
                              </style>

                              <table class="table invoice-table" width="100%" cellpadding="5" cellspacing="5" style="border:1px solid #000; border-collapse:collapse; font-size:12px; font-family:Arial, Helvetica, sans-serif;">
                                  <tbody>

                                      <tr style="background-color: <?php echo adjustBrightness($_SESSION['h_color_code_hex'], 0.9); ?>; color:white;">
                                          <td width="33%" style="border-bottom:1px solid #000; border-top:1px solid #000;"><?php echo '<strong>' . $test_name . '</strong>'; ?> | LAB NO: LB<?php echo $lab_no_bill ?></td>
                                          <td width="33%" style="border-bottom: 1px solid #000; border-top: 1px solid #000; text-align:left">
                                              <?php if ($_SESSION['section'] == 'Laboratory') {
                                                    echo 'Specimen: ' . $spm;
                                                } ?></td>
                                          <td width="33%" style="border-bottom: 1px solid #000; border-top: 1px solid #000; text-align:left">Requested By: <?php echo $requesting_physician ?></td>
                                      </tr>

                                      <?php if ($field_type == 'values') { ?>
                                          <?php
                                            $stmt5 = $db->prepare("SELECT * FROM lab_scan_input_results WHERE lab_request_no = :labrequest_no AND test_no = :test_id");
                                            $stmt5->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
                                            $stmt5->bindParam(':test_id', $test_id, PDO::PARAM_STR);
                                            $stmt5->execute();

                                            while ($rowx = $stmt5->fetch(PDO::FETCH_ASSOC)) {
                                                $sn = 1;
                                            ?>
                                              <tr>
                                                  <td width="33%" style="border-bottom: 1px solid #000; text-align:left"><?php echo $rowx['value_title'] ?></td>
                                                  <td width="33%" style="border-bottom: 1px solid #000; text-align:left"><?php echo '<strong>Result:</strong> ' . $rowx['result'] ?></td>
                                                  <td width="33%" style="border-bottom: 1px solid #000; text-align:left"><?php echo 'Ref.: ' . $rowx['value_ref'] ?></td>
                                              </tr>
                                          <?php } ?>
                                      <?php } elseif ($field_type == 'report' or $field_type == '') { ?>


                                          <?php if ($field_ref != '') { ?>
                                              <tr>
                                                  <td><strong>Reference</strong></td>
                                                  <td colspan="2" style="border-bottom: 1px solid #000; text-align:left"><?php echo $field_ref; ?></td>
                                              </tr>
                                          <?php } ?>
                                          <tr>
                                              <td colspan="3" style="font-size:12px; font-family:Arial, Helvetica, sans-serif; text-align:left"><?php echo $test_result; ?></td>
                                          </tr>

                                      <?php } elseif ($field_type == 'value' or $field_type == 'options') {

                                            $stmt5 = $db->prepare("SELECT reference FROM lab_scan_fields WHERE test_no = :test_id LIMIT 1");
                                            $stmt5->bindParam(':test_id', $test_id, PDO::PARAM_STR);

                                            if ($stmt5->execute()) {
                                                if ($rowx = $stmt5->fetch(PDO::FETCH_ASSOC)) {
                                                    $field_ref = $rowx['reference'];
                                                }
                                            }

                                        ?>
                                          <tr>
                                              <td width="33%" style="border-bottom: 1px solid #000; text-align:left"><?php echo $field_name; ?></td>
                                              <td width="33%" style="border-bottom: 1px solid #000; text-align:left"><?php echo '<strong>Result:</strong> ' . $test_result; ?></td>
                                              <td width="33%" style="border-bottom: 1px solid #000; text-align:left"><?php echo 'Ref.: ' . $field_ref; ?></td>
                                          </tr>
                                      <?php } ?>

                                      <?php if ($comment != '') { ?>
                                          <tr>
                                              <td style="border-bottom: 1px solid #000; text-align:left"><strong>Comment</strong></td>
                                              <td colspan="2" style="border-bottom: 1px solid #000; text-align:left"><?php echo $comment; ?></td>
                                          </tr>
                                      <?php } ?>

                                      <tr>
                                          <td colspan="2" style="border-bottom: 1px solid #000; text-align:left"><?php echo '<strong>Prepared By</strong>: ' . $lab_sci_name . ' [ ' . $lab_sci_speciality . ' ]'; ?></td>
                                          <td>
                                              Result Date:&nbsp; <?php echo date("d-m-Y", strtotime($result_date)); ?></td>
                                      </tr>

                                  </tbody>
                              </table>
                          </body>