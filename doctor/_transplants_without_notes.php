<hr>
<table width="100%">
    <tbody>
        <tr>
            <td align="center">
                <h3 style="color:#888">TRANSPLANT REQUEST WITHOUT NOTE  </h3>
            </td>
        </tr>
    </tbody>
</table>

<br>
<table class="table table-striped dataTables-example" border="2" >
    <thead>
        <tr>
            <th>SN</th>
            <th>HOSP. No.</th>
            <th>Name / Insurance</th>
            <th>TRANSPLANT TYPE</th>
            <th>Time</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sn = 1;
        $year_month = date('Y-m');
        $transplant_list = $Transplant->transplantWithOutNote();
        foreach ($transplant_list as $key => $transplant_) {
            $user = $AdminUser->find($transplant_->created_by);
        ?>

            <tr>

                <td><?= $sn++; ?></td>
                <td><?= $transplant_->hospital_no; ?></td>
                <td><?= $transplant_->patient_name; ?> </td>
                <td><?= $transplant_->transplant_type; ?> 
                 <br> <strong>Booked by: </strong> <?= $user->fullname; ?></BR></td>
                
                 <td><?= $transplant_->created_at; ?> 
                <td>
                <a href="transplant.php?hosp_no=<?= $transplant_->hospital_no;?>&trs=<?= $transplant_->id;?>" class="btn btn-sm btn-primary">Open </a>
                </td>
            </tr>
        <?php
        }
        ?>
    </tbody>
</table>