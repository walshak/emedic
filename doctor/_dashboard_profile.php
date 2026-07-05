<div class="ibox ">

                        <div class="ibox-content">
                            <div class="tab-content">
                                <div id="contact-1" class="tab-pane active">
                                    <div class="row m-b-lg">
                                        <div class="col-lg-12 text-center">
                                         

                                            <div class="m-b-sm">
                                                <img alt="image" class="img-circle" src="../img/no_photo.jpg"
                                                     style="width: 82px">
                                                        <h2><?php echo $_SESSION['fullname'];; ?></h2>
                                                       <button type="button" class="btn btn-primary btn-sm btn-block"><i
                                                    class="fa fa-user"></i> A  <?php  echo $_SESSION['Designation']; ?>
                                            </button>
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="client-detail">
                                    <div class="full-height-scroll">

                                        <strong>Today activity</strong>

                                        <ul class="list-group clear-list">
                                            <li class="list-group-item">
                                                <span class="pull-right" id="total_drugs_requested">00 </span>
                                                Medications Prescribed
                                            </li>
                                            <li class="list-group-item">
                                                <span class="pull-right" id="total_labs_requested">00</span>
                                                Laboratory Requested
                                            </li>
                                            <li class="list-group-item">
                                                <span class="pull-right" id="total_scans_requested"> 00</span>
                                                Radiology/Scans Requested
                                            </li>
                                           
                                        
                                        </ul>
                                        
                                        <div class="">

                                            <strong>Log Counter:</strong>

                                            <?php
                                            // $stmt_logC = $db->query("SELECT Log_in FROM admin_users_logs WHERE username='$profile'");
                                            // echo $stmt_logCOUNT = $stmt_logC->rowCount();
                                            ?>

                                            <br>
                                            <strong>Messages:</strong>
                                            <?php
                                            $dr_username = $_SESSION['username'];
                                            // $stmt = $db->query("SELECT sn FROM mails WHERE on_contact_username='$dr_username'");
                                            // echo $stmt->rowCount();
                                            ?>
                                        </div>
                                    
                                    </div>
                                    </div>
                                </div>
								
								
                            </div>
                        </div>
                    </div>