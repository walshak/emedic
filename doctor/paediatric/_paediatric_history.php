<section id="paediatric_history">
  <form method="post" id="paediatric_history_form">
      <p><input type="hidden" id="hospital_no" name="hospital_no" value="<?= $hospital_no;?>"></p>
  <h4 class="text-center">Paediatric Initial History and Physical</h4>
    <div class="panel well well-sm">
        <div class="panel-body">
            <div class="form_sep">
                <div style="color: #006; font-size:12px"><strong>HIV TEST </strong></div>
            </div>
            <br>



            <div>

                <div class="col-md-12">
                    <div class="col-md-6">
                        <div class="form_sep">
                            <label for="HIV_initial_test" class="">HIV Initial Test</label>
                            <select name="HIV_initial_test" id="HIV_initial_test" class="form-control">
                                <option selected="selected" value="">Select...</option>
                                <option value="DNA PCR" <?= $paediatric_history->HIV_initial_test == 'DNA PCR' ? 'selected' : ''; ?> >DNA PCR</option>
                                <option value="RNA PCR" <?= $paediatric_history->HIV_initial_test == 'RNA PCR' ? 'selected' : ''; ?>>RNA PCR</option>
                                <option value="ELISA/Rapid Test" <?= $paediatric_history->HIV_initial_test == 'ELISA/Rapid Test' ? 'selected' : ''; ?>>ELISA/Rapid Test</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form_sep">
                            <label for="HIV_initial_test_rslt" class="">Result</label>
                            <select name="HIV_initial_test_rslt" id="HIV_initial_test_rslt" class="form-control">
                                <option selected="selected" value="">Select...</option>
                                <option value="Positive"  <?= $paediatric_history->HIV_initial_test_rslt == 'Positive' ? 'selected' : ''; ?>>Positive</option>
                                <option value="Negative" <?= $paediatric_history->HIV_initial_test_rslt == 'Negative' ? 'selected' : ''; ?>>Negative</option>
                                <option value="Indeteminate" <?= $paediatric_history->HIV_initial_test_rslt == 'Indeteminate' ? 'selected' : ''; ?>>Indeteminate</option>
                                <option value="Not Availabe" <?= $paediatric_history->HIV_initial_test_rslt == 'Not Availabe' ? 'selected' : ''; ?>>Not Availabe</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <br>
        </div>

    </div>

    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form_sep">
                        <div style="color: #006; font-size:12px"><strong>BIRTH DETAILS </strong></div>
                    </div>
                    <br>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-xs-12">
                                <label>Weight at Birth <span class="text-primary">[Optional]</span></label>
                                <p><input type="text" name="birth_weight" id="birth_weight" placeholder="Weight at birth" class="form-control" value="<?= $paediatric_history->birth_weight ;?>"></p>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-xs-12">
                                <label>Gestation at Birth</label>
                                <select name="gestation_at_birth" id="gestation_at_birth" class="form-control">
                                    <option selected="selected" value="">Select...</option>
                                    <option value="Term" <?= $paediatric_history->gestation_at_birth == 'Term' ? 'selected' : ''; ?> >Term</option>
                                    <option value="Preterm" <?= $paediatric_history->gestation_at_birth == 'Preterm' ? 'selected' : ''; ?>>Preterm</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-xs-12">
                                <label>Neonatal Complication</label>
                                <select name="neonatal_comp" id="neonatal_comp" class="form-control">
                                    <option selected="selected" value="">Select...</option>
                                    <option value="Yes" <?= $paediatric_history->neonatal_comp == 'Yes' ? 'selected' : ''; ?>>Yes</option>
                                    <option value="No" <?= $paediatric_history->neonatal_comp == 'No' ? 'selected' : ''; ?>>No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-xs-12">
                                <label>Delivery Mode</label>
                                <select name="delivery_mode" id="delivery_mode" class="form-control">
                                    <option selected="selected" value="">Select...</option>
                                    <option value="SVD" <?= $paediatric_history->delivery_mode == 'SVD' ? 'selected' : ''; ?>>SVD</option>
                                    <option value="Emergency C-section" <?= $paediatric_history->delivery_mode == 'Emergency C-section' ? 'selected' : ''; ?>>Emergency C-section</option>
                                    <option value="Elective C-section" <?= $paediatric_history->delivery_mode == 'Elective C-section' ? 'selected' : ''; ?>>Elective C-section</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <br>
                </div>
                <div class="col-md-6">

                    <div><strong style="color: #006; font-size:12px">BREAST FEEDING</strong></div>
                    <hr>
                    <table width="100%" cellpadding="10">
                        <tbody>
                            <tr>
                                <td>
                                    <div class="form_sep">
                                        <label for="breastfeeding" class="">Breast Feeding</label>
                                        <select name="breastfeeding" id="breastfeeding" class="form-control">
                                            <option selected="selected" value="">Select...</option>
                                            <option value="Never Breastfed" <?= $paediatric_history->breastfeeding == 'Never Breastfed' ? 'selected' : ''; ?>>Never Breastfed</option>
                                            <option value="Exclusive BMS" <?= $paediatric_history->breastfeeding == 'Exclusive BMS' ? 'selected' : ''; ?>>Exclusive BMS</option>
                                            <option value="Exclusive BF" <?= $paediatric_history->breastfeeding == 'Exclusive BF' ? 'selected' : ''; ?>>Exclusive BF</option>
                                            <option value="Mixed BMS and BF" <?= $paediatric_history->breastfeeding == 'Mixed BMS and BF' ? 'selected' : ''; ?>>Mixed BMS &amp; BF</option>
                                        </select>
                                    </div>
                                </td>
                                <td>
                                    <div class="form_sep">
                                        <label class="">If BF Used Duration of BF</label>
                                        <input type="text" name="dura" class="form-control" maxlength="30" value="<?= $paediatric_history->dura ;?>">
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <br>
                    <div><strong style="color: #006; font-size:12px">PAST MEDICAL HISTORY</strong><small style="color:#F00">&nbsp; (Tick appropriate options below)</small></div>
                    <hr>
                    <?php
                    $past_med_hx =   preg_split("/,/", $paediatric_history->past_med_hx);
                   for ($i=0; $i < count($past_med_hx); $i++) { 
                      $past_med_hx[$i] = trim($past_med_hx[$i]);
                   }
                     ?>

                    <label for="reg_chbox_c" class="checkbox-inline">
                        <input name="past_med_hx[]" type="checkbox" value="Recurrent Pneumonia" <?= in_array('Recurrent Pneumonia', $past_med_hx) ? 'checked' : ''; ?>>Recurrent Pneumonia</label>
                    <label for="reg_chbox_c" class="checkbox-inline">
                        <input name="past_med_hx[]" type="checkbox" value="Oral thrush" <?= in_array('Oral thrush', $past_med_hx) ? 'checked' : ''; ?>>Oral thrush</label>

                    <label for="reg_chbox_c" class="checkbox-inline">
                        <input name="past_med_hx[]" type="checkbox" value="Present/past ear discharge" <?= in_array('Present/past ear discharge', $past_med_hx) ? 'checked' : ''; ?>>Present/past ear discharge</label>
                    <br>
                    <label for="reg_chbox_c" class="checkbox-inline">
                        <input name="past_med_hx[]" type="checkbox" value="Persistent diarrhoea" <?= in_array('Persistent diarrhoea', $past_med_hx) ? 'checked' : ''; ?>>Persistent diarrhoea</label>
                    <label for="reg_chbox_c" class="checkbox-inline">
                        <input name="past_med_hx[]" type="checkbox" value="Parotid elargement" <?= in_array('Parotid elargement', $past_med_hx) ? 'checked' : ''; ?>>Parotid elargement</label>
                    <label for="reg_chbox_c" class="checkbox-inline">
                        <input name="past_med_hx[]" type="checkbox" value="Very Low Weight" <?= in_array('Very Low Weight', $past_med_hx) ? 'checked' : ''; ?>>Very Low Weight</label>
                    <label for="reg_chbox_c" class="checkbox-inline">
                        <input name="past_med_hx[]" type="checkbox" value="Enlarged Lymph nodes" <?= in_array('Enlarged Lymph nodes', $past_med_hx) ? 'checked' : ''; ?>>Enlarged Lymph nodes</label>
                    <br>

                    <div class="form_sep">
                        <label for="reg_input_name" class="">Other Past Medical History</label>
                        <input type="text" id="past_other" name="past_other" class="form-control" placeholder="Other Past Medical History" value="<?= $paediatric_history->past_other ;?>">
                    </div>

                </div>
            </div>

        </div>

    </div>
    <hr />
    <hr>

    <div class="panel well well-sm">
        <div class="panel-body">

            <div>
                <div class="row">
                    <div class="col-md-6">
                        <div style="color: #006; font-size:12px"><strong>Patient currently on TB Medication?</strong></div>
                        <hr>

                        <div class="form_sep">
                            <label for="TB_drugs" class="">Current TB Drugs</label>
                            <select name="TB_drugs" id="TB_drugs" class="form-control">
                                <option selected="selected" value="">Select...</option>
                                <option value="INH/RIF/ETH/PZ" <?= $paediatric_history->TB_drugs == 'INH/RIF/ETH/PZ' ? 'selected' : ''; ?>>INH/RIF/ETH/PZ</option>
                                <option value="INH/RIF/ETH/PZ/STrep" <?= $paediatric_history->TB_drugs == 'INH/RIF/ETH/PZ/STrep' ? 'selected' : ''; ?>>INH/RIF/ETH/PZ/STrep</option>
                                <option value="INH/RIF/ETH" <?= $paediatric_history->TB_drugs == 'INH/RIF/ETH' ? 'selected' : ''; ?>>INH/RIF/ETH</option>
                                <option value="ETH/INH" <?= $paediatric_history->TB_drugs == 'ETH/INH' ? 'selected' : ''; ?>>ETH/INH</option>
                            </select>
                        </div>
                        <div class="form_sep">
                            <label class="">Date of current diagnosis</label>
                            <div class="input-group date enddate" data-date-format="yyyy-mm-dd">
                                <input name="TB_date" id="TB_date" class="form-control input_validate parsley-validated" type="date" data-type="dateIso" value="<?= $paediatric_history->TB_date ;?>">
                                <span class="input-group-addon"><i class="icon-calendar"></i></span>
                            </div>
                        </div>
                        <div class="form_sep">
                            <label for="TB_type" class="">Type of Current TB</label>
                            <select name="TB_type" id="TB_type" class="form-control">
                                <option selected="selected" value="">Select...</option>
                                <option value="Pulmonary" <?= $paediatric_history->TB_type == 'Pulmonary' ? 'selected' : ''; ?>>Pulmonary</option>
                                <option value="Smear Negative"  <?= $paediatric_history->TB_type == 'Smear Negative' ? 'selected' : ''; ?>>Smear Negative</option>
                                <option value="Smear Positive"  <?= $paediatric_history->TB_type == 'Smear Positive' ? 'selected' : ''; ?>>Smear Positive</option>
                                <option value="Smear Unknown"  <?= $paediatric_history->TB_type == 'Smear Unknown' ? 'selected' : ''; ?>>Smear Unknown</option>
                                <option value="ExtraPulmonary"  <?= $paediatric_history->TB_type == 'ExtraPulmonary' ? 'selected' : ''; ?>>ExtraPulmonary</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div style="color: #006; font-size:12px"><strong>Current Medication</strong></div>
                        <hr>
                        <?php
                    $current_med =   preg_split("/,/", $paediatric_history->current_med);
                   for ($i=0; $i < count($current_med); $i++) { 
                      $current_med[$i] = trim($current_med[$i]);
                   }
                     ?>

                        <label for="reg_chbox_c" class="checkbox-inline">
                            <input name="current_med[]" type="checkbox" value="Septrin" <?= in_array('Septrin', $current_med) ? 'checked' : ''; ?>>Septrin</label>
                        <label for="reg_chbox_c" class="checkbox-inline">
                            <input name="current_med[]" type="checkbox" value="Fluconazole" <?= in_array('Fluconazole', $current_med) ? 'checked' : ''; ?>>Fluconazole</label>

                        <label for="reg_chbox_c" class="checkbox-inline">
                            <input name="current_med[]" type="checkbox" value="Anti-malarials" <?= in_array('Anti-malarials', $current_med) ? 'checked' : ''; ?>>Anti-malarials</label>

                        <label for="reg_chbox_c" class="checkbox-inline">
                            <input name="current_med[]" type="checkbox" value="Tranditional Meds" <?= in_array('Tranditional Meds', $current_med) ? 'checked' : ''; ?>>Tranditional Meds</label>
                        <br>
                        <br>
                        <div class="form_sep">
                            <label for="reg_input_name" class="">Other Medications</label>
                            <input type="text" id="other_med" name="other_med" class="form-control" value="<?= $paediatric_history->other_med;?>">
                        </div>

                        <div class="form_sep">
                            <label for="drug_allergies" class="">Drugs Allergies</label>
                            <input type="text" id="drug_allergies" name="drug_allergies" class="form-control" value="<?= $paediatric_history->drug_allergies;?>">
                        </div>

                        <br>
                        <div class="form_sep">
                        <input type="hidden" name="action" value="regData">
                        <input type="hidden" name="appointment_number" name="<?= $appointment_number; ?>">
                            <button class="btn btn-success" type="submit" name="save_init5" id="save_init5">Save Initial Hx</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>


  </form>

</section>