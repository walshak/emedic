<div class="modal inmodal fade" id="enter_results_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">


            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Investigation Management</h4>
            </div>
            <div class="modal-body" id="enter_results_body">
            </div>
        </div>
    </div>
</div>
<div class="modal inmodal fade" id="consultations_vitals_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Consultation Notes</h4>
            </div>
            <div class="modal-body" id="consultations_vitals_body">
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="view_results_list_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="main_mdl_top">Investigation Results Preview</h4>
            </div>
            <div class="modal-body" id="view_results_list_body">
            </div>
        </div>
    </div>
</div>



<div class="modal inmodal fade" id="Lab_Request_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--            <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">New Lab Request</h4>
			</div>-->



            <div class="modal-body">
                <form method="POST" id="lab_request_form">

                    <div class="form_sep">
                        <table width="100%">
                            <tr>
                                <td width="20%">
                                    <label for="reg_input_no" class="">Patient Number</label>
                                    <input type="text" name="patient_no" id="patient_no" class="form-control" readonly />
                                </td>
                                <td>
                                    <label for="reg_input_no" class="">Name</label>
                                    <input type="text" name="patient_name" id="patient_name" class="form-control" readonly />
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="form_sep">
                        <label for="reg_input_no" class="">Requesting Physician/Doctor</label>
                        <input type="text" name="doctor_name" id="doctor_name" class="form-control" />
                    </div>

                    <?php

                    if ($_SESSION['section'] == "") {
                        // receptionist
                        $category = "";
                        $where = "status='0'";
                        //	$title="";
                    } else {
                        $category = $_SESSION['section'];
                        $where = "category='$category' and status='0'";
                        //if($category=="Laboratory"){$title="Lab";}else{$title="Scans";}
                    }
                    $stmt2 = $db->query("SELECT lab.*, d.department, d.sn as dept_id 
								 FROM lab_scan as lab inner join department as d on d.sn=lab.dept 
								 WHERE hosp_price>0 and ext_price > 0 and  $where");
                    ?>

                    <div class="form_sep">
                        <label for="reg_input_no" class="req">Investigation to Request <small>select multiple items enabled</small></label>
                        <select name="lab_request[]" data-placeholder="Search..." class="chosen-select" multiple style="width:350px;" tabindex="4">
                            <?php while ($roww = $stmt2->fetch(PDO::FETCH_ASSOC)) { ?>
                                <option value="<?php echo $roww["sn"] . '__' . $roww["test"] . '__' . $roww["dept_id"]
                                                    . '__' . $roww["hosp_price"] . '__' . $roww["nhis_price"]
                                                    . '__' . $roww["coverage"] . '__' . $roww["insurance_type"]
                                                    . '__' . $roww["category"] . '__' . $roww["ext_price"] . '__' . $roww["combo_test"]; ?>"><?php echo $roww["test"] . ' (' . $roww["department"]  . ')'; ?></option>
                            <?php } ?>
                        </select>

                    </div>


                    <div class="form_sep">
                        <label for="reg_input_no" class="">Preferred Specimen(s)</label>
                        <select name="specimen[]" data-placeholder="Select.." class="chosen-select" multiple style="width:350px;" tabindex="4">
                            <option value="">Select</option>
                            <option value="Aspirate">Aspirate</option>
                            <option value="Urine">Urine</option>
                            <option value="Blood">Blood</option>
                            <option value="C.S.F">C.S.F</option>
                            <option value="Ear Swab">Ear Swab</option>
                            <option value="Eye Swab">Eye Swab</option>
                            <option value="Fluids">Fluids</option>
                            <option value="No Specimen Required">No Specimen Required</option>
                            <option value="Pap Smear">Pap Smear</option>
                            <option value="Semen">Semen</option>
                            <option value="Skin Scraping">Skin Scraping</option>
                            <option value="Sputum">Sputum</option>
                            <option value="Stool">Stool</option>
                            <option value="Throat Swab">Throat Swab</option>
                            <option value="Tissue">Tissue</option>
                            <option value="Urethral Swab">Urethral Swab</option>
                            <option value="Bence Jones Protein (Urine)">Bence Jones Protein (Urine)</option>
                            <option value="Viginal Swab">Viginal Swab</option>
                            <option value="Wound Swab">Wound Swab</option>

                        </select>
                    </div>

                    <div class="form_sep">
                        <label for="reg_input_no" class="req">Request Notes</label>
                        <textarea name="request_note" id="request_note" cols="15" rows="2" class="form-control" data-minlength="10" required></textarea>
                    </div>

                    <div class="form_sep">
                        <table width="100%">
                            <tr>
                                <td><button class="btn btn-primary btn-xs" type="submit" name="add_request">Add Request</button></td>
                                <td align="right">
                                    <a href="" class="btn btn-warning btn-xs Cancel_lab_request">Cancel</a>


                        </table>
                    </div>

                    <input type="hidden" name="requester" value="<?php echo $_SESSION['fullname']; ?>" />
                    <input type="hidden" name="rights" value="<?php echo $_SESSION['rights']; ?>" />
                    <input type="hidden" name="MM_update" value="Add_lab_request" />
                    <input type="hidden" name="buz" id="buz" />
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="fill_result_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--            <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id=""></h4>
			</div>-->
            <div class="modal-body" id="fill_result_body">
            </div>
        </div>
    </div>
</div>


<div class="modal inmodal fade" id="reject_confirm_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Reject Lab Results</h4>
            </div>
            <div class="modal-body" id="reject_body">
            </div>
        </div>
    </div>
</div>


<div class="modal inmodal fade" id="cancel_scan_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Cancel Scan Request</h4>
            </div>

            <div class="modal-body">
                <form method="POST" id="cancel_scan_form">

                    <strong>Are you sure you want Cancel this Request?</strong>
                    <hr>

                    <div class="pull-left">
                        <button class="btn btn-primary btn-xs" type="submit" name="cancel_yes">&nbsp;Yes&nbsp;</button>
                    </div>
                    <div class="pull-right">

                        <a href="#" class="btn btn-warning btn-xs cancel_no">&nbsp;No&nbsp;</a>
                    </div>


            </div>


            <input type="hidden" name="scan_request_no" id="scan_request_no" />
            <input type="hidden" name="MM_update" value="Add_scan_request" />

            </form>
        </div>
    </div>
</div>
</div>


<div class="modal inmodal fade" id="approve_scan_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id=""></h4>
            </div>

            <div class="modal-body">
                <form method="POST" id="approve_scan_form">

                    <strong>Submit for approval?</strong>
                    <hr>

                    <div class="pull-left">
                        <button class="btn btn-primary btn-xs" type="submit" name="cancel_yes">&nbsp;Yes&nbsp;</button>
                    </div>
                    <div class="pull-right">

                        <a href="#" class="btn btn-warning btn-xs cancel_no">&nbsp;No&nbsp;</a>
                    </div>


            </div>


            <input type="hidden" name="scan_request_no2" id="scan_request_no2" />
            <input type="hidden" name="MM_update" value="approve_scan_request" />

            </form>
        </div>
    </div>
</div>
</div>

<div class="modal inmodal fade" id="approve_confirm_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Approve Lab Results</h4>
            </div>
            <div class="modal-body" id="approve_body">
            </div>
        </div>
    </div>
</div>


<div class="modal inmodal fade" id="reorder_request_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Reorder Lab Request</h4>
            </div>
            <div class="modal-body" id="reorder_request_body">
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="view_results_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Results</h4>
            </div>
            <div class="modal-body" id="view_result_body">
            </div>
        </div>
    </div>
</div>



<div class="modal inmodal fade" id="view_notes_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Notes</h4>
            </div>
            <div class="modal-body" id="view_notes_body">
            </div>
        </div>
    </div>
</div>


<div class="modal inmodal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div style="height:25px;">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <strong>Lab Notes</strong>
            </div>
            <div class="modal-body" id="view_notes_body">

                <div class="form_sep">
                    <label for="reg_input_no" class="">Notes</label>
                    <textarea name="test_name" id="test_name" cols="15" rows="3" class="form-control" data-minlength="10"></textarea>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="cancel_req_confirm_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Cancel Request</h4>
            </div>
            <div class="modal-body" id="cancel_request_body">
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="take_speciment_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Take Specimen</h4>
            </div>
            <div class="modal-body" id="take_speciment_body">



            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="capture_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id=""></h4>
            </div>
            <div class="modal-body" id="capture_body">



            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="new_sale_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">New External Patient</h4>
            </div>

            <div class="modal-body">
                <form method="POST" id="new_sale_form">

                    <div class="form_sep">
                        <label for="reg_input_name" class="req">Patient Fullname (Surname, Others):</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>

                    <div class="form_sep">
                        <label for="reg_select" class="req">Gender</label>
                        <select name="gender" id="gender" class="form-control" required>
                            <option selected="selected" value="">Select...</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>

                    <div class="form_sep">
                        <table width="100%" cellpadding="5">
                            <tr>
                                <td>
                                    <div class="form_sep" id="">
                                        <label class="font-noraml">Date of Birth or Age</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input type="date" class="form-control" name="dob">
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="form_sep">
                                        <label for="reg_select" class="req">Age</label>
                                        <input type="number" id="age" name="age" max="100" min="0" maxlength="3" value="0" class="form-control">
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="form_sep">
                        <label for="reg_input_name" class="req">Phone:</label>
                        <input type="text" id="phone" name="phone" class="form-control" required>
                    </div>

                    <div class="form_sep">
                        <label for="reg_input_name" class="req">Email Address:</label>
                        <input type="email" id="email_address" name="email_address" class="form-control" required>
                    </div>

                    <div class="form_sep">
                        <label for="reg_input_name" class="req">Address:</label>
                        <input type="text" id="addr" name="addr" class="form-control" required>
                    </div>



                    <div class="form_sep">
                        <label for="reg_select" class="">Patient is Referred from:</label>
                        <select name="referral_" id="referral_" class="form-control">
                            <option selected="selected" value="">Select...</option>
                            <?php $stmt = $db->query("SELECT name,sn FROM referrals");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                <option value="<?php echo $row['sn']; ?>"><?php echo $row['name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>


                    <div class="form_sep">
                        <button class="btn btn-success btn-xs" type="submit" name="save" id="save">Save</button>
                    </div>

                    <input type="hidden" name="MM_update" value="external_patient_add" />
                    <input type="hidden" name="transc_code" value="<?php echo $transc_code; ?>" />
                </form>


            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="bio_data_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Patient Biodata</h4>
            </div>
            <div class="modal-body" id="bio_data_body">
            </div>
        </div>
    </div>
</div>


<div class="modal inmodal fade" id="edit_ext_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">New External Patient</h4>
            </div>

            <div class="modal-body">
                <form method="POST" id="edit_ext_form">

                    <div class="form_sep">
                        <label for="reg_input_name" class="req">Patient Fullname (Surname, Others):</label>
                        <input type="text" id="name_edit" name="name_edit" class="form-control" required>
                    </div>

                    <div class="form_sep">
                        <label for="reg_select" class="req">Gender</label>
                        <select name="gender_edit" id="gender_edit" class="form-control" required>
                            <option selected="selected" value="">Select...</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>

                    <div class="form_sep">
                        <table width="100%" cellpadding="5">
                            <tr>
                                <td>
                                    <div class="form_sep" id="data_1">
                                        <label class="font-noraml">Date of Birth or Age</label>
                                        <div class="input-group date">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input type="text" class="form-control" name="dob_edit" id="dob_edit">
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="form_sep">
                                        <label for="reg_select" class="req">Age</label>
                                        <input type="number" id="age" name="age" max="100" min="0" maxlength="3" value="0" class="form-control">
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="form_sep">
                        <label for="reg_input_name" class="req">Phone:</label>
                        <input type="text" id="phone_edit" name="phone_edit" class="form-control" required>
                    </div>

                    <div class="form_sep">
                        <label for="reg_input_name" class="req">Address:</label>
                        <input type="text" id="addr_edit" name="addr_edit" class="form-control" required>
                    </div>


                    <div class="form_sep">
                        <label for="reg_input_name" class="req">Email Address:</label>
                        <input type="email" id="email_addr_edit" name="email_addr_edit" class="form-control" required>
                    </div>


                    <div class="form_sep">
                        <label for="reg_select" class="">Patient is Referred from:</label>
                        <select name="referral_edit" id="referral_edit" class="form-control">
                            <option selected="selected" value="">Select...</option>
                            <?php $stmt = $db->query("SELECT name,sn FROM referrals");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                <option value="<?php echo $row['sn']; ?>"><?php echo $row['name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form_sep">
                        <button class="btn btn-success btn-xs" type="submit" name="save" id="save">Save</button>
                    </div>

                    <input type="hidden" name="MM_update" value="update_ext_patient" />
                    <input type="hidden" name="referral_edit_2" id="referral_edit_2" />
                    <input type="hidden" name="transc_code_edit" id="transc_code_edit" />
                </form>


            </div>
        </div>
    </div>
</div>


<div id="myModalx" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title"><strong>Message!</strong></h4>
            </div>
            <div class="modal-body">

                <form action="notice_view.php" method="POST" id="sub" name="sub" enctype="multipart/form-data">

                    <p><?php echo $my_note; ?></p>

                    <button type="submit" class="btn btn-primary">Yes, I have read it</button>
                </form>
            </div>
        </div>
    </div>
</div>