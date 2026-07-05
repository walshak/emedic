
<?php
if($oldr=='cp'){
	$stmt=$db->query("SELECT * FROM chemical_pathology WHERE patient_no='$hos_no' and result_id='$result_id'");
	$labFecth=$stmt->fetch(PDO::FETCH_ASSOC);
?>

<table class="table table-bordered" >                 
          <tr>
            <td>Patient No</td>
            <td colspan="2"><?php echo $labFecth['patient_no']; ?></td>
            </tr>
          <tr>
            <td>Patient Name</td>
            <td colspan="2"><?php echo $labFecth['patient_firstName']." ".$labFecth['patient_surname']; ?></td>
            </tr>
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
            <td>(3.6 - 6.4 mmol/L)</td>
            </tr>
          <tr>
            <td height="20">RBS</td>
            <td><?php echo $labFecth['rbs']; ?></td>
            <td>(3.3 - 7.4 mmol/L)</td>
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
            <td>(0 - 4 ng/ml)</td>
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
          
          <hr>
          
<div class="pull-right">
<a href="investiga.php?hos_no=<?php echo $hos_no . '&old=cp'; ?>" class="btn btn-danger btn-xs" ><i class="fa fa-timees"></i>Close Result & Return</a>
</div>

<?php } ?>


<?php
if($oldr=='ur'){
	$stmt=$db->query("SELECT * FROM urinalysis WHERE patient_no='$hos_no' and result_id='$result_id'");
	$labFecth=$stmt->fetch(PDO::FETCH_ASSOC);
?>

<table class="table table-bordered" >                 
  
  
  <tr>
    <td height="20">Patient No</td>
    <td><?php echo $labFecth['patient_no']; ?></td>
  </tr>
  <tr>
    <td height="20">Patient Name</td>
    <td><?php echo $labFecth['patient_firstName']." ".$labFecth['patient_surname']; ?></td>
  </tr>
  <tr bgcolor="#FFCC00">
    <td height="20" bgcolor="#CCCCFF"><strong>Urinalysis</strong></td>
    <td bgcolor="#CCCCFF"><strong>Lab Values</strong></td>
  </tr>
  <tr>
    <td height="20">Blood</td>
    <td><?php echo $labFecth['Blood']; ?></td>
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
    <td height="20">Ketones</td>
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
    <td height="20">Others</td>
    <td><?php echo $labFecth['others']; ?></td>
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

<div class="pull-right">
<a href="investiga.php?hos_no=<?php echo $hos_no . '&old=ur'; ?>" class="btn btn-danger btn-xs" ><i class="fa fa-timees"></i>Close Result & Return</a>
</div>

<?php } ?>

<?php
if($oldr=='sem'){
	$stmt=$db->query("SELECT * FROM rdc_seminal WHERE patient_no='$hos_no' and result_id='$result_id'");
	$labFecth=$stmt->fetch(PDO::FETCH_ASSOC);
?>


<table width="575" border="0" align="center" height="930" cellpadding="5" cellspacing="5" id="searchBorder3">
  <tr bgcolor="#D6D6D6">
    <td height="20" colspan="3" bgcolor="#CCCCFF"><div align="left"><strong>Seminal Fluid Report</strong></div></td>
  </tr>
  <tr bgcolor="#FFCC00">
    <td height="20" colspan="3" bgcolor="#CCCCFF"><strong>Seminal Fluid Analysis</strong></td>
  </tr>
  <tr>
    <td height="20">Time Produced</td>
    <td colspan="2"><?php echo $labFecth['time_produced']; ?></td>
  </tr>
  <tr>
    <td height="20">Time Assayed</td>
    <td colspan="2"><label for="time_assayed"></label>
      <?php echo $labFecth['time_assayed']; ?>&nbsp;</td>
  </tr>
  <tr>
    <td height="20" colspan="3" bgcolor="#CCCCFF"><strong>Macroscopy</strong></td>
  </tr>
  <tr>
    <td height="20">Method of Collection</td>
    <td colspan="2"><?php echo $labFecth['method_collection']; ?>&nbsp;</td>
  </tr>
  <tr>
    <td height="20">Period of Abstinence</td>
    <td colspan="2"><?php echo $labFecth['abstinence']; ?>&nbsp;</td>
  </tr>
  <tr>
    <td height="20">Homogeneity</td>
    <td colspan="2"><?php echo $labFecth['homogeneity']; ?>&nbsp;</td>
  </tr>
  <tr>
    <td height="20">Colour</td>
    <td colspan="2"><?php echo $labFecth['colour']; ?>&nbsp;</td>
  </tr>
  <tr>
    <td height="20">Liquefaction</td>
    <td colspan="2"><?php echo $labFecth['liquefaction']; ?>&nbsp;</td>
  </tr>
  <tr>
    <td height="20">Volume</td>
    <td colspan="2"><?php echo $labFecth['volumes']; ?></td>
  </tr>
  <tr>
    <td height="20">Consistency</td>
    <td colspan="2"><?php echo $labFecth['consistency']; ?></td>
  </tr>
  <tr>
    <td height="20">ph</td>
    <td colspan="2"><?php echo $labFecth['ph']; ?>&nbsp;</td>
  </tr>
  <tr bgcolor="#CCCCFF">
    <td height="20" colspan="3"><strong>Motility</strong></td>
  </tr>
  <tr>
    <td height="20">Rapid Linear Progression</td>
    <td colspan="2"><?php echo $labFecth['rapid_progression']; ?>&nbsp;</td>
  </tr>
  <tr>
    <td height="20">Slow Linear Progression</td>
    <td colspan="2"><?php echo $labFecth['slow_progression']; ?>&nbsp;&nbsp;</td>
  </tr>
  <tr>
    <td height="20">Non - Progressive</td>
    <td colspan="2"><?php echo $labFecth['non_progression']; ?>&nbsp;</td>
  </tr>
  <tr>
    <td height="20">Immotile Cell</td>
    <td colspan="2"><?php echo $labFecth['immotile']; ?>&nbsp;&nbsp;</td>
  </tr>
  <tr>
    <td height="20">Sperm Agglutination</td>
    <td colspan="2"><?php echo $labFecth['agglutination']; ?>&nbsp;</td>
  </tr>
  <tr bgcolor="#CCCCFF">
    <td height="20" colspan="3"><strong>Morphology</strong></td>
  </tr>
  <tr>
    <td height="20">Normal Forms</td>
    <td colspan="2"><?php echo $labFecth['normal_forms']; ?>&nbsp;</td>
  </tr>
  <tr>
    <td height="20">Abnormal Forms</td>
    <td colspan="2"><?php echo $labFecth['abnormal_forms']; ?>&nbsp;&nbsp;</td>
  </tr>
  <tr>
    <td height="20">Head</td>
    <td colspan="2"><?php echo $labFecth['heads']; ?>&nbsp;</td>
  </tr>
  <tr>
    <td height="20">Middle Piece</td>
    <td colspan="2"><?php echo $labFecth['middle_piece']; ?></td>
  </tr>
  <tr>
    <td height="20">Tail</td>
    <td colspan="2"><?php echo $labFecth['tails']; ?>&nbsp;</td>
  </tr>
  <tr bgcolor="#CCCCFF">
    <td height="20" colspan="3"><strong>Viability</strong></td>
  </tr>
  <tr>
    <td height="20">Viable</td>
    <td colspan="2"><?php echo $labFecth['viable']; ?></td>
  </tr>
  <tr>
    <td height="20">Non - Viable</td>
    <td colspan="2"><?php echo $labFecth['non_viable']; ?></td>
  </tr>
  <tr>
    <td height="20">Total Sperm Count</td>
    <td colspan="2"><?php echo $labFecth['sperm_count']; ?></td>
  </tr>
  <tr bgcolor="#CCCCFF">
    <td height="20" colspan="3"><strong>Cellular Element</strong></td>
  </tr>
  <tr>
    <td height="20">White Blood Cell (PUS CELL)</td>
    <td colspan="2"><?php echo $labFecth['blood_cell']; ?></td>
  </tr>
  <tr>
    <td height="20">Polygonal Epithelial Cell</td>
    <td colspan="2"><?php echo $labFecth['polygonal']; ?></td>
  </tr>
  <tr>
    <td height="20">Spermatogenic</td>
    <td colspan="2"><?php echo $labFecth['spermatogenic']; ?></td>
  </tr>
  <tr>
    <td height="20">Others, Specify Please</td>
    <td colspan="2"><?php echo $labFecth['others']; ?></td>
  </tr>
  <tr>
    <td height="20">Comments</td>
    <td colspan="2"><label for="comments"></label>
      <?php echo $labFecth['comments']; ?></td>
  </tr>
  <tr>
    <td width="165" height="20">Lab Scientist  Name</td>

    <td colspan="2"><?php echo $labFecth['lab_tech']; ?></td>
  </tr>
</table>

<div class="pull-right">
<a href="investiga.php?hos_no=<?php echo $hos_no . '&old=sem'; ?>" class="btn btn-danger btn-xs" ><i class="fa fa-timees"></i>Close Result & Return</a>
</div>

<?php } ?>

<?php
if($oldr=='um'){
	$stmt=$db->query("SELECT * FROM urine_microscopy WHERE patient_no='$hos_no' and result_id='$result_id'");
	$labFecth=$stmt->fetch(PDO::FETCH_ASSOC);
?>

<table width="575" border="0" align="center" height="879" cellpadding="5" cellspacing="5" id="searchBorder">
  <tr bgcolor="#D6D6D6">
    <td height="20" colspan="2" bgcolor="#CCCCFF"><div align="left"><strong>Urine Microscopy Report Sheet</strong></div></td>
  </tr>
  <tr bgcolor="#CCCCFF">
    <td height="20" colspan="2"><strong>Patient Details</strong></td>
  </tr>
  <tr>
    <td height="20">Patient No</td>
    <td><?php echo $labFecth['patient_no']; ?></td>
  </tr>
  <tr>
    <td height="20">Patient Name</td>
    <td><?php echo $labFecth['patient_firstName']." ".$labFecth['patient_surname']; ?></td>
  </tr>
  <tr bgcolor="#CCCCFF">
    <td height="20"><strong>Microscopy</strong></td>
    <td><strong>Lab Values</strong></td>
  </tr>
  <tr>
    <td height="20">Epithelial Cells</td>
    <td><label for="Epithelia"></label>
      <?php echo $labFecth['Epithelia']; ?></td>
  </tr>
  <tr>
    <td height="20">PUS Cells</td>
    <td><?php echo $labFecth['PUS']; ?></td>
  </tr>
  <tr>
    <td height="20">Red Blood Cells</td>
    <td><?php echo $labFecth['Red_Blood']; ?></td>
  </tr>
  <tr>
    <td height="20">T. Vaginalis</td>
    <td><?php echo $labFecth['Vaginalis']; ?></td>
  </tr>
  <tr>
    <td height="20">Crystals</td>
    <td><?php echo $labFecth['Crystals']; ?></td>
  </tr>
  <tr>
    <td height="20">Sperm Cells</td>
    <td><?php echo $labFecth['Sperm']; ?></td>
  </tr>
  <tr>
    <td height="20">Others Specify</td>
    <td><label for="others"></label>
      <?php echo $labFecth['others']; ?>&nbsp;</td>
  </tr>
  <tr bgcolor="#CCCCFF">
    <td height="20"><strong>Antibiotics</strong></td>
    <td><strong>Lab Values</strong></td>
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
    <td height="20">ceftriaxime</td>
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

<div class="pull-right">
<a href="investiga.php?hos_no=<?php echo $hos_no . '&old=um'; ?>" class="btn btn-danger btn-xs" ><i class="fa fa-timees"></i>Close Result & Return</a>
</div>

<?php } ?>




<?php
if($oldr=='mc'){
	$stmt=$db->query("SELECT * FROM microbiology WHERE patient_no='$hos_no' and result_id='$result_id'");
	$labFecth=$stmt->fetch(PDO::FETCH_ASSOC);
?>

<table class="table table-bordered" >  
  <tr>
    <td height="20">Patient No</td>
    <td colspan="2"><?php echo $labFecth['patient_no']; ?></td>
  </tr>
  <tr>
    <td height="20">Patient Name</td>
    <td colspan="2"><?php echo $labFecth['patient_firstName']." ".$labFecth['patient_surname']; ?></td>
  </tr>
  <tr bgcolor="#CCCCFF">
    <td height="20"><strong>Widal Test</strong></td>
    <td width="180"><strong>O</strong></td>
    <td width="180"><strong>H</strong></td>
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
    <td height="20">Widal Comment</td>
    <td colspan="2"><?php echo $labFecth['widal_comment']; ?></td>
  </tr>
  <tr>
    <td width="165" height="20">Lab Scientist  Name</td>
    <td colspan="2"><?php echo $labFecth['lab_tech']; ?></td>
  </tr>
  <tr>
    <td height="20">Date Captured</td>
    <td colspan="2"><?php echo $labFecth['date_captured']; ?></td>
  </tr>
</table>


<div class="pull-right">
<a href="investiga.php?hos_no=<?php echo $hos_no . '&old=mc'; ?>" class="btn btn-danger btn-xs" ><i class="fa fa-timees"></i>Close Result & Return</a>
</div>

<?php } ?>







<?php
if($oldr=='hm'){
	$stmt=$db->query("SELECT * FROM haemotology WHERE patient_no='$hos_no' and result_id='$result_id'");
	$labFecth=$stmt->fetch(PDO::FETCH_ASSOC);
?>

<table class="table table-bordered" >

  <tr height="20">
    <td >Patient No</td>
    <td colspan="2"><?php echo $labFecth['patient_no']; ?></td>
  </tr>
  <tr height="20">
    <td>Patient Name</td>
    <td colspan="2"><?php echo $labFecth['patient_firstName']." ".$labFecth['patient_surname']; ?></td>
  </tr>
 <tr bgcolor="#FFCC00">
                  <td height="20" colspan="3" bgcolor="#FFCC33"><b>HAEMATOLOGY</b></td>
                  <td height="20" colspan="3" bgcolor="#FFCC33"><b>DIFFERENTIAL WBC COUNT</b></td>
                  </tr>
                <tr bgcolor="#FFCC00">
                  <td width="85" height="20" bgcolor="#CCCCFF"><strong>TEST NAME</strong></td>
                  <td width="77" bgcolor="#CCCCFF"><strong>LAB VALUES</strong></td>
                  <td width="109" bgcolor="#CCCCFF"><strong>REFERENCES RANGES</strong></td>
                  <td width="88" height="20" bgcolor="#CCCCFF"><strong>TEST NAME</strong></td>
                  <td width="97" bgcolor="#CCCCFF"><strong>LAB VALUES</strong></td>
                  <td width="109" bgcolor="#CCCCFF"><strong>REFERENCES RANGES</strong></td>
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
                  <td width="4">&nbsp;</td>
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
                  <td height="20">Bleeding Time</td>
                  <td><?php echo $labFecth['bleed']; ?></td>
                  <td>(2 - 7mml/hr)</td>
                </tr>
                 <tr>
                  <td height="20">MPV</td>
                  <td><?php echo $labFecth['mpv']; ?></td>
                  <td>(7.0 - 11.0 <sub>lu </sub>m^3)</td>
                 <td height="20">PT</td>
                  <td><?php echo $labFecth['pt']; ?></td>
                  <td>(12 - 14 sec)</td>
                </tr>
                 <tr>
                  <td height="20">PCT</td>
                  <td><?php echo $labFecth['pct']; ?></td>
                  <td>&nbsp;(0.200 - 0.500 %)</td>
                  <td height="20">INR</td>
                  <td><?php echo $labFecth['inr']; ?></td>
                  <td>&nbsp;(1 - 1.2)</td>
                </tr>
                 <tr>
                  <td height="20">PDW</td>
                  <td><?php echo $labFecth['pdw']; ?></td>
                  <td>&nbsp;(10.0 - 18.0 %)</td>
                 <td height="20">PTTK</td>
                  <td><?php echo $labFecth['pttk']; ?></td>
                  <td>(35 - 45)</td>
                </tr>
                  <tr >
    <td height="20" colspan="3" bgcolor="#FFCC33"><strong>SEROLOGY</strong></td>
     <td height="20">TT</td>
     <td><?php echo $labFecth['tt']; ?></td>
     <td>&nbsp;</td>
  </tr>
  <tr>
   <td height="20" bgcolor="#CCCCFF"><strong>TEST NAME</strong></td>
   <td bgcolor="#CCCCFF"><strong>LAB VALUES</strong></td>
   <td bgcolor="#CCCCFF"><strong>REFERENCES RANGES</strong></td>
    <td height="20">CT</td>
    <td><?php echo $labFecth['ct']; ?></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="20">HBsAg</td>
    <td><?php echo $labFecth['hbsag']; ?></td>
    <td>&nbsp;</td>
    <td height="20">Blood Tranfusion Compatability Test</td>
    <td><?php echo $labFecth['bloodComp']; ?></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="29">HCV</td>
    <td><?php echo $labFecth['hcv']; ?></td>
    <td>&nbsp;</td>
     <td colspan="3" bgcolor="#FFCC33"><b>IMMUNOHAEMATOLOGY</b></td>
  </tr>
  <tr>
    <td height="20">RVS</td>
    <td><?php echo $labFecth['rvs']; ?></td>
    <td>&nbsp;</td>
      <td height="20" bgcolor="#CCCCFF"><strong>TEST NAME</strong></td>
      <td bgcolor="#CCCCFF"><strong>LAB VALUES</strong></td>
      <td bgcolor="#CCCCFF"><strong>REFERENCES RANGES</strong></td>
  </tr>
  <tr>
    <td height="20">Aso Titre</td>
    <td><?php echo $labFecth['aso']; ?></td>
    <td>&nbsp;</td>
    <td height="20">Blood Group</td>
    <td><?php echo $labFecth['blood_group']; ?></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="20">Rheumotoid Factor</td>
    <td><?php echo $labFecth['rf']; ?></td>
    <td>&nbsp;</td>
    <td height="20">Anti Human Globulin Test</td>
    <td><?php echo $labFecth['antiHuman']; ?></td>
    <td>&nbsp;</td>
</tr>
<tr>
	<td height="20">VDRl</td>
	<td><?php echo $labFecth['vdrl']; ?></td>
	<td>&nbsp;</td>
	<td height="20">Transfusion Crossmatch</td>
	<td><?php echo $labFecth['transfusionC']; ?></td>
	<td>&nbsp;</td>
</tr>
<tr>
  <td height="20">Others, Specified</td>
  <td><?php echo $labFecth['others']; ?></td>
  <td >&nbsp;</td>
</tr>
  <tr>
  <td>&nbsp;</td>
  <td>&nbsp;</td>
  
    <td width="85" height="20">Lab Scientist  Name</td>
    <td><?php echo $labFecth['lab_tech']; ?></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
  <td>&nbsp;</td>
  <td>&nbsp;</td>
  
    <td height="20">Date Captured</td>
    <td><?php echo $labFecth['date_captured']; ?></td>
    <td>&nbsp;</td>
  </tr>
</table>


<div class="pull-right">
<a href="investiga.php?hos_no=<?php echo $hos_no . '&old=hm'; ?>" class="btn btn-danger btn-xs" ><i class="fa fa-timees"></i>Close Result & Return</a>
</div>

<?php } 




if($oldr=='sm'){
	$stmt=$db->query("SELECT * FROM stool_microscopy WHERE patient_no='$hos_no' and result_id='$result_id'");
	$labFecth=$stmt->fetch(PDO::FETCH_ASSOC);
?>

<table class="table table-bordered" >



    <td height="20">Patient No</td>
    <td><?php echo $labFecth['patient_no']; ?></td>
  </tr>
  <tr>
    <td height="20">Patient Name</td>
    <td><?php echo $labFecth['patient_firstName']." ".$labFecth['patient_surname']; ?></td>
  </tr>
  <tr bgcolor="#FFCC00">
    <td height="20" bgcolor="#CCCCFF"><strong>Microscopy</strong></td>
    <td bgcolor="#CCCCFF"><strong>Lab Values</strong></td>
  </tr>
  <tr>
    <td height="20">Macroscopy</td>
    <td><?php echo $labFecth['color']; ?></td>
  </tr>
  <tr>
    <td height="20">Microscopy</td>
    <td><?php echo $labFecth['cons']; ?></td>
  </tr>
  <tr>
    <td height="20">culture</td>
    <td><?php echo $labFecth['cul']; ?></td>
  </tr>
  <tr bgcolor="#FFCC00">
    <td height="20" bgcolor="#CCCCFF"><strong>Antibiotics</strong></td>
    <td bgcolor="#CCCCFF"><strong>Lab Values</strong></td>
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

<div class="pull-right">
<a href="investiga.php?hos_no=<?php echo $hos_no . '&old=sm'; ?>" class="btn btn-danger btn-xs" ><i class="fa fa-timees"></i>Close Result & Return</a>
</div>

<?php }


if($oldr=='om'){
	$stmt=$db->query("SELECT * FROM other_microscopy WHERE patient_no='$hos_no' and result_id='$result_id'");
	$labFecth=$stmt->fetch(PDO::FETCH_ASSOC);
?>

<table class="table table-bordered" >
  <tr>
    <td height="20">Patient No</td>
    <td><?php echo $labFecth['patient_no']; ?></td>
  </tr>
  <tr>
    <td height="20">Patient Name</td>
    <td><?php echo $labFecth['patient_firstName']." ".$labFecth['patient_surname']; ?></td>
  </tr>
  <tr bgcolor="#CCCCFF">
    <td height="20"><strong>Microscopy</strong></td>
    <td><strong>Lab Values</strong></td>
  </tr>
  <tr>
    <td height="20">Other M/C/S</td>
    <td><?php echo $labFecth['others']; ?></td>
  </tr>
  <tr bgcolor="#FFCC00">
    <td height="20" bgcolor="#CCCCFF"><strong>Antibiotics</strong></td>
    <td bgcolor="#CCCCFF"><strong>Lab Values</strong></td>
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
    <td width="165" height="20">Lab Scientist  Name</td>
    <td><?php echo $labFecth['lab_tech']; ?></td>
  </tr>
  <tr>
    <td height="20">Date Captured</td>
    <td><?php echo $labFecth['date_captured']; ?></td>
  </tr>
</table>

<div class="pull-right">
<a href="investiga.php?hos_no=<?php echo $hos_no . '&old=om'; ?>" class="btn btn-danger btn-xs" ><i class="fa fa-timees"></i>Close Result & Return</a>
</div>

<?php } ?>
