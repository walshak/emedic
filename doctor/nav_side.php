<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav" id="side-menu">
           <?php
$photo = "../staffphotos/port_" . $uname . ".jpg";
           $img   = file_exists($photo) ? $photo : "../img/user_avatar_lg.png";
           ?>

<li class="nav-header">
    <div class="dropdown profile-element">
        <span>
            <img alt="image" class="img-circle" src="<?php echo $img; ?>" height="50" width="50">
        </span>
        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
            <span class="clear">
                <span class="block m-t-xs">
                    <strong class="font-bold"><?php echo $fullname; ?></strong>
                </span>
                <span class="text-muted text-xs block">
                    <?php echo !empty($_SESSION['Designation']) ? $_SESSION['Designation'] : $_SESSION['specialist']; ?>
                    <b class="caret"></b>
                </span>
            </span>
        </a>
        <ul class="dropdown-menu animated fadeInRight m-t-xs">
            <li><a href="../profile/index.php?profile=<?php echo $_SESSION['username']; ?>">Profile</a></li>
            <li><a href="../profile/mailbox.php">Mailbox</a></li>
            <li class="divider"></li>
            <li><a href="../index.php">Logout</a></li>
        </ul>
    </div>
    <div class="logo-element">
        IN+
    </div>
</li>


            <?php
                       // Admin dashboard
                       if (!empty($_SESSION['navigate']) && $_SESSION['navigate'] == 'admin') {
                           echo '<li>
            <a href="../admin/index.php">
                <i class="fa fa-dashboard"></i> <span class="nav-label">Main Dashboard</span>
            </a>
          </li>';
                       }

           // Role-based dashboards
           $roleDashboards = [
               'PH' => ['../pharmacy/index.php', 'Dashboard'],
               'NS' => ['../nursing/index.php', 'Dashboard'],
               'LB' => ['../investigations/mgt.php', 'Investigation'],
               'DR' => ['index.php', 'Dashboard'],
           ];

           if (!empty($rights) && isset($roleDashboards[$rights])) {
               list($url, $label) = $roleDashboards[$rights];
               echo '<li>
            <a href="' . $url . '">
                <i class="fa fa-dashboard"></i> <span class="nav-label">' . $label . '</span>
            </a>
          </li>';
           }

           // Doctor/Nurse/Radiologist access
           $showDoctorMenu = (
               isset($_SESSION['doctor']) && $_SESSION['doctor'] == '1'
           ) || $rights == 'NS' || $rights == 'DR' || (
               isset($_SESSION['Designation']) && $_SESSION['Designation'] == 'Radiologist'
           );

           if ($showDoctorMenu) {
               echo '
    <li>
        <a href="#" data-toggle="modal" data-target="#patient_seach_modal">
            <i class="fa fa-search"></i> <span class="nav-label">Patient Search</span>
        </a>
    </li>
    <li id="toggle_external_patient_modal">
        <a href="#" data-toggle="modal" data-target="#external_patient_modal">
            <i class="fa fa-search"></i> <span class="nav-label">External Patient Search</span>
        </a>
    </li>
    <li>
        <a href="index.php?adm">
            <i class="fa fa-bed"></i> <span class="nav-label">Patients On-Admission</span>
        </a>
    </li>
    <li>
        <a href="../doctor/procedure.php?procedure">
            <i class="fa fa-eraser"></i> <span class="nav-label">Procedures</span>
        </a>
    </li>';

               if (!empty($_SESSION['dialysis_visible']) && $_SESSION['dialysis_visible'] == '1') {
                   echo '
        <li>
            <a href="../doctor/dialysis.php">
                <i class="fa fa-signal"></i> <span class="nav-label">Dialysis</span>
            </a>
        </li>
        <li>
            <a href="index.php?transplant">
                <i class="fa fa-cut"></i> <span class="nav-label">Transplant</span>
            </a>
        </li>';
               }
           }
           ?>

            <?php include('../inc/nav_side_profile.php'); ?>

        </ul>

    </div>
</nav>


<div class="modal inmodal" id="patient_seach_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Search Patient</h4>
            </div>
            <div class="modal-body" id="add_service_modal_body" style="min-height: 300px">
                <?php
               include('../search_patient_code.php');
           ?>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal full-screen-modal" id="external_patient_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">Search External Patient</h4>
            </div>
            <div class="modal-body" id="external_patient_modal_body" style="min-height: 300px">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="active"><a href="#patient_info_tab" role="tab" data-toggle="tab">Patient Info</a></li>
                    <!-- <li><a href="#notes_tab" role="tab" data-toggle="tab">Notes</a></li> -->
                    <li><a href="#lab_tab" role="tab" data-toggle="tab">Lab</a></li>
                    <li><a href="#pharmacy_tab" role="tab" data-toggle="tab">Drugs</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="patient_info_tab">
                        <div class="form_sep">
                            <label for="ex_search">(Name or Phone or EX Number)</label>
                            <input type="text" id="ex_search" name="ex_search" class="form-control" style="border-color: black;" required autocomplete="off">
                        </div>
                        <div id="search_results">
                            <p>Start typing to search for patients...</p>
                        </div>
                        <hr>
                        <div id="selected_patient_details"></div>
                        <div id="patient_form_container" style="display: none;">
                            <!-- Form Placeholder -->
                        </div>
                        <hr>
                        <h4>Existing Notes</h4>
                        <table class="table table-striped" id="notes_table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Prepared By</th>
                                    <th>Date</th>
                                    <th>Note</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Notes will be dynamically populated here -->
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane" id="lab_tab">
                        <h4>Lab and Radiology Results</h4>
                        <table class="table table-striped" id="lab_results_table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Test Name</th>
                                    <th>Result</th>
                                    <th>Specimen</th>
                                    <th>Notes</th>
                                    <th>Date</th>
                                    <th>Request (Prepared By)</th> <!-- Added column for Requester -->
                                    <th>Results Entered By</th> <!-- Added column for Results Entered -->
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Lab results will be dynamically populated here -->
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane" id="pharmacy_tab">
                        <h4>Pharmacy Drugs</h4>
                        <table class="table table-striped" id="pharmacy_drugs_table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Drug Name</th>
                                    <th>Quantity</th>
                                    <th>Invoiced</th>
                                    <th>Prepared</th>
                                    <th>Dispensed</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Pharmacy drugs will be dynamically populated here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    .full-screen-modal .modal-dialog {
        width: 100%;
        height: 100%;
        margin: 0;
        padding: 0;
    }

    .full-screen-modal .modal-content {
        height: 100%;
        border-radius: 0;
    }
</style>




<?php
include('../search_patient_code_scripts.php');
           include('../search_ext_patient_code_scripts.php');
           ?>

<script>
    // Load notes when the modal is opened
    function triggerLoadNotes() {
        const hospitalNo = $('#hospital_no').val();
        const appointmentNo = $('#appointment_number').val();

        if (hospitalNo && appointmentNo) {
            loadNotes(hospitalNo, appointmentNo);
            loadLabResults(hospitalNo, appointmentNo);
            loadPharmacyDrugs(hospitalNo, appointmentNo);
        }
    };
</script>