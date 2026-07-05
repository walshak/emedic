<div class="row">

    <div class="col-lg-12">
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
                            <option value="at">Consumable Sales Summary</option>
                            <option value="ps">Patient(s) Seen</option>
                            <option value="dcr">Credit Services</option>


                            <?php if ($unit_head == 1) { ?>
                                <option value="inv_drug">Nursing Services Report</option>
                            <?php } ?>
                            <option value="ap">All (Comsumable Dispensed Summary & Patient List)</option>
                            <option value="rhmo">Report by HMO</option>
                            <option value="visit">Patient Visit</option>
                            <option value="adm">Admission</option>
                            <option value="labour">Labour Summary Report</option>
                        </select>
                    </div>

                    <?php if ($unit_head == 1) { ?>

                        <div class="form_sep" id="inv_item">
                            <label for="reg_input_no" class="req">Select Service Name here</label>
                            <select name="searchdrug_inv" class="input-sm chosen-select" style="width:350px;">
                                <option selected="selected" value="">Search and Select</option>
                                <?php $stmt = $db->query("SELECT sn,item_service FROM prices_table where price_table='Nursing Services' order by item_service");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <option value="<?php echo $row['sn']; ?>"><?php echo $row['item_service']; ?></option>
                                <?php } ?>
                            </select>

                        </div>

                        <?php
                        $stmt = $db->query("SELECT distinct fullname FROM admin_users WHERE rights='NS' and status='1' order by count desc");
                        ?>
                        <div class="form_sep" id="staff_status">
                            <label for="reg_select" class="">Staff Name</label>
                            <select name="staff" id="staff" class="form-control" data-required="true" style="font-size:15px;">
                                <option selected="selected" value="">Select staff name...</option>
                                <option value="All Staff">All Staff</option>
                                <?php while ($row_rstSelect = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <option value="<?php echo $row_rstSelect["fullname"]; ?>"><?php echo $row_rstSelect["fullname"]; ?></option>
                                <?php  } ?>
                            </select>
                        </div>

                    <?php } ?>


                    <div id="rhmo_id">
                        <div class="form_sep"></div>
                        <div class="form_sep">
                            <?php
                            $stmt = $db->query("SELECT distinct insurance_no,insurance_name FROM insurance_tbl 
WHERE status='active' and (insurance_type='PHIS' or insurance_type='NHIS' or insurance_type='Corporate') order by insurance_name");
                            ?>
                            <label for="reg_select" class="">PHIS/NHIS</label>
                            <select name="hmo_nhis[]" id="hmo_nhis" data-placeholder="Select.." class="chosen-select" multiple style="width:350px;" tabindex="4">
                                <?php while ($row_rstSelect = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <option value="<?php echo $row_rstSelect["insurance_no"] . '__' . $row_rstSelect["insurance_name"]; ?>"><?php echo $row_rstSelect["insurance_name"]; ?></option>
                                <?php  } ?>
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

                    <div id="adm_type">
                        <div class="form_sep"></div>
                        <div class="form_sep">
                            <label for="reg_select" class="">Type</label>
                            <select name="admission_type" id="admission_type" class="form-control" data-required="true" style="font-size:15px;">
                                <option selected="selected" value="">Select Type...</option>
                                <option value="3">On-Admission</option>
                                <option value="4">Discharge</option>
                            </select>
                        </div>

                        <div class="form_sep" id="">
                            <?php
                            $stmt = $db->query("SELECT * FROM bed_mgt");
                            ?>
                            <label for="reg_select" class="">Room</label>
                            <select name="room" id="room" class="form-control" data-required="true" style="font-size:15px;">
                                <option selected="selected" value="">Select Type...</option>
                                <?php while ($row_rstSelect = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <option value="<?php echo $row_rstSelect["sn"]; ?>"><?php echo $row_rstSelect["tips"] . ': ' . $row_rstSelect["room_name"] . ' (bed: ' . $row_rstSelect["bed_no"] . ')'; ?></option>
                                <?php  } ?>
                            </select>
                        </div>




                    </div>

                    <div class="form_sep"></div>
                    <div class="form_sep">
                        <strong>Search All Requests by Dates</strong>
                        <div class="form-group" id="">
                            <div class="input-daterange input-group" id="">
                                <input type="date" class="input-sm form-control" name="from_date" />
                                <span class="input-group-addon">to</span>
                                <input type="date" class="input-sm form-control" name="to_date" />
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

</div>