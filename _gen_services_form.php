<?php $dept_id = $_SESSION['dept_id']; ?>
<form action="<?= $editFormAction; ?>" method="post">
    <div id="">
        <div class="form-group">
            <h4>Select Department Documentation Template </h4>
            <select name="serviceTemplate" class="form-control" style="font-size:15px" id="serviceTemplate" required>
                <option value=""> -- Select -- </option>
                <?php
                $stmt = $db->prepare("SELECT id, template_name FROM services_templates where department_id='$dept_id'");
                $stmt->execute();
                if ($stmt->execute() > 0) {
                    while ($service = $stmt->fetch()) {
                ?>
                        <option value="<?= $service["id"] . '||' . $service["template_name"]; ?>"> <?= $service["template_name"]; ?></option>
                <?php
                    }
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <h4>Enter Documentation Notes below:</h4>
            <div id="edit__mode" style="color: red;"></div>
            <div id="__genserviceNote___" class=" trumbowygEditor " style="font-size:18px; height: 600px;"></div>
            <input type="hidden" name="genserviceNote" id="genserviceNote" cols="30" rows="10" required>
        </div>


        <div class="form-group">
            <div class="row">
                <div class="col-md-2">Reported By:</div>
                <div class="col-md-5">


                    <div class="form_sep">
                        <label class="form_sep" class="req">Select Nurse Name</label>
                        <select name="consultant_name" id="consultant_name" class="form-control" required style="font-size:15px;">

                            <?php
                            $stmt = $db->query("SELECT h.FirstName,u.fullname, h.LastName,h.Designation,u.username FROM hremp as h inner join admin_users as u on h.EmployeeCode=u.EmployeeCode WHERE (u.rights = 'NS') AND h.status = '0'  order by h.FirstName");
                            ?>
                            <option selected="selected" value="">Select ...</option>
                            <?php while ($rxw = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                <option value="<?php echo $rxw["fullname"]; ?>"><?php echo $rxw["fullname"]; ?></option>
                            <?php  } ?>
                        </select>
                    </div>

                </div>
                <div class="col-md-5">
                    <input type="checkbox" name="isCompleted" id="isCompleted"> <strong>Mark to Complete</strong>
                </div>
            </div>
        </div>

        <div class="form_sep">
            <div class="pull-right">
                <button class="btn btn-primary" name="add-services-btn">Save</button>
                <button class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
    </div>
</form>