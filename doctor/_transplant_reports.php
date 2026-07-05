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
                            <a href="index.php?transplant" class="btn btn-xs btn-success pull-right"><i class="fa fa-home"></i> Home</a>
                        </div>
                        <div class="ibox-content">
                        
<form  action="#" method="post" id="show_report_by_type_and_date_form_">
                   
                   <div class="form_sep">
                    <label for="reg_input_no" class=""> Transplant Type</label>
                    <select name="transplant_type" id="transplant_type"  class="form-control">
                    <option selected="selected" value="">Select ...</option>   
                        <?php	
					$dept_id=$_SESSION['dept_id'];
                    $stmt=$db->query("SELECT  DISTINCT transplant_type FROM transplants WHERE transplant_type IS NOT NULL AND transplant_type !='' ORDER BY transplant_type "); 
                    $distinct_request_types=$stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($distinct_request_types as $key => $row) {?>
                <option value="<?php echo $row["transplant_type"] ?>"><?php echo $row["transplant_type"]; ?></option>
                                    <?php }?>
                            </select>          
                            </div>

                              <div class="form_sep">
                    <label for="reg_input_no" class="">Filter By Result/Outcome</label>
                    <select name="transplant_outcome" id="transplant_outcome"  class="form-control">
                    <option selected="selected" value="">Select ...</option>   
                        <?php	
					$dept_id=$_SESSION['dept_id'];
                    $stmt=$db->query("SELECT  DISTINCT transplant_outcome FROM transplants"); 
                    $distinct_transplant_outcome=$stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($distinct_transplant_outcome as $key => $row) {
                                    $outcome = $row["transplant_outcome"];
                                    if($row["transplant_outcome"] == null){
                                        $outcome = 'No Result';
                                    }
                                    ?>
                <option value="<?php echo $row["transplant_outcome"] ?>"><?php echo $outcome; ?></option>
                                    <?php }?>
                            </select>          
                            </div>

                   
                   <div class="form_sep">
                        <strong>Filter By Consultant</strong>
                    <div class="form-group" id="data_5">
                    <select name="main_surgeon" id="main_surgeon"  class="form-control">
                    <option selected="selected" value="">Select ...</option>   
                        <?php	
					$dept_id=$_SESSION['dept_id'];
                    $stmt=$db->query("SELECT DISTINCT main_surgeon FROM transplants where main_surgeon!=''AND main_surgeon IS NOT NULL "); 
                    $distinct_transplant_outcome=$stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($distinct_transplant_outcome as $key => $row) {
                                   // if(!empty($row["consultant_name"])){
                                    ?>
                <option value="<?php echo $row["main_surgeon"]; ?>"><?php echo $row["main_surgeon"]; ?></option>
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
                                $transplant_type = $_POST['transplant_type'];
                                $transplant_outcome = $_POST['transplant_outcome'];
                                ////performed date
                                $start = $_POST['start'];
                                $end = $_POST['end'];
                                $consultant = $_POST['main_surgeon'];
                                $query_sting = " ";
                                         if($current_date != $start || $current_date != $end ){  $query_sting .= " AND performed_date BETWEEN '$start' AND '$end' ";
                                          }

                                            if($consultant != ""){  $query_sting .= " AND main_surgeon='$consultant'";
                                            }

                                            if($transplant_type != ""){ $query_sting .= " AND transplant_type='$transplant_type'"; }

                                            if($transplant_outcome != ""){
                                                    $query_sting .= " AND transplant_outcome='$transplant_outcome'";
                                            }
                                            // $sql = "SELECT p.*, e.gender FROM procedures p INNER JOIN enrollee e on p.hospital_no = e.hospital_no where 1 $query_sting LIMIT 20000";
                                                $sql = "SELECT * FROM transplants p  where 1 $query_sting LIMIT 20000";
                                                $stmt = $db->prepare($sql);
                                                $stmt->execute();
                                                $sn = 1;
                                                $tr = '';
                                                $total_male = 0;
                                                $total_female = 0;

                                                $rows =  $stmt->fetchAll(PDO::FETCH_ASSOC);
                                               
                                                foreach ($rows as $key => $row) {
                                                     $pr = $row['id'];
                                                    $patient_name = $row['patient_name'];
                                                    $transplant_type = $row['transplant_type'];
                                                    // $post_opt_notes_id = $row['post_opt_notes_id'];
                                                    $transplant_outcome = $row['transplant_outcome'];
                                                    $performed_date_time = '';
                                                    if(!empty($row['performed_time'])){
                                                        $performed_date_time = date('d M, Y h:i:s A', strtotime(''.$row['performed_time']));
                                                    }
                                                   

                                                 /////////////// PATIENT GENDER 
                                                    $stmt2 = $db->prepare("SELECT gender FROM enrollee  WHERE hospital_no = ? ");
                                                    $stmt2->execute([$row['hospital_no']]);
                                                    $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);

                                                    if($row2['gender'] == 'Male' || $row2['gender'] == 'M'){
                                                        $total_male++;
                                                    }else if($row2['gender'] == 'Female' || $row2['gender'] == 'F'){
                                                         $total_female++;
                                                    }

                                                    /////////////// END PATIENT GENDER 

                                                           /////////////// PAYMENT STATUS
                                                           $pay_status = '';
                                                    $stmt = $db->prepare("SELECT sn,paystatus FROM patient_ap_services WHERE app_no = ? AND hospital_no = ? AND item_services = ?  ");
                                                    $stmt->execute(array($row["app_no"], $row["hospital_no"], $row["transplant_type"]));
                                                    if ($stmt->rowCount() > 0) {
                                                        $rowx = $stmt->fetch();
                                                        $payment_sn = $rowx['sn'];

                                                        if ($rowx['paystatus'] == 1) {
                                                            $pay_status = 'Paid';
                                                        }else{
                                                            $pay_status = 'Pending';
                                                        }
                                                    }
                                                     /////////////// END  PAYMENT STATUS

                                                        /////////////// RESOURCE PERSONS
                                                        $resourse_persons_text = '';
                                                       
                                              

                                                            $tr .= '
                                                                <tr>
                                                                    <td>'.$sn++.'</td>
                                                                    <td>'.$patient_name.'</td>
                                                                    <td>'.$transplant_type.'</td>
                                                                    <td>'.(!empty($transplant_outcome) ? 'Done <br> ['.$transplant_outcome.']' : '<span class="text-danger">Not Done</span>').'</td>
                                                                    <td>'.$pay_status.'</td>
                                                                    <td>
                                                                    <b>Booked By:</b> '.$row["created_by"].'
                                                                    <br>  <b>Consultant</b> :'. $row["main_surgeon"].' 
                                                                   <br> <b>Date Time:</b>'. date("d M, Y H:i A", strtotime("".$row["created_at"])).'
                                                                    </td>
                                                                    <td><a href="index.php?transplant&trs='.base64_encode(base64_encode($pr)).'==" class="btn btn-xs btn-success">view</a></td>
                                                                </tr>
                                                            ';

                                                    
                                             }
                                          
                                ?>
                                    <h4>
                                    Total Transplant: <?php echo count($rows); ?><br>
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
                                    <table class="table table-stripped table-bordered dataTables-example" id="">
                                        <thead>
                                            <tr>
                                                <td>#</td>
                                                <td>Patient</td>
                                                <td>Transplant </td>
                                                <td>Status </td>
                                                <td>Payment </td>
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
                                      $sql = "SELECT COUNT(id) total  FROM `transplants` WHERE `performed_date` LIKE '%$year_-$months_arr->value-%'  AND transplant_outcome IS NOT NULL LIMIT 20000";
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
                                            <h5>[<?php echo $year_;?>] Transplant Report By Month 
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
                                $stmt=$db->query("SELECT  DISTINCT transplant_outcome FROM transplants WHERE   transplant_outcome IS NOT NULL ORDER BY transplant_type "); 
                                                $distinct_transplant_outcome=$stmt->fetchAll(PDO::FETCH_ASSOC);
                                                foreach ($distinct_transplant_outcome as $key => $distinct_post_op_result) {

                                                    if(strtolower($distinct_post_op_result['transplant_outcome']) == 'death'){
                                                        $color_ = 'danger';
                                                    }
                                                   else if(strtolower($distinct_post_op_result['transplant_outcome']) == 'successful'){
                                                        $color_ = 'primary';
                                                    }else{
                                                        $color_ = 'success';
                                                    }
                                                  

                                                     $sql = "SELECT COUNT(id) total  FROM transplants WHERE `transplant_outcome` LIKE ? AND `performed_date` LIKE '%$year_-%'  AND transplant_outcome IS NOT NULL ";
                                      $stmt = $db->prepare($sql);
                                      $stmt->execute(array($distinct_post_op_result['transplant_outcome']));
                                      $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                                      ?>
                                                        <li class="list-group-item fist-item">
                                                            <span class="pull-right">
                                                                <?= $row["total"]; ?>
                                                            </span>
                                                            <span class="label label-<?= $color_; ?>"><?= $sn++; ?></span> <?= (empty($distinct_post_op_result['transplant_outcome']) ? 'No Result' : $distinct_post_op_result['transplant_outcome']); ?>
                                                        </li>

                                                    <?php
                                                }
                                            ?>
                                            </ul>
                            </div>					
                            </div>

                            <div>
                                <h4>[<?php echo $year_;?>] Transplant Report By Type  
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
                                                      <td>'.$distinct_request_type["transplant_type"].'</td>
                                                ';
                                                $total = 0;
                                                foreach ($months_array as $key => $months_arr_lab) {

                                                    $sql = "SELECT COUNT(id) total  FROM `transplants` WHERE `performed_date` LIKE '%$year_-$months_arr_lab->value-%' AND transplant_type = ?  AND transplant_outcome IS NOT NULL";
                                                    $stmt = $db->prepare($sql);
                                                    $stmt->execute(array($distinct_request_type["transplant_type"]));
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
 <script src="../js/jquery-3.1.1.min.js"></script>
    <script src="../js/plugins/chartJs/Chart.min.js"></script>
       <script>
          $(document).ready(function () {
            $('#reportDatable').DataTable();
    var lineData = {
        labels: <?php echo json_encode($months_arr_label);?>,
        datasets: [

            {
                label: "No. of transplants",
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