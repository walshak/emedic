<div class="modal inmodal fade" id="bio_data_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
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



<div class="modal inmodal" id="seeSpecialistNotesModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Send Specialist/Doctor Request to Front Desk for Booking/Appointment</h4>
            </div>
            <div class="modal-body" id="seeSpecialistNotes_body">



                <script>
                    function toggleSpecialistDropdown() {
                        var type = document.getElementById("appointment_type").value;
                        var dropdown = document.getElementById("specialist_dropdown");
                        if (type === "see_specialist") {
                            dropdown.style.display = "block";
                        } else {
                            dropdown.style.display = "none";
                        }
                    }
                </script>

            </div>
        </div>
    </div>
</div>


<div class="modal inmodal" id="fluids_report_mdl" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">All Reports</h4>
            </div>
            <div class="modal-body" id="fluids_report_body">
                <h4 class="text-center text-danger">Loading, please wait...</h4>
            </div>
        </div>
    </div>
</div>


<div class="modal inmodal" id="adm_request" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Admission Request</h4>
            </div>
            <div class="modal-body">
                <form action="patient.php?hosp_no=<?php echo $hospital_no; ?>" method="POST" id="subject1" name="subject" enctype="multipart/form-data">
                    <div class="form_sep">
                        <label for="reg_select" class="req">Reason for Admission</label>
                        <textarea class="form-control" rows="5" name="reason_adm" required></textarea>
                    </div>
                    <br>

                    <?php if ($_SESSION['h_code'] == 'RHO') { ?>

                        <input type="hidden" value="admit_p" name="admit_type">
                    <?php } else { ?>
                        <div class="form_sep">
                            <label for="reg_select" class="req">Admission Type</label>
                            <select name="admit_type" class="form-control" required>
                                <option value=""> -- Select Admission Type --</option>
                                $_SESSION['h_code'] = $row['code'];
                                <option value="admit_o">Admit to Observation (24 hours or less)</option>
                                <option value="admit_p">Admit Patient</option>
                            </select>
                        </div>

                    <?php } ?>



                    <br>


                    <div>
                        <label for="reg_select" class="req">Doctor Name Sending Admission Request</label>
                        <input type="text" class="typeahead form-control  " data-provide="typeahead" id="typeahead_search_specialist"
                            placeholder="Search for Doctor/Specialist"
                            value="<?= $_SESSION["fullname"]; ?>"
                            autocomplete="off" required>
                        <input type="hidden" name="created_by" id="typeahead_search_specialist_id" value="<?= $_SESSION["id"]; ?>">
                        <input type="hidden" name="created_by_name" id="typeahead_search_specialist_name" value="<?= $_SESSION["fullname"]; ?>">
                    </div>
                    <br>
                    <br>
                    <table width="100%">
                        <tr>
                            <td>

                                <button class="btn btn-success btn-sm" type="submit" name="add_admit_request" id="add_admit_request" onclick="return confirm('Are you sure you want to SAVE Admission request ?')">Send Request Now</button>
                            </td>
                            <td>
                                <div align="right">
                                    <button class="btn btn-danger btn-sm" name="cancel_admit" id="cancel_admit" data-dismiss="modal">Cancel</button>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <input type="hidden" name="app_no" value="<?php echo $appointment_number; ?>" />
                    <input type="hidden" name="hosp_no" value="<?php echo $hospital_no; ?>" />
                    <input type="hidden" name="ap_type" value="<?php echo $patient_access_type; ?>" />
                    <input type="hidden" name="interest" value="<?php echo $appointment_interest; ?>" />
                    <input type="hidden" name="insurance_type" value="<?php echo $patient_insurance; ?>" />
                </form>
            </div>
        </div>
    </div>
</div>


<div class="modal inmodal" id="patient-alert-modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
                <h4 class="text-center">Set Patient Alert!</h4>
                <div>
                    <textarea name="patient-alert-notes" id="patient-alert-notes" class="form-control" cols="30" rows="4" maxlength="50"></textarea>
                </div>

                <div class="form_sep">
                    <label for="reg_select" class="req">Who Should See this Alert</label>
                    <select name="who_should_see" id="who_should_see" class="form-control" required>
                        <option value="1" selected>--select --</option>
                        <option value="1">Clinical Staff Only</option>
                        <option value="2">All Staff</option>
                    </select>
                </div>

                <hr>
                <input type="hidden" name="hosp_no" id="patient_alert_hospital_no" value="<?php echo $hospital_no; ?>" />
                <button class="btn btn-sm btn-primary" id="save-patient-alert_2">Save Alert</button>
                <button class="btn btn-sm btn-default" data-dismiss="modal">Close</button>
                <HR>
                <div id="patient-alert-tbody_2"></div>
            </div>

        </div>
    </div>
</div>

<div class="modal inmodal" id="ward_review_mdl" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>

                <h4 class="modal-title">Doctor's Ward Rounds</h4>
            </div>

            <div class="modal-body" id="ward_review_body">


            </div>
        </div>
    </div>
</div>
<div class="modal inmodal" id="view_Edit_stock_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>

                <h4 class="modal-title">Doctor's Ward Rounds</h4>
            </div>

            <div class="modal-body" id="view_Edit_stock_body">


            </div>
        </div>
    </div>
</div>


<div class="modal inmodal" id="previous_adm_reason_mdl" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>

                <h4 class="modal-title"></h4>
            </div>

            <div class="modal-body" id="previous_adm_reason_body">


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>


<!-- Medication Plan Modal -->
<div class="modal inmodal" id="add_view_plan_mdl" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xxl">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title">Medication Plan</h4>
            </div>

            <div class="modal-body" id="add_view_plan_body">
                <!-- Your content goes here -->
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>




<div class='modal  fade' id='newPatientAllergiesModal' tabindex='-1' role='dialog' aria-hidden='true' data-keyboard='false'>
    <div class='modal-dialog'>
        <div class='modal-content'>

            <div class='modal-header'>
                <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>×</button>
                <h4 class='modal-title' id=''>Add Allergies </h4>
            </div>
            <div class='modal-body' style='min-height: 300px'>

                <h3>Enter Patient allergies below (Max: 100/ Min: 10 Char.)</h3>
                <textarea cols='30' rows='4' class='form-control ' name='allergies_notes' id='allergies_notes' maxlength="100" required><?= $complain; ?></textarea>

                <strong style="color:black;">To Delete Allergies, Clear and click Save button </strong>
                <input type='hidden' name='hospital_no' id='hospital_no' value="<?php echo $hospital_no; ?>">
                <input type='hidden' name='app_no' id='app_no' value="<?php echo $appointment_number; ?>">
                <hr>
                <button class='btn btn-sm btn-primary ' onClick="save_allergies()" name="save">Save </button>
                <button class="btn btn-sm btn-danger btn-sm" data-dismiss="modal">Close</button>

            </div>


        </div>
    </div>
</div>

<div class="modal inmodal" id="chart_mdl" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl" style="width: 90%;">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>

                <h4 class="modal-title"></h4>
            </div>

            <div class="modal-body" id="chart_body" style="overflow-y: scroll; height:800px;">


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>


<div class="modal inmodal" id="checkplan_mdl" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>

                <h4 class="modal-title"></h4>
            </div>

            <div class="modal-body" id="checkplan_body">


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>





<div class="modal inmodal" id="fluids_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
                <h4 class="text-center">STOP FLUIDS CHARTS</h4>

                <form action="patient.php?hosp_no=<?= $hospital_no; ?>" method="POST" id="subject" name="subject">

                    <div>
                        <strong>Enter Remarks</strong>
                        <textarea name="remarks_" class="form-control" cols="30" rows="4" maxlength="50"></textarea>
                    </div>
                    <hr>
                    <input type="hidden" name="hospital_no_rmk" id="" value="<?php echo $hospital_no; ?>" />
                    <input type="hidden" name="starting" id="" value="<?php echo $starting; ?>" />


                    <button class="btn btn-sm btn-primary" name="fluids_remarks">Save</button>
                    <button class="btn btn-sm btn-default" data-dismiss="modal">Close</button>
                </form>
            </div>

        </div>
    </div>
</div>



<div class="modal inmodal fade" id="patient_pharm_doctor_chats_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Messages from Pharmacy</h4>
            </div>
            <div id="patient_pharm_doctor_chats_modal_body">
            </div>
            <div>
                <hr>
                <p class="text-center"><button class="btn btn-danger" class="close" data-dismiss="modal" aria-hidden="true"> Close </button></p>
            </div>
        </div>
    </div>
</div>



<div class="modal inmodal" id="Consumable_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

            </div>
            <div class="modal-body" id="Consumable_Modal_body">


            </div>
        </div>
    </div>
</div>


<div class="modal inmodal" id="billable_room" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>

                <h4 class="modal-title"></h4>
            </div>

            <div class="modal-body" id="billable_room_form">


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>