<div class="row" id="emr_print_area">
<div class="col-sm-12" id="content">
        <!--<div class="panel-heading">
                <h4 class="panel-title">Simple Validation</h4>
        </div>-->

<div style="font:bold 14px 'Arial'; width:200px; position: absolute;right: 0px; top: 0px; color:#C00">EMR<br>APP/#: <?= $appointment_number;?></div>
<div align="center">

        <div style="font:bold 14px 'Arial';"></div>
        <br><br>
</div>
    <div align="left" style="font:bold 14px 'Arial';">
      </div>
<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
<tbody><tr>
<td width="50%" align="left"><img <img src="../img/logo.png" width="196" height="111"></td>
<td width="50%" align="right"></td>
</tr>

</tbody></table>   
<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
        <tbody><tr bgcolor="#FFCC66" style="font-weight:100">
            <td style="font:bold 14px 'Arial';" width="30%">Patient No: </td>
            <td style="font:bold 14px 'Arial';" width="30%">Patient Name:</td>
            <td style="font:bold 14px 'Arial';" width="30%">Appointment Number:</td>
        </tr>
        <tr>
        <td><?= $hospital_no; ?></td> 
        <td><?= $patient_name;?> </td> 
        <td><?= $appointment_number;?> </td> 
		</tr>
        <tr bgcolor="#FFCC66">
            <td style="font:bold 14px 'Arial';">Age/Sex: </td>
            <td style="font:bold 14px 'Arial';">Covarage:</td>
            <td style="font:bold 14px 'Arial';">Phone Number: </td>
        </tr>
        <tr>
        <td><?= $age_full.' / '.$sex;?> </td> 
        <td><?= $insurance_type;?> </td> 
        <td></td> 
		</tr>
        
        <tr bgcolor="#FFCC66">
            <td style="font:bold 14px 'Arial';">Request Number: </td>
            <td style="font:bold 14px 'Arial';">Request By:</td>
            <td style="font:bold 14px 'Arial';">Date: </td>
        </tr>
        <tr>
            <td></td> 
            <td><?= $_SESSION['fullname'];?> </td> 
			<td><?=  date('D M, Y h:i a') ;?></td>
		</tr>
	</tbody></table>
  
                                                                                    
    <br>
    <div align="left" style="font:bold 14px 'Arial';">Ocular History</div><br>

	<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
		<thead>
			<tr bgcolor="#CCCCCC">
				<th width="20%">Date </th>
				<th width="40%">Name </th>
				<th width="20%">Result </th>
				<th width="20%">Captured By </th>
			</tr>
		</thead>
		<tbody>
			<?php

                $sn = 1;
                $index = 0;
                $opth_list =  $Opthalmology->get(['hospital_no' => $hospital_no], true);
                foreach ($opth_list as $key => $hx) {

                    echo '
                                <tr class="record">
                                <td style="border-bottom: 1px solid #ddd;">' . date("d M, Y h:i a", strtotime($hx->date_ented)). '</td>
                                <td style="border-bottom: 1px solid #ddd;">' . $hx->ocular_list . '</td>
                                <td style="border-bottom: 1px solid #ddd;" >' . $hx->result . '  Left: '.$hx->left.'  Right: '.$hx->right.'</td>
                                <td style="border-bottom: 1px solid #ddd;" > ' . $hx->entered_by . '</td>
                            </tr>
                                ';
                }
                ?>
			              
				
						</tbody>
	</table>
   
<br>

          
                            
<br>     
                            
<br>
    <div align="left" style="font:bold 14px 'Arial';">Visit/Notes</div><br>

    <table cellpadding="5" cellspacing="5" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;border: 1px solid black">
		<thead>
			<tr bgcolor="#CCCCCC">
				
                <th width="20%">Date </th>
                <th width="60%">Notes </th>
                 <th width="20%">Noted By</th>
			  </tr>
		</thead>
		<tbody>
			 
		</tbody>
	</table>
   
    <br>
 
   

</div>
</div>