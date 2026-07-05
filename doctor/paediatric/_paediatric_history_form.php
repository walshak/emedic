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
                                <option value="DNA PCR">DNA PCR</option>
                                <option value="RNA PCR">RNA PCR</option>
                                <option value="ELISA/Rapid Test">ELISA/Rapid Test</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form_sep">
                            <label for="HIV_initial_test_rslt" class="">Result</label>
                            <select name="HIV_initial_test_rslt" id="HIV_initial_test_rslt" class="form-control">
                                <option selected="selected" value="">Select...</option>
                                <option value="Positive">Positive</option>
                                <option value="Negative">Negative</option>
                                <option value="Indeteminate">Indeteminate</option>
                                <option value="Not Availabe">Not Availabe</option>
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
                                <p><input type="text" name="birth_weight" id="birth_weight" placeholder="Weight at birth" class="form-control"></p>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-xs-12">
                                <label>Gestation at Birth</label>
                                <select name="gestation_at_birth" id="gestation_at_birth" class="form-control">
                                    <option selected="selected" value="">Select...</option>
                                    <option value="Term">Term</option>
                                    <option value="Preterm">Preterm</option>
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
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
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
                                    <option value="SVD">SVD</option>
                                    <option value="Emergency C-section">Emergency C-section</option>
                                    <option value="Elective C-section">Elective C-section</option>
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
                                            <option value="Never Breastfed">Never Breastfed</option>
                                            <option value="Exclusive BMS">Exclusive BMS</option>
                                            <option value="Exclusive BF">Exclusive BF</option>
                                            <option value="Mixed BMS &amp; BF">Mixed BMS &amp; BF</option>
                                        </select>
                                    </div>
                                </td>
                                <td>
                                    <div class="form_sep">
                                        <label class="">If BF Used Duration of BF</label>
                                        <input type="text" name="dura" class="form-control" maxlength="30">
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <br>
                    <div><strong style="color: #006; font-size:12px">PAST MEDICAL HISTORY</strong><small style="color:#F00">&nbsp; (Tick appropriate options below)</small></div>
                    <hr>

                    <label for="reg_chbox_c" class="checkbox-inline">
                        <input name="past_med_hx[]" type="checkbox" value="Recurrent Pneumonia">Recurrent Pneumonia</label>
                    <label for="reg_chbox_c" class="checkbox-inline">
                        <input name="past_med_hx[]" type="checkbox" value="Oral thrush">Oral thrush</label>

                    <label for="reg_chbox_c" class="checkbox-inline">
                        <input name="past_med_hx[]" type="checkbox" value="Present/past ear discharge">Present/past ear discharge</label>
                    <br>
                    <label for="reg_chbox_c" class="checkbox-inline">
                        <input name="past_med_hx[]" type="checkbox" value="Persistent diarrhoea">Persistent diarrhoea</label>
                    <label for="reg_chbox_c" class="checkbox-inline">
                        <input name="past_med_hx[]" type="checkbox" value="Parotid elargement">Parotid elargement</label>
                    <label for="reg_chbox_c" class="checkbox-inline">
                        <input name="past_med_hx[]" type="checkbox" value="Very Low Weight">Very Low Weight</label>
                    <label for="reg_chbox_c" class="checkbox-inline">
                        <input name="past_med_hx[]" type="checkbox" value="Enlarged Lymph nodes">Enlarged Lymph nodes</label>
                    <br>

                    <div class="form_sep">
                        <label for="reg_input_name" class="">Other Past Medical History</label>
                        <input type="text" id="past_other" name="past_other" class="form-control" placeholder="Other Past Medical History">
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
                                <option value="INH/RIF/ETH/PZ">INH/RIF/ETH/PZ</option>
                                <option value="INH/RIF/ETH/PZ/STrep">INH/RIF/ETH/PZ/STrep</option>
                                <option value="INH/RIF/ETH">INH/RIF/ETH</option>
                                <option value="ETH/INH">ETH/INH</option>
                            </select>
                        </div>
                        <div class="form_sep">
                            <label class="">Date of current diagnosis</label>
                            <div class="input-group date enddate" data-date-format="yyyy-mm-dd">
                                <input name="TB_date" id="TB_date" class="form-control input_validate parsley-validated" type="text" data-type="dateIso">
                                <span class="input-group-addon"><i class="icon-calendar"></i></span>
                            </div>
                        </div>
                        <div class="form_sep">
                            <label for="TB_type" class="">Type of Current TB</label>
                            <select name="TB_type" id="TB_type" class="form-control">
                                <option selected="selected" value="">Select...</option>
                                <option value="Pulmonary">Pulmonary</option>
                                <option value="Smear Negative">Smear Negative</option>
                                <option value="Smear Positive">Smear Positive</option>
                                <option value="Smear Unknown">Smear Unknown</option>
                                <option value="ExtraPulmonary">ExtraPulmonary</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div style="color: #006; font-size:12px"><strong>Current Medication</strong></div>
                        <hr>

                        <label for="reg_chbox_c" class="checkbox-inline">
                            <input name="current_med[]" type="checkbox" value="Septrin">Septrin</label>
                        <label for="reg_chbox_c" class="checkbox-inline">
                            <input name="current_med[]" type="checkbox" value="Fluconazole">Fluconazole</label>

                        <label for="reg_chbox_c" class="checkbox-inline">
                            <input name="current_med[]" type="checkbox" value="Anti-malarials">Anti-malarials</label>

                        <label for="reg_chbox_c" class="checkbox-inline">
                            <input name="current_med[]" type="checkbox" value="Tranditional Meds">Tranditional Meds</label>
                        <br>
                        <br>
                        <div class="form_sep">
                            <label for="reg_input_name" class="">Other Medications</label>
                            <input type="text" id="other_med" name="other_med" class="form-control">
                        </div>

                        <div class="form_sep">
                            <label for="drug_allergies" class="">Drugs Allergies</label>
                            <input type="text" id="drug_allergies" name="drug_allergies" class="form-control">
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