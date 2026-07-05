<?php
$radiologies = [];
$selected_radiologies = [];
?>
<div id="Investigation_vue_instance">
    <hr>
    <div class="row">
        <div class="col-md-6">
            <label for="reg_select" class="">Sort By Category</label>
            <select name="" id="rad_cat" onchange="filterradiologies()">
                <option value="Radiology" selected>Radiology</option>
            </select>
        </div>
        <div class="col-sm-6" style="color:#F00">
            <label for="search-for-drug-input">Search:</label>
            <input style="color:#000" type="text" name="search-for-rrad-input" id="search-for-rad-input" class="form-control" placeholder="Search by investigation name" onkeyup="filterTbleFx('search-for-rad-input', 'rad-datatale')">
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div style="max-height: 480px;overflow-y:scroll;">
                <table class="table table-striped table-bordered table-hover dataTables-exampl" id="rad-datatale">
                    <thead>
                        <tr>
                            <th width="3%">#</th>
                            <th width="">Name</th>
                            <th width="40%">Request Note</th>

                            <th width="7%"></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="RadTable1_tr">


                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-md-4">
            <div style="max-height:470px;overflow-y:auto" id="selected_radt_">
                <h5>Selected:</h5>
                <table class="table table-striped table-bordered table-hover dataTables-exampl" id="selected_radt_tdl">
                    <thead>
                        <tr>
                            <th width="3%">#</th>
                            <th width="">Name</th>
                            <th width="">Price</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="RadTable2_tr">


                    </tbody>
                </table>
                <hr>
                <div>

                </div>
            </div>
        </div>
    </div>
</div>







<script>
    var filter_radiologies = [];
    // var rad_cat = $("#rad_cat").val();
    var selected_radiologies = <?php echo json_encode($selected_radiologies); ?>;
    var radiologies = <?php echo json_encode($radiologies); ?>;
    var hospital_no = <?php echo json_encode($hospital_no); ?>;
    var appointment_number = <?php echo json_encode($appointment_number); ?>;

    var save_image_url = "<?php echo $save_investigation_url; ?>";

    function showRadTable1(filter_radiologies) {
        let tr = '';
        filter_radiologies.forEach((rad, index) => {
            let price = parseFloat(rad.cash_price) || 0; // safely convert to number
            tr += `
        <tr>
            <td>${index + 1}</td>
            <td>${rad.test} [N${price.toLocaleString()}]</td>
            <td>
                <input type="hidden" value=" " id="rad_specimen_${rad.sn}">
                <input type="text" class="form-control" name="request_note" placeholder="Request Note" id="rad_request_note_${rad.sn}">
            </td>
            <td><button class="btn btn-sm btn-success" onclick="addRad(${index})"><i class="fa fa-plus"></i></button></td>
            <td></td>
        </tr>`;
        });

        $("#RadTable1_tr").html(tr);
    }


    function showRadTable2(selected_radiologies) {
        let tr = '';
        let total_cost = 0;

        selected_radiologies.forEach((rad, index) => {
            let remove_btn = '';
            if (rad.paystatus == 0) {
                remove_btn = `<button class="btn btn-sm btn-danger" onclick="removeRadTest(` + index + `)"><i class="fa fa-minus"></i></button>`;
            }

            // Convert to number safely and add to total
            let price = parseFloat(rad.cash_price) || 0;
            total_cost += price;

            tr += `
        <tr>
            <td>${index + 1}</td>
            <td>${rad.test}
                ${(rad.request_note != '' ? '<br><b>Note:</b> [' + rad.request_note + ']' : '')}
            </td>
            <td class="text-right">${price.toLocaleString()}</td>
            <td>${remove_btn}</td>
        </tr>`;
        });

        // Add total row
        tr += `
    <tr style="font-weight:bold; background:#f9f9f9;">
        <td colspan="2" class="text-right">Total:</td>
        <td class="text-right">${total_cost.toLocaleString()}</td>
        <td></td>
    </tr>`;

        $("#RadTable2_tr").html(tr);
    }



    function filterradiologies() {
        filter_radiologies = radiologies


        // filter_radiologies.forEach((element, index) => {
        //     selected_radiologies.forEach(elem => {
        //         if (elem.sn == element.sn) {
        //             filter_radiologies.splice(index, 1);
        //         }
        //     })
        // })

        // return filter_radiologies;
        showRadTable1(filter_radiologies);
        showRadTable2(selected_radiologies);
    }

    filterradiologies();

    function addRad(index_) {
        let obj = filter_radiologies[index_]
        let rad_specimen = $("#rad_specimen_" + obj.sn).val()
        let rad_request_note = $("#rad_request_note_" + obj.sn).val()
        let quantity = 1


        if (rad_request_note == "") {
            return toastr.error('Request note is required', 'Error', {
                timeOut: 2000
            })
        }
        let added = false;
        var cleanedString = rad_request_note.replace(/[^\w\s]/g, '');

        if (added) {
            return alert('Selected already')
        } else {
            obj.paystatus = 0
            obj.specimen = rad_specimen
            obj.request_note = cleanedString
            obj.quantity = quantity
            let new_name = '';

            $("#rad_request_note_" + obj.sn).val("")
            $("#rad_specimen_" + obj.sn).val("")
        }

        let data = {
            appointment_number: appointment_number,
            hospital_no: hospital_no,
            investigation: obj,
            action: 'add'
        };
        filterradiologies()

        $.ajax({
            url: save_image_url,
            type: "POST",
            data: data,
            success: function(response) {
                toastr.clear();
                if (response.status == 200) {
                    toastr.success(response.message, 'Success', {
                        timeOut: 1000
                    })
                    selected_radiologies.unshift(obj)
                    filterradiologies()
                } else {
                    toastr.error(response.message, 'Error', {
                        timeOut: 1000
                    })
                }
            },
            error: function(response) {
                console.log(response)
            }
        });

        // return checkMgtNotes(hospital_no,appointment_number);
    }

    function removeRadTest(index) {
        let obj = selected_radiologies[index];
        filter_radiologies.push(obj)
        selected_radiologies.splice(index, 1)

        let data = {
            appointment_number: appointment_number,
            hospital_no: hospital_no,
            investigation: obj,
            action: 'remove'
        };
        toastr.info('Removing,please wait..', 'Removing', {
            timeOut: 500
        })
        filterradiologies()
        $.ajax({
            url: save_image_url,
            type: "POST",
            data: data,
            success: function(response) {
                toastr.success(' Removed successfully..', 'Success', {
                    timeOut: 1000
                })
                console.log(response)
            },
            error: function(response) {
                console.log(response)
            }
        });

        //return checkMgtNotes(hospital_no,appointment_number);
    }

    function getSelectedradiologies() {
        return selected_radiologies;
    }

    function addRadToGroup() {
        let radiology_ = $('#radiology_').val();
        let rad_request_note_ = $('#rad_request_note_').val();

        if (radiology_ == '') {
            return alert('Select lab test')
        }
        $.ajax({
            url: 'controllers/management.php',
            type: "POST",
            data: {
                group_id: current_group_id,
                lab_id: radiology_,
                rad_request_note_: rad_request_note_,
                addRadToGroup: true
            },
            success: function(response) {
                if (response.status == 200) {
                    toastr.success(response.message, 'Success', {
                        timeOut: 1000
                    })
                    load_add_to_group('rad')
                } else {

                    toastr.error(response.message, 'Error', {
                        timeOut: 1000
                    })
                }

            },
            error: function(response) {
                console.log(response)
            }
        });
    }
</script>