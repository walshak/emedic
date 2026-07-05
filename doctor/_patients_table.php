<?php

$all_patients= $Appointment->all();
   
 

?>
<table class="table table-striped table-bordered table-hover dataTables-example">
    <thead>
        <tr>
            <th width="2%">No</th>
            <th width="3%">#</th>
            <th width="5%">Surname</th>
            <th width="5%">First Name</th>
            <th width="5%">Occupation</th>
            <th width="3%">Gender</th>
            <th width="7%">Date</th>
        </tr>
    </thead>
    <tbody>

        <?php 
        $n = 1;
        foreach ($all_patients as $key => $patient) {
        
        
        ?>
            <tr>
                <td><?php echo $n; ?></td>
                <td><a href="index.php?ptm=<?php echo $url . '/' . $patient->hospital_no; ?>"><?php echo $patient->hospital_no; ?> </a> </td>
                <td><?php echo $patient->surname; ?></td>
                <td><?php echo $patient->fname; ?></td>
                <td><?php echo $patient->occupation; ?></td>
                <td><?php echo $patient->gender; ?></td>
                <td><?php echo date("d,M y", strtotime($patientdate_capture)); ?></td>
            </tr>
        <?php $n++;
        } ?>
    </tbody>
</table>