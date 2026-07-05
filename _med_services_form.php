<?php
if (isset($_POST['getTemplate'])) {
    require_once('Connections/Conn.php');
    include('doctor/helpers.php');
    date_default_timezone_set('Africa/Lagos');
    $setdate = date("Y-m-d");
    $setdatetime = date("Y-m-d H:i:s");
    $idString = intval(cleanInput($_POST['template']));
    $data = explode('||', $idString);
    $id = $data[0];
    $stmt = $db->prepare("SELECT template FROM services_templates WHERE id = ?");
    $stmt->execute(array($id));
    if ($stmt->execute() > 0) {
        $temp = $stmt->fetch();
        echo $temp['template'];
    }

    exit;
}

?>
<div>
    <script>
        var isCompleted = 0;
    </script>
    <?php
    if (isset($_GET['editMedService'])) {


        ///    editMedService
        $note_id = base64_decode($_GET['editMedService']);
        $cr = $_GET['xccrxcccx'];
        $stmt = $db->prepare("SELECT * FROM notes_services WHERE id = ? ");
        $stmt->execute([$note_id]);
        if ($stmt->rowCount() > 0) {

            $serviceNoteInfo = $stmt->fetch();
            $app_service_tbl_id = $serviceNoteInfo['app_service_tbl_id'];
            $app_service_id = $serviceNoteInfo['app_service_id'];
            $updated_by_name = $serviceNoteInfo['updated_by_name'];
            $service = $serviceNoteInfo['service'];

    ?>
            <form action="patient.php?hosp_no=<?php echo $hospital_no; ?>&editMedService= <?php echo base64_encode($note_id); ?>" method="post">
                <div id="">
                    <input type="hidden" name="app_service_tbl_id_post" value="<?= $serviceNoteInfo["app_service_tbl_id"]; ?>" />
                    <input type="hidden" name="note_token" value="<?= $serviceNoteInfo["id"]; ?>" />
                    <div class="form-group">
                        <h2>Select <u>Template</u> </h2>
                        <select name="serviceTemplate" class="form-control chosen-select" style="font-size:15px" id="serviceTemplate" onchange="loadMedService(this.value)">
                            <option value=""> -- Select Template from list -- </option>
                            <?php
                            $stmt = $db->prepare("SELECT id, template_name FROM services_templates where category!='Laboratory' and category!='Radiology' order by template_name ");
                            $stmt->execute();
                            if ($stmt->execute() > 0) {
                                while ($service = $stmt->fetch()) {
                            ?>
                                    <option value="<?= $service["id"] . '||' . $service["template_name"]; ?>" <?= $serviceNoteInfo["template_id"] == $service['id'] ? 'selected' : ''; ?>> <?= $service["template_name"]; ?></option>
                            <?php
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <select name="billableServices" class="form-control chosen-select" id="billableServices" style="font-size:15px">
                            <option value=""> -- Select from list -- </option>
                            <?php
                            $stmt = $db->prepare("SELECT item_service, sn FROM  prices_table  WHERE price_table = 'Medical Services' ORDER BY item_service ");
                            $stmt->execute();
                            if ($stmt->execute() > 0) {
                                while ($service = $stmt->fetch()) {
                            ?>
                                    <option value="<?= $service["sn"] . '||' . $service["item_service"]; ?>" <?= $app_service_id == $service['sn'] ? 'selected' : ''; ?>> <?= $service["item_service"]; ?></option>
                            <?php
                                }
                            }
                            ?>
                        </select>

                    </div>
                    <div class="form-group">
                        <h4>Enter Documentation Notes below: [ <span class='text-danger'> Click on <i class='fa fa-full-screen'></i> for fullscreen mode</span> ]</h4>
                        <div id="edit__mode" style="color: red;"></div>
                        <div id="__genserviceNote___" class=" trumbowygEditor " style="font-size:18px; height: 400px;"><?= $serviceNoteInfo["notes"]; ?> </div>
                        <input type="hidden" name="genserviceNote" id="genserviceNote" cols="30" rows="10" required>
                        <script>
                            $(document).ready(function() {
                                setTimeout(() => {
                                    $('#genserviceNote').val($('#__genserviceNote___').html())
                                }, 2000);

                                $(document).on('keyup', '#__genserviceNote___', function() {
                                    $('#genserviceNote').val($('#__genserviceNote___').html())
                                })

                            })




                            const loadMedService = (id) => {
                                toastr.info('Please wait...', '', {
                                    timeOut: 5000
                                })
                                $.ajax({
                                    url: "../_med_services_form.php",
                                    method: "POST",
                                    data: {
                                        getTemplate: true,
                                        template: id
                                    },
                                    success: function(data) {
                                        // $('#__genserviceNote___').trumbowyg('disable');
                                        $('#__genserviceNote___').html(data);
                                        // $('#__genserviceNote___').trumbowyg('enable');


                                        toastr.clear();
                                        // $("#mgt_notes").html(data);
                                    }
                                });
                            }


                            isCompleted = <?= $serviceNoteInfo["isCompleted"]; ?>;
                        </script>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <h3>Reported By:</h3>
                            </div>
                            <div class="col-md-9">
                                <div>
                                    <input type="text" style="z-index:2000" class="typeahead form-control  " data-provide="typeahead" value="<?= $serviceNoteInfo["consultant_name"]; ?>" id="typeahead_search_specialist" placeholder="" autocomplete="off" required>
                                    <input type="hidden" name="consultant_id" id="typeahead_search_specialist_id" value="<?= $serviceNoteInfo["consultant_id"]; ?>" required>
                                    <input type="hidden" name="consultant_name" id="typeahead_search_specialist_name" value="<?= $serviceNoteInfo["consultant_name"]; ?>" required>
                                    <input type="hidden" name="app_service_tbl_id" id="app_service_tbl_id" value="<?= $app_service_tbl_id; ?>">
                                    <input type="hidden" name="app_service_id" id="app_service_id" value="<?= $app_service_id; ?>">
                                    <input type="hidden" name="updated_by_name" id="updated_by_name" value="<?= $updated_by_name; ?>">
                                    <input type="hidden" name="service" id="service" value="<?= $service; ?>">
                                    <input type="hidden" name="cr" id="cr" value="<?= $cr; ?>">
                                </div>
                            </div>

                        </div>
                        <div class="">
                            <h3> <input type="checkbox" name="isCompleted" id="isCompleted" <?= $serviceNoteInfo["isCompleted"] ? 'checked' : ''; ?>> Mark to Complete</h3>
                        </div>
                    </div>


                    <div class="form_sep">
                        <div class="pull-right">

                            <button class="btn btn-primary" name="updateMedService" id="updateMedService" <?php if ($serviceNoteInfo["isCompleted"] == 1) { ?> disabled<?php } ?>>Save</button>
                            <a href="patient.php?hosp_no=<?= $hospital_no; ?>&medService" class="btn btn-danger" data-dismiss="modal">Close</a>
                        </div>
                    </div>

                </div>
            </form>

        <?php
        } else {
            ///echo '<h4> Page not found !</h4>';
        }
    } else {

        $cur_date_time = date('Y-m-d H:i:s');

        if ($_SESSION['dispensory'] == 1) {
            $dept_id = $_SESSION['dept_id'];
            $x_search = " AND dept='$dept_id'";
        } else {
            $x_search = null;
        }


        ?>

        <form action="#" method="post">


            <table>
                <tr>
                    <td>
                        <div class="form-group">
                            <h4>1. SEARCH FOR SERVICE</h4>
                            <select id="app_service_tbl_id_" name="app_service_tbl_id" style="width:550px;" required></select>
                        </div>
                    </td>
                    <td>

                        <div class="form-group">
                            <h4>2. QTY</h4>
                            <input type="number" name="qty_serv_med" id="qty_serv_med" min=1 required>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="text-right">
                <button class="btn btn-sm btn-primary pull-right" id="post_med_service_req" name="post_med_service_req">
                    Post Request
                </button>
            </div>

            <input type="hidden" name="insurance_type" value="<?= $insurance_type; ?>">
            <input type="hidden" name="insurancen_no" value="<?= $insurancen_no; ?>">
            <input type="hidden" name="payment_mode" value="<?= $payment_mode; ?>">
            <input type="hidden" name="add_minus" value="<?= $add_minus; ?>">
        </form>

    <?php
    }

    ?>

</div>

<script>
    $(document).on('click', '#isCompleted', function() {
        if (isCompleted == 0) {
            if (confirm('Note: Marking it complete will lock the note section and won\'t  allow further modification ')) {
                $('#updateMedService').text('Submit & Lock')
                $('#updateMedService').removeClass('btn-primary');
                $('#updateMedService').addClass('btn-danger');
                $(this).prop('checked', true);

            } else {
                $(this).prop('checked', false);
                $('#updateMedService').text('Save');
                $('#updateMedService').removeClass('btn-danger');
                $('#f').addClass('btn-primary');

            }
        } else {
            $(this).prop('checked', true);
        }
    })

    $(document).on('click', '#post_med_service_req', function() {})
</script>