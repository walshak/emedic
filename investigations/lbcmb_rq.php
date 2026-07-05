	
    <?php
	
	
    $stmt2 = $db->prepare("SELECT c.test_id, l.test 
    FROM lab_combos_items as c 
    INNER JOIN lab_scan as l ON c.test_id=l.sn 
    WHERE combos_id = :cb_no");

$stmt2->bindParam(':cb_no', $cb_no, PDO::PARAM_STR);
$stmt2->execute();

if ($stmt2->rowCount() > 0) {

	while($row_test=$stmt2->fetch(PDO::FETCH_ASSOC)) { 
	
				 $test_name=$row_test['test'].'<br>';
					 $test_id=$row_test['test_id'];
		?>
        			   
				   <div class="form_sep">
   <strong>FILL RESULT:&nbsp; <?php echo $row_test['test'] ?></strong>
   </div>
   
                       <div class="form_sep">
                  <label for="reg_input_no" class="">Preferred Specimen</label>
                <input type="text" id="preferred_specimen" name="preferred_specimen" class="form-control"  readonly value="<?php echo $row_test['preferred_specimen']; ?>">
            </div>
            	

          
            <div class="form_sep">
                  <label for="reg_input_no" class="">Specimen</label>
<select name="Specimen[]" data-placeholder="Select" class="form-control" >
                        <?php if ($row_test['collected_specimen']!=''){?>
                        		 <option value="<?php echo $row_test['collected_specimen']; ?>" selected="selected"><?php echo $row_test['collected_specimen']; ?></option>
                        <?php }else{ ?>
              <option value="">Select...</option>
              			<?php } ?>
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
            
            
            <?php   
					   
					   
$stmt = $db->prepare("SELECT * FROM lab_scan_fields WHERE test_no = :test_id ORDER BY sn");
$stmt->bindParam(':test_id', $test_id, PDO::PARAM_STR);
$stmt->execute();

if ($stmt->rowCount() > 0) {
			
			//// body of one REQUEST -----------------------------------------------------------------------------
			
			$Total_field_count=$stmt->rowCount();
			while($row_fields=$stmt->fetch(PDO::FETCH_ASSOC)){ ?>
				
                    <div class="form_sep">   
                          <table width="100%"><tr><td><label for="reg_input_no" class=""><?php echo $row_fields['field']; ?></label></td><td align="right"><div align="right"><label for="reg_input_no" style="text-align:right"><?php if ($row_fields['reference']!=''){echo 'Reference' . '( ' . $row_fields['reference'] .' )';} ?></label></div></td></tr></table>
                          
                          <?php if($row_fields['field_type']=='options'){
							  ////////////// OPTION 
							  		$input_type='option';
                                      $stmt_opt = $db->prepare("SELECT * FROM lab_scan_rlts_opt WHERE field_id_no = :field_id ORDER BY sn");
                                      $stmt_opt->bindParam(':field_id', $row_fields['sn'], PDO::PARAM_STR);
                                      $stmt_opt->execute();                                      
                              
                            if($stmt_opt->rowCount()>0){ ?>
                                        <select name="fvalue[]" class="form-control" data-required="true">
                                        <option selected="selected" value="">Select ...</option>            
                                    <?php while($row_opt=$stmt_opt->fetch(PDO::FETCH_ASSOC)){	?>
                                        <option value="<?php echo $row_opt['options']; ?>"><?php echo $row_opt['options']; ?></option>
                                    <?php } ?>
                                     </select>    
                            <?php } ?>
                            
                            
                            
                         	<?php }elseif($row_fields['field_type']=='values'){ 
							  ////////////// VALUES  
							  $input_type='values';
                              $stmt_values = $db->prepare("SELECT * FROM lab_scan_rlts_values WHERE field_id_no = :field_id ORDER BY sn");
                              $stmt_values->bindParam(':field_id', $row_fields['sn'], PDO::PARAM_STR);
                              $stmt_values->execute();
                              
                              if ($stmt_values->rowCount() > 0) { ?>
                            <table width="100%" cellpadding="5" cellspacing="5">
								 <?php while($row_vxls=$stmt_values->fetch(PDO::FETCH_ASSOC)){?>
       <tr><td><input type="text" name="fvalues[]" class="form-control" placeholder="Enter result for <?php echo $row_vxls['options']; ?>"></td><td>&nbsp;<strong>Ref.:</strong>&nbsp;<?php echo $row_vxls['reference']; ?>
       <input type="hidden" name="values_fre[]" value="<?php echo $row_vxls['reference']; ?>" />
       <input type="hidden" name="values_title[]" value="<?php echo $row_vxls['options']; ?>" />
       </td></tr>
                           <?php } ?>
                           </table>
      <input type="hidden"  name="fvalue[]" class="form-control" placeholder="Enter result for <?php echo $row_fields['field']; ?>">
                             <?php } ?>
                             
                            <?php }elseif($row_fields['field_type']=='report'){ 
							
							///// ENTER REPORT
							$input_type='report';
							?>
                             
                             
                             <?php
    $stmt2 = $db->prepare("SELECT template_2 FROM invsti_template WHERE sn = :test_id");
    $stmt2->bindParam(':test_id', $test_id, PDO::PARAM_STR);
    $stmt2->execute();
    
    if ($stmt2->rowCount() > 0) {
        $row_NOTE = $stmt2->fetch(PDO::FETCH_ASSOC);
    }
    
                   ?>   
                   <div class="mail-text h-200">
                               <textarea name="template_note" id="template_note" cols="45" rows="5" class="summernote" placeholder="Type Your Message Here" ><?php echo $row_NOTE['template_2']; ?></textarea>  
                           </div>  
     <input type="hidden"  name="fvalue[]" class="form-control" placeholder="Enter result for <?php echo $row_fields['field']; ?>">
                         
                             
                             
                             
                             
                            <?php }else{ 
							//// SINGLE
							$input_type='single';
							?>
        <input type="text"  name="fvalue[]" class="form-control" placeholder="Enter result for <?php echo $row_fields['field']; ?>">
                            <?php } ?>
        
        <input type="hidden" name="fname[]" value="<?php echo $row_fields['field']; ?>" />
        <input type="hidden" name="fre[]" value="<?php echo $row_fields['reference']; ?>" />
        <input type="hidden" name="fno[]" value="<?php echo $row_fields['sn']; ?>" />
        <input type="hidden" name="labrequest_no[]" value="<?php echo $labrequest_no; ?>" />
                    </div>	
     		
                     <?php  }
					$total_c=$total_c+$Total_field_count;
					 ?>
			
            
                          <div class="form_sep">
                            <label for="reg_input_no" class="">COMMENT</label>
  <textarea name="comment[]" cols="15" rows="2" class="form-control" data-minlength="10" placeholder="Enter result for COMMENT"><?php echo $row_test['result_comment']; ?></textarea>
                            </div>
                            <div class="form_sep">
  <textarea name="notes[]" cols="15" rows="2" class="form-control" data-minlength="10" placeholder="Note"><?php echo $row_test['result_note']; ?></textarea>
                            </div>  
 
	<?php if($_SESSION['speciality']=="Data Operator"){ ?>
                <input type="hidden" name="entered_by[]" value="<?php echo $_SESSION['fullname']; ?>"/>
                <input type="hidden" name="lab_sci_name[]" value=""/>
                <input type="hidden" name="lab_sci_speciality[]" value=""/>
    <?php }else{ ?>
                <input type="hidden" name="entered_by[]" value="<?php echo $_SESSION['fullname']; ?>"/>
                <input type="hidden" name="lab_sci_name[]" value="<?php echo $_SESSION['fullname']; ?>"/>
                <input type="hidden" name="lab_sci_speciality[]" value="<?php echo $_SESSION['speciality']; ?>"/>	
    <?php } ?>
                            
     <input type="hidden" name="labrequest_no2[]" value="<?php echo $labrequest_no; ?>" />         
                 <br><hr>           
                                        		 
		<?php			 
//// body of one REQUEST ---------------------END        END--------------------------------------------------------
				 
		}
					  }
					  ?>
                <input type="hidden" name="input_type" value="<?php echo $input_type; ?>" />       
		 <button class="btn btn-success btn-sm" type="submit" name="submit_result" ><i class="fa fa-check"></i>&nbsp;&nbsp; Submit Result</button>
	<input type="hidden" name="Total_field_count"  id="Total_field_count"  value="<?php echo $total_c; ?>" />
</form>	
			
	<?php }else{ ?>		
<div class="alert alert-danger">No test display sheet to view now </div>            
					<?php }