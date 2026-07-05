<div class="modal inmodal fade bannerformmodal" id="bannerformmodal" tabindex="-1" role="dialog"  aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm" >
        <div class="modal-content">
            <!--<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Open Patient Account</h4>
			</div>-->

                <div class="modal-body">  
                     <form method="POST" action="process_search.php">

<h4>Enter Hospital Number </h4><hr>






<table width="100%" height="50%"  style="padding:20px;">
<tr>
<td>
        
        <div id="">
            <label for="reg_input_no" class="req">Hospital Patients</label>
            <select name="in_patient"  class="input-sm chosen-select" style="width:350px;" >
             <option selected="selected" value="">Search and Select Patient</option>
            <?php  $stmt = $db->query("SELECT hospital_no, surname, fname FROM enrollee WHERE hmo_no!='' AND insurance!='' order by hospital_no");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){ ?>
<option value="<?php echo $row["hospital_no"]; ?>"><?php echo $row["hospital_no"] .' '. $row["fname"]  .', ' . $row["surname"]; ?></option>
            <?php } ?>
            </select>
            </div>
            
 </td>
<?php /*?> <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
 <td>
 
     <div id="">
    <label for="reg_input_no" class="req">RDC Patients</label>
        <select name="ex_patient"  class="input-sm chosen-select" style="width:350px;"  >
         <option selected="selected" value="">Search and Select Patient</option>
        <?php $stmt = $db->query("SELECT patient_no, patient_names FROM rdc_patient_tbl order by patient_no");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){ ?>
<option value="<?php echo $row["patient_no"]; ?>"><?php echo $row["patient_no"] .' '. $row["patient_names"]; ?></option>
        <?php } ?>
        </select>
    </div>
    
 
 </td><?php */?>
 </tr>
 
 
 <tr>
 <td><br><br>

 </td>
 <td></td>
 

 <td><br><br>
  <div align="right">
 <button type="submit" name="go_search" class="btn btn-sm btn-primary"><i class="fa fa-search-plus"></i> &nbsp;&nbsp;Go &nbsp;&nbsp;</button>
 </div>
</td>
 
 </tr>
 </table>
            
   
   
    
   


<br><br>
<br><br>

<br><br>

<br><br>
 <a href="index.php" class="btn btn-danger btn-sm" ><i class="fa fa-times"></i> &nbsp; Close</a>

            
		<input type="hidden" name="section" value="<?php echo $_SESSION['section']; ?>"  />
          </form>
        </div>
        
       
      </div>
    </div>
  </div>