 <?php include("../Connections/Conn.php"); ?>

 <?php

       if (isset($_POST["add_specialist_id"])) {


              $add_specialist_id = $_POST["add_specialist_id"];
              $patt = explode("___", $add_specialist_id);
              $id = $patt[0];
              $target = $patt[1];
       ?>

        <form action="index.php?Specialist" method="POST" enctype="multipart/form-data">

               <?php if ($target == 'consultant') { ?>
                      <div class="form_sep">
                             <h3 class="req">Select Specialist</h3>
                             <select name="doctor_specialist_selected" class="form-control" style="font-size: 16px;">
                                    <option selected="selected" value="">.. Select ..</option>

                                    <?php
                                          $stmt = $db->query("SELECT id,fullname FROM admin_users where (rights='MD' or rights='AD' or rights='DR') and status='1' order by fullname");
                                          if ($stmt->rowCount() > 0) {
                                                 while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                                  <option value="<?php echo $row["id"]; ?>"><?php echo $row["fullname"]; ?></option>
                                    <?php }
                                          }
                                          ?>
                             </select>
                      </div>
                      <input type="hidden" name="specialist_id_" value="<?php echo $id; ?>">

               <?php } ?>

               <?php if ($target == 'services') { ?>
                      <div class="form_sep">
                             <h3 class="req">Select Consultation Services</h3>
                             <select name="doctor_consultation" class="form-control" style="font-size: 16px;">
                                    <option selected="selected" value="">Select ...</option>

                                    <?php
                                          $stmt = $db->query("SELECT sn,item_service FROM prices_table where price_table='Consultation' order by item_service");
                                          if ($stmt->rowCount() > 0) {
                                                 while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                                  <option value="<?php echo $row["sn"]; ?>"><?php echo $row["item_service"]; ?></option>
                                    <?php }
                                          }
                                          ?>
                             </select>
                      </div>
                      <input type="hidden" name="specialist_id_" value="<?php echo $id; ?>">

               <?php } ?>




               <div class="form_sep"></div>
               <div class="pull-left">
                      <button class="btn btn-primary" type="submit" name="assign_add_specialist">Add Specialist / Services</button>
               </div>

        </form>

 <?php }
