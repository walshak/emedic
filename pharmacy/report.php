<div class="row">

    <div class="col-lg-6">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Stock Control Reports</h5>
            </div>

            <div class="ibox-content">
                <br><br>

                <a href="index.php?print&all">All Drugs Status Report</a>

                <hr>
                <form action="index.php?print" method="POST" id="subject2" name="subject2" enctype="multipart/form-data">

                    <div class="form_sep">
                        <label for="reg_select" class="req">Category Report</label>
                        <select name="desccc" id="desccc" class="form-control" data-required="true">
                            <option selected="selected" value="">Select...</option>
                            <option value="Expired">Expired drugs</option>
                            <option value="Expiring">Expiring drugs in days</option>
                            <option value="rorder">Re-order drugs</option>
                        </select>
                    </div>

                    <div class="form_sep">
                        <div id="expire_days">
                            <label for="reg_select" class="">Enter days</label>
                            <input type="number" id="days" name="days" class="form-control" data-required="true" min="1">
                        </div>
                    </div>

                    <div class="form_sep">
                        <button class="btn btn-success" type="submit" name="apply_task" id="apply_task">Apply</button>
                    </div>
                </form>



                <br>
                <hr>

                <h3 style="color:brown;">VIEW MORE REPORT AND STATISTICS <sup style="color: red;;">New</sup></h3>

                <a href="statistics.php" style="font-size: 16px;;"> >> Click for more Reports</a>



            </div>

        </div>
    </div>
    <div class="col-lg-6">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Sales Report</h5>
            </div>

            <div class="ibox-content">
                <form action="index.php?print" method="POST" id="subject2" name="subject2" enctype="multipart/form-data">

                    <div class="form_sep">
                        <label for="reg_select" class="req">Report type</label>
                        <select name="rpt_type" id="rpt_type" class="form-control" data-required="true">
                            <option selected="selected" value="">Select...</option>
                            <option value="at">Summary</option>
                            <option value="ps">Patient(s) Seen</option>
                            <option value="dcr">Credit Dispensed</option>
                            <option value="inv">Inventory Report</option>
                            <option value="inv_summary">Inventory Closing Balance</option>

                            <?php if ($unit_head == 1) { ?>
                                <option value="inv_drug">Inventory by Item</option>
                            <?php } ?>
                            <option value="ap">All (Drug Dispensed Summary & Patient List)</option>
                            <!--         <option value="d_r">Drugs Return by Patients</option>-->
                            <option value="rhmo">Report by HMO</option>
                            <option value="visit">Patient Visit</option>
                            <option value="doc_pres">Prescription Dispense/Not Dispense Status for Patients</option>
                        </select>
                    </div>

                    <?php if ($unit_head == 1) { ?>

                        <div class="form_sep" id="inv_item">
                            <label for="reg_input_no" class="req">Search for stock here</label>
                            <select name="searchdrug_inv" class="input-sm chosen-select" style="width:350px;">
                                <option selected="selected" value="">Search and Select</option>
                                <?php
                                $stmt = $db->query("SELECT sn, product_name FROM stock_table WHERE stock_table = 'Pharmacy' ORDER BY product_name");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <option value="<?php echo $row['sn']; ?>"><?php echo $row['product_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <?php
                        $stmt = $db->prepare("SELECT DISTINCT fullname FROM admin_users WHERE rights = :rights AND status = :status ORDER BY count DESC");
                        $stmt->bindValue(':rights', 'PH', PDO::PARAM_STR);
                        $stmt->bindValue(':status', '1', PDO::PARAM_STR);
                        $stmt->execute();
                        ?>

                        <div class="form_sep" id="staff_status">
                            <label for="reg_select" class="">Staff Name</label>
                            <select name="staff" id="staff" class="form-control" data-required="true" style="font-size:15px;">
                                <option selected="selected" value="">Select staff name...</option>
                                <option value="All Staff">All Staff</option>
                                <?php while ($row_rstSelect = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <option value="<?php echo $row_rstSelect['fullname']; ?>"><?php echo $row_rstSelect['fullname']; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                    <?php } ?>



                    <div id="rhmo_id">
                        <div class="form_sep"></div>
                        <div class="form_sep">
                            <?php
                            $stmt = $db->prepare("SELECT * FROM insurance_tbl 
    WHERE status = :status AND (insurance_type = :type1 OR insurance_type = :type2 OR insurance_type = :type3) 
    ORDER BY insurance_name");
                            $stmt->bindValue(':status', 'active', PDO::PARAM_STR);
                            $stmt->bindValue(':type1', 'PHIS', PDO::PARAM_STR);
                            $stmt->bindValue(':type2', 'NHIS', PDO::PARAM_STR);
                            $stmt->bindValue(':type3', 'Corporate', PDO::PARAM_STR);
                            $stmt->execute();
                            ?>
                            <label for="reg_select" class="">PHIS/NHIS</label>
                            <select name="hmo_nhis[]" id="hmo_nhis" data-placeholder="Select.." class="chosen-select" multiple style="width:350px;" tabindex="4">
                                <?php while ($row_rstSelect = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <option value="<?php echo $row_rstSelect["insurance_no"] . '__' . $row_rstSelect["insurance_name"]; ?>"><?php echo $row_rstSelect["insurance_name"]; ?></option>
                                <?php } ?>
                            </select>
                        </div>


                        <div class="form_sep">
                            <label for="reg_select" class="">PHIS/NHIS Type</label>
                            <select name="hmo_type" id="hmo_type" class="form-control" data-required="true" style="font-size:15px;">
                                <option selected="selected" value="">Select HMO...</option>
                                <option value="PHIS">PHIS</option>
                                <option value="NHIS">NHIS</option>
                                <option value="Corporate">Corporate</option>
                            </select>
                        </div>
                    </div>

                    <div class="form_sep"></div>
                    <div class="form_sep">
                        <strong>Search All Requests by Dates</strong>
                        <div class="form-group">
                            <div class="input-daterange input-group">
                                <input type="date" class="form-control" name="from_date" value="<?php echo date('Y-m-d'); ?>" />
                                <span class="input-group-addon">to</span>
                                <input type="date" class="form-control" name="to_date" value="<?php echo date('Y-m-d'); ?>" />

                            </div>
                        </div>
                    </div>


                    <div class="form_sep">
                        <table width="100%">
                            <tr>
                                <td>
                                    <button class="btn btn-success" type="submit" name="apply_task2" id="apply_task2">Apply</button>
                                </td>
                                <td>
                                    <div align="right">
                                        <a href="index.php?rpt" class="btn btn-warning">Reset</a>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <hr>
                </form>


            </div>

        </div>
    </div>

    <div class="col-lg-6">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Clinical forms Report</h5>
            </div>

            <div class="ibox-content">
                <form action="clinical_forms_rep.php" method="GET" name="subject2">

                    <div class="input-daterange input-group">
                        <input type="date" class="form-control" name="from_date" value="<?php echo date('Y-m-d'); ?>" />
                        <span class="input-group-addon">to</span>
                        <input type="date" class="form-control" name="to_date" value="<?php echo date('Y-m-d'); ?>" />

                    </div>
                    <hr>
                    <div class="form_sep">
                        <div id="expire_days">
                            <label for="reg_select" class="">Enter Patient Hopital Number(optional)</label>
                            <input type="text" id="hosp_no" name="hosp_no" class="form-control" data-required="true" min="1">
                        </div>
                    </div>

                    <div class="form_sep">
                        <button class="btn btn-success" type="submit" name="apply_task3" id="apply_task3">Apply</button>
                    </div>
                </form>

            </div>

        </div>
    </div>

</div>