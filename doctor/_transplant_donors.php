                     <div class="row">
                                                    <div class="col-md-12">
                                <div id="donor_table_wrap">
                                    <h3> <u>Donors </u></h3>
                                    <?php

                                  //  $donor_list = $Transplant->getDonor(['transplant_id' => $transplant_id], true);

                                    foreach ($donor_list as $key => $donor) {
                                    ?>
                                        <div class="row" style="border: 2px solid #888">

                                            <div class="col-md-6">
                                                <span style="font-size:18px">Details: </span>
                                                <span style="font-size:20px"><?php echo '  ' . $donor->hospital_no . '/' .$donor->name; ?>
                                                </span>
                                                <br>
                                                <table class="table border" style="font-size: 14px;">
                                                    <tr>
                                                        <td><strong>Gender:</strong>&nbsp; <?= $donor->gender; ?></td>
                                                        <td><strong>Blood/Group::&nbsp; <?= $donor->blood_group; ?></strong></td>
                                                        <td><strong>Phone:&nbsp; <?= $donor->phone_number; ?></strong></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="3"><strong>Address:</strong> <?= $donor->address; ?><br>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="3"><strong>Notes:</strong> <?= $donor->result_notes; ?><br>
                                                        </td>
                                                    </tr>
                                                </table>

                                            </div>
                                            <div class="col-md-6">
                                                <h3 class="text-right"> Crossmatch </h6>
                                                    <br>
                                                    <h4 class="text-right">
                                                        <span class="text-<?= ($donor->comment >= "Positive" ? 'info' : 'danger'); ?>"><?= $donor->comment; ?></span>

                                                        <?php
                                                        if (!empty($donor->percentage)) {
                                                        ?>
                                                            / <span><?= $donor->percentage; ?>%</span>
                                                        <?php
                                                        }
                                                        ?>

                                                    </h4>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h2 class="text-center">
                                                    <a href="#<?= $donor->id; ?>" class="btn btn-white btn-xs " data-toggle="modal" data-target="#addHLAResultModal<?= $donor->id; ?>"  <?php if($performed_date!=''){?> disabled <?php } ?>>> <b>Add HLA Result</b></a> |
                                                    <?php
                                                    if (!empty($donor->hla_result_link)) {
                                                    ?>
                                                        <a href="<?= $donor->hla_result_link; ?>" target="_BLANK" class="btn btn-white btn-xs "> <b>View HLA Result</b></a> |
                                                    <?php
                                                    }
                                                    ?>
                                                    <a href="#<?= $donor->id; ?>" class=" btn btn-white btn-xs " data-toggle="modal" data-target="#addCrossMatchResultModal<?= $donor->id; ?>"  <?php if($performed_date!=''){?> disabled <?php } ?>>> <b>Add Crossmatch Result</b></a> |
                                                    <?php
                                                        if(empty($donor->hla_result_link) && empty($donor->percentage)){
                                                            ?>
                                                            <form action="<?= $editFormAction; ?>" method="post" onsubmit="return confirm('Please confirm your action to remove donor?') " style="display: inline;">
                                                        <input type="hidden" name="donor_id" value="<?= $donor->id; ?>">
                                                        <button type="submit" class=" btn btn-white btn-xs text-danger" name="removeDonorBtn"> <b>Remove Donor</b></button>
                                                    </form>
                                                            <?php
                                                        }else{?>
	<?php  if($selected_yes==''){?>												
    <a href="#<?= $donor->id; ?>" class=" btn btn-white btn-xs " data-toggle="modal" data-target="#SelectedDonor<?= $donor->id; ?>"> <b style="color: red;">Set as Selected Donor</b></a>
	<?php } ?>												
													
                                                    <?php } ?>
													
   <a href="#<?= $donor->id; ?>" class=" btn btn-white btn-xs " data-toggle="modal" data-target="#checklist<?= $donor->id; ?>"> <b style="color: darkblue;">Donor Check List</b></a>
													
                                                </h2>
                                            </div>
                                        </div>

<div class="modal inmodal fade" id="addHLAResultModal<?= $donor->id; ?>" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog modal-lg">
<div class="modal-content">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h4 class="modal-title" id="">HLA Result </h4>
	</div>
	<div class="modal-body" >
		<form action="<?= $editFormAction; ?>" method="POST" name="subject" enctype="multipart/form-data">
			<div>
				<label for="reg_input_no" class="req">HLA Result File [jpg, png, pdf] </label>
				<input type="file" name="hla_result_file" id="hla_result_file" class="form-control">
			</div>
			<br>
			 <div>
				<label for="result_notes" class=""> Notes: </label>
				<textarea name="result_notes" id="result_notes" class="form-control" cols="30" rows="10"></textarea>
			</div>
			<br>

			<div class="text-right">
				<input type="hidden" name="donor_id" value="<?= $donor->id; ?>">
				<input type="hidden" name="transplant_id" value="<?= $transplant_id; ?>">
				<button type="submit" class="btn btn-primary" name="addDonorHLAResultBtn" >Save</button>
				<button  class="btn btn-danger"  data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
</div>
</div>

<div class="modal inmodal fade" id="addCrossMatchResultModal<?= $donor->id; ?>" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog modal-lg">
<div class="modal-content">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h4 class="modal-title" id="">Crossmatch Result </h4>
	</div>
	<div class="modal-body" >
		<form action="<?= $editFormAction; ?>" method="POST" name="subject" enctype="multipart/form-data">
			<div>
				<label for="reg_input_no" class="req">Result (%)</label>
				<input type="text" name="percentage" id="percentage" value="<?= $donor->percentage; ?>" class="form-control">
			</div>
			<br>
			<div>
				<label for="reg_input_no" class="req">Comment</label>
				<select name="comment" id="comment" class="form-control">
					<option value=""></option>
					<option value="Negative" <?= $donor->comment == "Negative" ? "selected" : ""; ?>>Negative</option>
					<option value="Positive" <?= $donor->comment == "Positive" ? "selected" : ""; ?>>Positive</option>
				</select>
			</div>
			<br>
			<div class="text-right">
				<input type="hidden" name="donor_id" value="<?= $donor->id; ?>">
				<input type="hidden" name="transplant_id" value="<?= $transplant_id; ?>">
				<button type="submit" class="btn btn-primary" name="addCrossMatchResultBtn" >save</button>
				<button  class="btn btn-danger"  data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
</div>
</div>
<br>

									
<div class="modal inmodal fade" id="SelectedDonor<?= $donor->id; ?>" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Surgery / Matched Donor</h4>
			</div>
			<div class="modal-body" >
				<form action="<?= $editFormAction; ?>" method="POST" name="subject" enctype="multipart/form-data">

					<strong>Donor Details : <?php echo '  ' . $donor->hospital_no . ' / ' .$donor->name; ?></strong>
					<hr>
					<br>
					
					<h3>Are you sure you want to set this Donor as a selected Donor?</h3>
					
					<div class="pull-left">
						<input type="hidden" name="donor_id" value="<?= $donor->id; ?>">
						<input type="hidden" name="transplant_id" value="<?= $transplant_id; ?>">
						<button type="submit" class="btn btn-primary" name="selected_donor_btn" >save</button>
					</div>					
					
					<div class="pull-right">
						<button  class="btn btn-danger"  data-dismiss="modal">Close</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>				

									
									
<div class="modal inmodal fade" id="checklist<?= $donor->id; ?>" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Checklist</h4>
			</div>
			<div class="modal-body" >
				<form action="<?= $editFormAction; ?>" method="POST" name="subject" enctype="multipart/form-data">
			<div class="form_sep">
			 <label for="reg_input_no" class="req">Select Check list</label>
				<select name="comment" id="comment" class="form-control">
					<option value=""></option>
					<option value=""></option>
					<option value=""></option>
				</select>
				</div>
					
					
					
					<div class="form_sep">
					<div id="edit___mode" style="color: red;"></div>
					<div name="mgt_notes_wards" id="mgt_notes_wards" class="trumbowygEditor" cols="3" rows="1" style=" height:30px; font-size: 17px; border-color: black;">
		
					</div> 
					</div> 	
					
					<hr>
					
					<div class="pull-left">
						<input type="hidden" name="donor_id" value="<?= $donor->id; ?>">
						<input type="hidden" name="transplant_id" value="<?= $transplant_id; ?>">
						<button type="submit" class="btn btn-primary" name="selected_donor_btn" >save</button>
					</div>					
					
					<div class="pull-right">
						<button  class="btn btn-danger"  data-dismiss="modal">Close</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>									
									
									
                                    <?php
                                    }


                                    ?>
                                </div>
                            </div>
                                                    </div>









