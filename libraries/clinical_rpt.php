
 <?php
if(isset($_GET['clinical_rpt'])){
	  	$hos_no=$_GET['clinical_rpt'];
	?>

<div class="row">
<div class="col-lg-12">
<div class="ibox ">
<div class="ibox-title"><h5><span class="fa fa-flask"></span>&nbsp; Past Clinical Task Records</h5>
   <div class="ibox-tools">
                                
<a href="index.php?hosp_no=<?php echo $hos_no; ?>" class="btn btn-danger btn-xs"><span class="fa fa-times"></span>&nbsp;Close</a>
</div>
</div>                          
<div class="ibox-content">
<?php
		$stmt=$db->query("SELECT r.*, c.* FROM clinical_task_routine r 
		inner join clinical_task c on r.task_sn=c.sn WHERE c.hospital_no='$hos_no' order by r.sn");
                if($stmt->rowCount()>0){?>
                                       
                               <table class="table table-striped table-bordered table-hover dataTables-example" >                 
                                                <thead>
                                                <tr>
                                                    <th width="10%">Task </th>
                                                    <th width="15%">Due Date</th>
													<th width="10%">Captured Date</th>
                                                    <th width="15%">Data</th>
                                                    <th width="15%">Patient</th>
                                                </tr>
                                                </thead>
                                                <tbody>
            
                                                    <?php 
                                                        $n=1;
                                                        while($roww=$stmt->fetch(PDO::FETCH_ASSOC)) {
												
                                                        ?>
                                                       <tr>  
                                     
                                                        <td><?php echo $roww['task_desc']; ?></td>
										 <td><?php echo date('d,M y h:i a', strtotime($roww['nDate']));?></td>
										<td><?php echo date('d,M y h:i a', strtotime($roww['date_captured']));?></td>
                                                        <td><?php echo $roww['task_value']; ?></td>
                                                        <td><?php echo $roww['staff_name']; ?></td>
                                                        </tr>
                                                    <?php 
                                                       $n++;	
                                                    }?>
                                                        
                                                </tbody>
                                                </table>
                                                               
<?php }else{ ?>		
	 <div class="alert alert-warning">No Record found</div>
<?php } ?>     
     
</div>
</div>
</div>
</div>
 
 <?php } ?>

 
    

