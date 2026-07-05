<?php  
$hosp_no=$hospital_no;
$stmt=$db->prepare("SELECT * FROM fbs_rbs  WHERE hospital_no='$hosp_no' order by sn desc limit 100");
$stmt->execute();							 
if($stmt->rowCount()>0) {?>
<h2>FBS/RBS</h2>
	<table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="40">
				<thead>
					<tr>
						<th data-hide="phone,tablet">Date/Time</th>
						<th data-toggle="true">FBS</th>
						<th data-toggle="true">RBS</th>
						 <th data-toggle="true">Urinalysis</th>
						<th data-toggle="true">Responsible</th>
					</tr>
				</thead>
				<tbody>
			<?php  while($row=$stmt->fetch(PDO::FETCH_ASSOC)) {?>
					<tr>
					<td><?php echo date('d/m/Y', strtotime($row['date_entry'])) .' / '.$row['time_captured']; ?></td>
					<td><?php echo $row['fbs']; ?></td>
					<td><?php echo $row['rbs']; ?></td>
					<td><?php echo $row['urinalysis']; ?></td>
					<td><?php echo $row['prepared_by']; ?></td>
					</tr>
				<?php 	}?>
				</tbody>
			</table>
	<?php } ?>
<hr>
<?php  
$stmt=$db->prepare("SELECT * FROM feedings  WHERE hospital_no='$hosp_no' order by sn desc limit 100");
$stmt->execute();							 
if($stmt->rowCount()>0) {?>
<h2>FEEDING</h2>
<table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="40">
										<thead>
											<tr>
												<th data-hide="phone,tablet">Date</th>
                                                <th data-toggle="true">Nature of Feeding</th>
                                                <th data-toggle="true">Quantity of Feeding</th>
                                                <th data-toggle="true">Comments</th>
												<th data-toggle="true">Responsible</th>
											</tr>
										</thead>
										<tbody>
									<?php  while($row=$stmt->fetch(PDO::FETCH_ASSOC)) {?>
											<tr>
                       <td><?php echo date('d/m/Y', strtotime($row['date_entry'])) .' / '.$row['time_feeding']; ?></td>
                                            <td><?php echo $row['nature_feed']; ?></td>
                                            <td><?php echo $row['quantity_feed']; ?></td>
                                            <td><?php echo $row['feed_comment']; ?></td>
                                            <td><?php echo $row['prepared_by']; ?></td>	
												</td>
                                            </tr>
                                        <?php 	}?>
											
										</tbody>
									</table>
                            <?php } ?>		
<hr>
<?php  
$stmt=$db->prepare("SELECT * FROM intake_out WHERE fluid_cat='i' and  hospital_no='$hosp_no' order by sn desc limit 100");
$stmt->execute();							 
if($stmt->rowCount()>0) {?>
								<h2>Input Records</h2>
                          <table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="40">
										<thead>
											<tr>
												<th data-hide="phone,tablet">Date</th>
                                                <th data-toggle="true">Input(ml)</th>
												<th data-toggle="true">Input Type</th>
                                                <th data-toggle="true">Entered by</th>
											</tr>
										</thead>
										<tbody>
									<?php  while($row=$stmt->fetch(PDO::FETCH_ASSOC)) {?>
											<tr>
                                            <td><?php echo date('d/m/Y h:i:s a', strtotime($row['date_entry'])); ?></td>
                                            <td><?php echo $row['fluid_input_output']; ?></td>
                                            <td><?php echo $row['fluid_type']; ?></td>
                                            <td><?php echo $row['prepared_by']; ?></td>
                                            </tr>
                                        <?php 	}?>
										</tbody>
					  </table>
                            <?php } ?>
<hr>
<?php  
$stmt=$db->prepare("SELECT * FROM intake_out WHERE fluid_cat='o' and  hospital_no='$hosp_no' order by sn desc limit 100");
$stmt->execute();							 
if($stmt->rowCount()>0) {?>
								<h2>Output Records</h2>
                          <table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="40">
										<thead>
											<tr>
												<th data-hide="phone,tablet">Date</th>
                                                <th data-toggle="true">Output(ml)</th>
												<th data-toggle="true">Output Type</th>
                                                <th data-toggle="true">Entered by</th>
											</tr>
										</thead>
										<tbody>
									<?php  while($row=$stmt->fetch(PDO::FETCH_ASSOC)) {?>
											<tr>
                                            <td><?php echo date('d/m/Y h:i:s a', strtotime($row['date_entry'])); ?></td>
                                            <td><?php echo $row['fluid_input_output']; ?></td>
                                            <td><?php echo $row['fluid_type']; ?></td>
                                            <td><?php echo $row['prepared_by']; ?></td>
                                            </tr>
                                        <?php 	}?>
										</tbody>
					  </table>
                            <?php } ?>	
<hr>
<?php  
$stmt=$db->prepare("SELECT * FROM oxygen WHERE hospital_no='$hosp_no' order by sn desc limit 100");
$stmt->execute();							 
if($stmt->rowCount()>0) {?>
								<h2>SPO2 Records</h2>
<table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="40">
										<thead>
											<tr>
												<th data-hide="phone,tablet">Date/Time</th>
                                                <th data-toggle="true">SPO</th>
												<th data-toggle="true">Responsible</th>
											</tr>
										</thead>
										<tbody>
                                        <?php  while($row=$stmt->fetch(PDO::FETCH_ASSOC)) {?>
											<tr>
                                            <td><?php echo $row['time_captured']; ?></td>
                                            <td><?php echo $row['SPO']; ?></td>
                                            <td><?php echo $row['prepared_by']; ?></td>
                                            </tr>
                                        <?php 	}?>
										</tbody>
									</table>
                            <?php } ?>
		
