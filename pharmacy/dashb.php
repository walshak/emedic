     <div class="row">
         <div class="col-lg-2">
             <div class="ibox float-e-margins">
                 <div class="ibox-title">
                     <span class="label label-success pull-right">Today</span>
                     <h5>Seen</h5>
                 </div>
                 <div class="ibox-content">
                     <h2 class="no-margins" id="seen-today-count">0</h2>
                     <div class="stat-percent font-bold text-success"><i class="fa fa-bolt"></i></div>
                     <small>Total Patients</small>
                 </div>
             </div>
         </div>

         <div class="col-lg-2">
             <div class="ibox float-e-margins">
                 <div class="ibox-title">
                     <span class="label label-primary pull-right">Today</span>
                     <h5>Dispensed</h5>
                 </div>
                 <div class="ibox-content">
                     <h2 class="no-margins" id="dsp-today-count">0</h2>
                     <div class="stat-percent font-bold text-success"><i class="fa fa-bolt"></i></div>
                     <small>Dispensed Total</small>
                 </div>
             </div>
         </div>

         <div class="col-lg-4">
             <div class="ibox float-e-margins">
                 <div class="ibox-title">
                     <h5>Stocks Expiring Status & Re-Order Information</h5>
                 </div>
                 <div class="ibox-content">
                     <table width="100%">
                         <tr>
                             <td>
                                 <h2 class="no-margins" id="exp-today-count"></h2>
                                 <small><strong style="color:#F00">Expired</strong></small>
                             </td>

                             <td>
                                 <h2 class="no-margins" id="exp_three_months_count"></h2>
                                 <small><strong style="color:#F00">In (3-Months)</strong></small>
                             </td>
                             <td>
                                 <h2 class="no-margins" id="exp_six_months_count"></h2>
                                 <small><strong style="color:#F00">In (6-Months)</strong></small>
                             </td>

                             <td>
                                 <h2 class="no-margins" id="odr-today-count"></h2>
                                 <small><b>Reorder Level</b></small>
                             </td>
                         </tr>
                     </table>
                 </div>
             </div>
         </div>


         <div class="col-lg-4">
             <div class="ibox float-e-margins">
                 <div class="ibox-title">
                     <span class="label label-danger pull-right">YEAR <?= date('Y'); ?></span>
                     <h5>Drug Dispensed/On-Credits</h5>
                 </div>
                 <div class="ibox-content">
                     <h2 class="no-margins" id="cr-today-count"></h2>
                     <div class="stat-percent font-bold text-success"><i class="fa fa-bolt"></i></div>
                     <small>Cash / Claim</small>
                 </div>
             </div>
         </div>


     </div>


     <div class="row">

         <div class="col-lg-4">
             <div class="ibox float-e-margins">
                 <div class="ibox-title">

                     <h5>Main Dashboard</h5>
                 </div>
                 <div class="ibox-content">
                     <div class="row">

                         <a href="index.php?price" class="btn btn-app"><i class="fa fa-question"></i>Price Enquiry</a>
                         <hr>
                         <?php



                            if ($_SESSION['stock_mgr_pharm'] == 1 && $_SESSION['dispensory'] == 0) { ?>
                             <a href="../admin/index.php?stock" class="btn btn-app"><i class="fa fa-tasks"></i>Stock/Inventory</a>
                         <?php } ?>


                         <a href="../admin/index.php?sale" class="btn btn-app"><i class="fa fa-shopping-cart"></i>External Sales</a>

                         <hr>
                         <a href="index.php?rpt" class="btn btn-app"><i class="fa fa-archive"></i>Report</a>
                         <?php if ($_SESSION['unit_head'] == 1) {                    ?>
                             <a href="../admin/index.php?datab" class="btn btn-app"><i class="fa fa-database"></i>Data Bank</a>
                             <a href="graph/index.php" class="btn btn-app"><i class="fa fa-bar-chart" style='color:brown; '></i>Pharmacy Graph</a>
                         <?php }
                            ?>
                     </div>
                 </div>
             </div>
         </div>

         <div class="col-lg-8">
             <div class="ibox float-e-margins">
                 <div class="ibox-title">
                     <h5>Patients Panel </h5>
                 </div>

                 <div class="ibox-content">

                     <form action="index.php?presc" method="POST">

                         <h2>General Patient Search</h2><strong></strong>

                         <div class="form_sep">
                             <label for="reg_input_no" class="">(Name or Phone or Hospital Number) <small style="color: red;">[Press Enter key Enabled]</small> </label>
                             <input type="text" id="search" name="search" class="form-control" style="border-color: black; " required>
                         </div>
                         <br>
                         <div class="form_sep">
                             <button type="submit" class="btn btn-primary btn btn-sm" name="apply_action" id="apply_action" onclick="search_patient()"><i class="fa fa-search"></i>&nbsp;Apply Search</button>
                         </div>
                     </form>
                     <hr>
                     <?php

                        if ($_SESSION['dispensory'] == 0) {
                            $patch_Dispens_query = "";
                        } else {
                            $patch_Dispens_query = " AND dept_dispensory_id ='$dept_id'"; // Use a placeholder for binding
                        }




                        // Assuming $db is your PDO database connection

                        // Optional: Sanitize or define $patch_Dispens_query if not done already

                        // Pending Dispense Count
                        $dsp_pd_stmt = $db->query("
                            SELECT COUNT(DISTINCT hospital_no) AS cnt 
                            FROM patient_ap_services 
                            WHERE serv_group = 'Pharmacy' 
                              AND paystatus = '1' 
                              AND drug_status = '0' 
                              $patch_Dispens_query
                        ");
                        $dsp_pd_count = $dsp_pd_stmt->fetch()['cnt'];

                        // Invoiced (but not paid) Count
                        $_inv_stmt = $db->query("
                        SELECT COUNT(DISTINCT hospital_no) AS cnt
                        FROM patient_ap_services
                        WHERE date_entry >= DATE_SUB(NOW(), INTERVAL 2 DAY)
                        AND date_entry <= NOW()
                        AND serv_group = 'Pharmacy'
                        AND paystatus = '0'
                        AND invoice_status = '1'
                        AND (pay_mode = 'cash' OR pay_mode = 'claim')
                        $patch_Dispens_query");

                        $_inv_count = $_inv_stmt->fetch()['cnt'];


                        // Encounter (Today's Appointments) Count
                        $att = '';
                        if (isset($_SESSION['dispensory']) && $_SESSION['dispensory'] == 1) {
                            $att = "AND dept = '$dept_id'";
                        }

                        $date_ap = date('Y-m-d');
                        $pVst_stmt = $db->query("
                            SELECT COUNT(DISTINCT hospital_no) AS cnt 
                            FROM apptm 
                            WHERE date_ap = '$date_ap' 
                            $att
                        ");
                        $pVst_count = $pVst_stmt->fetch()['cnt'];

                        // ✅ Set last 3 days
                        $dateStart = date('Y-m-d', strtotime('-2 days'));
                        $dateEnd   = date('Y-m-d'); // optional if needed for BETWEEN

                        // ✅ Count query
                        $query = "
    SELECT COUNT(*) AS cnt
    FROM stock_table_inven AS i
    WHERE i.inven_desc LIKE '%Returned%'
      AND i.insertion_date_time >= :dateStart
";

                        // ✅ Prepare & bind
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':dateStart', $dateStart);
                        $stmt->execute();

                        // ✅ Fetch count
                        $Rvs_count = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];




                        ?>

                     <!-- Action Buttons -->
                     <a href="index.php" class="btn btn-primary btn btn-xs">
                         <i class="fa fa-tag"></i> &nbsp;Request List
                     </a> &nbsp;|&nbsp;

                     <a href="index.php?adm" class="btn btn-success btn btn-xs">
                         <i class="fa fa-bed"></i> &nbsp;Admitted Patient
                     </a> &nbsp;|&nbsp;

                     <a href="index.php?pend" class="btn btn-warning btn btn-xs">
                         <i class="fa fa-edit"></i> &nbsp;Pending Dispense ( <?php echo $dsp_pd_count; ?> )
                     </a> &nbsp;|&nbsp;

                     <a href="index.php?vst" class="btn btn-success btn btn-xs">
                         <i class="fa fa-edit"></i> &nbsp;Encounter ( <?php echo $pVst_count; ?> )
                     </a> &nbsp;|&nbsp;

                     <a href="index.php?invoiced" class="btn btn-danger btn btn-xs">
                         <i class="fa fa-tasks"></i> &nbsp;Invoiced ( <?php echo $_inv_count; ?> )
                     </a>&nbsp;|&nbsp;

                     <a href="index.php?Reversed" class="btn btn-warning btn btn-xs">
                         <i class="fa fa-tasks"></i> &nbsp;Reversal ( <?php echo $Rvs_count; ?> )
                     </a>

                     <hr>
                     <?php if (isset($_GET['adm'])) { ?>
                         <div id="adm-table-container"></div>
                     <?php } elseif (isset($_GET['vst'])) { ?>
                         <div id="encounter-table-container"></div>
                     <?php } elseif (isset($_GET['invoiced'])) { ?>
                         <div id="invoiced-table-container"></div>
                     <?php } elseif (isset($_GET['Reversed'])) { ?>


                         <form action="index.php?Reversed" method="POST">

                             <div id="Reversed-table-container"></div>
                             <hr>
                             <div class="form_sep">
                                 <table width="">
                                     <tr>
                                         <td>

                                             <div class="form_sep">
                                                 <strong>Filter More Record by Dates</strong>
                                                 <div class="form-group">
                                                     <div class="input-daterange input-group">
                                                         <input type="date" class="form-control" name="from_date" value="<?php echo date('Y-m-d'); ?>" />
                                                         <span class="input-group-addon">to</span>
                                                         <input type="date" class="form-control" name="to_date" value="<?php echo date('Y-m-d'); ?>" />

                                                     </div>
                                                 </div>
                                             </div>
                                         </td>
                                         <td>

                                             <button class="btn btn-success" type="submit" name="apply_reversed">Apply</button>

                                         </td>
                                     </tr>
                                 </table>
                             </div>
                         </form>

                     <?php } elseif (isset($_GET['pend'])) { ?>
                         <div id="pend-table-container"></div>
                     <?php } else { ?>


                         <form action="index.php" method="POST">
                             <div class="">
                                 <table width="100%">
                                     <tr>

                                         <td>
                                             <label for="" class="" style="color: red; ">Patient(s) Request by Nurse/Doctor Name</label>
                                             <select class="chosen-select" class="form-control" name="requesters_drugs" id="requesters_drugs" required>

                                                 <option value="">-- Select --</option>
                                             </select>
                                         </td>
                                         <td>&nbsp;</td>
                                         <td>
                                             <label for="" class="">.</label><br>
                                             <button class="btn btn-success btn btn-sm" type="submit" name="apply_requester">Apply</button>
                                         </td>

                                         <td><label for="" class="">.</label><br><a href="index.php" class="btn btn-white btn btn-sm"><b>Refresh List</b></a></td>
                                     </tr>

                                 </table>
                             </div>
                         </form>
                         <hr>
                         <h4>Drug Request Lists</h4>
                         <div id="request-table-container"></div>
                     <?php } ?>

                 </div>

             </div>
         </div>

     </div>

     <div class="modal inmodal fade" id="patient_search_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="true">
         <div class="modal-dialog modal-xl">
             <div class="modal-content">
                 <div class="modal-header">
                     <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                     <h4 class="modal-title" id="">Patient Search Modal</h4>
                 </div>
                 <div class="modal-body" id="patient_search_body">

                 </div>
             </div>
         </div>
     </div>