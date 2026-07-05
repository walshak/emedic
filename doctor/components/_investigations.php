<?php
$labtests = [];
$selected_labtests = [];
?>
<div id="Investigation_vue_instance">



    <div class="row">
        <div class="col-md-6">
            <label for="reg_select" class="">Sort By Category</label>
            <select name="" id="lab_cat" onchange="filterLabtests()">
                <option value="Laboratory" selected>Laboratory</option>
            </select>
        </div>
        <div class="col-sm-6" style="color:#F00">
            <label for="search-for-drug-input">Search:</label>
            <input style="color:#000" type="text" name="search-for-lab-input" id="search-for-lab-input" class="form-control" placeholder="Search by investigation name" onkeyup="filterTbleFx('search-for-lab-input', 'lab-datatale')">
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div style="max-height: 480px;overflow:auto">
                <table class="table table-striped table-bordered table-hover lab-test-dataTable" id="lab-datatale">
                    <thead>
                        <tr>
                            <th width="3%">#</th>
                            <th width="">Name</th>
                            <th width="">Specimen</th>
                            <th width="">Qty</th>
                            <th width="">Note</th>
                            <th width=""></th>
                        </tr>
                    </thead>
                    <tbody id="LabTable1_tr">


                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-md-4">

            <div style="max-height:470px;overflow-y:auto" id="selected_labt_">
                <h5>Selected:</h5>
                <table class="table table-striped table-bordered table-hover selected-lab-dataTable" id="selected_labt_tdl">
                    <thead>
                        <tr>
                            <th width="3%">#</th>
                            <th width="10%">Name</th>
                            <th width="10%">Price</th>
                            <th width="7%"></th>
                        </tr>
                    </thead>
                    <tbody id="LabTable2_tr">

                    </tbody>
                </table>
                <hr>
                <div>
                    <?php


                    ?>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    var filter_labtests = [];
    // var lab_cat = $("#lab_cat").val();
    var selected_labtests = <?php echo json_encode($selected_labtests); ?>;
    var labtests = <?php echo json_encode($labtests); ?>;
    var hospital_no = <?php echo json_encode($hospital_no); ?>;
    var appointment_number = <?php echo json_encode($appointment_number); ?>;

    var save_investigation_url = "<?php echo $save_investigation_url; ?>";

    function refreshLabDataTable(class_name) {
        $('.' + class_name).dataTable({
            responsive: true,
            "dom": 'T<"clear">lfrtip',
            "tableTools": {
                "sSwfPath": "js/plugins/dataTables/swf/copy_csv_xls_pdf.swf"
            }
        });
    }


    function showLabTable1(filter_labtests) {
        let tr = '';
        filter_labtests.forEach((lab, index) => {
            let price = parseFloat(lab.cash_price) || 0; // safely convert to number
            tr += `
        <tr>
            <td>${index + 1}</td>
            <td>${lab.test} [N${price.toLocaleString()}]</td>
            <td>
                <select name="specimen" data-placeholder="Select.." class="form-control" data-required="true" id="lab_specimen_${lab.sn}">
                    <option value="Not Specified">Not Specified</option>
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
            <td><input type="number" value="1" id="quantity_${lab.sn}"></td>
            <td>
                <input type="text" class="form-control" name="request_note" placeholder="Request Note" id="lab_request_note_${lab.sn}">
            </td>
            <td><button class="btn btn-sm btn-success" onclick="addLabTest(${index})"><i class="fa fa-plus"></i></button></td>
        </tr>`;
        });
        $("#LabTable1_tr").html(tr);
    }


    function showLabTable2(selected_labtests) {
        let tr = '';
        let total_cost = 0;

        selected_labtests.forEach((lab, index) => {
            let remove_btn = '';
            if (lab.paystatus == 0) {
                remove_btn = `<button class="btn btn-sm btn-danger" onclick="removeLabTest(` + index + `)"><i class="fa fa-minus"></i></button>`;
            }

            let price = parseFloat(lab.cash_price) || 0;
            total_cost += price;

            tr += `
        <tr>
            <td>${index + 1}</td>
            <td>
                ${lab.test}<br>
                <b>Specimen:</b> [${lab.specimen}]
                ${(lab.request_note != '' ? '<br><b>Note:</b> [' + lab.request_note + ']' : '')}
            </td>
            <td class="text-right">₦${price.toLocaleString()}</td>
            <td>${remove_btn}</td>
        </tr>`;
        });

        // Add total row
        tr += `
    <tr style="font-weight:bold; background:#f9f9f9;">
        <td colspan="2" class="text-right">Total:</td>
        <td class="text-right">₦${total_cost.toLocaleString()}</td>
        <td></td>
    </tr>`;

        $("#LabTable2_tr").html(tr);
    }


    function filterLabtests() {

        filter_labtests = labtests
        // filter_labtests.forEach((element, index) => {
        //     selected_labtests.forEach(elem => {
        //         if (elem.sn == element.sn) {
        //             filter_labtests.splice(index, 1);
        //         }
        //     })
        // })

        // return filter_labtests;
        showLabTable1(filter_labtests);
        showLabTable2(selected_labtests);

    }

    filterLabtests();

    function addLabTest(index_) {
        let obj = filter_labtests[index_]


        let lab_specimen = $("#lab_specimen_" + obj.sn).val()
        let lab_request_note = $("#lab_request_note_" + obj.sn).val()
        let quantity = parseInt($("#quantity_" + obj.sn).val())
        if (quantity == "") {
            quantity = 1;
        }

        ///alert(lab_request_note);

        let added = false;

        if (added) {
            return alert('Selected already')
        } else {

            obj.paystatus = 0
            obj.specimen = lab_specimen
            obj.request_note = lab_request_note
            obj.quantity = quantity
            let new_name = '';
            //            for (let index = 0; index < quantity; index++) {
            //                new_name = obj.test
            //                if(quantity> 1){
            //                  //  obj.test = new_name+'('+(index+1)+')'
            //                }
            //                
            //                
            //            }

            $("#lab_request_note_" + obj.sn).val("")
            $("#lab_specimen_" + obj.sn).val("")
        }

        let data = {
            appointment_number: appointment_number,
            hospital_no: hospital_no,
            investigation: obj,
            action: 'add'
        };

        toastr.info('Saving, Please wait..', 'Saving', {
            timeOut: 5000
        })
        filterLabtests()
        $.ajax({
            url: save_investigation_url,
            type: "POST",
            data: data,
            success: function(response) {
                toastr.clear();
                if (response.status == 200) {
                    toastr.success(response.message, 'Success', {
                        timeOut: 1000
                    })

                    tempObj = obj;
                    selected_labtests.unshift(obj)
                    if (quantity > 1) {
                        for (i = 2; i <= quantity; i++) {
                            let testname = obj.test;
                            obj.test = '';
                            obj.test = testname + ' ';
                            selected_labtests.unshift(obj)
                            obj = tempObj
                        }
                    }

                    filterLabtests()
                } else {
                    toastr.error(response.message, 'Success', {
                        timeOut: 1000
                    })
                }
                console.log(response)
            },
            error: function(response) {
                console.log(response)
            }
        });
        // return checkMgtNotes(hospital_no,appointment_number);
    }

    function removeLabTest(index) {

        let obj = selected_labtests[index];
        filter_labtests.push(obj)
        selected_labtests.splice(index, 1)

        let data = {
            appointment_number: appointment_number,
            hospital_no: hospital_no,
            investigation: obj,
            action: 'remove'
        };

        toastr.info('Removing,please wait..', 'Removing', {
            timeOut: 500
        })
        filterLabtests()
        $.ajax({
            url: save_investigation_url,
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

        // return checkMgtNotes(hospital_no,appointment_number);
    }

    function getSelectedLabTests() {
        return selected_labtests;
    }

    function addLabToGroup() {
        let labtest_ = $('#labtest_').val();
        let specimen_ = $('#specimen_').val();
        let request_note_ = $('#request_note_').val();

        if (labtest_ == '') {
            return alert('Select lab test')
        }

        if (specimen_ == '') {
            return alert('Select specimen')
        }

        $.ajax({
            url: 'controllers/management.php',
            type: "POST",
            data: {
                group_id: current_group_id,
                lab_id: labtest_,
                specimen: specimen_,
                request_note: request_note_,
                addLabToGroup: true
            },
            success: function(response) {
                if (response.status == 200) {
                    toastr.success(response.message, 'Success', {
                        timeOut: 1000
                    })
                    load_add_to_group('lab')
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


    function removeFromGroup(id, cat) {
        $.ajax({
            url: 'controllers/management.php',
            type: "POST",
            data: {
                id: id,
                cat: cat,
                removeFromGroup: true
            },
            success: function(response) {
                if (response.status == 200) {
                    toastr.success(response.message, 'Success', {
                        timeOut: 1000
                    })
                    load_add_to_group(cat)
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

    function pickLabFromGroup(group_id, cat) {
        let con = confirm('Please confirm to proceed');
        if (con) {
            toastr.info('Picking, please wait...', 'Success', {
                timeOut: 1000
            });
            $.ajax({
                url: 'controllers/management.php',
                type: "POST",
                data: {
                    cat: cat,
                    group_id: group_id,
                    hospital_no: hospital_no,
                    appointment_number: appointment_number,
                    pickLabFromGroup: true
                },
                success: function(response) {
                    if (response.status == 200) {
                        toastr.success(response.message, 'Success', {
                            timeOut: 1000
                        })

                        if (cat == 'lab') {

                            selected_labtests = response.data;
                            filterLabtests();
                        } else {
                            selected_radiologies = response.data;
                            filterradiologies()
                        }

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
    }


    var current_group_id;

    function openAdd_to_group_modal(group_id, modal) {
        current_group_id = group_id;
        $('#' + modal).modal('show');
    }

    function create_custom_group(cat) {
        let name = $('#' + cat + '_group_name').val();

        if (name.trim() != '') {
            $.ajax({
                url: "controllers/management.php",
                method: "POST",
                data: {
                    creatCustomGroup: true,
                    name: name,
                    category: cat
                },
                success: function(data) {
                    alert(data.message)
                    if (data.status == 200) {
                        $('#' + cat + '_group_name').val('');
                        $('#' + cat + '_group_modal').modal('hide');

                        let id = data.data;

                        load_pick_from_group(cat)
                        load_add_to_group(cat)
                    }
                },
                error: function(error) {
                    alert("error")
                    console.log(error)
                }
            });
        }

    }

    function load_pick_from_group(cat) {
        $.ajax({
            url: "components/_pick_from_group.php",
            method: "POST",
            data: {
                category: cat
            },
            success: function(data) {
                $('#_pick_' + cat + '_from_group').html(data);
            },
            error: function(error) {
                alert("error")
                console.log(error)
            }
        });
    }

    function load_add_to_group(cat) {
        $.ajax({
            url: "components/_add_to_group.php",
            method: "POST",
            data: {
                category: cat
            },
            success: function(data) {
                $('#_add_' + cat + '_to_group').html(data);
            },
            error: function(error) {
                alert("error")
                console.log(error)
            }
        });
    }

    function closeModal(id) {
        $('#' + id).modal('hide');
    }
</script>