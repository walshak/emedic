<?php
///http://localhost/webmedic/billing/pacct.php?emr=000007&rof&delete=TWpNeE5UUT0=
if (isset($_POST['resource_role'])) {
    session_start();
    try {
        include_once('../../Connections/Conn.php');
        $resource_code = $_POST['resource_role'];
        $id = $_POST['resource_sn'];
        $procedure_sn = $_POST['procedure_sn'];
        $hospital_no = $_POST['hospital_no'];

        $resources = [
            ['resource_code' => 'rss', 'role' => 'Surgeon'],
            ['resource_code' => 'ras', 'role' => 'Assistant Surgeon'],
            ['resource_code' => 'ran', 'role' => 'Anaesthetist'],
            ['resource_code' => 'rsn', 'role' => 'Nurse']
        ];

        $role = null;
        $name = null;
        foreach ($resources as $resource) {
            if ($resource["resource_code"] == $resource_code) {
                $role = $resource["role"];
                break;
            }
        }

        $stmt = $db->prepare("SELECT * FROM admin_users WHERE id = ? AND status = '1'");
        $stmt->execute(array($id));
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $name = $row["fullname"];
        }

        $check_stmt = $db->prepare("SELECT * FROM procedure_resources WHERE prdure_sn = ? AND resource_sn = ? AND resource_code = ?");
        $check_stmt->execute(array($procedure_sn, $id, $resource_code));
        if ($check_stmt->rowCount() == 0) {
            $stmt = $db->prepare("INSERT INTO procedure_resources (prdure_sn, hosp_no, resource_sn, name, resource_code, dateadd, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $save = $stmt->execute(array($procedure_sn, $hospital_no, $id, $name, $resource_code, date('Y-m-d h:i:s'), $role));

            if ($save) {
                $sn_stmt = $db->prepare("SELECT COUNT(*) AS count FROM procedure_resources WHERE prdure_sn = ?");
                $sn_stmt->execute(array($procedure_sn));
                $sn = $sn_stmt->fetch(PDO::FETCH_ASSOC)['count'];

                echo json_encode([
                    'status' => 'success',
                    'sn' => $sn,
                    'name' => $name,
                    'role' => $role,
                    'hospital_no' => $hospital_no,
                    'prdure_sn' => base64_encode($procedure_sn),
                    'rsn' => $db->lastInsertId()
                ]);
            } else {
                echo json_encode(['status' => 'error']);
            }
        } else {
            echo json_encode(['status' => 'exists']);
        }
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
    exit;
}

?>

<div class="modal inmodal fade" id="resource_persons_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg" style="min-height: 500px;">
        <div class="modal-content">
            <form action="#" method="post" id="pre_opt_note_form">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title"> Resource Persons </h4>
                </div>
                <div class="" style="padding: 20px">
                    <div class="form_sep">
                        <label for="reg_input_no" class="req">Role </label>
                        <select name="resource_role" class="input-sm chosen-select" style="width:350px;" required>
                            <option selected="selected" value="">Search</option>
                            <option value="rss">Surgeon</option>
                            <option value="ras">Assistant Surgeon</option>
                            <option value="ran">Anaesthetist</option>
                            <option value="rsn">Nurse</option>
                        </select>
                    </div>
                    <div class="form_sep">
                        <label for="reg_input_no" class="req">Resource Persons </label>
                        <select name="resource_sn" class="input-sm chosen-select" style="width:350px;" required>
                            <option selected="selected" value="">Search </option>
                            <?php $stmt = $db->query("SELECT * FROM admin_users where (rights = 'MD' OR rights = 'NS' OR rights = 'NS') AND status='1' order by count desc");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                <option value="<?php echo $row["id"]; ?>"><?php echo $row["fullname"]; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <br>
                </div>
                <div class="text-right" style="padding: 20px">
                    <input type="hidden" name="hospital_no" value="<?php echo $hospital_no; ?>">
                    <input type="hidden" name="procedure_sn" value="<?php echo $procedure_sn; ?>">
                    <button class="btn btn-success" type="submit" name="add_resource_person">Save</button>
                    <button class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>
                <br>
            </form>
            <br>
            <div id="resource-persons-table-container" style="padding: 20px;">
                <table class="table table-bordered" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="resource-persons-table-body">
                        <?php
                        $check_stmt = $db->prepare("SELECT * FROM procedure_resources WHERE prdure_sn = ?");
                        $check_stmt->execute(array($procedure_sn));

                        if ($check_stmt->rowCount() > 0) {
                            $sn = 1;
                            while ($procedure_resource = $check_stmt->fetch(PDO::FETCH_ASSOC)) {
                                $rsn = $procedure_resource["sn"];
                                $prdure_sn = $procedure_resource["prdure_sn"];
                                $name = $procedure_resource["name"];
                                $role = $procedure_resource["role"];
                        ?>
                                <tr>
                                    <td><?= $sn++; ?></td>
                                    <td><?= $name; ?></td>
                                    <td><?= $role; ?></td>
                                    <td>
                                        <form action="<?= $editFormAction; ?>" method="POST" onsubmit="return confirm('Please confirm your action to remove <?= $name; ?> as a resource person')">
                                            <input type="hidden" name="hosp" value="<?= $hospital_no; ?>">
                                            <input type="hidden" name="pr" value="<?= base64_encode($prdure_sn); ?>">
                                            <input type="hidden" name="rsn" value="<?= $rsn; ?>">
                                            <input type="submit" name="remove-resource-person-btn" class="btn btn-xs btn-danger" value="Remove">
                                        </form>
                                    </td>
                                </tr>
                        <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script> -->
<script>
    $(document).ready(function() {
        $('#pre_opt_note_form').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: './procedures/resource_people_modal.php', // Path to the PHP file handling the form submission
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    var data = JSON.parse(response);
                    if (data.status === 'success') {
                        toastr.success('Added successfully...', 'Success', {
                            timeOut: 1000
                        })
                        // Update the table with the new resource person
                        var newRow = `
                            <tr>
                                <td>${data.sn}</td>
                                <td>${data.name}</td>
                                <td>${data.role}</td>
                                <td>
                                    <form action="<?= $editFormAction; ?>" method="POST" onsubmit="return confirm('Please confirm your action to remove ${data.name} as a resource person')">
                                        <input type="hidden" name="hosp" value="${data.hospital_no}">
                                        <input type="hidden" name="pr" value="${data.prdure_sn}">
                                        <input type="hidden" name="rsn" value="${data.rsn}">
                                        <input type="submit" name="remove-resource-person-btn" class="btn btn-xs btn-danger" value="Remove">
                                    </form>
                                </td>
                            </tr>
                        `;
                        $('#resource-persons-table-body').append(newRow);
                    } else {
                        alert('Failed to add resource person.');
                    }
                }
            });
        });
    });
</script>