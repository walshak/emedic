<?php
$drug_stocks = [];
$drug_stocks_list = [];
$selected_drug_stocks = [];

//get hospital details

$hd = $db->query("SELECT * FROM hospital_details LIMIT 1");
$hd->execute();

$hd = $hd->fetch(PDO::FETCH_ASSOC);

?>
<script>
    var prescription_format = 'loose_format';
</script>
<div id="Medication_vue_instance">

    <div class="alert alert-danger" id="drug_loading_div" style="display:none">Loading drugs, please wait...</div>
    <div class="row">
        <div class="col-md-5">
            <div class="form_sep">
                <h3 style="color: black;"><i>1. DRUG SEARCH:</i></h3>
                <!-- <input type="text" style="padding: 20px" class="typeahead form-control typeahead_elem " data-provide="typeahead" id="typeahead1" placeholder="Enter First 3 Letters of  Drug or Brand Name" autocomplete="off"> -->
                <input type="text" style="padding: 20px" class="form-control " id="drug_search_typeahead_d" placeholder="Enter First 3 Letters of  Drug or Brand Name" autocomplete="off">
                <input type="hidden" style="padding: 20px" data-provide="typeahead" id="drug_search_typeahead_id" autocomplete="off">
                <input type="hidden" style="padding: 20px" data-provide="typeahead" id="drug_search_typeahead_product_name" autocomplete="off">
                <input type="hidden" style="padding: 20px" data-provide="typeahead" id="drug_search_typeahead_hosp_price" autocomplete="off">
                <input type="hidden" style="padding: 20px" data-provide="typeahead" id="drug_search_typeahead_cash_price" autocomplete="off">
                <input type="hidden" style="padding: 20px" data-provide="typeahead" id="drug_search_typeahead_nhis_price" autocomplete="off">
            </div>


            <div class="form_sep">
                <h3 style="color: black;"><i>2. PRESCRIPTION:</i></h3>
                <?php if ($hd['use_simple_presc'] != 1): ?>
                    <table>
                        <tr>
                            <td>
                                <label class="req" style="color: black;">DOSAGE</label>
                                <input type="text" class="form-control" placeholder="Enter Number" name="med_dosage" id="med_dosage" style="display:inline-block !important" min="1">
                            </td>
                            <td>
                                <label class="req" style="color: black;"></label>
                                <select name="med_dosage_unit" id="med_dosage_unit" class="form-control">
                                    <option value="">Unit</option>
                                    <option value="mg">mg</option>
                                    <option value="drop(s)">drop(s)</option>
                                    <option value="g">g</option>
                                    <option value="gt">gt</option>
                                    <option value="ungt">ungt</option>
                                    <option value="cl">cl</option>
                                    <option value="iu">iu</option>
                                    <option value="ml">ml</option>
                                    <option value="mcg">mcg</option>
                                    <option value="%v/v">%v/v</option>
                                    <option value="%w/w">%w/w</option>
                                    <option value="%w/v">%w/v</option>
                                    <option value="N/A">N/A</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan='2'>
                                <br />
                                <label class="req" style="color: black;">FREQUENCY</label>
                                <select class="form-control" name="med_frequency" id="med_frequency" class="form-control">
                                    <option value="" selected>--Select--</option>
                                    <option value="STAT">STAT</option>
                                    <option value="OD">OD</option>
                                    <option value="BD">BD</option>
                                    <option value="BID">BID</option>
                                    <option value="TID">TID</option>
                                    <option value="TDS">TDS</option>
                                    <option value="NOCTE">NOCTE</option>
                                    <option value="QID">QID</option>
                                    <option value="PRN">PRN</option>
                                    <option value="4 hourly">4 hourly</option>
                                    <option value="6 hourly">6 hourly</option>
                                    <option value="8 hourly">8 hourly</option>
                                    <option value="Weekly">Weekly</option>
                                    <option value="N/A">N/A</option>
                                </select>

                            </td>

                        </tr>
                        <tr>
                            <td colspan='2'>
                                <br />
                                <label class="req" style="color: black;">DURATION</label>
                                <input type="number" class="form-control" placeholder="Enter Number" name="med_duration" id="med_duration" name="no_of_days" style="display:inline-block !important" min="1">
                            </td>
                            <td colspan='2'>
                                <br />
                                <label class="req" style="color: black;"></label>
                                <select name="med_duration_unit" id="med_duration_unit" class="form-control">
                                    <option value="" selected>Duration</option>
                                    <option value="hours">hours</option>
                                    <option value="days">days</option>
                                    <option value="weeks">Weeks</option>
                                    <option value="months">months</option>
                                    <option value="N/A">N/A</option>
                                </select>

                            </td>
                        </tr>
                        <tr>
                            <td colspan='2'>
                                <br />
                                <label style="color: black;">REMARK</label>
                                <input type="text" class="form-control" placeholder="Remark" name="med_remark" id="med_remark" name="med_remark" style="display:inline-block !important" min="1">
                            </td>
                            <td> <br />
                                <label style="color: black;">QUANTITY</label>
                                <input type="number" class="form-control" placeholder="Quantity" name="qty" id="qty" name="qty">

                            </td>
                        </tr>
                    </table>

                    <br>


                    <button class="btn btn-primary addSelectedDrugBtn" onclick="addSelectedDrug()" id="addSelectedDrugBtn"><i class="fa fa-plus"></i>&nbsp;Add Drug </button>

                <?php else: ?>
                    <table>
                        <tr>
                            <td colspan='2'>
                                <label class="req" style="color: black;">Presciption</label>
                                <input type="text" class="form-control" placeholder="e.g 20 mg 4 hourly 3 days.." name="med_dosage" id="med_dosage" style="display:inline-block !important" min="1">
                            </td>

                            <td colspan='2'>
                                <label style="color: black;">QUANTITY</label>
                                <input type="number" class="form-control" placeholder="Quantity" name="qty" id="qty" name="qty">

                            </td>
                        </tr>


                        <tr>
                            <td colspan='2'>
                                <br />
                                <label style="color: black;">REMARK</label>
                                <input type="text" class="form-control" placeholder="Remark" name="med_remark" id="med_remark" name="med_remark" style="display:inline-block !important" min="1">
                            </td>

                        </tr>
                    </table>

                    <br>


                    <!-- <button class="btn btn-primary addSelectedDrugBtn" onclick="addSelectedDrug()" id="addSelectedDrugBtn" disabled="disabled"><i class="fa fa-plus"></i>&nbsp;Add Drug </button> -->
                    <button class="btn btn-primary addSelectedDrugBtn" onclick="addSelectedDrugSimple()" id="addSelectedDrugSimpleBtn"><i class="fa fa-plus"></i>&nbsp;Add Drug </button>

                <?php endif ?>
            </div>
        </div>
        <div class="col-md-7">
            <table width="100%">
                <tr>
                    <td>
                        <h3 style="color: brown;"><i>SELECTED DRUG(s):</i></h3>
                    </td>
                    <td align="right">
                        <?php //if ($rights == 'DR') { 
                        ?>
                        <input type="button" name="edit_users" value="ADD PLAN HERE " data-target="#modal" id="<?php echo $hospital_no . '___' . $plan_edit_mode . '___' . $appointment_number . '___' . $adm_status; ?>" class="btn btn-success add_view_plan" <?php if ($plan_edit_mode == 0) { ?> disabled<?php } ?> />
                        <?php //} 
                        ?>
                    </td>
                </tr>
            </table>

            <div style="max-height:430px;overflow-y:auto">

                <table class="table table-striped table-bordered table-hover ">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Drug Name</th>
                            <th>Price</th>
                            <th>Prescription</th>

                            <th data-toggle="true"></th>
                        </tr>
                    </thead>
                    <tbody id="DrugTable2_tr">

                    </tbody>
                </table>
            </div>

            <div style="max-height:300px;overflow-y:auto">
                <?php ?>
            </div>



        </div>
    </div>

</div>


<div class="modal inmodal" id="add_view_plan_mdl" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>

                <h4 class="modal-title">Add and View Plan</h4>
            </div>

            <div class="modal-body" id="add_view_plan_body">


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

<script>
    var filter_drug_stocks = [];
    var drug_form = "";
    var selected_drug_stocks = <?php echo json_encode($selected_drug_stocks); ?>;
    var drug_stocks = <?php echo json_encode($drug_stocks); ?>;
    var hospital_no = <?php echo json_encode($hospital_no); ?>;
    var appointment_number = <?php echo json_encode($appointment_number); ?>;


    function showDrugTable2(selected_drug_stocks) {
        let tr = '';
        let total_cost = 0;
        selected_drug_stocks.forEach((drug, index) => {

            let remove_btn = '';
            let edit_btn = '';
            if (drug.invoice_status == 0) {
                remove_btn = `<button class="btn btn-sm btn-danger" onclick="removeDrug(` + index + `)"><i class="fa fa-minus" ></i></button>`;
                edit_btn = `<br> <a href="#" class="text-danger" onclick="editPrescription(` + index + `)"><small>[Edit]</small></a>`;
            }
            tr += `
            <tr>
            <td>` + (index + 1) + `</td>
            <td>
                ` + drug.product_name + `<br><i><b>Drug Prescribed by: </b></i>` + drug.prepared_by + `
             
            </td>
            <td>` + drug.cash_price + `</td>
             <td>
                ` + drug.remarks + ` 
                
                ` + (drug.prescription != null ? ' <br><b>Remark: </b> ' + drug.prescription : '') + ` 
                ` + edit_btn + `
             </td>
            
            <td>` + remove_btn + `</td>
        </tr>
            `;
            total_cost += parseInt(drug.cash_price);

        })
        tr += `
            <tr >
            <td></td>
            <td><strong>Total: </strong></td>
            <td><strong>N` + total_cost + `</strong></td>
            <td></td>
            <td></td>
        </tr>
            `;
        $("#DrugTable2_tr").html(tr);
    }

    function filterDrugStocks() {


        if ($('#drug_form').val() == "") {
            filter_drug_stocks = drug_stocks
        } else {
            filter_drug_stocks = drug_stocks.filter(drug => drug.dosage == $('#drug_form').val())
        }

        showDrugTable2(selected_drug_stocks);

    }

    function editPrescription(index) {

        let obj = selected_drug_stocks[index];
        let remarks = prompt('Type new prescription:', obj.remarks)
        if (remarks) {
            if (obj.remarks != remarks) {
                toastr.info('Please wait..', 'Saving', {
                    timeOut: 500
                });
                $.ajax({
                    url: save_drug_url,
                    type: "POST",
                    data: {
                        action: 'editPrescription',
                        sn: obj.ap_services_id,
                        remarks: remarks,
                        appointment_number: null,
                        hospital_no: null,
                        drug: null
                    },
                    success: function(response) {
                        toastr.clear();
                        if (response.status == 200) {
                            toastr.success(response.message, 'Success', {
                                timeOut: 1000
                            })
                            selected_drug_stocks[index].remarks = remarks
                            filterDrugStocks()
                        } else {
                            toastr.error(response.message, 'Error', {
                                timeOut: 1000
                            })
                        }
                        console.log(response)
                    },
                    error: function(response) {
                        toastr.clear();
                        console.log(response)
                    }
                });
            }
        }
    }


    function removeDrug(index) {


        let obj = selected_drug_stocks[index];

        console.log(obj)

        let data = {
            appointment_number: appointment_number,
            hospital_no: hospital_no,
            drug: obj,
            action: 'remove'
        };


        var rr = confirm("Are you sure you want to Delete this Drug?");
        if (rr === true) {

            toastr.info('Removing, Please wait..', 'Removing', {
                timeOut: 500
            })
            filterDrugStocks()
            $.ajax({
                url: save_drug_url,
                type: "POST",
                data: data,
                success: function(response) {
                    if (response.status == 200) {
                        toastr.success(' Removed successfully..', 'Success', {
                            timeOut: 5000
                        })
                        filter_drug_stocks.push(obj)
                        selected_drug_stocks.splice(index, 1)
                        filterDrugStocks()
                        console.log(response)
                    } else {
                        toastr.error(response.message, 'Error', {
                            timeOut: 5000
                        })
                    }



                },
                error: function(response) {
                    console.log(response)
                }
            });

        }
    }



    var current_medication_group_id = null;

    function openAdd_medication_to_group_modal(group_id, modal) {
        current_medication_group_id = group_id;
        $('#' + modal).modal('show');
    }

    function closeModal(id) {
        $('#' + id).modal('hide');
    }


    $(document).on('change', '.represcribe-checkbox', function() {

        $.ajax({
            url: "fetch_set.php",
            method: "POST",
            data: {
                hospital_no: window.hospital_no,
                appointment_number: window.appointment_number
            },
            success: function(data) {
                var jsonn = JSON.parse(data);

                if (jsonn["status"] == 0) {
                    toastr.error('Empty Consultation Notes/No Current Appointment!', 'Empty Notes', {
                        timeOut: 5000
                    });
                    exit;
                } else {

                    var numberOfChecked = $('.represcribe-checkbox:checked').length;
                    if (numberOfChecked > 0) {
                        $('#represcribe-btn').slideDown('fast');
                    } else {
                        $('#represcribe-btn').slideUp('fast');
                    }
                }
            }

        });
    });



    function represcribe() {
        var selected = [];
        $('.represcribe-checkbox').each(function() {
            if ($(this).is(":checked")) {
                selected.push($(this).val());
            }
        });

        if (selected != []) {
            toastr.info('Please wait...', '', {
                timeOut: 1000
            })
            console.log(selected)
            $.ajax({
                url: medication_controller_url,
                type: "POST",
                data: {
                    hospital_no: window.hospital_no,
                    appointment_number: window.appointment_number,
                    selected: selected,
                    represcribe: true
                },
                success: function(response) {
                    console.log(response)
                    $('#represcribe-btn').slideUp('fast');
                    if (response.status == 200) {
                        toastr.success(response.message, 'Success', {
                            timeOut: 1000
                        })
                        if (response.data != []) {
                            window.selected_drug_stocks = response.data;
                            filterDrugStocks()
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


    $(document).on('keyup', '#search-for-drug-input', function() {

        if (prescription_format == 'standard_format') {
            filterTbleFx('search-for-drug-input', 'med-datatale');
        } else {

        }

    });

    function getFormattedDate(date) {
        let year = date.getFullYear();
        let month = (1 + date.getMonth()).toString().padStart(2, '0');
        let day = date.getDate().toString().padStart(2, '0');

        return day + '-' + month + '-' + year;
    }

    $(document).on('change', '#use_standard_format', function() {
        let use_standard_format = $(this)[0].checked

        if (use_standard_format) {
            prescription_format = 'standard_format';
        } else {
            prescription_format = 'loose_format';
        }
        return switch_format()
    });

    function switch_format() {
        $('#standard_format').hide('fast');
        $('#loose_format').hide('fast');
        $('#' + prescription_format).show('fast');
    }

    switch_format();

    var type_ahead_drugs = [];

    var adm_status = "<?php echo $adm_status; ?>";
    var med_fetch_url = "<?php echo $med_fetch_url; ?>";

    $(document).on('keyup', '.typeahead_elem', function() {

        let selected_drug_string = $('#typeahead1').val();
        let data = {
            appointment_number: window.appointment_number,
            hospital_no: window.hospital_no
        };

        const button = document.querySelector('.addSelectedDrugBtn');

        $.ajax({
            url: med_fetch_url, //"fetch_set.php",  
            method: "POST",
            data: {
                appointment_number: appointment_number,
                hospital_no: hospital_no
            },
            success: function(data) {

                var jsonn = JSON.parse(data);

                if (adm_status == 3) { // On-admission
                    jsonn['status'] = 1;
                }

                console.log(jsonn);
                if (selected_drug_string != '' && jsonn['status'] == 1) {

                    button.disabled = false;
                    $('#addSelectedDrugBtn').attr('disabled', false);


                } else {
                    if (jsonn['status'] != 1) {
                        button.disabled = true;
                        // $('#addSelectedDrugBtn').attr('disabled', 'disabled');
                        // toastr.error('No Records', 'Error', {
                        toastr.error('- Save Consultation Notes / <br>- No Current Appointment', 'Error', {
                            timeOut: 3000
                        })
                    }

                }
            },
            error: function(err) {
                alert(err);
            }
        });


    });


    var save_drug_url = "<?php echo $save_drug_url; ?>";

    function addSelectedDrug() {
        let sn = $('#drug_search_typeahead_id').val();
        let product_name = $('#drug_search_typeahead_product_name').val();
        let hosp_price = $('#drug_search_typeahead_hosp_price').val();
        let cash_price = $('#drug_search_typeahead_cash_price').val();
        let nhis_price = $('#drug_search_typeahead_nhis_price').val();
        let selected_drug_prescription = ''; //$('#typeahead2').val();
        let med_frequency = $('#med_frequency').val();
        let med_dosage = $('#med_dosage').val();
        let med_dosage_unit = $('#med_dosage_unit').val();
        let med_duration = $('#med_duration').val();
        let med_duration_unit = $('#med_duration_unit').val();
        let med_remark = $('#med_remark').val();
        let qty = $('#qty').val();

        if (med_dosage == '' || med_dosage_unit == '' || med_frequency == '' || med_duration == '' || med_duration_unit == '') {
            return toastr.error('Prescriptiosn is not complete', 'Error', {
                timeOut: 2000
            })
        }
        selected_drug_prescription = ` ${med_dosage}${med_dosage_unit}  ${med_frequency}  ${med_duration}${med_duration_unit}`;
        let obj = {};

        if (sn != '' && selected_drug_prescription != "") {
            obj = {
                sn,
                product_name,
                hosp_price,
                cash_price,
                nhis_price
            };


            obj.med_frequency = ""
            obj.med_dosage = ""
            obj.med_dosage_unit = ""
            obj.med_duration = ""
            obj.med_duration_unit = ""
            obj.remarks = selected_drug_prescription
            obj.med_remark = med_remark
            obj.qty = qty
            obj.invoice_status = 0
            // this.selected_drug_stocks.unshift(obj)

            ///alert(save_drug_url);

            let data = {
                appointment_number: window.appointment_number,
                hospital_no: window.hospital_no,
                drug: obj,
                action: 'add'
            };
            toastr.info('Adding,please wait..', 'Requesting', {
                timeOut: 50000
            })
            $.ajax({
                url: save_drug_url, //'controllers/_saveMedication.php',
                type: "POST",
                data: data,
                success: function(response) {

                    toastr.clear();
                    if (response.status == 200) {
                        toastr.success(response.message, 'Success', {
                            timeOut: 1000
                        })
                        $('#drug_search_typeahead').val("");
                        $('#drug_search_typeahead_id').val("");
                        $('#med_frequency').val('');
                        $('#med_dosage').val('');
                        $('#med_dosage_unit').val('');
                        $('#med_duration').val('');
                        $('#med_duration_unit').val('');
                        $('#med_remark').val('');
                        $('#drug_search_typeahead_d').val('');
                        $('#qty').val('');


                        filterDrugStocks()
                        openMedicationModal(window.hospital_no, window.appointment_number)
                    } else {
                        toastr.error(response.message, 'Error', {
                            timeOut: 1000
                        })
                    }
                    console.log(response)
                },
                error: function(response) {
                    toastr.clear();
                    console.log(response)
                }
            });
        }
    }

    function addSelectedDrugSimple() {
        let sn = $('#drug_search_typeahead_id').val();
        let product_name = $('#drug_search_typeahead_product_name').val();
        let hosp_price = $('#drug_search_typeahead_hosp_price').val();
        let cash_price = $('#drug_search_typeahead_cash_price').val();
        let nhis_price = $('#drug_search_typeahead_nhis_price').val();
        let selected_drug_prescription = ''; //$('#typeahead2').val();
        let med_dosage = $('#med_dosage').val();
        let med_remark = $('#med_remark').val();
        let qty = $('#qty').val();

        if (med_dosage == '') {
            return toastr.error('Prescriptiosn is not complete', 'Error', {
                timeOut: 2000
            })
        }
        selected_drug_prescription = ` ${med_dosage}`;
        let obj = {};

        if (sn != '' && selected_drug_prescription != "") {
            obj = {
                sn,
                product_name,
                hosp_price,
                cash_price,
                nhis_price
            };


            obj.med_frequency = ""
            obj.med_dosage = ""
            obj.med_dosage_unit = ""
            obj.med_duration = ""
            obj.med_duration_unit = ""
            obj.remarks = selected_drug_prescription
            obj.med_remark = med_remark
            obj.qty = qty
            obj.invoice_status = 0
            // this.selected_drug_stocks.unshift(obj)

            let data = {
                appointment_number: window.appointment_number,
                hospital_no: window.hospital_no,
                drug: obj,
                action: 'add'
            };
            toastr.info('Adding,please wait..', 'Requesting', {
                timeOut: 50000
            })
            $.ajax({
                url: save_drug_url, //'controllers/_saveMedication.php',
                type: "POST",
                data: data,
                success: function(response) {
                    toastr.clear();
                    if (response.status == 200) {
                        toastr.success(response.message, 'Success', {
                            timeOut: 1000
                        })
                        $('#drug_search_typeahead').val("");
                        $('#drug_search_typeahead_id').val("");
                        $('#med_dosage').val('');
                        $('#med_remark').val('');
                        $('#qty').val('');
                        $('#drug_search_typeahead_d').val('');

                        filterDrugStocks()
                        openMedicationModal(window.hospital_no, window.appointment_number)
                    } else {
                        toastr.error(response.message, 'Error', {
                            timeOut: 1000
                        })
                    }
                    console.log(response)
                },
                error: function(response) {
                    toastr.clear();
                    console.log(response)
                }
            });
        }
    }
</script>