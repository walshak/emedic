<?php
$oo = $db->prepare("SELECT * FROM enrollee WHERE hospital_no = ?");
$oo->execute([$hos_no]);
$p_det = $oo->fetch();

$p_det_age = date_diff(new DateTime('now'), new DateTime($p_det['dob']));
$p_det_age = $p_det_age->y;
?>
<div id="Patient_discharge_councelling_form">
    REF Form
    <label for="discharge_note"> Refferal Notes:</label>
    <textarea name="refferal_notes_Patient_discharge_councelling_form" id="refferal_notes_Patient_discharge_councelling_form" style="height:600px" cols="30" rows="45" class="form-control trumbowygEditor" required>
						<div>
						

							<table border="2" width="100%">
									
									<tr>
										<th><b> Name of Patient: <?php echo isset($names) ? $names : ''; ?></b> </th>
										<th><b> Hospital No:</b> <?php echo $hos_no; ?></th>
									</tr>
							</table>

							<br>
						
							
							<table border="2" width="100%">
							<tr>
							<td style="width: 33%"><b>Reason for Admission</b></td>
							<td style="width: 33%"><b>Diagnosis On Admission</b></td>
							<td style="width: 33%"><b>Monitoring Parameter</b></td>
							</tr>
							<tr style="height: 100px;">
							<td style="height: 20%;"><?php echo $reason_adm; ?></td>
							<td style="height: 20%;">&nbsp;</td>
							<td style="height: 20%;">&nbsp;</td>
							</tr>
							</table>

							<table border="2" width="100%">
							<tr>
							<td style="width: 50%"><b>Treatment Summary (Plan)</b></td>
							<td style="width: 50%"><b>Patient Outcomes</b></td>
							</tr>
							<tr style="height: 100px;">
							<td style="height: 20%;"><?php echo $notes; ?></td>
							<td style="height: 20%;">&nbsp;</td>
							</tr>
							</table>

							<h3>DISCHARGE MEDICATIONS</h3>	

							<?php

                            $stmt = $db->prepare('SELECT drug_name, dosage, frequent, sDate, nDate FROM drug_charts WHERE hospital_no = :hos_no AND adm_id = :adm_id');
                            $stmt->execute([':hos_no' => $hos_no, ':adm_id' => $sn]);

                            if ($stmt->rowCount() > 0) { ?>
  <table border="2" width="100%">
    <thead>
      <tr>
        <th width="20%">Name</th>
        <th width="25%">Dose</th>
        <th width="10%">Frequency</th>
        <th width="15%">Start Date</th>
        <th width="15%">End Date</th>
        <th width="15%">Target Goals</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
        <tr>
          <td><?php echo $row['drug_name']; ?></td>
          <td><?php echo $row['dosage']; ?></td>
          <td><?php echo $row['frequent']; ?></td>
          <td><?php echo date('d M,Y', strtotime($row['sDate'])); ?></td>
          <td><?php echo $row['nDate']; ?></td>
          <td>&nbsp;</td>
        </tr>
      <?php } ?>
	  <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
	  <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
	  <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
	  <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
    </tbody>
  </table>
<?php } ?>

<br>
<h3>DISNOTES AND COUNSELLING POINTS</h3>	

<table border="2" width="100%">   
  <tr>
    <td width="40%" valign="top"><p>HAVE ENOUGH REST</p></td>
    <td width="4%"></td>
    <td width="14%"></td>
    <td width="4%"></td>
    <td width="18%"></td>
    <td width="4%"></td>
    <td width="11%"></td>
    <td width="4%"></td>
  </tr>
  <tr>
    <td width="40%" valign="top"><p>TAKE WITH LOTS OF WATER</p></td>
    <td width="4%"></td>
    <td width="14%"></td>
    <td width="4%"></td>
    <td width="18%"></td>
    <td width="4%"></td>
    <td width="11%"></td>
    <td width="4%"></td>
  </tr>
  <tr>
    <td width="40%" valign="top"><p>SWALLOW WHOLE, DO NOT CHEW</p></td>
    <td width="4%"></td>
    <td width="14%"></td>
    <td width="4%"></td>
    <td width="18%"></td>
    <td width="4%"></td>
    <td width="11%"></td>
    <td width="4%"></td>
  </tr>
  <tr>
    <td width="40%" valign="top"><p>DISSOLVE IN A GLASS OF WATER</p></td>
    <td width="4%"></td>
    <td width="14%"></td>
    <td width="4%"></td>
    <td width="18%"></td>
    <td width="4%"></td>
    <td width="11%"></td>
    <td width="4%"></td>
  </tr>
  <tr>
    <td width="40%" valign="top"><p>WITH RESPECT TO MEALS, TAKE BEFORE</p></td>
    <td width="4%"></td>
    <td width="14%" valign="top"><p>AFTER</p></td>
    <td width="4%"></td>
    <td width="18%" valign="top"><p>WITH MEAL</p></td>
    <td width="4%"></td>
    <td width="11%"></td>
    <td width="4%"></td>
  </tr>
  <tr>
    <td width="40%" valign="top"><p>KINDLY REDUCE OR AVOID ALCOHOL</p></td>
    <td width="4%"></td>
    <td width="14%" valign="top"><p>SMOKING</p></td>
    <td width="4%"></td>
    <td width="18%" valign="top"><p>DAIRLY PRODUCT</p></td>
    <td width="4%"></td>
    <td width="11%"></td>
    <td width="4%"></td>
  </tr>
  <tr>
    <td width="40%" valign="top"><p>ONCE DAILY DOSING, TAKE IN THE MORNING</p></td>
    <td width="4%"></td>
    <td width="14%" valign="top"><p>AFTERNOON</p></td>
    <td width="4%"></td>
    <td width="18%" valign="top"><p>EVENING</p></td>
    <td width="4%"></td>
    <td width="11%" valign="top"><p>NIGHT</p></td>
    <td width="4%"></td>
  </tr>
  <tr>
    <td width="40%" valign="top"><p>FREQUENCY OF ADMINISTRATION; 12HOURLY</p></td>
    <td width="4%"></td>
    <td width="14%" valign="top"><p>8 HOURLY</p></td>
    <td width="4%"></td>
    <td width="18%" valign="top"><p>6 HOURLY</p></td>
    <td width="4%"></td>
    <td width="11%" valign="top"><p>4 HOURLY</p></td>
    <td width="4%"></td>
  </tr>
  <tr>
    <td width="40%" valign="top"></td>
    <td width="4%"></td>
    <td width="14%"></td>
    <td width="4%"></td>
    <td width="18%"></td>
    <td width="4%"></td>
    <td width="11%"></td>
    <td width="4%"></td>
  </tr>
  <tr>
    <td width="40%" valign="top">OTHERS</td>
    <td colspan='7'></td>
  </tr>
</table>

<hr>

<table border="2" width="100%">  
  <tr>
    <td width="33.33%"><p>NAME OF PHARMACIST</p></td>
    <td width="33.33%"><p>SIGNATURE</p></td>
    <td width="33.33%"><p>DATE</p></td>
  </tr>
  <tr>
    <td></td>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td><p>START DATE</p></td>
    <td><p>END DATE</p></td>
    <td><p>FOLLOW-UP</p></td>
  </tr>
</table>

						</div>
				</textarea>
</div>
<div id="MEDICATION_INTERVENTION_FORM">
    <p style="margin: 0; padding: 0;"><strong>MEDICATION INTERVENTION FORM</strong></p>
    <textarea name="refferal_notes_MEDICATION_INTERVENTION_FORM" id="refferal_notes_MEDICATION_INTERVENTION_FORM" style="height:600px" cols="30" rows="45" class="form-control trumbowygEditor" required>
    <table style="border-collapse: collapse; width: 100%;">
        <tbody>
            <tr>
                <td style="border: 1px solid black; padding: 8px;">
                    PATIENT INITIALS:
                </td>
                <td style="border: 1px solid black; padding: 8px;">
                <?php echo isset($names) ? $names : ''; ?>
                </td>
                <td style="border: 1px solid black; padding: 8px;">
                    PREVIOUS ADMISSION:
                </td>
                <td style="border: 1px solid black; padding: 8px;">

                </td>
            </tr>
            <tr>
                <td style="border: 1px solid black; padding: 8px;">
                    PATIENT ID:
                </td>
                <td style="border: 1px solid black; padding: 8px;">
                <?php echo isset($hos_no) ? $hos_no : ''; ?>
                </td>
                <td style="border: 1px solid black; padding: 8px;">AGE:</td>
                <td style="border: 1px solid black; padding: 8px;">
                    <?php echo  $p_det_age; ?>
                </td>
                <td style="border: 1px solid black; padding: 8px;">DATE OF ADM.:</td>
                <td style="border: 1px solid black; padding: 8px;"></td>
                <td style="border: 1px solid black; padding: 8px;">WEIGHT:</td>
                <td style="border: 1px solid black; padding: 8px;"></td>
            </tr>
        </tbody>
    </table><br>
    <p style="margin: 0; padding: 0;"><strong>WARD:</strong></p>

    <table style="border-collapse: collapse; width: 100%; margin-bottom: 10px;">
        <tbody>
            <tr>
                <td style="border: 1px solid #000; padding: 8px;">PEDIATRIC & CHILD HEALTH (Y/N)</td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
                <td style="border: 1px solid #000; padding: 8px;">ORTHOPAEDIC (Y/N)</td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
                <td style="border: 1px solid #000; padding: 8px;">OPHTHALMOLOGY (Y/N)</td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 8px;">GYNAECOLOGY (Y/N)</td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
                <td style="border: 1px solid #000; padding: 8px;">OTORHINOLARYNGOLOGY (Y/N)</td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
                <td style="border: 1px solid #000; padding: 8px;">SURGERY (Y/N)</td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 8px;">UROLOGY (Y/N)</td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
                <td style="border: 1px solid #000; padding: 8px;">DENTISTRY (Y/N)</td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
                <td style="border: 1px solid #000; padding: 8px;">ONCOLOGY (Y/N)</td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 8px;">HAEMODIALYSIS (Y/N)</td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
                <td style="border: 1px solid #000; padding: 8px;">RADIOLOGY (Y/N)</td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
                <td style="border: 1px solid #000; padding: 8px;">ACCIDENT & EMERGENCY (Y/N)</td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 8px;">INTENSIVE CARE UNIT (Y/N)</td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
            </tr>
        </tbody>
    </table>

    <p style="margin: 0; padding: 0;"></p>

    <table style="border-collapse: collapse; width: 100%; border: 1px solid #000; margin-bottom: 10px;">
        <colgroup>
            <col width="292">
            <col width="285">
        </colgroup>
        <tbody>
            <tr>
                <td style="border: 1px solid #000; padding: 8px; text-align: center;">
                    <strong>DIAGNOSIS</strong>
                </td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center;">
                    <strong>PLAN</strong>
                </td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 8px; height: 34px;"></td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
            </tr>
        </tbody>
    </table>

    <p style="margin: 0; padding: 0;"></p>

    <p style="margin: 0; padding: 0;">CURRENT MEDICATIONS (CHECK PHYSICAL STOCK)</p>

    <table style="border-collapse: collapse; width: 100%; border: 1px solid #000; margin-bottom: 10px;">
        <thead>
            <tr>
                <th style="border: 1px solid #000; padding: 8px;">NAME/ STRENGTH</th>
                <th style="border: 1px solid #000; padding: 8px;">HOW IS IT TAKEN</th>
                <th style="border: 1px solid #000; padding: 8px;">WHAT IS IT USED FOR?</th>
                <th style="border: 1px solid #000; padding: 8px;">START DATE</th>
                <th style="border: 1px solid #000; padding: 8px;">END DATE</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="border: 1px solid #000; padding: 8px;"></td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 8px;"></td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 8px;"></td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
            </tr>
        </tbody>

    </table>

    <p style="margin: 0; padding: 0;"></p>
    <p style="margin: 0; padding: 0;"></p>

    <p style="margin: 0; padding: 0;"><strong>PHARMACEUTICAL CARE ISSUE</strong></p>

    <table style="border-collapse: collapse; width: 100%; border: 1px solid #000; margin-bottom: 10px;">
        <tbody>
            <tr>
                <td style="border: 1px solid #000; padding: 8px;">DRUG WITHOUT INDICATION (Y/N)</td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
                <td style="border: 1px solid #000; padding: 8px;">MEDICINE INTERACTIONS (Y/N)</td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 8px;">INDICATION WITHOUT MEDICATION (Y/N)</td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
                <td style="border: 1px solid #000; padding: 8px;">OVERDOSE (Y/N)</td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 8px;">IMPROPER DRUG SELECTION (Y/N)</td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
                <td style="border: 1px solid #000; padding: 8px;">ADVERSE DRUG REACTION (Y/N)</td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 8px;">SUB-THERAPEUTIC DOSE (Y/N)</td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
                <td style="border: 1px solid #000; padding: 8px;">NON-COMPLIANCE (Y/N)</td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
            </tr>
        </tbody>
    </table>

    <p style="margin: 0; padding: 0;"></p>
    <p style="margin: 0; padding: 0;"></p>

    <p style="margin: 0; padding: 0;">OTHERS: ____________________________</p>
    <p style="margin: 0; padding: 0;">EXPLANATION SPECIFIC MEDICATION</p>
    <p style="margin: 0; padding: 0;">_____</p>
    <p style="margin: 0; padding: 0;"></p>




<table border="2" width="100%">
        <tr>
            <td width="33.33%">
                <p>NAME OF PHARMACIST</p>
            </td>
            <td width="33.33%">
                <p>SIGNATURE</p>
            </td>
            <td width="33.33%">
                <p>DATE</p>
            </td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>
                <p>START DATE</p>
            </td>
            <td>
                <p>END DATE</p>
            </td>
            <td>
                <p>FOLLOW-UP</p>
            </td>
        </tr>
    </table>

    

    </textarea>






</div>
<div id="MEDICATION_RECONCILE_FORM">
    <h4>MEDICATION RECONCILIATION FORM</h4>
    <textarea name="refferal_notes_MEDICATION_RECONCILE_FORM" id="refferal_notes_MEDICATION_RECONCILE_FORM" style="height:600px" cols="30" rows="45" class="form-control trumbowygEditor" required>

    <table style="border-collapse: collapse; width: 100%;">
        <tbody>
            <tr>
                <td style="border: 1px solid black; padding: 8px;">
                    PATIENT INITIALS:
                </td>
                <td style="border: 1px solid black; padding: 8px;">
                <?php echo isset($names) ? $names : ''; ?>
                </td>
                <td style="border: 1px solid black; padding: 8px;">
                    PREVIOUS ADMISSION:
                </td>
                <td style="border: 1px solid black; padding: 8px;">

                </td>
            </tr>
            <tr>
                <td style="border: 1px solid black; padding: 8px;">
                    PATIENT ID:
                </td>
                <td style="border: 1px solid black; padding: 8px;">
                <?php echo isset($hos_no) ? $hos_no : ''; ?>
                </td>
                <td style="border: 1px solid black; padding: 8px;">AGE:</td>
                <td style="border: 1px solid black; padding: 8px;">
                    <?php echo  $p_det_age; ?>
                </td>
                <td style="border: 1px solid black; padding: 8px;">DATE OF ADM.:</td>
                <td style="border: 1px solid black; padding: 8px;"></td>
                <td style="border: 1px solid black; padding: 8px;">WEIGHT:</td>
                <td style="border: 1px solid black; padding: 8px;"></td>
            </tr>
        </tbody>
    </table><br>
    <table style="border-collapse: collapse; width: 100%;">
        <tbody>
            <tr valign="top">
                <td style="border: 1px solid black; padding: 8px;">
                    PAST MEDICAL HISTORY
                </td>
                <td colspan="3" style="border: 1px solid black; padding: 8px;">DRUG HISTORY (HERBAL MEDICATIONS SUPPLEMENTS)
                </td>
                <td style="border: 1px solid black; padding: 8px;"></td>
                <td style="border: 1px solid black; padding: 8px;"></td>
            </tr>
            <tr>
                <th style="border: 1px solid black; padding: 8px;">MEDICINE</th>
                <th style="border: 1px solid black; padding: 8px;">DURATION</th>
                <th style="border: 1px solid black; padding: 8px;">MEDICINE</th>
                <th style="border: 1px solid black; padding: 8px;">DURATION</th>
            </tr>
            <tr>
                <td style="border: 1px solid black; padding: 8px;"></td>
                <td style="border: 1px solid black; padding: 8px;"></td>
                <td style="border: 1px solid black; padding: 8px;"></td>
                <td style="border: 1px solid black; padding: 8px;"></td>
            </tr>
            <tr>
                <td style="border: 1px solid black; padding: 8px;"></td>
                <td style="border: 1px solid black; padding: 8px;"></td>
                <td style="border: 1px solid black; padding: 8px;"></td>
                <td style="border: 1px solid black; padding: 8px;"></td>
            </tr>
            <tr>
                <td style="border: 1px solid black; padding: 8px;"></td>
                <td style="border: 1px solid black; padding: 8px;"></td>
                <td style="border: 1px solid black; padding: 8px;"></td>
                <td style="border: 1px solid black; padding: 8px;"></td>
            </tr>
        </tbody>
    </table><br>
    <table style="border-collapse: collapse; width: 100%;">
        <tbody>
            <tr>
                <td style="border: 1px solid black; padding: 8px;">
                    HAVE YOU BEEN TAKING THE MEDICATIONS AS PRESCRIBED?(Y/N)
                </td>
                <td style="border: 1px solid black; padding: 8px;"></td>
                <td style="border: 1px solid black; padding: 8px;">IF NO, WHAT IS PREVENTING YOU:</td>
                <td style="border: 1px solid black; padding: 8px;"></td>
            </tr>
            <tr>
                <td style="border: 1px solid black; padding: 8px;"> HAVE YOU DEFAULTED YOUR PRESCRIBED MEDICATIONS IN THE LAST 3 DAYS? (Y/N) </td>
                <td style="border: 1px solid black; padding: 8px;"></td>
            </tr>
            <tr>
                <td style="border: 1px solid black; padding: 8px;">
                    HAVING ANY ALLERGIES?(Y/N)
                </td>
                <td style="border: 1px solid black; padding: 8px;"></td>
            </tr>
        </tbody>
    </table>
    <h3 style="margin: 16px 0;">CURRENT MEDICATIONS (CHECK PHYSICAL STOCK)</h3>
    <table style="border-collapse: collapse; width: 100%;">
        <tbody>
            <tr>
                <td style="border: 1px solid black; padding: 8px;">
                    NAME/STRENGTH
                </td>
                <td style="border: 1px solid black; padding: 8px;">
                    HOW IS IT TAKEN
                </td>
                <td style="border: 1px solid black; padding: 8px;">
                    WHAT IS IT USED FOR?
                </td>
                <td style="border: 1px solid black; padding: 8px;">
                    START DATE
                </td>
                <td style="border: 1px solid black; padding: 8px;">
                    END DATE
                </td>
            </tr>
            <tr>
                <td style="border: 1px solid black; padding: 8px;"></td>
                <td style="border: 1px solid black; padding: 8px;"></td>
                <td style="border: 1px solid black; padding: 8px;"></td>
                <td style="border: 1px solid black; padding: 8px;"></td>
                <td style="border: 1px solid black; padding: 8px;"></td>
            </tr>
        </tbody>
    </table><br>
    <h3 style="margin: 16px 0;">
        DRUGS RELATED PROBLEM
    </h3>
    <table style="border-collapse: collapse; width: 100%;">
        <tr>
            <td style="border: 1px solid black; padding: 8px;">UNTREATED INDICATION(Y/N)</td>
            <td style="border: 1px solid black; padding: 8px;"></td>
            <td style="border: 1px solid black; padding: 8px;">IMPROPER MEDICINE SELECTION(Y/N)</td>
            <td style="border: 1px solid black; padding: 8px;"></td>
        </tr>
        <tr>
            <td style="border: 1px solid black; padding: 8px;">IMPROPER ROUTE OF ADMINISTRATION(Y/N)</td>
            <td style="border: 1px solid black; padding: 8px;"></td>
            <td style="border: 1px solid black; padding: 8px;">OVERDOSE(Y/N)</td>
            <td style="border: 1px solid black; padding: 8px;"></td>
        </tr>
        <tr>
            <td style="border: 1px solid black; padding: 8px;">SUB-THERAPEUTIC DOSE(Y/N)</td>
            <td style="border: 1px solid black; padding: 8px;"></td>
            <td style="border: 1px solid black; padding: 8px;">ADVERSE DRUG REACTION(Y/N)</td>
            <td style="border: 1px solid black; padding: 8px;"></td>
        </tr>
        <tr>
            <td style="border: 1px solid black; padding: 8px;">MEDICINE INTERACTIONS(Y/N)</td>
            <td style="border: 1px solid black; padding: 8px;"></td>
            <td style="border: 1px solid black; padding: 8px;">MEDICINE USE WITH NO INDICATION(Y/N)</td>
            <td style="border: 1px solid black; padding: 8px;"></td>
        </tr>
        <tr>
            <td style="border: 1px solid black; padding: 8px;">DUPLICATION OF THERAPY(Y/N)</td>
            <td style="border: 1px solid black; padding: 8px;"></td>
            <td style="border: 1px solid black; padding: 8px;">UNAVAILABILITY OF MEDICINES(Y/N)</td>
            <td style="border: 1px solid black; padding: 8px;"></td>
        </tr>
        <tr>
            <td style="border: 1px solid black; padding: 8px;">MONITORING NEED(Y/N)</td>
            <td style="border: 1px solid black; padding: 8px;"></td>
            <td style="border: 1px solid black; padding: 8px;">COUNSELLING NEED(Y/N)</td>
            <td style="border: 1px solid black; padding: 8px;"></td>
        </tr>
    </table>
    <hr>
    <p style="margin: 16px 0;">
        <strong>OTHERS:</strong>
    </p>
    <hr>
    <p style="margin: 16px 0;">
        <strong>RECOMMENDATIONS:</strong>



        
    </p>


  <table border="2" width="100%">
        <tr>
            <td width="33.33%">
                <p>NAME OF PHARMACIST</p>
            </td>
            <td width="33.33%">
                <p>SIGNATURE</p>
            </td>
            <td width="33.33%">
                <p>DATE</p>
            </td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>
                <p>START DATE</p>
            </td>
            <td>
                <p>END DATE</p>
            </td>
            <td>
                <p>FOLLOW-UP</p>
            </td>
        </tr>
    </table>



    </textarea>


</div>