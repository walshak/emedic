<?php

if($oldr=='bc'){
$stmt=$db->query("SELECT * FROM rdc_blood_culture WHERE patient_no='$emr' and result_id='$result_id'");
	$labFecth=$stmt->fetch(PDO::FETCH_ASSOC);

?>

<h3>BLOOD CULTURE</h3>
                                <table class="table invoice-table" width="100%" cellpadding="5" cellspacing="5" style="border:1px solid #000; border-collapse:collapse; font-size:12px; font-family:Arial, Helvetica, sans-serif; text-align:left">
            
            <tr>
              <td height="436" colspan="4" valign="top"><input type="hidden" name="result_id" value="<?php echo $labFecth['result_id']; ?>" />
                <input type="hidden" name="patient_no" value="<?php echo $labFecth['patient_no']; ?>" />
                <input type="hidden" name="Newdate" value="<?php echo $labFecth['Newdate']; ?>" />
                <table width="100%" border="0" align="center" height="432" cellpadding="2" cellspacing="2" id="searchBorder3">
                 <tr bgcolor="#CCCCFF">
                    <td width="222" height="20" colspan="4" bgcolor="#FFCC33"><strong>BLOOD CULTURE</strong></td>
                  </tr>
                  <tr bgcolor="#CCCCFF">
                    <td width="222" height="20"><strong>Test Name</strong></td>
                    <td colspan="2"><strong>Lab Values</strong></td>
                  </tr>
                  <tr>
                    <td>Microscopy</td>
                    <td colspan="2"><?php echo $labFecth['microscopy']; ?></td>
                  </tr>
                  <tr>
                    <td>Culture</td>
                    <td colspan="2"><?php echo $labFecth['culture']; ?></td>
                  </tr>
                  <tr bgcolor="#CCCCFF">
           <td width="222" height="20" colspan="4" bgcolor="#FFCC33"><strong>ANTIBIOTICS</strong></td>
       </tr>
       <tr bgcolor="#FFCC00">
           <td height="20" bgcolor="#CCCCFF"><strong>Test Name</strong></td>
           <td width="246" bgcolor="#CCCCFF"><strong>Remark</strong></td>
           <td width="341" bgcolor="#CCCCFF"><strong>S/I/R</strong></td>
       </tr>
                  <tr>
                    <td height="32">Penicillin</td>
                    <td><?php echo $labFecth['Penicillin']; ?>&nbsp;</td>
                    <td><?php echo $labFecth['sir_pen']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Erythromycin</td>
                    <td><?php echo $labFecth['Erythromycin']; ?></td>
                    <td><?php echo $labFecth['sir_ery']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Gentamycin</td>
                    <td><?php echo $labFecth['Gentamycin']; ?></td>
                    <td><?php echo $labFecth['sir_gent']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Colistin Sulphate</td>
                    <td><?php echo $labFecth['Colistin']; ?></td>
                    <td><?php echo $labFecth['sir_coli']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Contrimoxazole</td>
                    <td><?php echo $labFecth['Contrimoxazole']; ?></td>
                    <td><?php echo $labFecth['sir_cont']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Ofloxacin</td>
                    <td><?php echo $labFecth['Ofloxacin']; ?></td>
                    <td><?php echo $labFecth['sir_oflo']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Cloxacillin</td>
                    <td><?php echo $labFecth['Cloxacillin']; ?></td>
                    <td><?php echo $labFecth['sir_clox']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Ceftazidime</td>
                    <td><?php echo $labFecth['Ceftazidine']; ?></td>
                    <td><?php echo $labFecth['sir_cef']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Augmentin</td>
                    <td><?php echo $labFecth['Augmentin']; ?></td>
                    <td><?php echo $labFecth['sir_aug']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Cefuroxime</td>
                    <td><?php echo $labFecth['Cefuroxime']; ?></td>
                    <td><?php echo $labFecth['sir_cefo']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Ciprofloxacin</td>
                    <td><?php echo $labFecth['ciprofloxacin']; ?></td>
                    <td><?php echo $labFecth['sir_cip']; ?></td>
                  </tr>
                  <tr>
                    <td>Cefixime</td>
                    <td><?php echo $labFecth['cefixime']; ?></td>
                    <td><?php echo $labFecth['sir_cefi']; ?></td>
                  </tr>
                  <tr>
                    <td>Ceftriaxime</td>
                    <td><?php echo $labFecth['ceftriaxime']; ?></td>
                    <td><?php echo $labFecth['sir_ceftri']; ?></td>
                  </tr>
                  <tr>
                    <td>Imipenem</td>
                    <td><?php echo $labFecth['imi']; ?></td>
                    <td><?php echo $labFecth['sir_imi']; ?></td>
                  </tr>
                  <tr>
                    <td>Vancomycim</td>
                    <td><?php echo $labFecth['van']; ?></td>
                    <td><?php echo $labFecth['sir_van']; ?></td>
                  </tr>
                  <tr>
                    <td>Piperacillin/Tazobactan</td>
                    <td><?php echo $labFecth['pipe']; ?></td>
                    <td><?php echo $labFecth['sir_pipe']; ?></td>
                  </tr>
                  <tr>
                    <td>Meropenem</td>
                    <td><?php echo $labFecth['meropenem']; ?></td>
                    <td><?php echo $labFecth['penem']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Date Captured</td>
                    <td colspan="2"><?php echo $labFecth['date_captured']; ?></td>
                  </tr>
                </table></td>
            </tr>
            <tr>
              <td height="20"><strong><em>Comments</em></strong></td>
              <td height="20" colspan="3"><hr />
              <?php echo $labFecth['comments']; ?>                <hr /></td>
            </tr>
            <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20">&nbsp;</td>
              <td height="20">&nbsp;</td>
            </tr>
            <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20"><em>Lab Scientist/Technician:</em></td>
              <td height="20"><?php echo $labFecth['lab_tech']; ?></td>
            </tr>
            <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20"><em>Pathologist Name &amp; Signature:</em></td>
              <td height="20"><strong>Dr. KENNETH ONYEDIBE</strong></td>
            </tr>
            <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20">&nbsp;</td>
              <td height="20"><img src="images/microbiology.jpg" alt="" /></td>
            </tr>
          </table>
      </form>      </td>
    </tr>
  </table>
          
          <hr>


<?php }


if($oldr=='cp'){
$stmt=$db->query("SELECT * FROM chemical_pathology WHERE patient_no='$emr' and result_id='$result_id'");
	$labFecth=$stmt->fetch(PDO::FETCH_ASSOC);
?>

<h3 align="center">Chemical Pathology</h3>

                                <table class="table invoice-table" width="100%" cellpadding="5" cellspacing="5" style="border:1px solid #000; border-collapse:collapse; font-size:12px; font-family:Arial, Helvetica, sans-serif; text-align:left">

          <tr bgcolor="#FFCC00">
            <td height="20" bgcolor="#CCCCFF"><strong>Blood</strong></td>
            <td bgcolor="#CCCCFF"><strong>Lab Values</strong></td>
            <td bgcolor="#CCCCFF"><strong>Reference Ranges</strong></td>
            </tr>
          <tr>
            <td height="20">Na +</td>
            <td><label for="blood_na"></label>
              <?php echo $labFecth['blood_na']; ?>&nbsp;</td>
            <td>(132 - 145 mmoI/L)</td>
            </tr>
          <tr>
            <td height="20">K +</td>
            <td><?php echo $labFecth['blood_k']; ?>&nbsp;</td>
            <td>(3.5 - 5.5 mmoI/L)</td>
            </tr>
          <tr>
            <td height="20">C1 -</td>
            <td><?php echo $labFecth['blood_c']; ?>&nbsp;</td>
            <td>(96 - 106 mmoI/L)</td>
            </tr>
          <tr>
            <td height="20">HCO</td>
            <td><?php echo $labFecth['blood_hco']; ?>&nbsp;</td>
            <td>(21 - 31 mmoI/L)</td>
            </tr>
          <tr>
            <td height="20">Urea</td>
            <td><?php echo $labFecth['blood_urea']; ?>&nbsp;</td>
            <td>(2.5 - 6.6 mmoI/L)</td>
            </tr>
          <tr>
            <td height="20">Creatinine</td>
            <td><?php echo $labFecth['blood_creat']; ?>&nbsp;</td>
            <td>(72 - 126 mmoI/L)</td>
            </tr>
          <tr>
            <td height="20">Uric Acid</td>
            <td><?php echo $labFecth['blood_acid']; ?></td>
            <td>&nbsp;(120 - 420 mmoI/L)</td>
            </tr>
          <tr>
            <td height="20">Glucose</td>
            <td><?php echo $labFecth['blood_glucose']; ?>&nbsp;</td>
            <td>(3.9 - 5.6 mmoI/L)</td>
            </tr>
          <tr>
            <td height="20">Total Protein</td>
            <td><?php echo $labFecth['blood_protein']; ?>&nbsp;</td>
            <td>(62 - 80 g/L)</td>
            </tr>
          <tr>
            <td height="20">Albumin</td>
            <td><?php echo $labFecth['blood_albumin']; ?></td>
            <td>&nbsp;(28 - 40 g/L)</td>
            </tr>
          <tr>
            <td height="20">Ca <sup>+</sup> <sub>2</sub></td>
            <td><?php echo $labFecth['blodd_ca2']; ?>&nbsp;</td>
            <td>(2.1 - 2.6 mmoI/L)</td>
            </tr>
          <tr>
            <td height="20">Po <sup>3</sup> <sub>4</sub></td>
            <td><?php echo $labFecth['blood_po']; ?>&nbsp;</td>
            <td>(0.8 - 1.4 mmoI/L)</td>
            </tr>
          <tr>
            <td height="20">Bilirubin (Total)</td>
            <td><?php echo $labFecth['blood_bilirubin']; ?></td>
            <td>&nbsp;(3.4 - 17 mmoI/L)</td>
            </tr>
          <tr>
            <td height="20">Bilirubin (Conj.)</td>
            <td><?php echo $labFecth['blood_bilirubin2']; ?></td>
            <td>&nbsp;</td>
            </tr>
          <tr>
            <td height="20">Alkaline Phosph</td>
            <td><?php echo $labFecth['blood_phosph']; ?></td>
            <td>&nbsp;(21 - 92 IU/L)</td>
            </tr>
          <tr>
            <td height="20">AIAT (G.P.T)</td>
            <td><?php echo $labFecth['blood_aiat']; ?>&nbsp;</td>
            <td>Up to 40 IU/L</td>
            </tr>
          <tr>
            <td height="20">AsPAT (G.O.T)</td>
            <td><?php echo $labFecth['blood_pat']; ?>&nbsp;&nbsp;</td>
            <td>Up to 40 IU/L</td>
            </tr>
          <tr>
            <td height="20">Acid Phos (Total)</td>
            <td><?php echo $labFecth['blood_phos']; ?></td>
            <td>&nbsp;(3.10 UL/L)</td>
            </tr>
          <tr>
            <td height="20">Acid Phos (Prostatic)</td>
            <td><?php echo $labFecth['blood_prostatic']; ?></td>
            <td>&nbsp;(up to 4 IU/L)</td>
            </tr>
          <tr>
            <td height="20">Amylase</td>
            <td><?php echo $labFecth['blood_amylase']; ?>&nbsp;</td>
            <td>(less than 300IU/L)</td>
            </tr>
          <tr>
            <td height="20">Total Cholesterol</td>
            <td><?php echo $labFecth['blood_cholesterol']; ?>&nbsp;</td>
            <td>(3.5 - 6.5  mmoI/L)</td>
            </tr>
          <tr>
            <td height="20">HDL Cholesterol</td>
            <td><?php echo $labFecth['blood_hdl']; ?></td>
            <td>&nbsp;</td>
            </tr>
          <tr>
            <td height="20">LDL Cholesterol</td>
            <td><?php echo $labFecth['blood_ldl']; ?></td>
            <td>&nbsp;</td>
            </tr>
          <tr>
            <td height="20">Triglyceride</td>
            <td><?php echo $labFecth['blood_tri']; ?>&nbsp;</td>
            <td>(0.50 - 1.75 mmoI/L)</td>
            </tr>
          <tr>
            <td height="20">T4</td>
            <td><?php echo $labFecth['blood_t4']; ?></td>
            <td>&nbsp;(60 - 160 nmoI/L)</td>
            </tr>
          <tr>
            <td height="20">T3</td>
            <td><?php echo $labFecth['blood_t3']; ?></td>
            <td>&nbsp;(1 - 3 mmoI/L)</td>
            </tr>
          <tr>
            <td height="20">TSH</td>
            <td><?php echo $labFecth['blood_tsh']; ?>&nbsp;</td>
            <td>(0.5 - 5 OmU/L)</td>
            </tr>
          <tr bgcolor="#FFCC00">
            <td height="20" bgcolor="#CCCCFF"><strong>Urine</strong></td>
            <td width="180" bgcolor="#CCCCFF"><strong>Lab Values</strong></td>
            <td width="227" bgcolor="#CCCCFF"><strong>References Ranges</strong></td>
            </tr>
          <tr>
            <td>Na +</td>
            <td><?php echo $labFecth['urine_na']; ?>&nbsp;</td>
            <td>(100 - 250 mmoI/24hrs)</td>
            </tr>
          <tr>
            <td>K +</td>
            <td><?php echo $labFecth['urine_k']; ?></td>
            <td>&nbsp;(40 - 120 mmoI/24hrs)</td>
            </tr>
          <tr>
            <td height="20">Ca<sup>+</sup><sub>2</sub></td>
            <td><?php echo $labFecth['urine_ca']; ?>&nbsp;</td>
            <td>(100 - 300 mmoI/24hrs)</td>
            </tr>
          <tr>
            <td height="20">C1-</td>
            <td><?php echo $labFecth['urine_c']; ?>&nbsp;</td>
            <td>(100 - 250 mmoI/24hrs)</td>
            </tr>
          <tr>
            <td height="20">Creatinine</td>
            <td><?php echo $labFecth['urine_creat']; ?>&nbsp;</td>
            <td>(9 - 17 mmoI/L)</td>
            </tr>
          <tr bgcolor="#FFCC00">
            <td height="20" bgcolor="#CCCCFF"><strong>CSP</strong></td>
            <td bgcolor="#CCCCFF"><strong>Lab Values</strong></td>
            <td bgcolor="#CCCCFF"><strong>References Ranges</strong></td>
            </tr>
          <tr>
            <td height="20">Protein</td>
            <td><?php echo $labFecth['csp_protein']; ?></td>
            <td>&nbsp;(150 - 400 mg/L)</td>
            </tr>
          <tr>
            <td height="20">Glucose</td>
            <td><?php echo $labFecth['csp_glucose']; ?></td>
            <td>&nbsp;(2.8 - 4.4 mmoI/L)</td>
            </tr>
          <tr>
            <td height="20">Chloride</td>
            <td><?php echo $labFecth['csp_chloride']; ?></td>
            <td>&nbsp;(100 - 120 mmoI/L)</td>
            </tr>
          <tr>
            <td height="20">FBS</td>
            <td><?php echo $labFecth['fbs']; ?></td>
            <td>&nbsp;</td>
            </tr>
          <tr>
            <td height="20">RBS</td>
            <td><?php echo $labFecth['rbs']; ?></td>
            <td>&nbsp;</td>
            </tr>
          <tr>
            <td height="20">Pregnancy Test</td>
            <td><?php echo $labFecth['pregnancy']; ?></td>
            <td>&nbsp;</td>
            </tr>
          <tr>
            <td height="20">Feacal Occult Blood Test</td>
            <td><?php echo $labFecth['occult']; ?></td>
            <td>&nbsp;</td>
            </tr>
          <tr>
            <td height="20">PSA</td>
            <td><?php echo $labFecth['psa']; ?></td>
            <td>&nbsp;</td>
            </tr>
          <tr>
            <td width="165" height="20">Lab Scientist  Name</td>
            <td><?php echo $labFecth['lab_tech']; ?></td>
            <td>&nbsp;</td>
            </tr>
          <tr>
            <td height="20">Date Captured</td>
            <td><?php echo $labFecth['date_captured']; ?></td>
            <td>&nbsp;</td>
            </tr>
          </table>


<?php } ?>

<?php
if($oldr=='csf'){
$stmt=$db->query("SELECT * FROM rdc_csf_microscopy WHERE patient_no='$emr' and result_id='$result_id'");
	$labFecth=$stmt->fetch(PDO::FETCH_ASSOC);
?>

                                <table class="table invoice-table" width="100%" cellpadding="5" cellspacing="5" style="border:1px solid #000; border-collapse:collapse; font-size:12px; font-family:Arial, Helvetica, sans-serif; text-align:left">

            <tr>
              <td height="20" colspan="4"><div align="center">
                <h2>CSF MICROSCOPY RESULT
                  <hr /></h2>
              </div></td>
            </tr>
            <tr>
              <td height="436" colspan="4" valign="top"><input type="hidden" name="result_id" value="<?php echo $labFecth['result_id']; ?>" />
                <input type="hidden" name="patient_no" value="<?php echo $labFecth['patient_no']; ?>" />
                <input type="hidden" name="Newdate" value="<?php echo $labFecth['Newdate']; ?>" />
  <table width="100%">
                  <tr bgcolor="#CCCCFF">
                    <td width="222" height="20"><strong>Microscopy</strong></td>
                    <td colspan="2"><strong>Lab Values</strong></td>
                  </tr>
                  <tr>
                    <td>White Blood Cell Count</td>
                    <td colspan="2"><?php echo $labFecth['wbc']; ?></td>
                  </tr>
                  <tr>
                    <td>Gram Stain</td>
                    <td colspan="2"><?php echo $labFecth['gram']; ?></td>
                  </tr>
                  <tr>
                    <td>Indian Ink</td>
                    <td colspan="2"><?php echo $labFecth['indian']; ?></td>
                  </tr>
                  <tr>
                    <td>Other Microscopy</td>
                    <td colspan="2"><?php echo $labFecth['others']; ?></td>
                  </tr>
                  <tr>
                    <td>Culture</td>
                    <td colspan="2"><?php echo $labFecth['culture']; ?></td>
                  </tr>
                  <tr bgcolor="#FFCC00">
                    <td height="20" bgcolor="#CCCCFF"><strong>Antibiotics</strong></td>
                    <td width="246" bgcolor="#CCCCFF"><strong>Zone of Inhibition</strong></td>
                    <td width="341" bgcolor="#CCCCFF"><strong>Zone of Inhibition</strong></td>
                  </tr>
                  <tr>
                    <td height="32">Penicillin</td>
                    <td><?php echo $labFecth['Penicillin']; ?>&nbsp;</td>
                    <td><?php echo $labFecth['sir_pen']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Erythromycin</td>
                    <td><?php echo $labFecth['Erythromycin']; ?></td>
                    <td><?php echo $labFecth['sir_ery']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Gentamycin</td>
                    <td><?php echo $labFecth['Gentamycin']; ?></td>
                    <td><?php echo $labFecth['sir_gent']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Colistin Sulphate</td>
                    <td><?php echo $labFecth['Colistin']; ?></td>
                    <td><?php echo $labFecth['sir_coli']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Contrimoxazole</td>
                    <td><?php echo $labFecth['Contrimoxazole']; ?></td>
                    <td><?php echo $labFecth['sir_cont']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Ofloxacin</td>
                    <td><?php echo $labFecth['Ofloxacin']; ?></td>
                    <td><?php echo $labFecth['sir_oflo']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Cloxacillin</td>
                    <td><?php echo $labFecth['Cloxacillin']; ?></td>
                    <td><?php echo $labFecth['sir_clox']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Ceftazidime</td>
                    <td><?php echo $labFecth['Ceftazidine']; ?></td>
                    <td><?php echo $labFecth['sir_cef']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Augmentin</td>
                    <td><?php echo $labFecth['Augmentin']; ?></td>
                    <td><?php echo $labFecth['sir_aug']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Cefuroxime</td>
                    <td><?php echo $labFecth['Cefuroxime']; ?></td>
                    <td><?php echo $labFecth['sir_cefo']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Ciprofloxacin</td>
                    <td><?php echo $labFecth['ciprofloxacin']; ?></td>
                    <td><?php echo $labFecth['sir_cip']; ?></td>
                  </tr>
                  <tr>
                    <td>Cefixime</td>
                    <td><?php echo $labFecth['cefixime']; ?></td>
                    <td><?php echo $labFecth['sir_cefi']; ?></td>
                  </tr>
                  <tr>
                    <td>Ceftriaxime</td>
                    <td><?php echo $labFecth['ceftriaxime']; ?></td>
                    <td><?php echo $labFecth['sir_ceftri']; ?></td>
                  </tr>
                  <tr>
                    <td>Imipenem</td>
                    <td><?php echo $labFecth['imi']; ?></td>
                    <td><?php echo $labFecth['sir_imi']; ?></td>
                  </tr>
                  <tr>
                    <td>Vancomycim</td>
                    <td><?php echo $labFecth['van']; ?></td>
                    <td><?php echo $labFecth['sir_van']; ?></td>
                  </tr>
                  <tr>
                    <td>Piperacillin/Tazobactan</td>
                    <td><?php echo $labFecth['pipe']; ?></td>
                    <td><?php echo $labFecth['sir_pipe']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Date Captured</td>
                    <td colspan="2"><?php echo $labFecth['date_captured']; ?></td>
                  </tr>
                </table></td>
            </tr>
            <tr>
              <td height="20"><strong><em>Comments</em></strong></td>
              <td height="20" colspan="3"><hr />
              <?php echo $labFecth['comments']; ?>                <hr /></td>
            </tr>
            <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20">&nbsp;</td>
              <td height="20">&nbsp;</td>
            </tr>
            <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20"><em>Lab Scientist/Technician:</em></td>
              <td height="20"><?php echo $labFecth['lab_tech']; ?></td>
            </tr>
            <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20"><em>Pathologist Name &amp; Signature:</em></td>
              <td height="20"><strong>Dr. KENNETH ONYEDIBE</strong></td>
            </tr>
            <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20">&nbsp;</td>
              <td height="20"><img src="images/microbiology.jpg" alt="" /></td>
            </tr>
          </table>
      </form>      </td>
    </tr>
  </table>

<?php } ?>

<?php
if($oldr=='lip'){
$stmt=$db->query("SELECT * FROM rdc_lipid WHERE patient_no='$emr' and result_id='$result_id'");
	$labFecth=$stmt->fetch(PDO::FETCH_ASSOC);
?>

                                <table class="table invoice-table" width="100%" cellpadding="5" cellspacing="5" style="border:1px solid #000; border-collapse:collapse; font-size:12px; font-family:Arial, Helvetica, sans-serif; text-align:left">

              <td height="20" colspan="4"><div align="center">
                <h2>LIPID/CARDIAC RISK RESULT
                  <hr /></h2>
              </div></td>
            </tr>
            <tr>
              <td height="20" colspan="4" valign="top"><table width="857" border="0" align="center" height="422" cellpadding="2" cellspacing="2" id="searchBorder">
                <tr bgcolor="#CCCCFF">
                  <td width="341" height="20"><strong> Test Name</strong></td>
                     <td colspan="1"><strong>Lab Values</strong></td>
                  <td colspan="1"><strong>Ref Range</strong></td>
                  </tr>
                <tr>
    <td height="20">Total Cholesterol</td>
    <td width="332"><label for="blood_cholesterol"></label>
      <?php echo $labFecth ['blood_cholesterol']; ?></td>
      <td height="20">(3.5 - 6.5  mmoI/L)</td>
  </tr>
  <tr>
    <td height="20">HDL Cholesterol</td>
    <td><?php echo $labFecth['blood_hdl']; ?></td>
  </tr>
  <tr>
    <td height="20">LDL Cholesterol</td>
    <td><?php echo $labFecth['blood_ldl']; ?></td>
  </tr>
  <tr>
    <td height="20">Triglyceride</td>
    <td><?php echo $labFecth['blood_tri']; ?></td>
  </tr>
   <tr>
    <td height="20">Faecal Occult Blood Test</td>
    <td><?php echo $labFecth['occult']; ?></td>
    <td height="20">(0.50 - 1.75 mmoI/L)</td>
  </tr>
  <tr>
    <td height="20">Hs CRP</td>
    <td><?php echo $labFecth['crp']; ?></td>
  </tr>
                <tr>
                  <td height="20">&nbsp;</td>
                  <td colspan="2">&nbsp;</td>
                  <td>&nbsp;</td>
                  <td colspan="2">&nbsp;</td>
                </tr>
              </table></td>
            </tr>
            <tr>
              <td height="20"><strong><em>Comments</em></strong></td>
              <td height="20" colspan="3"><hr />
              <?php echo $labFecth['comments']; ?>                <hr /></td>
            </tr>
         <tr>
         <td height="20" colspan="2">&nbsp;</td>
   		 <td width="165" height="20">Lab Scientist  Name</td>
   		 <td><?php echo $labFecth['lab_tech']; ?></td>
  		 </tr>
          <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20"><em>Pathologist Name &amp; Signature:</em></td>
              <td height="20"><strong>Dr. LUCIUS IMOH</strong></td>
            </tr>
            <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20">&nbsp;</td>
              <td height="20"><img src="images/lucius.jpg" alt="" /></td>
            </tr>
          </table>
      </form>      </td>
    </tr>
  </table>
<?php } ?>

<?php
if($oldr=='urm'){
$stmt=$db->query("SELECT * FROM rdc_urine_microscopy WHERE patient_no='$emr' and result_id='$result_id'");
	$labFecth=$stmt->fetch(PDO::FETCH_ASSOC);
?>


                                <table class="table invoice-table" width="100%" cellpadding="5" cellspacing="5" style="border:1px solid #000; border-collapse:collapse; font-size:12px; font-family:Arial, Helvetica, sans-serif; text-align:left">
            <tr>
              <td height="20" colspan="4"><div align="center">
                <h2>URINE MICROBIOLOGY RESULT
                  <hr /></h2>
              </div></td>
            </tr>
            <tr>
              <td height="20" colspan="4" valign="top"><table width="857" border="0" align="center" height="422" cellpadding="2" cellspacing="2" id="searchBorder">
                <tr bgcolor="#CCCCFF">
                  <td width="155" height="20"><strong> Test Name</strong></td>
                  <td colspan="2"><strong>Lab Values</strong></td>
                  <td width="160" height="20" bgcolor="#FFCC33">Antibiotics </td>
                  <td width="159" height="20" bgcolor="#FFCC33">Remark</td>
                  <td width="155" bgcolor="#FFCC33">S/I/R</td>
                  </tr>
                <tr>
                  <td height="20">Epithelial Cells</td>
                  <td colspan="2"><label for="Epithelia"></label>
                    <?php echo $labFecth['Epithelia']; ?></td>
                    <td height="20">Ofloxacin</td>
                  <td><?php echo $labFecth['Ofloxacin']; ?></td>
                  <td><?php echo $labFecth['sir_oflo']; ?></td>
                  </tr>
                <tr>
                  <td height="20">PUS Cells</td>
                  <td colspan="2"><?php echo $labFecth['PUS']; ?></td>
                  <td height="20">Cloxacillin</td>
                  <td height="20"><?php echo $labFecth['Cloxacillin']; ?></td>
                  <td><?php echo $labFecth['sir_clox']; ?></td>
                  </tr>
                <tr>
                  <td height="20">Red Blood Cells</td>
                  <td colspan="2"><?php echo $labFecth['Red_Blood']; ?></td>
                  <td>Ceftazidime</td>
                  <td><?php echo $labFecth['Ceftazidine']; ?></td>
                  <td><?php echo $labFecth['sir_cef']; ?></td>
                  </tr>
                <tr>
                  <td height="20">T. Vaginalis</td>
                  <td colspan="2"><?php echo $labFecth['Vaginalis']; ?></td>
                 <td>Augmentin</td>
                  <td><?php echo $labFecth['Augmentin']; ?></td>
                  <td><?php echo $labFecth['sir_aug']; ?></td>
                  </tr>
                <tr>
                  <td height="20">Crystals</td>
                  <td colspan="2"><?php echo $labFecth['Crystals']; ?></td>
                  <td>Cefuroxime</td>
                  <td><?php echo $labFecth['Cefuroxime']; ?></td>
                  <td><?php echo $labFecth['sir_cefo']; ?></td>
                  </tr>
                <tr>
                  <td height="20">Sperm Cells</td>
                  <td colspan="2"><?php echo $labFecth['Sperm']; ?></td>
                   <td>Cefixime</td>
                  <td><?php echo $labFecth['cefixime']; ?></td>
                  <td><?php echo $labFecth['sir_cefi']; ?></td>
                  </tr>
                <tr>
                   <td height="20">Cul</td>
                  <td colspan="2"><?php echo $labFecth['cul']; ?></td>
                  <td>Ceftriaxone</td>
                  <td><?php echo $labFecth['ceftriaxime']; ?></td>
                  <td><?php echo $labFecth['sir_ceftri']; ?></td>
                </tr>
                <tr>
                 <td height="20">Others Specify</td>
                  <td colspan="2"><?php echo $labFecth['others']; ?></td>
                 <td>Imipenem</td>
                  <td><?php echo $labFecth['imi']; ?></td>
                  <td><?php echo $labFecth['sir_imi']; ?></td>
                  </tr>
                <tr>
                 <td height="20" bgcolor="#FFCC33">Antibiotics </td>
                  <td width="112" height="20" bgcolor="#FFCC33">Remark</td>
                  <td width="78" bgcolor="#FFCC33">S/I/R</td>
                   <td>Vancomycim</td>
                  <td><?php echo $labFecth['van']; ?></td>
                  <td><?php echo $labFecth['sir_van']; ?></td>
                  </tr>
                <tr>
                  <td height="32">Erythromycin</td>
                  <td><?php echo $labFecth['Erythromycin']; ?></td>
                  <td><?php echo $labFecth['sir_ery']; ?></td>
                 <td>Piperacillin/Tazobactan</td>
                  <td><?php echo $labFecth['pipe']; ?></td>
                  <td><?php echo $labFecth['sir_pipe']; ?></td>
                  <td colspan="2">&nbsp;</td>
                  </tr>
                <tr>
                   <td height="32">Gentamycin</td>
                  <td><?php echo $labFecth['Gentamycin']; ?></td>
                  <td><?php echo $labFecth['sir_gent']; ?></td>
                  <td>Meropenem</td>
                  <td><?php echo $labFecth['meropenem']; ?></td>
                  <td><?php echo $labFecth['penem']; ?></td>
                  <td colspan="2">&nbsp;</td>
                  </tr>
                <tr>
                    <td height="20">Penicillin</td>
                  <td><label for="others"></label>
                  &nbsp;<?php echo $labFecth['Penicillin']; ?></td>
                  <td><?php echo $labFecth['sir_pen']; ?></td>
                  
                 
                  <td>&nbsp;</td>
                  <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                  
                 <td height="20">Ciprofloxacin</td>
                  <td><?php echo $labFecth['ciprofloxacin']; ?></td>
                  <td><?php echo $labFecth['sir_cip']; ?></td>
                  <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                
                 <td height="20">Colistin Sulphate</td>
                  <td><?php echo $labFecth['Colistin']; ?></td>
                  <td><?php echo $labFecth['sir_coli']; ?></td>
                  <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                  <td height="20">Cotrinioxazole</td>
                  <td><?php echo $labFecth['Contrimoxazole']; ?></td>
                  <td><?php echo $labFecth['sir_cont']; ?></td>
                  <td colspan="2">&nbsp;</td>
                  <td>&nbsp;</td>
                  <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                  
                  <td>&nbsp;</td>
                  <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                  <td height="20">&nbsp;</td>
                  <td colspan="2">&nbsp;</td>
                  <td>&nbsp;</td>
                  <td colspan="2">&nbsp;</td>
                </tr>
              </table></td>
            </tr>
            <tr>
              <td height="20"><strong><em>Comments</em></strong></td>
              <td height="20" colspan="3"><hr />
              <?php echo $labFecth['comments']; ?>                <hr /></td>
            </tr>
            <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20"><em>Lab Scientist/Technician:</em></td>
              <td height="20"><?php echo $labFecth['lab_tech']; ?></td>
            </tr>
            <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20"><em>Pathologist Name &amp; Signature:</em></td>
              <td height="20"><strong>Dr. KENNETH ONYEDIBE</strong></td>
            </tr>
            <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20">&nbsp;</td>
              <td height="20"><img src="images/microbiology.jpg" alt="" /></td>
            </tr>
          </table>

<?php } ?>


<?php
if($oldr=='uri'){
$stmt=$db->query("SELECT * FROM rdc_Urinalysis WHERE patient_no='$emr' and result_id='$result_id'");
	$labFecth=$stmt->fetch(PDO::FETCH_ASSOC);
?>
                                <table class="table invoice-table" width="100%" cellpadding="5" cellspacing="5" style="border:1px solid #000; border-collapse:collapse; font-size:12px; font-family:Arial, Helvetica, sans-serif; text-align:left">

              <td height="20" colspan="4"><div align="center">
                <h2>URINALYSIS MICROBIOLOGY RESULT
                  <hr /></h2>
              </div></td>
            </tr>
            <tr>
              <td height="20" colspan="4" valign="top"><table width="857" border="0" align="center" height="422" cellpadding="2" cellspacing="2" id="searchBorder">
                <tr bgcolor="#CCCCFF">
                  <td width="341" height="20"><strong> Test Name</strong></td>
                  <td colspan="2"><strong>Lab Values</strong></td>
                  </tr>
                 <tr>
    <td height="20">Blood</td>
    <td width="332"><label for="Blood"></label>
      <?php echo $labFecth['Blood']; ?></td>
  </tr>
  <tr>
    <td height="20">Bilirubin</td>
    <td><?php echo $labFecth['Bilirubin']; ?></td>
  </tr>
  <tr>
    <td height="20">Urobilirubin</td>
    <td><?php echo $labFecth['Urobilirubin']; ?></td>
  </tr>
  <tr>
    <td height="20">TKetones</td>
    <td><?php echo $labFecth['Ketones']; ?></td>
  </tr>
  <tr>
    <td height="20">Glucose</td>
    <td><?php echo $labFecth['Glucose']; ?></td>
  </tr>
  <tr>
    <td height="20">Protein</td>
    <td><?php echo $labFecth['Protein']; ?></td>
  </tr>
   <tr>
    <td height="20">Nitrate</td>
    <td><?php echo $labFecth['Nitrate']; ?></td>
  </tr>
   <tr>
    <td height="20">Leucocytes</td>
    <td><?php echo $labFecth['Leucocytes']; ?></td>
  </tr>
   <tr>
    <td height="20">PH</td>
    <td><?php echo $labFecth['PH']; ?></td>
  </tr>
   <tr>
    <td height="20">Specific Gravity</td>
    <td><?php echo $labFecth['Specific_Gravity']; ?></td>
  </tr>
 
                <tr>
                  <td height="20">&nbsp;</td>
                  <td colspan="2">&nbsp;</td>
                  <td width="41">&nbsp;</td>
                  <td width="43" colspan="2">&nbsp;</td>
                </tr>
               <tr>
    <td height="20">Others Specify</td>
    <td><label for="others"></label>
      <?php echo $labFecth['others']; ?>&nbsp;</td>
  </tr>
                <tr>
                  <td height="20">&nbsp;</td>
                  <td colspan="2">&nbsp;</td>
                  <td>&nbsp;</td>
                  <td colspan="2">&nbsp;</td>
                </tr>
              </table></td>
            </tr>
            <tr>
              <td height="20"><strong><em>Comments</em></strong></td>
              <td height="20" colspan="3"><hr />
              <?php echo $labFecth['comments']; ?>                <hr /></td>
            </tr>
         <tr>
         <td height="20" colspan="2">&nbsp;</td>
   		 <td width="165" height="20">Lab Scientist  Name</td>
   		 <td><?php echo $labFecth['lab_tech']; ?></td>
  		 </tr>
          <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20"><em>Pathologist Name &amp; Signature:</em></td>
              <td height="20"><strong>Dr. LUCIUS IMOH</strong></td>
            </tr>
            <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20">&nbsp;</td>
              <td height="20"><img src="images/lucius.jpg" alt="" /></td>
            </tr>
          </table>



<?php } ?>

<?php
if($oldr=='sto'){
$stmt=$db->query("SELECT * FROM rdc_stool_microscopy WHERE patient_no='$emr' and result_id='$result_id'");
	$labFecth=$stmt->fetch(PDO::FETCH_ASSOC);
?>

                                <table class="table invoice-table" width="100%" cellpadding="5" cellspacing="5" style="border:1px solid #000; border-collapse:collapse; font-size:12px; font-family:Arial, Helvetica, sans-serif; text-align:left">

              <td height="20" colspan="4"><div align="center">
                <h2>STOOL MICROBIOLOGY RESULT
                  <hr /></h2>
              </div></td>
            </tr>
            <tr>
              <td height="837" colspan="4" valign="top"><input type="hidden" name="result_id" value="<?php echo $labFecth['result_id']; ?>" />
                <input type="hidden" name="patient_no" value="<?php echo $labFecth['patient_no']; ?>" />
                <input type="hidden" name="Newdate" value="<?php echo $labFecth['Newdate']; ?>" />
                <table width="802" border="0" align="center" height="839" cellpadding="5" cellspacing="5" id="searchBorder3">
                  <tr bgcolor="#FFCC00">
                    <td height="20" bgcolor="#CCCCFF"><strong>Microscopy</strong></td>
                    <td colspan="2" bgcolor="#CCCCFF"><strong>Lab Values</strong></td>
                  </tr>
                  <tr>
                    <td height="20">Macroscopy</td>
                    <td colspan="2"><?php echo $labFecth['color']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Microscopy</td>
                    <td colspan="2"><?php echo $labFecth['cons']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">culture</td>
                    <td colspan="2"><?php echo $labFecth['cul']; ?></td>
                  </tr>
                  <tr bgcolor="#FFCC33">
                    <td height="20"><strong>Antibiotics</strong></td>
                    <td width="270"><strong>Remark</strong></td>
                    <td width="256"><strong>S/I/R</strong></td>
                  </tr>
                  <tr>
                    <td height="47">Penicillin</td>
                    <td><?php echo $labFecth['Penicillin']; ?>&nbsp;</td>
                    <td><?php echo $labFecth['sir_pen']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Erythromycin</td>
                    <td><?php echo $labFecth['Erythromycin']; ?></td>
                    <td><?php echo $labFecth['sir_ery']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Gentamycin</td>
                    <td><?php echo $labFecth['Gentamycin']; ?></td>
                    <td><?php echo $labFecth['sir_gent']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Colistin Sulphate</td>
                    <td><?php echo $labFecth['Colistin']; ?></td>
                    <td><?php echo $labFecth['sir_coli']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Cotrinioxazole</td>
                    <td><?php echo $labFecth['Contrimoxazole']; ?></td>
                    <td><?php echo $labFecth['sir_cont']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Ofloxacin</td>
                    <td><?php echo $labFecth['Ofloxacin']; ?></td>
                    <td><?php echo $labFecth['sir_oflo']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Cloxacillin</td>
                    <td><?php echo $labFecth['Cloxacillin']; ?></td>
                    <td><?php echo $labFecth['sir_clox']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Ceftazidime</td>
                    <td><?php echo $labFecth['Ceftazidine']; ?></td>
                    <td><?php echo $labFecth['sir_cef']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Augmentin</td>
                    <td><?php echo $labFecth['Augmentin']; ?></td>
                    <td><?php echo $labFecth['sir_aug']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Cefuroxime</td>
                    <td><?php echo $labFecth['Cefuroxime']; ?></td>
                    <td><?php echo $labFecth['sir_cefo']; ?></td>

                  </tr>
                  <tr>
                    <td height="20">Ciprofloxacin</td>
                    <td><?php echo $labFecth['ciprofloxacin']; ?></td>
                    <td><?php echo $labFecth['sir_cip']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">cefixime</td>
                    <td><?php echo $labFecth['cefixime']; ?></td>
                    <td><?php echo $labFecth['sir_cefi']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Ceftriaxone</td>
                    <td><?php echo $labFecth['ceftriaxime']; ?></td>
                    <td><?php echo $labFecth['sir_ceftri']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Imipenem</td>
                    <td><?php echo $labFecth['imi']; ?></td>
                    <td><?php echo $labFecth['sir_imi']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Vancomycim</td>
                    <td><?php echo $labFecth['van']; ?></td>
                    <td><?php echo $labFecth['sir_van']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Piperacillin/Tazobactan</td>
                    <td><?php echo $labFecth['pipe']; ?></td>
                    <td><?php echo $labFecth['sir_pipe']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Meropenem</td>
                    <td><?php echo $labFecth['meropenem']; ?></td>
                    <td><?php echo $labFecth['penem']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Others</td>
                    <td><?php echo $labFecth['othersAnti']; ?></td>
                    <td>&nbsp;</td>
                  </tr>
                  
                </table></td>
            </tr>
            <tr>
              <td height="20"><strong><em>Comments</em></strong></td>
              <td height="20" colspan="3"><hr />
              <?php echo $labFecth['comments']; ?>                <hr /></td>
            </tr>
            <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20"><em>Lab Scientist/Technician:</em></td>
              <td height="20"><?php echo $labFecth['lab_tech']; ?></td>
            </tr>
            <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20"><em>Pathologist Name &amp; Signature:</em></td>
              <td height="20"><strong>Dr. KENNETH ONYEDIBE</strong></td>
            </tr>
            <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20">&nbsp;</td>
              <td height="20"><img src="images/microbiology.jpg" alt="" /></td>
            </tr>
          </table>
      </form>      </td>
    </tr>
  </table>
  
<?php } ?>




<?php
if($oldr=='ser'){
$stmt=$db->query("SELECT * FROM rdc_serology WHERE patient_no='$emr' and result_id='$result_id'");
	$labFecth=$stmt->fetch(PDO::FETCH_ASSOC);
?>
                                <table class="table invoice-table" width="100%" cellpadding="5" cellspacing="5" style="border:1px solid #000; border-collapse:collapse; font-size:12px; font-family:Arial, Helvetica, sans-serif; text-align:left">

              <td height="20" colspan="4" valign="top"><table width="769" border="0" align="center" height="805" cellpadding="5" cellspacing="5" id="searchBorder3">
                  <tr bgcolor="#D6D6D6">
                    <td height="20" colspan="4" bgcolor="#FFCC00"><div align="left">SEROLOGY</div></td>
                  </tr>
                  <tr bgcolor="#FFCC00">
                    <td height="20" bgcolor="#CCCCFF">Test Name</td>
                    <td width="564" colspan="2" bgcolor="#CCCCFF">Lab Values</td>
                  </tr>
                  <tr>
                    <td height="20">Hepatitis B (HbsAg)</td>
                    <td colspan="2"><label for="hepa"></label>
                    <?php echo $labFecth['hepa']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Hepatitis C Virus (HCV)</td>
                    <td colspan="2"><?php echo $labFecth['hepati']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Retroviral Screening (RVS)</td>
                    <td colspan="2"><?php echo $labFecth['retro']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">CD 4 Count </td>
                    <td colspan="2"><?php echo $labFecth['cd4']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">VDRL</td>
                    <td colspan="2"><?php echo $labFecth['vdrl']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">ASO Titre</td>
                    <td colspan="2"><?php echo $labFecth['aso']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Toxoplasmosis IgM</td>
                    <td colspan="2"><?php echo $labFecth['toxo']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Toxoplasmosis IgG</td>
                    <td colspan="2"><?php echo $labFecth['toxopla']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Rubella IgM</td>
                    <td colspan="2"><?php echo $labFecth['rube']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Rubella IgG</td>
                    <td colspan="2"><?php echo $labFecth['rubella']; ?></td>

                  </tr>
                  <tr>
                    <td height="20">Cytomegalovirus IgM</td>
                    <td colspan="2"><?php echo $labFecth['cyto']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Cytomegalovirus IgG</td>
                    <td colspan="2"><?php echo $labFecth['cytome']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Hepatitis A Virus</td>
                    <td colspan="2"><?php echo $labFecth['hepatitis']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">HB surface Ag</td>
                    <td colspan="2"><?php echo $labFecth['hb']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Hepatitis C Virus (HCV)</td>
                    <td colspan="2"><?php echo $labFecth['hepatitisc']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Anti-HB core</td>
                    <td colspan="2"><?php echo $labFecth['antia']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Anti-HB core IgM</td>
                    <td colspan="2"><?php echo $labFecth['antihb']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Anti-Hbe</td>
                    <td colspan="2"><?php echo $labFecth['antihbe']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Anti-HBs</td>
                    <td colspan="2"><?php echo $labFecth['antihbs']; ?></td>
                  </tr>
                  <tr>
                    <td height="20">Hepatitis B Combo</td>
                    <td colspan="2"><?php echo $labFecth['hepatitisb']; ?></td>
                  </tr>
                   <tr>
                    <td height="20">Culture</td>
                    <td colspan="2"><?php echo $labFecth['cul']; ?></td>
                  </tr>
                   <tr>
                    <td height="20">Others</td>
                    <td colspan="2"><?php echo $labFecth['others']; ?></td>
                  </tr>
                  <tr>
                    <td height="20" colspan="3">(HBsAg,HBsAb, HbeAg, HbeAb, Hbcore-1gM)</td>
                 
                  
                  <tr>
                    <td height="20">&nbsp;</td>
                    <td colspan="2">&nbsp;</td>
                  </tr>
              </table></td>
            </tr>
            <tr>
              <td height="20"><strong><em>Comments</em></strong></td>
              <td height="20" colspan="3"><hr />
              <?php echo $labFecth['comments']; ?>                <hr /></td>
            </tr>
            <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20"><em>Lab Scientist/Technician:</em></td>
              <td height="20"><?php echo $labFecth['lab_tech']; ?></td>
            </tr>
            <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20"><em>Pathologist Name &amp; Signature:</em></td>
              <td height="20"><strong>Dr. KENNETH ONYEDIBE</strong></td>
            </tr>
            <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20">&nbsp;</td>
              <td height="20"><img src="images/microbiology.jpg" alt="" /></td>
            </tr>
          </table>


<?php } ?>

<?php
if($oldr=='sem'){
$stmt=$db->query("SELECT * FROM rdc_seminal WHERE patient_no='$emr' and result_id='$result_id'");
	$labFecth=$stmt->fetch(PDO::FETCH_ASSOC);
?>

                                <table class="table invoice-table" width="100%" cellpadding="5" cellspacing="5" style="border:1px solid #000; border-collapse:collapse; font-size:12px; font-family:Arial, Helvetica, sans-serif; text-align:left">
              <td height="20" colspan="4"><div align="center">
                <h2>MICROBIOLOGY SEMINAL FLUID RESULT
                  <hr /></h2>
              </div></td>
            </tr>
            <tr>
              <td height="20" colspan="4" valign="top"><table width="857" border="0" align="center" height="422" cellpadding="2" cellspacing="2" id="searchBorder">
                <tr bgcolor="#CCCCFF">
                  <td width="255" height="20"><strong> Test Name</strong></td>
                  <td colspan="2"><strong>Lab Values</strong></td>
                  <td width="235" height="20"><strong> Test Name</strong></td>
                  <td colspan="2"><strong>Lab Values</strong></td>
                  </tr>
                 <tr>
                     <td height="20">Time Produced</td>
                      <td colspan="2"><label for="Time Produced"></label>
                        <?php echo $labFecth['time_produced']; ?></td>
                      <td height="20">Normal Forms</td>
                      <td height="20"><?php echo $labFecth['normal_form']; ?></td>
                    </tr>
                   <tr>
                      <td height="20">Time Assayed</td>
                      <td colspan="2"><?php echo $labFecth['time_assayed']; ?></td>
                      <td height="20">Abnormal Forms</td>
                      <td height="20"><?php echo $labFecth['abnormal_form']; ?></td>
                    </tr>
                    <tr>
                  <td height="20">Method of Collection</td>
                  <td colspan="2"><?php echo $labFecth['method_collection']; ?></td>	
                     <td height="20">Head</td>
                      <td height="20"><?php echo $labFecth['heads']; ?></td>
                    </tr>
                    <tr>
                      <td height="20">Period of Abstinence</td>
                      <td colspan="2"><?php echo $labFecth['abstinence']; ?></td>
                      <td height="20">Middle Piece</td>
                      <td height="20"><?php echo $labFecth['middle_piece']; ?></td>
                    </tr>
                    <tr>
                      <td height="20">Homogeneity</td>
                      <td colspan="2"><?php echo $labFecth['homogeneity']; ?></td>
                      <td height="20">Tail</td>
                      <td height="20"><?php echo $labFecth['tails']; ?></td>
                    </tr>
                    <tr>
                      <td height="20">Colour</td>
                      <td colspan="2"><?php echo $labFecth['colour']; ?></td>
                      <td height="20">Viable</td>
                      <td height="20"><?php echo $labFecth['viable']; ?></td>

                    </tr>
                    <tr>
                      <td height="20">Liquefaction</td>
                      <td colspan="2"><?php echo $labFecth['liquefaction']; ?></td>
                      <td height="20">Non - Viable</td>
                      <td height="20"><?php echo $labFecth['non_viable']; ?></td>
                    </tr>
                    <tr>
                      <td height="20">Volume</td>
                      <td colspan="2"><?php echo $labFecth['volumes']; ?></td>
                      <td height="20">Total Sperm Count</td>
                      <td height="20"><?php echo $labFecth['sperm_count']; ?></td>
                    </tr>
                    <tr>
                      <td height="20">Consistency</td>
                      <td colspan="2"><?php echo $labFecth['consistency']; ?></td>
                      <td height="20">White Blood Cell (PUS CELL)</td>
                      <td height="20"><?php echo $labFecth['blood_cell']; ?></td>
                    </tr>
                    <tr>
                      <td height="20">ph</td>
                      <td colspan="2"><?php echo $labFecth['ph']; ?></td>
                      <td height="20">Polygonal Epithelial Cell</td>
                      <td height="20"><?php echo $labFecth['polygonal']; ?></td>
                    </tr>
                    <tr>
                  <td height="20">Rapid Linear Progression</td>
                  <td colspan="2"><?php echo $labFecth['rapid_progression']; ?></td>	
                     <td height="20">Spermatogenic</td>
                      <td height="20"><?php echo $labFecth['spermatogenic']; ?></td>   					</tr>
                    <tr>
                      <td height="20">Slow Linear Progression</td>
                      <td colspan="2"><?php echo $labFecth['slow_progression']; ?></td>
                    </tr>
                    <tr>
                      <td height="20">Non - Progressive</td>
                      <td colspan="2"><?php echo $labFecth['non_progression']; ?></td>
                    </tr>
                    <tr>
                      <td height="20">Immotile Cell</td>
                      <td colspan="2"><?php echo $labFecth['immotile']; ?></td>
                    </tr>
                   <tr>
                      <td height="20">Sperm Agglutination</td>
                      <td colspan="2"><?php echo $labFecth['agglutination']; ?></td>
                    </tr>
 					 <tr>
                      <td height="20">Culture</td>
                      <td colspan="2"><?php echo $labFecth['cul']; ?></td>
                    </tr>
                <tr>
                  <td height="20">&nbsp;</td>
                  <td colspan="2">&nbsp;</td>
                  <td width="235">&nbsp;</td>
                  <td width="163" colspan="2">&nbsp;</td>
                </tr>
               <tr>
    <td height="20">Others Specify</td>
    <td width="175"><label for="others"></label>
      <?php echo $labFecth['others']; ?>&nbsp;</td>
  </tr>
                <tr>
                  <td height="20">&nbsp;</td>
                  <td colspan="2">&nbsp;</td>
                  <td>&nbsp;</td>
                  <td colspan="2">&nbsp;</td>
                </tr>
              </table></td>
            </tr>
            <tr>
              <td height="20"><strong><em>Comments</em></strong></td>
              <td height="20" colspan="3"><hr />
              <?php echo $labFecth['comments']; ?>                <hr /></td>
            </tr>
         <tr>
         <td height="20" colspan="2">&nbsp;</td>
   		 <td width="165" height="20">Lab Scientist  Name</td>
   		 <td><?php echo $labFecth['lab_tech']; ?></td>
  		 </tr>
          <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20"><em>Pathologist Name &amp; Signature:</em></td>
              <td height="20"><strong>Dr. LUCIUS IMOH</strong></td>
            </tr>
            <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20">&nbsp;</td>
              <td height="20"><img src="images/lucius.jpg" alt="" /></td>
            </tr>
          </table>
      </form>      </td>
    </tr>
  </table>


<?php } ?>



<?php
if($oldr=='omi'){
$stmt=$db->query("SELECT * FROM rdc_other_microscopy WHERE patient_no='$emr' and result_id='$result_id'");
	$labFecth=$stmt->fetch(PDO::FETCH_ASSOC);
?>
                                <table class="table invoice-table" width="100%" cellpadding="5" cellspacing="5" style="border:1px solid #000; border-collapse:collapse; font-size:12px; font-family:Arial, Helvetica, sans-serif; text-align:left">
        <tr bgcolor="#FFCC00">
          <td height="20">Microscopy</td>
          <td>Lab Values</td>
        </tr>
        <tr>
          <td height="20">Other M/C/S</td>
          <td><?php echo $labFecth['others']; ?></td>
        </tr>
        <tr bgcolor="#FFCC00">
          <td height="20">Antibiotics</td>
          <td>Lab Values</td>
        </tr>
        <tr>
          <td height="32">Penicillin</td>
          <td><?php echo $labFecth['Penicillin']; ?>&nbsp;</td>
        </tr>
        <tr>
          <td height="32">Ampicillin</td>
          <td><?php echo $labFecth['Ampicillin']; ?></td>
        </tr>
        <tr>
          <td height="20">Erythromycin</td>
          <td><?php echo $labFecth['Erythromycin']; ?></td>
        </tr>
        <tr>
          <td height="20">Tetracycline</td>
          <td><?php echo $labFecth['Tetracycline']; ?></td>
        </tr>
        <tr>
          <td height="20">Streptomycin</td>
          <td><?php echo $labFecth['Streptomycin']; ?></td>
        </tr>
        <tr>
          <td height="20">Gentamycin</td>
          <td><?php echo $labFecth['Gentamycin']; ?></td>
        </tr>
        <tr>
          <td height="20">Nalidixic Acid</td>
          <td><?php echo $labFecth['Nalidixi']; ?></td>
        </tr>
        <tr>
          <td height="20">Nitrofurantoin</td>
          <td><?php echo $labFecth['Nitrofurantoin']; ?></td>
        </tr>
        <tr>
          <td height="20">Colistin Sulphate</td>
          <td><?php echo $labFecth['Colistin']; ?></td>
        </tr>
        <tr>
          <td height="20">Contrimoxazole</td>
          <td><?php echo $labFecth['Contrimoxazole']; ?></td>
        </tr>
        <tr>
          <td height="20">Ofloxacin</td>
          <td><?php echo $labFecth['Ofloxacin']; ?></td>
        </tr>
        <tr>
          <td height="20">Cloxacillin</td>
          <td><?php echo $labFecth['Cloxacillin']; ?></td>
        </tr>
        <tr>
          <td height="20">Ceftazidime</td>
          <td><?php echo $labFecth['Ceftazidine']; ?></td>
        </tr>
        <tr>
          <td height="20">Augmentin</td>
          <td><?php echo $labFecth['Augmentin']; ?></td>
        </tr>
        <tr>
          <td height="20">Cefuroxime</td>
          <td><?php echo $labFecth['Cefuroxime']; ?></td>
        </tr>
        <tr>
          <td height="20">Sulphonamide</td>
          <td><?php echo $labFecth['Sulphonamide']; ?></td>
        </tr>
        <tr>
          <td height="20">ciprofloxacin</td>
          <td><?php echo $labFecth['ciprofloxacin']; ?></td>
        </tr>
        <tr>
          <td height="20">cefixime</td>
          <td><?php echo $labFecth['cefixime']; ?></td>
        </tr>
        <tr>
          <td height="20">Ceftriaxime</td>
          <td><?php echo $labFecth['ceftriaxime']; ?></td>
        </tr>
        <tr>
          <td width="165" height="20">Lab Scientist  Name</td>
     
          <td><?php echo $labFecth['lab_tech']; ?></td>
        </tr>
        <tr>
          <td height="20">Date Captured</td>
          <td><?php echo $labFecth['date_captured']; ?></td>
        </tr>
      </table>
      </td>
    </tr>
  </table>

<?php } ?>


<?php
if($oldr=='omb'){
$stmt=$db->query("SELECT * FROM rdc_other_microbiology WHERE patient_no='$emr' and result_id='$result_id'");
	$labFecth=$stmt->fetch(PDO::FETCH_ASSOC);
?>
                                <table class="table invoice-table" width="100%" cellpadding="5" cellspacing="5" style="border:1px solid #000; border-collapse:collapse; font-size:12px; font-family:Arial, Helvetica, sans-serif; text-align:left">
              <td height="20" colspan="4"><div align="center">
                <h2>OTHER MICROBIOLOGY RESULT
                  <hr /></h2>
              </div></td>
            </tr>
            <tr>
              <td height="20" colspan="4" valign="top"><table width="857" border="0" align="center" height="422" cellpadding="2" cellspacing="2" id="searchBorder">
                <tr bgcolor="#CCCCFF">
                  <td width="155" height="20"><strong> Test Name</strong></td>
                  <td colspan="2"><strong>Lab Values</strong></td>
                  <td width="160" height="20" bgcolor="#FFCC33">Antibiotics </td>
                  <td width="159" height="20" bgcolor="#FFCC33">Zone of Inhibition</td>
                  <td width="155" bgcolor="#FFCC33">S/I/R</td>
                  </tr>
                <tr>
                  <td height="20">Epithelial Cells</td>
                  <td colspan="2"><label for="Epithelia"></label>
                    <?php echo $labFecth['Epithelia']; ?></td>
                  <td height="20">Cloxacillin</td>
                  <td height="20"><?php echo $labFecth['Cloxacillin']; ?></td>
                  <td><?php echo $labFecth['sir_clox']; ?></td>
                  </tr>
                <tr>
                  <td height="20">PUS Cells</td>
                  <td colspan="2"><?php echo $labFecth['PUS']; ?></td>
                  <td>Ceftazidime</td>
                  <td><?php echo $labFecth['Ceftazidine']; ?></td>
                  <td><?php echo $labFecth['sir_cef']; ?></td>
                  </tr>
                <tr>
                  <td height="20">Red Blood Cells</td>
                  <td colspan="2"><?php echo $labFecth['Red_Blood']; ?></td>
                  <td>Augmentin</td>
                  <td><?php echo $labFecth['Augmentin']; ?></td>
                  <td><?php echo $labFecth['sir_aug']; ?></td>
                  </tr>
                <tr>
                  <td height="20">T. Vaginalis</td>
                  <td colspan="2"><?php echo $labFecth['Vaginalis']; ?></td>
                  <td>Cefuroxime</td>
                  <td><?php echo $labFecth['Cefuroxime']; ?></td>
                  <td><?php echo $labFecth['sir_cefo']; ?></td>
                  </tr>
                <tr>
                  <td height="20">Crystals</td>
                  <td colspan="2"><?php echo $labFecth['Crystals']; ?></td>
                  <td>Cefixime</td>
                  <td><?php echo $labFecth['cefixime']; ?></td>
                  <td><?php echo $labFecth['sir_cefi']; ?></td>
                  </tr>
                <tr>
                  <td height="20">Sperm Cells</td>
                  <td colspan="2"><?php echo $labFecth['Sperm']; ?></td>
                  <td>Ceftriaxone</td>
                  <td><?php echo $labFecth['ceftriaxime']; ?></td>
                  <td><?php echo $labFecth['sir_ceftri']; ?></td>
                  </tr>
                <tr>
                  <td height="20" bgcolor="#FFCC33">Antibiotics </td>
                  <td width="112" height="20" bgcolor="#FFCC33">Zone of Inhibition</td>
                  <td width="78" bgcolor="#FFCC33">S/I/R</td>
                  <td>Imipenem</td>
                  <td><?php echo $labFecth['imi']; ?></td>
                  <td><?php echo $labFecth['sir_imi']; ?></td>
                </tr>
                <tr>
                  <td height="20">Penicillin</td>
                  <td><label for="others"></label>
                  &nbsp;<?php echo $labFecth['Penicillin']; ?></td>
                  <td><?php echo $labFecth['sir_pen']; ?></td>
                  <td>Vancomycim</td>
                  <td><?php echo $labFecth['van']; ?></td>
                  <td><?php echo $labFecth['sir_van']; ?></td>
                  </tr>
                <tr>
                  <td height="32">Erythromycin</td>
                  <td><?php echo $labFecth['Erythromycin']; ?></td>
                  <td><?php echo $labFecth['sir_ery']; ?></td>
                  <td>Piperacillin/Tazobactan</td>
                  <td><?php echo $labFecth['pipe']; ?></td>
                  <td><?php echo $labFecth['sir_pipe']; ?></td>
                  </tr>
                <tr>
                  <td height="32">Gentamycin</td>
                  <td><?php echo $labFecth['Gentamycin']; ?></td>
                  <td><?php echo $labFecth['sir_gent']; ?></td>
                  <td>&nbsp;</td>
                  <td colspan="2">&nbsp;</td>
                  </tr>
                <tr>
                  <td height="20">Ciprofloxacin</td>
                  <td><?php echo $labFecth['ciprofloxacin']; ?></td>
                  <td><?php echo $labFecth['sir_cip']; ?></td>
                  <td>&nbsp;</td>
                  <td colspan="2">&nbsp;</td>
                  </tr>
                <tr>
                  <td height="20">Colistin Sulphate</td>
                  <td><?php echo $labFecth['Colistin']; ?></td>
                  <td><?php echo $labFecth['sir_coli']; ?></td>
                  <td>&nbsp;</td>
                  <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                  <td height="20">Contrimoxazole</td>
                  <td><?php echo $labFecth['Contrimoxazole']; ?></td>
                  <td><?php echo $labFecth['sir_cont']; ?></td>
                  <td>&nbsp;</td>
                  <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                  <td height="20">Ofloxacin</td>
                  <td><?php echo $labFecth['Ofloxacin']; ?></td>
                  <td><?php echo $labFecth['sir_oflo']; ?></td>
                  <td>&nbsp;</td>
                  <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                  <td height="20">&nbsp;</td>
                  <td colspan="2">&nbsp;</td>
                  <td>&nbsp;</td>
                  <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                  <td height="20">Others Specify</td>
                  <td colspan="2"><?php echo $labFecth['others']; ?></td>
                  <td>&nbsp;</td>
                  <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                  <td height="20">&nbsp;</td>
                  <td colspan="2">&nbsp;</td>
                  <td>&nbsp;</td>
                  <td colspan="2">&nbsp;</td>
                </tr>
              </table></td>
            </tr>
            <tr>
              <td height="20"><strong><em>Comments</em></strong></td>
              <td height="20" colspan="3"><hr />
              <?php echo $labFecth['comments']; ?>                <hr /></td>
            </tr>
            <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20"><em>Lab Scientist/Technician:</em></td>
              <td height="20"><?php echo $labFecth['lab_tech']; ?></td>
            </tr>
            <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20"><em>Pathologist Name &amp; Signature:</em></td>
              <td height="20"><strong>Dr. KENNETH ONYEDIBE</strong></td>
            </tr>
            <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20">&nbsp;</td>
              <td height="20"><img src="images/microbiology.jpg" alt="" /></td>
            </tr>
          </table>



<?php } ?>

<?php
if($oldr=='mi'){
$stmt=$db->query("SELECT * FROM rdc_microbiology WHERE patient_no='$emr' and result_id='$result_id'");
	$labFecth=$stmt->fetch(PDO::FETCH_ASSOC);
?>

                                <table class="table invoice-table" width="100%" cellpadding="5" cellspacing="5" style="border:1px solid #000; border-collapse:collapse; font-size:12px; font-family:Arial, Helvetica, sans-serif; text-align:left">

              <td height="20" colspan="4"><div align="center">
                <h2>WIDAL &amp; BLOOD PARASITE RESULT
                  <hr /></h2>
              </div></td>
            </tr>
            <tr>
              <td height="20" colspan="4" valign="top"><table width="693" border="0" align="center" height="205" cellpadding="5" cellspacing="5" id="searchBorder">
                <tr bgcolor="#CCCCFF">
                  <td width="165" height="20"><strong>Widal Test</strong></td>
                  <td width="180"><strong>O</strong></td>
                  <td width="298"><strong>H</strong></td>
                </tr>
                <tr>
                  <td height="20">Salmonella Typhi</td>
                  <td><?php echo $labFecth['salmonella_o']; ?></td>
                  <td><?php echo $labFecth['salmonella_h']; ?></td>
                </tr>
                <tr>
                  <td height="20">Salmonella Paratyphi A</td>
                  <td><?php echo $labFecth['salmonella_a_o']; ?></td>
                  <td><?php echo $labFecth['salmonella_a_h']; ?></td>
                </tr>
                <tr>
                  <td height="20">Salmonella Paratyphi B</td>
                  <td><?php echo $labFecth['salmonella_b_o']; ?></td>
                  <td><?php echo $labFecth['salmonella_b_h']; ?></td>
                </tr>
                <tr>
                  <td height="20">Salmonella Paratyphi C</td>
                  <td><?php echo $labFecth['salmonella_c_o']; ?></td>
                  <td><?php echo $labFecth['salmonella_c_h']; ?></td>
                </tr>
                <tr>
                  <td height="20">Malaria Parasite Test</td>
                  <td colspan="2"><?php echo $labFecth['mp']; ?></td>
                </tr>
                <tr>
                  <td height="20">Other Heamoparasite</td>
                  <td colspan="2"><?php echo $labFecth['oh']; ?></td>
                </tr>
                <tr>
                  <td height="20">Mantoux Test</td>
                  <td colspan="2"><?php echo $labFecth['mantoux']; ?></td>
                </tr>
                 <tr>
                  <td height="20">Culture</td>
                  <td colspan="2"><?php echo $labFecth['cul']; ?></td>
                </tr>
              </table></td>
            </tr>
            <tr>
              <td height="20"><strong><em>Comments</em></strong></td>
              <td height="20" colspan="3"><hr />
              <?php echo $labFecth['comments']; ?>                <hr /></td>
            </tr>
            <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20"><em>Lab Scientist:</em></td>
              <td height="20"><?php echo $labFecth['lab_tech']; ?></td>
            </tr>
            <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20"><em>Pathologist Name &amp; Signature:</em></td>
              <td height="20"><strong>Dr. KENNETH ONYEDIBE</strong></td>
            </tr>
            <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20">&nbsp;</td>
              <td height="20"><img src="images/microbiology.jpg" alt="" /></td>
            </tr>
          </table>
      </form>      </td>
    </tr>
  </table>


<?php } ?>



<?php
if($oldr=='ha'){
$stmt=$db->query("SELECT * FROM rdc_haemotology WHERE patient_no='$emr' and result_id='$result_id'");
	$labFecth=$stmt->fetch(PDO::FETCH_ASSOC);
?>

                                <table class="table invoice-table" width="100%" cellpadding="5" cellspacing="5" style="border:1px solid #000; border-collapse:collapse; font-size:12px; font-family:Arial, Helvetica, sans-serif; text-align:left">
              <td height="20" colspan="4"><div align="center">
                <h3>HAEMATOLOGY RESULT
                  <hr /></h3>
              </div></td>
            </tr>
            <tr bgcolor="#FFFFFF">
              <td height="20" colspan="4" valign="top"><table width="865" border="0" align="center" height="552" cellpadding="2" cellspacing="2" id="searchBorder3">
                <tr bgcolor="#FFCC00">
                  <td height="20" colspan="3" bgcolor="#FFCC33"><b>HAEMATOLOGY</b></td>
                  <td height="20" colspan="3" bgcolor="#FFCC33"><b>DIFFERENTIAL WBC COUNT</b></td>
                  </tr>
                <tr bgcolor="#FFCC00">
                  <td width="162" height="20" bgcolor="#CCCCFF"><strong>TEST NAME</strong></td>
                  <td width="94" bgcolor="#CCCCFF"><strong>LAB VALUES</strong></td>
                  <td width="160" bgcolor="#CCCCFF"><strong>REFERENCES RANGES</strong></td>
                  <td height="20" bgcolor="#CCCCFF"><strong>TEST NAME</strong></td>
                  <td bgcolor="#CCCCFF"><strong>LAB VALUES</strong></td>
                  <td bgcolor="#CCCCFF"><strong>REFERENCES RANGES</strong></td>
                  </tr>
                <tr>
                  <td height="20">PCV</td>
                  <td><?php echo $labFecth['PCV']; ?></td>
                  <td>&nbsp;(0.36 - 0.54 x 10 <sup>9</sup>/<sub>L</sub>)</td>
                <td height="20">Neut</td>
                  <td><?php echo $labFecth['Neut']; ?></td>
                  <td colspan="4">&nbsp;&nbsp;(1.2 - 4.7 x 10 <sup>9</sup>/<sub>L</sub>)</td>
                </tr>
                <tr>
                  <td height="20">Hb</td>
                  <td><label for="PCV"></label>                    &nbsp;<?php echo $labFecth['Hb']; ?></td>
                  <td>(120 - 160 g/L)</td>
                  <td height="20">Lymp</td>
                  <td><?php echo $labFecth['Lymp']; ?>&nbsp;</td>
                  <td colspan="4">&nbsp;(1.3 - 3.4 x 10 <sup>9</sup>/<sub>L</sub>)</td>
                </tr>
                <tr>
                  <td height="20">Total WBC</td>
                  <td><?php echo $labFecth['WBC']; ?></td>
                  <td>(2.3 - 8.2 x10 <sup>9</sup>/<sub>L </sub>)</td>
                  <td height="20">Mono<sub></sub></td>
                  <td><?php echo $labFecth['Mono']; ?></td>
                  <td colspan="4">(0.2 - 1.0 x 10 <sup>9</sup>/<sub>L</sub><sub></sub>)</td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td height="20">Platelets</td>
                  <td><?php echo $labFecth['Platelets']; ?></td>
                  <td>(100 - 400 x 10 <sup>9</sup>/<sub>L</sub>)</td>
                   <td height="20">Eosin</td>
                  <td><?php echo $labFecth['Eosin']; ?>&nbsp;&nbsp;</td>
                  <td colspan="4">(0 - 0.8 x 10 <sup>9</sup>/<sub>L</sub>)</td>
                  </tr>
                <tr>
                  <td height="20">ESR</td>
                  <td><?php echo $labFecth['ESR']; ?></td>
                  <td>(0 - 10mm/hr)</td>
                    <td height="20">Baso</td>
                  <td><?php echo $labFecth['Baso']; ?></td>
                  <td colspan="4">&nbsp;(0.01 - 0.1 x 10 <sup>9</sup>/<sub>L</sub>)</td>
                </tr>
                <tr>
                  <td height="20">Retics</td>
                  <td><?php echo $labFecth['Retics']; ?></td>
                  <td>(0 - 2.5%)</td>
                   <td height="20">Stab</td>
                  <td><?php echo $labFecth['Stab']; ?>&nbsp;</td>
                  <td colspan="4">&nbsp;</td>
                 
                </tr>
                <tr>
                  <td height="20">Solubility Test</td>
                  <td><?php echo $labFecth['Solubility']; ?></td>
                  <td>&nbsp;</td>
                    <td height="20">Blasts</td>
                  <td><?php echo $labFecth['Blasts']; ?>&nbsp;</td>
                  <td colspan="4">&nbsp;</td>
                  </tr>
                <tr>
                  <td height="20">Hb Electrohoresis</td>
                  <td><?php echo $labFecth['Electrohoresis']; ?></td>
                  <td>&nbsp;</td>
                  <td height="20">Promy</td>
                  <td><?php echo $labFecth['Promy']; ?>&nbsp;&nbsp;</td>
                  <td colspan="4">&nbsp;</td>
                  </tr>
                   <tr>
                  <td height="20">RBC</td>
                  <td><?php echo $labFecth['rbc']; ?></td>
                  <td>&nbsp;(4.00 - 6.20 x 10 <sup>9</sup>/<sub>L</sub>)</td>
                  <td height="20">Myelo</td>
                  <td><?php echo $labFecth['Myelo']; ?>&nbsp;&nbsp;</td>
                  <td colspan="4">&nbsp;</td>
                  </tr>
                   <tr>
                  <td height="20">MCV</td>
                  <td><?php echo $labFecth['mcv']; ?></td>
                  <td>(80.0 - 100.0 <sub>lu </sub>m^3)</td>
                  <td height="20">Metamy</td>
                  <td><?php echo $labFecth['Metamy']; ?>&nbsp;&nbsp;</td>
                  <td colspan="4">&nbsp;</td>
                  </tr>
                   <tr>
                  <td height="20">MCH</td>
                  <td><?php echo $labFecth['mch']; ?></td>
                  <td>&nbsp;(26.0- 34.0 pg)</td>
                 <td colspan="3" bgcolor="#FFCC33"><b>CLOTTING PROFILE</b></td>
                  </tr>
                   <tr>
                  <td height="20">MCHC</td>
                  <td><?php echo $labFecth['mchc']; ?></td>
                  <td>(31.0- 35.5  g/dl)</td>
                  <td height="20" bgcolor="#CCCCFF"><strong>TEST NAME</strong></td>
                  <td bgcolor="#CCCCFF"><strong>LAB VALUES</strong></td>
                  <td bgcolor="#CCCCFF"><strong>REFERENCES RANGES</strong></td>
                  </tr>
                  <tr>
                  <td height="20">RDW</td>
                  <td><?php echo $labFecth['rdw']; ?></td>
                  <td>(10.0- 16.0 %)</td>
                  <td>Bleeding Time</td>
                  <td><?php echo $labFecth['bleed']; ?></td>
                  <td>(2 - 7mml/hr)</td>
                </tr>
                 <tr>
                  <td height="20">MPV</td>
                  <td><?php echo $labFecth['mpv']; ?></td>
                  <td>(7.0 - 11.0 <sub>lu </sub>m^3)</td>
                 <td>PT</td>
                  <td><?php echo $labFecth['pt']; ?></td>
                  <td>(12 - 14 sec)</td>
                </tr>
                 <tr>
                  <td height="20">PCT</td>
                  <td><?php echo $labFecth['pct']; ?></td>
                  <td>&nbsp;(0.200 - 0.500 %)</td>
                  <td>INR</td>
                  <td><?php echo $labFecth['inr']; ?></td>
                  <td>&nbsp;(1 - 1.2)</td>
                </tr>
                 <tr>
                  <td height="20">PDW</td>
                  <td><?php echo $labFecth['pdw']; ?></td>
                  <td>&nbsp;(10.0 - 18.0 %)</td>
                 <td>PTTK</td>
                  <td><?php echo $labFecth['pttk']; ?></td>
                  <td>(35 - 45)</td>
                </tr>
                 <tr>
                 <td colspan="3" bgcolor="#FFCC33"><b>IMMUNOHAEMATOLOGY</b></td>
                  <td>TT</td>
                  <td><?php echo $labFecth['tt']; ?></td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                 <td height="20" bgcolor="#CCCCFF"><strong>TEST NAME</strong></td>
                  <td bgcolor="#CCCCFF"><strong>LAB VALUES</strong></td>
                  <td bgcolor="#CCCCFF"><strong>REFERENCES RANGES</strong></td>
                 <td>CT</td>
                  <td><?php echo $labFecth['ct']; ?></td>
                  <td>&nbsp;</td>
                </tr>
                 <tr>
                 <td>Blood Group</td>
                  <td><?php echo $labFecth['blood_group']; ?></td>
                  <td>&nbsp;</td>
                  <td>Blood Tranfusion Compatability Test</td>
                  <td><?php echo $labFecth['bloodComp']; ?></td>
                  <td>&nbsp;</td>
                </tr>
                 <tr>
                 <td>Anti Human Globulin Test</td>
                  <td><?php echo $labFecth['antiHuman']; ?></td>
                  <td>&nbsp;</td>
                 </tr>
                  <tr>
                   <td>Transfusion Crossmatch</td>
                  <td><?php echo $labFecth['transfusionC']; ?></td>
                  <td>&nbsp;</td>
                 </tr>
               <tr>
                  <td height="20">Others, Specified</td>
                  <td><?php echo $labFecth['others']; ?></td>
                  <td colspan="4">&nbsp;</td>
                </tr>
              </table></td>
            </tr>
            <tr>
              <td height="20"><strong><em>Comments</em></strong></td>
              <td height="20" colspan="3"><hr />
              <?php echo $labFecth['comments']; ?>
              <hr /></td>
            </tr>
            <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20">Lab Scientist</td>
              <td height="20"><?php echo $labFecth['lab_tech']; ?></td>
            </tr>
            <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20"><em>Pathologist Name &amp; Signature:</em></td>
              <td height="20"><strong>Dr. JATAU EZRA DANJUMA</strong></td>
            </tr>
            <tr>
              <td height="20" colspan="2">&nbsp;</td>
              <td height="20">&nbsp;</td>
              <td height="20"><img src="images/haema.jpg" alt="" /></td>
            </tr>
          </table>
          

<?php } ?>