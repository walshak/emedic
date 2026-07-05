<?php




//// SELECTED investigation
$selected_labs_stmt = $db->prepare("SELECT sn lab_manage_id, test_id, preferred_specimen specimen,request_note,labrequest_no FROM lab_manage WHERE app_no = ? and patient = ?  ");
$selected_labs_stmt->execute(array($appointment_number, $hospital_no));
$selected_labs_rows = $selected_labs_stmt->fetchAll(PDO::FETCH_ASSOC);
$selected_imaging_scans = [];

foreach ($selected_labs_rows as $key => $selected_labs_row) {

    $investigation = $Investigation->find($selected_labs_row["test_id"]);

    $PatientApService_ = $PatientApService->get(
        [
            'drug_sn' => $investigation->labrequest_no
        ]
    );
    if(!empty($PatientApService_)){
        $investigation->specimen = $selected_labs_row['specimen'];
        $investigation->request_note = $selected_labs_row['request_note'];
        $investigation->invoice_status = $PatientApService_->invoice_status;
        array_push( $selected_imaging_scans, $investigation );
    }

    // $investigation->specimen = $selected_labs_row["specimen"];
    // $investigation->request_note = $selected_labs_row["request_note"];
    // array_push($selected_imaging_scans, $investigation);
}

// patient_access_type
// patient_insurance

?>
<div id="Imaging_vue_instance">

    <br>

    <br>
    <hr>
    <label for="reg_select" class="">Sort By Category</label>
    <select name="" id="lab_cat" onchange="filterimaging_scans()">
        <option value="">All</option>
        <option value="Radiology">Imaging / SCan</option>
    </select>
    <div class="row">
        <div class="col-md-8">
            <table class="table table-striped table-bordered table-hover rad-dataTable">
                <thead>
                    <tr>
                        <th width="3%">#</th>
                        <th width="15%">Name</th>
                        <th width="10%">Category</th>
                        <th width="7%">Specimen</th>
                        <th width="7%">Note</th>
                        <th width="7%"></th>
                    </tr>
                </thead>
                <tbody id="LabTable1_tr">


                </tbody>
            </table>
        </div>
        <div class="col-md-4">
            <div style="margin-left:20px;" id="selected_labt_">
                <h5>Selected:</h5>
                <table class="table table-striped table-bordered table-hover selected-rad-dataTable" id="selected_labt_tdl">
                    <thead>
                        <tr>
                            <th width="3%">#</th>
                            <th width="10%">Name</th>
                            <th width="10%">Category</th>
                            <th width="7%"></th>
                        </tr>
                    </thead>
                    <tbody id="LabTable2_tr">


                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<script>
    var filter_imaging_scans = [];
    // var lab_cat = $("#lab_cat").val();
    var selected_imaging_scans = <?php echo json_encode($selected_imaging_scans); ?>;
    var imaging_scans = <?php echo json_encode($Investigation->get(['category' => 'Radiology'], true)); ?>;
    var hospital_no = <?php echo json_encode($hospital_no); ?>;
    var appointment_number = <?php echo json_encode($appointment_number); ?>;




    function refreshRadDataTable(class_name) {
    $('.' + class_name).dataTable({
        responsive: true,
        "dom": 'T<"clear">lfrtip',
        "tableTools": {
            "sSwfPath": "js/plugins/dataTables/swf/copy_csv_xls_pdf.swf"
        }
    });
    
    }


    function showLabTable1(filter_imaging_scans) {
        let tr = '';
        filter_imaging_scans.forEach((lab, index) => {
            tr += `
            <tr>
                        <td>` + (index + 1) + `</td>
                        <td>` + lab.test + `</td>
                        <td>` + lab.category + `</td>
                        <td>
                            <select name="specimen" data-placeholder="Select.." class="form-control" data-required="true" id="lab_specimen_` + lab.sn + `">
                                <option value="">Select</option>
                                <option value="No Specimen Required">No Specimen Required</option>
                                <option value="Aspirate">Aspirate</option>
                                <option value="Urine">Urine</option>
                                <option value="Blood">Blood</option>
                                <option value="C.S.F">C.S.F</option>
                                <option value="Ear Swab">Ear Swab</option>
                                <option value="Eye Swab">Eye Swab</option>
                                <option value="Fluids">Fluids</option>
                                <option value="Pap Smear">Pap Smear</option>
                                <option value="Semen">Semen</option>
                                <option value="Skin Scraping">Skin Scraping</option>
                                <option value="Sputum">Sputum</option>
                                <option value="Stool">Stool</option>
                                <option value="Throat Swab">Throat Swab</option>
                                <option value="Tissue">Tissue</option>
                                <option value="Urethral Swab">Urethral Swab</option>
                                <option value="Bence Jones Protein (Urine)">Bence Jones Protein (Urine)</option>
                                <option value="Viginal Swab">Viginal Swab</option>
                                <option value="Wound Swab">Wound Swab</option>

                            </select>
                        </td>
                        <td>
                            <input type="text" name="request_note" placeholder="Request Note" id="lab_request_note_` + lab.sn + `">
                        </td>
                        <td><button class="btn btn-sm btn-success" onclick="addLabTest(` + index + `)"><i class="fa fa-plus"></i></button></td>
                    </tr>
            `;
        })


        $("#LabTable1_tr").html(tr);
        refreshRadDataTable('rad-dataTable');
    }

    function showLabTable2(selected_imaging_scans) {
        let tr = '';
        selected_imaging_scans.forEach((lab, index) => {
            tr += `
            <tr >
            <td>` + (index + 1) + `</td>
            <td>` + lab.test + `
                <br>
                Specimen:[` + lab.specimen + `]
                <br>
                Note:[` + lab.request_note + `]
            </td>
            <td>` + lab.category + `</td>
            <td><button class="btn btn-sm btn-danger" onclick="removeLabTest(` + index + `)"><i class="fa fa-minus"></i></button></td>
        </tr>
            `;
        })

       // if (selected_imaging_scans.length > 0) {
            // $("#selected_labt_").fadeIn('slow')
       // }
        $("#LabTable2_tr").html(tr);

    }


    function filterimaging_scans() {


        // if (lab_cat == "") {
            filter_imaging_scans = imaging_scans
        // } else {
        //     filter_imaging_scans = imaging_scans.filter(lab => lab.category == lab_cat)
        // }


        filter_imaging_scans.forEach((element, index) => {
            selected_imaging_scans.forEach(elem => {
                if (elem.sn == element.sn) {
                    filter_imaging_scans.splice(index, 1);
                }
            })
        })

        // return filter_imaging_scans;
        showLabTable1(filter_imaging_scans);
        showLabTable2(selected_imaging_scans);
      
    }

    filterimaging_scans();

    function addLabTest(index_) {


        let obj = filter_imaging_scans[index_]


        let lab_specimen = $("#lab_specimen_" + obj.sn).val()
        let lab_request_note = $("#lab_request_note_" + obj.sn).val()

        if (lab_specimen == "") {
            return alert("Select specimen type")
        }
        let added = false;
        selected_imaging_scans.forEach((elem, index) => {
            if (elem.sn == obj.sn) {
                added = true
            }
        })

        if (added) {
            return alert('Selected already')
        } else {
            obj.specimen = lab_specimen
            obj.request_note = lab_request_note
            selected_imaging_scans.push(obj)
            $("#lab_request_note_" + obj.sn).val("")
            $("#lab_specimen_" + obj.sn).val("")
        }

        let data = {appointment_number: appointment_number, hospital_no: hospital_no, investigation: obj, action: 'add' };
        filterimaging_scans()
        // getSelectedimaging_scans()

      
        $.ajax({
            url: 'controllers/_saveInvestigation.php',
            type: "POST",
            data: data,
            success: function (response) {
                console.log(response)
            },
            error: function (response) {
                console.log(response)
            }
        });

    }

    function removeLabTest(index) {
        let obj = selected_imaging_scans[index];
        filter_imaging_scans.push(obj)
        selected_imaging_scans.splice(index, 1)
        
        let data = {appointment_number: appointment_number, hospital_no: hospital_no, investigation: obj, action: 'remove' };
        toastr.info('Removing,please wait..', 'Removing', {timeOut: 500})
        filterimaging_scans()
        $.ajax({
            url: 'controllers/_saveInvestigation.php',
            type: "POST",
            data: data,
            success: function (response) {
                toastr.success(' Removed successfully..', 'Success', {timeOut: 1000})
                console.log(response)
            },
            error: function (response) {
                console.log(response)
            }
        });
    }

    function getSelectedimaging_scans() {
        return selected_imaging_scans;
    }



  


    $(document).ready(() => {
        
        setTimeout(() => {
            
            refreshRadDataTable('selected-rad-dataTable');

            $(".chosen-select").chosen({
                allow_single_deselect: true,
                enable_search_threshold: 10,
                no_results_text: 'Oops, nothing found!',
                width: "100%"
            });
            $('.chosen-drop').css({
                "width": "100%",
                "white-space": "nowrap"
            })

        }, 3000);


    });
</script>