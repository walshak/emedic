
<table width="100%">
    <tbody>
        <tr>
            <td align="center">
                <h3 style="color:#888">TRANSPLANT REQUEST LIST  </h3>
            </td>
        </tr>
    </tbody>
</table>

<br>
<table class="table table-striped dataTables-example" >
    <thead>
        <tr>
            <th>#</th>
            <th>Hospital #</th>
            <th>Name</th>
            <th>Details</th>
            <th>Donor(s)</th>
            <th>Request Date/Time</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sn = 1;
        $year_month = date('Y-m');
  if(isset($_POST['patient_trs_display'])){
	  	  
	  $pp = explode('__', $_POST['hospital_no']);
	  $hospital_no=$pp[0];
	  $Patient_name=$pp[1];
	  
            $stmt = $db->prepare("SELECT * from transplants WHERE hospital_no = '$hospital_no' AND status = '1' order by id desc ");
             $stmt->execute();
        }else{
            $stmt = $db->prepare("SELECT * from transplants WHERE status = '1'  and performed_date is null order by id desc ");
        $stmt->execute();
        }

       while ($transplant_ = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $transplant_ = json_decode(json_encode($transplant_));
            $user = $AdminUser->find($transplant_->created_by);
		   
		 ///  echo $transplant_->performed_date
		   
			$id=$transplant_->id;
			$app_no=$transplant_->app_no;
			$trans_note_stmt = $db->prepare( "SELECT id FROM transplants_donors WHERE transplant_id ='$id'" );
			$trans_note_stmt->execute();		   
        ?>

            <tr>

                <td><?= $sn++; ?></td>
                <td><?= $transplant_->hospital_no; ?> </td>
                <td><?= $transplant_->patient_name; ?> </td>
                
                <td><?= $transplant_->transplant_type; ?> 
                <br> <strong>Booked by: </strong> <?= $user->fullname; ?></BR></td>
				<td><?= $trans_note_stmt->rowCount() ;?> </td>
                
				<td><?= date('d M, Y h:i:s A', strtotime($transplant_->created_at)); ?></td> 
                <td>
                <a href="<?php echo $editFormAction;?>&trs=<?= base64_encode(base64_encode($transplant_->id)); ?>" 
				   class="btn btn-sm btn-success" style="font-size: 14px; ">
				<?php if ($trans_note_stmt->rowCount() > 0 and $transplant_->performed_date =='' ) { ?>
					View & Complete Documentation	
				<?php }else{?>
					Open
				<?php } ?>	
					 </a>
<?php 	

		   		if ($trans_note_stmt->rowCount() == 0) {
					?>
					&nbsp;&nbsp;|&nbsp;&nbsp;
 <a href="index.php?transplant&del_trs=<?= $id .'__' . $app_no ;?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to Delete request ?')">Delete </a>
				<?php } ?>	
                </td>

				<td>
<input type="button" name="on_cr" value="Patient Biodata" data-target="#modal" id="<?php echo $transplant_->hospital_no;  ?>" class="btn btn-primary btn-xs bio_data_link" style="font-size: 15px; color:white; " />

				</td>
            </tr>
        <?php
        }
        ?>
    </tbody>
</table>