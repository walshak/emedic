   <div class="modal inmodal fade" id="view_lab_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
       <div class="modal-dialog modal-lg">
           <div class="modal-content">
               <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                   <h4 class="modal-title" id=""></h4>
               </div>
               <div class="modal-body" id="view_lab_body">

               </div>

           </div>
       </div>
   </div>


   <div class="modal inmodal fade" id="view_lab_option_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
       <div class="modal-dialog modal-lg">
           <div class="modal-content">
               <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                   <h4 class="modal-title" id="">Lab Field Options</h4>
               </div>
               <div class="modal-body" id="view_lab_option_body">

               </div>

           </div>
       </div>
   </div>

   <div class="modal inmodal fade" id="view_lab_values_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
       <div class="modal-dialog modal-lg">
           <div class="modal-content">
               <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                   <h4 class="modal-title" id="">Lab Field Values</h4>
               </div>
               <div class="modal-body" id="view_lab_values_body">

               </div>

           </div>
       </div>
   </div>

   <div class="modal inmodal fade" id="add_Lab_TestParameters_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
       <div class="modal-dialog modal-sm">
           <div class="modal-content">
               <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                   <h4 class="modal-title" id=""></h4>
               </div>
               <div class="modal-body">

                   <form method="POST" id="insert_form_fields">

                       <input type="hidden" id="test_id" name="test_id" class="form-control" readonly>

                       <div class="form_sep">
                           <label for="reg_input_no" class="req">Investigation Name</label>
                           <input type="text" id="test_name" name="test_name" class="form-control" readonly>
                       </div>


                       <div class="form_sep">
                           <label for="reg_input_no" class="req">Field</label>
                           <input type="text" id="field_name" name="field_name" class="form-control" required="required">
                       </div>

                       <div class="form_sep">
                           <label for="reg_input_no" class="req">Field Input type</label>
                           <select name="field_type" id="field_type" class="form-control" required>
                               <option selected="selected" value="">Select...</option>

                               <option value="value">Value</option>
                               <!--                                <option value="values">More Values (More than one input box required)</option>
 -->
                               <option value="options">Options (Pick result from list)</option>
                               <option value="report">Enter Template Report</option>
                           </select>
                       </div>

                       <div class="form_sep">
                           <label for="reg_input_no" class="">Reference</label>
                           <input type="text" id="Reference" name="Reference" class="form-control">
                       </div>

                       <div class="form_sep">
                           <input type="submit" name="insert" id="insert" value="Insert" class="btn btn-success btn-xs" />
                       </div>
                       <input type="hidden" id="test_no" name="test_no" class="form-control">
                       <input type="hidden" name="MM_update" value="insert_form_fields" />

                   </form>


               </div>

           </div>
       </div>
   </div>

   <div class="modal inmodal fade" id="add_field_option_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
       <div class="modal-dialog modal-sm">
           <div class="modal-content">
               <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                   <h4 class="modal-title" id="">Lab Test Field Options [Entry]<< /h4>
               </div>

               <div class="modal-body">

                   <form method="POST" id="add_field_option">


                       <div class="form_sep">
                           <label for="reg_input_no" class="">Lab Test Field No.:</label>
                           <input type="text" id="field_opt_no" name="field_opt_no" class="form-control">
                       </div>

                       <div class="form_sep">
                           <label for="reg_input_no" class="">Reference</label>
                           <input type="text" id="Reference" name="Reference" class="form-control">
                       </div>

                       <br />
                       <div class="form_sep">
                           <input type="submit" name="insert" id="insert" value="Insert" class="btn btn-success" />


                           <input type="hidden" name="MM_update" value="add_field_option" />
                       </div>
                   </form>


               </div>

           </div>
       </div>
   </div>


   <div class="modal inmodal fade" id="manage_price_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
       <div class="modal-dialog modal-sm">
           <div class="modal-content">
               <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                   <h4 class="modal-title" id=""></h4>
               </div>

               <div class="modal-body">

                   <form method="POST" id="manage_price_body">

                       <div class="form_sep">
                           <label for="reg_input_no" class="">Investigation #</label>
                           <input type="text" id="lab_test_no" name="lab_test_no" class="form-control" readonly>
                       </div>

                       <div class="form_sep">
                           <label for="reg_input_no" class="">Investigation Name</label>
                           <input type="text" id="lab_test_name" name="lab_test_name" class="form-control" readonly>
                       </div>

                       <div class="form_sep">
                           <label for="reg_input_no" class="">Hospital/General Price</label>
                           <input type="number" id="hosp_price" name="hosp_price" class="form-control">
                       </div>

                       <div class="form_sep">
                           <label for="reg_input_no" class="">External Price</label>
                           <input type="number" id="external_price" name="external_price" class="form-control">
                       </div>

                       <div class="form_sep">
                           <label for="reg_input_no" class="">NHIS Price (Enter Price if Covered by NHIS or Skip it)</label>
                           <input type="number" id="NHIS_price" name="NHIS_price" class="form-control">
                       </div>

                       <!--                   <div class="form_sep">
                           <label for="reg_input_no" class="">NHIS Care Service Type (Covarage Extension)</label>
                           <select name="insurance_type" id="insurance_type" class="form-control">
                               <option selected="selected" value="">Select...</option>

                               <option value="1">Primary Care (Without Authorization Code)</option>
                               <option value="2">Secondary Care (Authorization Code Required)</option>
                           </select>
                       </div> -->

                       <div class="form_sep">
                           <input type="submit" name="Save" id="Save" value="Save" class="btn btn-success btn-xs" />
                           <input type="hidden" name="MM_update" value="manage_price_body" />
                           <input type="hidden" name="insurance_type" id="insurance_type" value="1" />
                       </div>
                   </form>


               </div>

           </div>
       </div>
   </div>


   <div class="modal inmodal fade" id="edit_lab_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
       <div class="modal-dialog modal-sm">
           <div class="modal-content">
               <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                   <h4 class="modal-title" id=""></h4>
               </div>

               <div class="modal-body">

                   <form method="POST" id="edit_lab_body">


                       <div class="form_sep">
                           <label for="reg_input_no" class="">Investigation #</label>
                           <input type="text" id="lab_test_no2" name="lab_test_no2" class="form-control" readonly>
                       </div>

                       <div class="form_sep">
                           <label for="reg_input_no" class="">Investigation Name</label>
                           <input type="text" id="lab_test_name2" name="lab_test_name2" class="form-control" required>
                       </div>


                       <div class="form_sep">
                           <label for="reg_input_no" class="req">Sub-Category</label>
                           <select name="sub_category" id="sub_category" class="form-control" required>
                               <option selected="selected" value="">--Category--</option>
                               <option value="Laboratory Test">Laboratory Test</option>
                               <option value="X-RAY/CT SCAN">X-RAY/CT SCAN</option>
                               <option value="SCAN/IMAGING">SCAN/IMAGING</option>
                               <option value="MRI">MRI</option>
                           </select>
                       </div>
                       <?php
                        $stmt2 = $db->query("SELECT * FROM department WHERE department_type='Laboratory' or department_type='Radiology'");
                        ?>

                       <div class="form_sep">
                           <label for="reg_input_no" class="">Department</label>
                           <select name="Department2" id="Department2" class="form-control" required>

                               <?php while ($row_rstdepartment = $stmt2->fetch(PDO::FETCH_ASSOC)) { ?>
                                   <option value="<?php echo $row_rstdepartment["sn"]; ?>"><?php echo $row_rstdepartment["department"]; ?></option>
                               <?php } ?>
                           </select>
                       </div>

                       <div class="form_sep">
                           <label for="reg_input_no" class="">Unit/Section Name</label>
                           <select name="category" id="category" class="form-control" required>
                               <option value="Laboratory">Laboratory</option>
                               <option value="Radiology">Radiology</option>
                           </select>
                       </div>


                       <div class="form_sep">
                           <label for="reg_input_no" class="req">Lab Combination <small>eg. Full Blood Count (FBC)</small></label>
                           <select name="labcombos" id="labcombos" class="form-control" required>
                               <option value="0">No</option>
                               <option value="1">Yes</option>
                           </select>
                       </div>


                       <div class="form_sep">
                           <label for="reg_input_no" class="req">Doctors can upload result status from their module</label>
                           <select name="doctor_can_upload" id="doctor_can_upload" class="form-control" required>
                               <option value="0">No</option>
                               <option value="1">Yes</option>
                           </select>
                       </div>



                       <div class="form_sep">
                           <input type="submit" name="Save" id="Save" value="Save" class="btn btn-success btn-xs" />
                           <input type="hidden" name="MM_update" value="edit_lab_body" />
                       </div>
                   </form>


               </div>

           </div>
       </div>
   </div>

   <div class="modal inmodal fade" id="view_combos_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
       <div class="modal-dialog modal-lg">
           <div class="modal-content">
               <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                   <h4 class="modal-title" id="">Lab Combination</h4>
               </div>
               <div class="modal-body" id="view_combos_body">
               </div>

           </div>
       </div>
   </div>


   <div class="modal inmodal fade" id="Add_Test_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
       <div class="modal-dialog modal-lg">
           <div class="modal-content">
               <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                   <h4 class="modal-title" id="">Add Examination Name</h4>
               </div>

               <div class="modal-body">
                   <form method="post" action="setup.php" id="">


                       <div class="form_sep">
                           <label for="reg_input_no" class="req">Investigation/Test Name <small> (Type Title/Upper Case Letter)</small></label>
                           <input type="text" id="labname" name="labname" class="form-control" placeholder="" maxlength="100" value="" required>
                       </div>


                       <div class="form_sep">
                           <label for="reg_input_no" class="req">Sub-Category</label>
                           <select name="sub_category" id="sub_category" class="form-control" required>
                               <option selected="selected" value="">--Category--</option>
                               <option value="Laboratory Test">Laboratory Test</option>
                               <option value="X-RAY/CT SCAN">X-RAY/CT SCAN</option>
                               <option value="SCAN/IMAGING">SCAN/IMAGING</option>
                               <option value="MRI">MRI</option>
                           </select>
                       </div>



                       <?php
                        $stmt2 = $db->query("SELECT * FROM department WHERE department_type='Laboratory' or department_type='Radiology'");
                        ?>
                       <?php
                        if ($_SESSION['col4'] == 1) { ?>
                           <input type="hidden" id="Department" name="Department" value="<?= $_SESSION['dept_id']; ?>">
                       <?php  } else {
                        ?>
                           <div class="form_sep">
                               <label for="reg_input_no" class="req">Department/Unit Name</label>
                               <select name="Department" id="Department" class="form-control" required>
                                   <option selected="selected" value="">Select Department...</option>

                                   <?php while ($row_rstdepartment = $stmt2->fetch(PDO::FETCH_ASSOC)) { ?>
                                       <option value="<?php echo $row_rstdepartment["sn"] . '__' . $row_rstdepartment["department_type"]; ?>"><?php echo $row_rstdepartment["department"]; ?></option>
                                   <?php } ?>
                               </select>
                           </div>
                       <?php } ?>


                       <div class="form_sep">
                           <div class="checkbox"><label>
                                   <input type="checkbox" value="1" name="labcombos">Check if you are adding Combination Test <br> <small><strong>eg. Full Blood Count (FBC)</strong></small></label></div>

                       </div>


                       <div class="form_sep">
                           <label for="reg_input_no" class="req">Doctors can upload result status from their module</label>
                           <select name="doctor_can_upload" id="doctor_can_upload" class="form-control" required>
                               <option selected="selected" value="0">--select--</option>
                               <option value="0">No</option>
                               <option value="1">Yes</option>
                           </select>
                       </div>


                       <div class="form_sep">
                           <button class="btn btn-primary btn-xs" type="submit" name="Add_Test_Body">Save</button>

                       </div>
                   </form>
               </div>
           </div>
       </div>
   </div>


   <div class="modal inmodal fade" id="add_new_combos_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
       <div class="modal-dialog modal-lg">
           <div class="modal-content">
               <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                   <h4 class="modal-title" id=""></h4>
               </div>

               <div class="modal-body">

                   <form method="POST" id="add_new_combos_form">
                       <div class="form_sep">
                           <label for="reg_input_no" class="req">Lab Combination Name</label>
                           <input type="text" id="combos" name="combos" class="form-control" data-required="true" placeholder="" maxlength="50">
                       </div>


                       <div class="form_sep">
                           <button class="btn btn-primary btn-xs" type="submit" name="Save">Save</button>
                           <input type="hidden" name="MM_update" value="add_new_combos_form" />
                       </div>
                   </form>
               </div>
           </div>
       </div>
   </div>