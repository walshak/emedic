
<table width="100%">
    <tbody>
        <tr>
            <td align="center">
                <h3 style="color:#888">TRANSPLANT DONOR POOL </h3>
            </td>
        </tr>
    </tbody>
</table>

<br>
<table class="table table-striped dataTables-example" >
    <thead>
        <tr>
            <th>#</th>
            <th>Donor</th>
            <th>Donor Name</th>
            <th>Recipient</th>			
            <th>Phone</th>
            <th>Gender</th>
            <th>Blood Group</th>
            <th>GenoType</th>
            <th>Comment</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sn = 1;
        $year_month = date('Y-m');
			$stmt = $db->prepare("
			SELECT d.*, t.hospital_no as recipient from transplants_donors as d 
			inner join transplants as t  on t.id=d.transplant_id
			order by id desc ");
             $stmt->execute();
      
       while ($transplant_ = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $transplant_ = json_decode(json_encode($transplant_));
		    $selected=$transplant_->is_matched;
        ?>

            <tr <?php if($selected=='1'){?>style="background-color: aquamarine"<?php } ?>>

                <td><?= $sn++; ?></td>
				
				<?php if($_SESSION['rights'] == 'NS'){
						$path = "../nursing/";}else{$path='';}?>
				
                <td>
					<?php if($_SESSION['rights'] != 'NS' and $_SESSION['rights'] != 'DR'){ ?>
					<input type="button" name="on_cr" value="Donor Biodata" data-target="#modal" id="<?php echo $transplant_->hospital_no;  ?>" class="btn btn-primary btn-xs bio_data_link" style="font-size: 15px; color:white; " />
					<?php }else{?>
					<a href="<?= $path; ?>patient.php?hosp_no=<?= $transplant_->hospital_no; ?>"><?= $transplant_->hospital_no; ?></a>
					<?php }?>
				</td>
				   <td><?= $transplant_->name; ?> </td>
				
                <td>
					<?php if($_SESSION['rights'] != 'NS' and $_SESSION['rights'] != 'DR'){ ?>
					<input type="button" name="on_cr" value="Recipient Biodata" data-target="#modal" id="<?php echo $transplant_->recipient;  ?>" class="btn btn-primary btn-xs bio_data_link" style="font-size: 15px; color:white; " />
					<?php }else{?>
					<a href="<?= $path; ?>patient.php?hosp_no=<?= $transplant_->recipient; ?>"><?= $transplant_->recipient; ?></a>
             		<?php } ?>
				</td>
				
                <td><?= $transplant_->phone_number; ?> </td>
                <td><?= $transplant_->gender; ?> </td>
                <td><?= $transplant_->blood_group; ?></td> 
                <td><?= $transplant_->genotype; ?> </td>
                <td><?= $transplant_->comment . '(' . $transplant_->percentage .')'; ?> </td>
                <td <?php if($selected=='1'){?>style="background-color: red"<?php } ?>> </td>
             

            </tr>
        <?php
        }
        ?>
    </tbody>
</table>