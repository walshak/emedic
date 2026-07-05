<?php
$current_date = date('Y-m-d');
if(isset($_GET['yr'])){
   $year_ = $_GET['yr'];
      $month_ = $_GET['mnth'];
}else{
      $year_ = date('Y');
      $month_ = date('m');
}
?>

     
            <div class="row animated fadeInRight">
                <div class="col-lg-3">
                    <div class="ibox ">
                        <div class="ibox-title">
                            <h5>Report Form</h5>
                            <a href="procedure.php?procedure" class="btn btn-xs btn-success pull-right"><i class="fa fa-home"></i> Home</a>
                        </div>
                        <div class="ibox-content">
                        
<form  action="#" method="post" id="show_report_by_type_and_date_form_">
                   
                   <div class="form_sep">
                    <label for="reg_input_no" class="">Select Procedure Type</label>
                    <select name="procedures" id="procedures"  class="form-control">
                    <option selected="selected" value="">Select ...</option>   
                        <?php	
					$dept_id=$_SESSION['dept_id'];
                    $stmt=$db->query("SELECT  DISTINCT procedures FROM procedures WHERE procedures IS NOT NULL AND procedures !='' ORDER BY procedures "); 
                    $distinct_request_types=$stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($distinct_request_types as $key => $row) {?>
                <option value="<?php echo $row["procedures"] ?>"><?php echo $row["procedures"]; ?></option>
                                    <?php }?>
                            </select>          
                            </div>

                              <div class="form_sep">
                    <label for="reg_input_no" class="">Filter By Result/Outcome</label>
                    <select name="post_op_results" id="post_op_results"  class="form-control">
                    <option selected="selected" value="">Select ...</option>   
                        <?php	
					$dept_id=$_SESSION['dept_id'];
                    $stmt=$db->query("SELECT  DISTINCT post_op_results FROM procedures"); 
                    $distinct_post_op_results=$stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($distinct_post_op_results as $key => $row) {
                                    $outcome = $row["post_op_results"];
                                    if($row["post_op_results"] == null){
                                        $outcome = 'No Result';
                                    }
                                    ?>
                <option value="<?php echo $row["post_op_results"] ?>"><?php echo $outcome; ?></option>
                                    <?php }?>
                            </select>          
                            </div>

                   
                   <div class="form_sep">
                        <strong>Filter By Consultant</strong>
                    <div class="form-group" id="data_5">
                    <select name="consultant" id="consultant"  class="form-control">
                    <option selected="selected" value="">Select ...</option>   
                        <?php	
					$dept_id=$_SESSION['dept_id'];
                    $stmt=$db->query("SELECT DISTINCT consultant_name FROM procedures where consultant_name!=''"); 
                    $distinct_post_op_results=$stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($distinct_post_op_results as $key => $row) {
                                   // if(!empty($row["consultant_name"])){
                                    ?>
                <option value="<?php echo $row["consultant_name"]; ?>"><?php echo $row["consultant_name"]; ?></option>
                                    <?php 
                                //    }
                                }?>
                            </select> 
                        
                    </div>
                </div>

                <div class="form_sep">
                        <strong>Filter By Date Performed</strong>
                    <div class="form-group" id="data_5">
                        <div class="col-md-6"><input type="date" class="input-sm form-control" name="start" id="start" value="<?php echo date("Y-m-d"); ?>"/></div>
                        <div class="col-md-6"> <input type="date" class=" form-control" name="end" id="end" value="<?php echo date("Y-m-d"); ?>" /></div>
                        
                    </div>
                </div>

  
  
  
                    <div class="form_sep">
                    <button class="btn btn-success btn-sm" type="submit" name="show_report_by_type_and_date" >Show Report </button>
                    </div>
                    </form>
                                      
                                                               
                        </div>
                    </div>

                    

                </div>
                <div class="col-lg-9">
                    <div class="ibox ">
                        <div class="ibox-title">
                            <h5>Reports</h5>
                        </div>
                        <div class="ibox-content">
                
                     				<?php

                            if(isset($_POST['show_report_by_type_and_date'])){
                                $procedures = $_POST['procedures'];
                                $post_op_results = $_POST['post_op_results'];
                                ////performed date
                                $start = $_POST['start'];
                                $end = $_POST['end'];
                                $consultant = $_POST['consultant'];
                                $query_sting = " ";
                                         if($current_date != $start || $current_date != $end ){  $query_sting .= " AND p.performed_date BETWEEN '$start' AND '$end' ";
                                          }

                                            if($consultant != ""){  $query_sting .= " AND p.consultant_name='$consultant'";
                                            }

                                            if($procedures != ""){ $query_sting .= " AND p.procedures='$procedures'"; }

                                            if($post_op_results != ""){
                                                    $query_sting .= " AND p.post_op_results='$post_op_results'";
                                            }
                                            // $sql = "SELECT p.*, e.gender FROM procedures p INNER JOIN enrollee e on p.hospital_no = e.hospital_no where 1 $query_sting LIMIT 20000";
                                                $sql = "SELECT p.* FROM procedures p  where 1 $query_sting LIMIT 20000";
                                                $stmt = $db->prepare($sql);
                                                $stmt->execute();
                                                $sn = 1;
                                                $tr = '';
                                                $total_male = 0;
                                                $total_female = 0;

                                                while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                                                    $pr = $row['sn'];
                                                    $patient_name = $row['name'];
                                                    $procedures = $row['procedures'];
                                                    $post_opt_notes_id = $row['post_opt_notes_id'];
                                                    $post_op_results = $row['post_op_results'];
                                                    $performed_date_time = date('d M, Y h:i:s A', strtotime(''.$row['performed_date '].' '.$row['performed_time']));

                                                 /////////////// PATIENT GENDER 
                                                    $stmt2 = $db->prepare("SELECT gender FROM enrollee  WHERE hospital_no = ? ");
                                                    $stmt2->execute([$row['hospital_no']]);
                                                    $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);

                                                    if($row2['gender'] == 'Male'){
                                                        $total_male++;
                                                    }else if($row2['gender'] == 'Female'){
                                                         $total_female++;
                                                    }
                                                    /////////////// END PATIENT GENDER 

                                                           /////////////// PAYMENT STATUS
                                                    $stmt = $db->prepare("SELECT sn,paystatus FROM patient_ap_services WHERE app_no = ? AND hospital_no = ? AND drug_sn = ?  ");
                                                    $stmt->execute(array($row["app_no"], $row["hospital_no"], $row["service_id"]));
                                                    if ($stmt->rowCount() > 0) {
                                                        $rowx = $stmt->fetch();
                                                        $payment_sn = $rowx['sn'];

                                                        if ($rowx['paystatus'] == 1) {
                                                            $pay_status = 'Paid';
                                                        }else{
                                                            $pay_status = 'Pending';
                                                        }
                                                     /////////////// END  PAYMENT STATUS

                                                        /////////////// RESOURCE PERSONS
                                                        $resourse_persons_text = '<ul>';
                                                        $stmt3 = $db->prepare("SELECT name,role,resource_code FROM procedure_resources WHERE prdure_sn = ?  ");
                                                            $stmt3->execute(array($row['sn']));
                                                            if ($stmt3->rowCount() > 0) {
                                                                $resourse_persons = $stmt3->fetchAll();
                                                                foreach ($resourse_persons as $key => $resourse_person) {
                                                                    $resource_code = $resourse_person["resource_code"];

                                                                    if($resource_code == 'rss'){
                                                                        $role = 'Surgeon';
                                                                    }else  if($resource_code == 'ras'){
                                                                        $role = 'Assistant Surgeon';
                                                                    }else  if($resource_code == 'ran'){
                                                                        $role = 'Anaesthetist';
                                                                    }else  if($resource_code == 'rsn'){
                                                                        $role = 'Nurse';
                                                                    }else{
                                                                        $role = '';
                                                                    }


                                                                    $resourse_persons_text .= ' <li>'.$resourse_person["name"].' - '.$role.'</li> ';
                                                                }
                                                            }
                                                            $resourse_persons_text .= '</ul>';
                                                        /////////////// END RESOURCE PERSONS


                                                            $tr .= '
                                                                <tr>
                                                                    <td>'.$sn++.'</td>
                                                                    <td>'.$patient_name.'</td>
                                                                    <td>'.$procedures.'</td>
                                                                    <td>'.(!empty($post_op_results) ? 'Done <br> ['.$post_op_results.']' : '<span class="text-danger">Not Done</span>').'</td>
                                                                    <td>'.$pay_status.'</td>
                                                                    <td>'.$resourse_persons_text.'</td>
                                                                    <td>
                                                                    <b>Booked By:</b> '.$row["prepared_by"].'
                                                                    <br>  <b>Consultant</b> :'. $row["consultant_name"].' 
                                                                   <br> <b>Date Time:</b>'. date("d M, Y H:i A", strtotime("".$row["date_entry"])).'
                                                                    </td>
                                                                    <td><a href="index.php?procedure&pr='.base64_encode(base64_encode($pr)).'==" class="btn btn-xs btn-success">view</a></td>
                                                                </tr>
                                                            ';

                                                    }
                                             }
                                ?>
                                    <h4>
                                    Total Procedures: <?php echo $stmt->rowCount(); ?><br>
                                    Total Male: <?php echo $total_male; ?><br>
                                    Total Female: <?php echo $total_female; ?><br>
                                    <?php
                                         if($current_date != $start || $current_date != $end ){
                                                 echo '<h4> Report from '.date('d M, Y ', strtotime(''.$start)).' to '.date('d M, Y', strtotime(''.$end)).'</h4>';
                                         }else{
                                             echo '<h4> Report is based on current date </h4>';
                                         }
                                    ?>
                                </h4>
                                    <table class="table table-stripped table-bordered dataTables-example" id="reportDatable">
                                        <thead>
                                            <tr>
                                                <td>#</td>
                                                <td>Patient</td>
                                                <td>Procedure </td>
                                                <td>Status </td>
                                                <td>Payment </td>
                                                <td>Resource Persons </td>
                                                <td>Datails</td>
                                                <td></td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            echo $tr;
                                            ?>
                                           
                                        </tbody>
                                    </table>
                                <?php
                                // }else{
                                //     echo '<hr><h3 class="text-center"> <a href="'.$editFormAction.'" class="btn btn-sm btn-success">Refresh</a></h3>';
                                   
                                // }

                            

                               


                            }else{
                                ?>
                                    <form action="<?php echo $editFormAction;?>" method="get" id="report_year_form" arial-url="">
                                        <div class="text-right">
                                        Year: 
                                        <select name="yr" id="report_year">
                                            <?php

                                            
                                            for ($i=$year_ - 3; $i <= date('Y'); $i++) { 
                                                echo '<option value="'.$i.'" '.($year_ == $i ? 'selected':'').'>'.$i.'</option>';
                                            }
                                            ?>>
                                        </select>
                                    </div>
                                    </form>
                                <?php
                            $dialysis_by_month = [];
                            $months_arr_label = [];
                                $months_array = months_array();
                               
                                foreach ($months_array as $key => $months_arr) {
                                    // print_r($months_arr->value);
                                     $months_arr->value;
                                      $sql = "SELECT COUNT(sn) total  FROM `procedures` WHERE `performed_date` LIKE '%$year_-$months_arr->value-%'  AND post_op_results IS NOT NULL LIMIT 20000";
                                      $stmt = $db->prepare($sql);
                                      $stmt->execute();
                                      $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                      
                                      array_push($dialysis_by_month, $row['total']);
                                      array_push($months_arr_label, $months_arr->month);
                                      
                                }
                                ?>
                                       <div class="row">
							
										       <div class="col-lg-9">
                                    <div class="ibox float-e-margins light-card">
                                        <div class="ibox-title">
                                            <h5>[<?php echo $year_;?>] Procedure Report By Month 
                                                <small></small>
                                            </h5>
                                        </div>
                                        <div class="ibox-content">
                                            <div>
                                                <canvas id="lineChart" height="140"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                
                            </div>
                            <div class="col-lg-3">
                                 <ul class="list-group clear-list m-t">
                                <?php
                                $sn = 1;
            $color = ["success", "info", "primary"];
                                $stmt=$db->query("SELECT  DISTINCT post_op_results FROM procedures WHERE   post_op_results IS NOT NULL ORDER BY procedures "); 
                                                $distinct_post_op_results=$stmt->fetchAll(PDO::FETCH_ASSOC);
                                                foreach ($distinct_post_op_results as $key => $distinct_post_op_result) {

                                                    if(strtolower($distinct_post_op_result['post_op_results']) == 'death'){
                                                        $color_ = 'danger';
                                                    }
                                                   else if(strtolower($distinct_post_op_result['post_op_results']) == 'successful'){
                                                        $color_ = 'primary';
                                                    }else{
                                                        $color_ = 'success';
                                                    }
                                                  

                                                     $sql = "SELECT COUNT(sn) total  FROM `procedures` WHERE `post_op_results` LIKE ? AND `performed_date` LIKE '%$year_-%'  AND post_op_results IS NOT NULL ";
                                      $stmt = $db->prepare($sql);
                                      $stmt->execute(array($distinct_post_op_result['post_op_results']));
                                      $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                                      ?>
                                                        <li class="list-group-item fist-item">
                                                            <span class="pull-right">
                                                                <?= $row["total"]; ?>
                                                            </span>
                                                            <span class="label label-<?= $color_; ?>"><?= $sn++; ?></span> <?= (empty($distinct_post_op_result['post_op_results']) ? 'No Result' : $distinct_post_op_result['post_op_results']); ?>
                                                        </li>

                                                    <?php
                                                }
                                            ?>
                                            </ul>
                            </div>					
                            </div>

                            <div>
                                <h4>[<?php echo $year_;?>] Procedure Report By Type  
                                                <small></small>
                                            </h4>
                                <table class="table table-bordered table-stripped">
                                    <thead>
                                        <tr>
                                            <td>#</td>
                                            <td>Type</td>
                                            <?php
                                                foreach ($months_array as $key => $months_arr_lab) {
                                                    echo '<td>'.$months_arr_lab->mnth.'</td>';
                                                }
                                            ?>
                                            <td>Total</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                          <?php
                                          $sn = 1;
                                            foreach ($distinct_request_types as $key => $distinct_request_type) {
                                                echo '<tr>
                                                    <td>'.$sn++.'</td>
                                                      <td>'.$distinct_request_type["procedures"].'</td>
                                                ';
                                                $total = 0;
                                                foreach ($months_array as $key => $months_arr_lab) {

                                                    $sql = "SELECT COUNT(sn) total  FROM `procedures` WHERE `performed_date` LIKE '%$year_-$months_arr_lab->value-%' AND procedures = ?  AND post_op_results IS NOT NULL";
                                                    $stmt = $db->prepare($sql);
                                                    $stmt->execute(array($distinct_request_type["procedures"]));
                                                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                                    echo '<td class="text-center">'.$row["total"].'</td>';
                                                    $total += $row["total"];
                                                }
                                                 echo '<td class="text-center">'.$total.'</td></tr>';
                                            }
                                        ?>
                                    </tbody>
                                </table>
                                <?php
                                        

                                    ?>
                            </div>
                                <?php
                            }
                            
                            ?>
           					
														
                        </div>
                    </div>
                </div>
            </div>
 <!-- ChartJS-->
    <script src="../js/plugins/chartJs/Chart.min.js"></script>
       <script>
          $(document).ready(function () {
            $('#reportDatable').DataTable();
    var lineData = {
        labels: <?php echo json_encode($months_arr_label);?>,
        datasets: [

            {
                label: "No. of procedures",
                backgroundColor: 'rgba(26,179,148,0.5)',
                borderColor: "rgba(26,179,148,0.7)",
                pointBackgroundColor: "rgba(26,179,148,1)",
                pointBorderColor: "#fff",
                data: <?php echo json_encode($dialysis_by_month);?>
            }
        ]
    };

    var lineOptions = {
        responsive: true
    };


    var ctx = document.getElementById("lineChart").getContext("2d");
    new Chart(ctx, {type: 'line', data: lineData, options:lineOptions});


          });
      </script>