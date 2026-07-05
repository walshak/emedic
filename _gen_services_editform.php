<form action="gen_services.php?hosp_no=<?php echo base64_encode(base64_encode($hospital_no . '||' . $app_no)); ?>" method="post">
    <div id="">
        <div class="form-group">
            <h2>Select <u>Template</u> </h2>
            <select name="serviceTemplate" class="form-control" style="font-size:15px" id="serviceTemplate">
                <option value=""> -- Select Template from list -- </option>
                <?php
                $stmt = $db->prepare("SELECT id, template_name FROM services_templates ");
                $stmt->execute();
                if ($stmt->execute() > 0) {
                    while ($service = $stmt->fetch()) {
                ?>
                        <option value="<?= $service["id"] . '||' . $service["template_name"]; ?>" <?= $note["template_id"] == $service['id'] ? 'selected' : ''; ?>> <?= $service["template_name"]; ?></option>
                <?php
                    }
                }
                ?>
            </select>
        </div>


        <div class="form-group">
            <h2>Enter Documentation Notes below:</h2>
            <div id="edit__mode" style="color: red;"></div>
            <div id="__genserviceNote___" class=" trumbowygEditor " style="font-size:18px; height: 600px;"><?= $note["notes"]; ?> </div>
            <input type="hidden" name="genserviceNote" id="genserviceNote" cols="30" rows="10" required>

            <script>
                $(document).ready(function() {
                    setTimeout(() => {
                        $('#genserviceNote').val($('#__genserviceNote___').html())
                    }, 2000);

                })
            </script>
        </div>


        <div class="form-group">
            <div class="row">
                <div class="col-md-2">
                    <h3>Reported By:</h3>
                </div>
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
                <div class="col-md-5">
                    <input type="checkbox" name="isCompleted" id="isCompleted" <?= $note["isCompleted"] ? 'checked' : ''; ?>>
                    <h3>Mark to Complete</h3>
                </div>
            </div>
        </div>

        <div class="form_sep">
            <div class="pull-right">

                <button class="btn btn-primary" name="add-services-btn" <?php if ($note["isCompleted"] == 1) { ?> disabled<?php } ?>>Update</button>
                <button class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>



    </div>
</form>