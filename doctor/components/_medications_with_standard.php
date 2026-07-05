<?php
$drug_stocks = [];
$drug_stocks_list = [];
$selected_drug_stocks = [];
?>
<script>
    var prescription_format = 'loose_format';
</script>
<div id="Medication_vue_instance">
    <div class="alert alert-danger" id="drug_loading_div" style="display:none">Loading drugs, please wait...</div>
    <p class="text-left"><input type="checkbox" name="use_standard_format" id="use_standard_format"  style="width:20px; height:20px"><b> Use Standard Format</b></p>
    <div class="row">
        <div class="col-md-7">
            <div style="display:none;max-height: 500px;overflow:auto" id="standard_format">
                <div class="row">
                    <div class="col-md-6" style="color:#F00">
                        <label for="search-for-drug-input">Search:</label>
                        <input style="color:#000" type="text" name="search-for-drug-input" id="search-for-drug-input" class="form-control" placeholder="Search by drug name or brand">

                        <!-- onkeyup="searchForDrug('search-for-drug-input')" -->
                    </div>
                    <div class="col-md-6">
                        <div class="form_sep">
                            <label for="reg_select" class="">Filter By Form</label>
                            <select name="drug_form" class="form-control" id="drug_form" onchange="filterDrugStocks()">
                                <option selected="selected" value="" style="font-size:14px"></option>
                                <?php
                                foreach ($DrugStock->distinctDosageForm() as $key => $dosage_arr) {
                                    if (!empty($dosage_arr->dosage)) {
                                ?>
                                        <option value="<?= $dosage_arr->dosage; ?>"><?= $dosage_arr->dosage; ?></option>
                                <?php
                                    }
                                }
                                ?>
                            </select>
                        </div>

                    </div>
                </div>
                <hr>
                <table class="table table-striped table-bordered table-hover med-datatale" id="med-datatale">
                    <thead>
                        <tr>
                            <th data-toggle="true" width="1%">#</th>
                            <th data-toggle="true" width="25%"><strong style="color:#000"><i>Select Drug Name</i></strong></th>
                            <th data-toggle="true" class="text-center">Dosage</th>
                            <th data-toggle="true" width="80px">Frequency</th>
                            <th data-toggle="true">Duration</th>
                            <th data-toggle="true"></th>
                        </tr>
                    </thead>
                    <tbody id="DrugTable1_tr">

                    </tbody>
                </table>
            </div>
            <div id="loose_format">

                <div>
                    <label for="reg_input_no" class="req">Search for Drug</label>
                    <input type="text" style="padding: 20px" class="typeahead form-control typeahead_elem " data-provide="typeahead" id="typeahead1" placeholder="Enter First 3 Letters of  Drug or Brand Name" autocomplete="off">
                </div>
                <div>
                    <label for="reg_input_no" class="req">Prescription</label>

                    <input type="text" style="padding: 20px" class="typeahead form-control typeahead_elem" data-provide="typeahead" id="typeahead2" placeholder="Enter Prescription here" autocomplete="off">
                    <br>
                    <button class="btn btn-primary" onclick="addSelectedDrug()" id="addSelectedDrugBtn" disabled="disabled">Add Drug <i class="fa fa-plus"></i></button>
                </div>


            </div>
        </div>
        <div class="col-md-5">

            <div style="max-height:270px;overflow-y:auto">

                <table class="table table-striped table-bordered table-hover ">
                    <thead>
                        <tr>
                            <th data-toggle="true" width="1%">#</th>
                            <th data-toggle="true" width=""><strong style="color:000"><i>Selected Drug Names</i></strong></th>
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

<script>
    var filter_drug_stocks = [];
    var drug_form = "";
    var selected_drug_stocks = <?php echo json_encode($selected_drug_stocks); ?>;
    var drug_stocks = <?php echo json_encode($drug_stocks); ?>;
    var hospital_no = <?php echo json_encode($hospital_no); ?>;
    var appointment_number = <?php echo json_encode($appointment_number); ?>;

    function refreshDataTable(class_name) {

        $('.' + class_name).dataTable({
            responsive: true,
            "dom": 'T<"clear">lfrtip',
            "tableTools": {
                "sSwfPath": "js/plugins/dataTables/swf/copy_csv_xls_pdf.swf"
            }
        });
    }

    function showDrugTable1(filter_drug_stocks) {
        let tr = '';
        filter_drug_stocks.forEach((drug, index) => {

            tr += `
            <tr>
                        <td>` + (index + 1) + `</td>
                        <td>` + drug.product_name + `</td>
                        <td> 
                             <div class="input-group">
                                
                                <input type="number"  class="form-control" placeholder="No. " name="med_dosage" id="med_dosage_` + drug.sn + `"  style="width:100px">
                               
                                <select name="med_dosage_unit" id="med_dosage_unit_` + drug.sn + `" class="form-control" style="width:100px">
                                            <option value="">Select Unit</option>
                                            <option value="mg">mg</option>
                                            <option value="g">g</option>
                                            <option value="cl">cl</option>
                                            <option value="iu">iu</option>
                                        </select>
                                </div>
                        </td>
                        <td>
                            <select class="form-control" name="med_frequency" id="med_frequency_` + drug.sn + `" class="" style="margin-left: 5px;width:70px;padding-bottom:5px">

                                <option value="OD">OD</option>
                                <optio value="BD">BD</optio
                                <optio value="BID">BID</optio
                                <option value="TID">TID</option>
                                <option value="NOCTE">NOCTE</option>
                                <option value="QID">QID</option>
                                <option value="PRN">PRN</option>
                                <option value="4 hourly">4 hourly</option>
                                <option value="8 hourly">6 hourly</option>
                                <option value="12 hourly">12 hourly</option>
                            </select>
                        </td>
                        <td>
                     

                                <div class="input-group">
                                
                                <input type="number" class="form-control" placeholder="No." name="med_duration"  id="med_duration_` + drug.sn + `" name="no_of_days" style="width:100px">
                                <select name="med_duration_unit" id="med_duration_unit_` + drug.sn + `" class="form-control" style="width:100px">
                                            <option value="">Select Unit</option>
                                            <option value="hours">hours</option>
                                            <option value="days">days</option>
                                            <option value="weeks">Weeks</option>
                                            <option value="months">months</option>
                                        </select>
                                </div>
                        </td>
                        <td><button class="btn btn-sm btn-success" onclick="addDrug(` + drug.sn + `)"><i class="fa fa-plus"></i></button></td>
                    </tr>
            `;
        })


        $("#DrugTable1_tr").html(tr);
        //refreshDataTable('med-dataTable')
    }

    function showDrugTable2(selected_drug_stocks) {
        let tr = '';
        let total_cost = 0;
        selected_drug_stocks.forEach((drug, index) => {
            
            let remove_btn = '';
            if(drug.invoice_status == 0){
                remove_btn = `<button class="btn btn-sm btn-danger"><i class="fa fa-minus" onclick="removeDrug(` + index + `)"></i></button>`;
            }
            tr += `
            <tr>
            <td>` + (index + 1) + `</td>
            <td>
                ` + drug.product_name + `
             
            </td>
            <td>` + drug.cash_price + `</td>
            <td>` + drug.remarks + `</td>
            
            <td>`+remove_btn+`</td>
        </tr>
            `;
            total_cost += drug.cash_price;
         
        })
        tr += `
            <tr >
            <td></td>
            <td>Total => </td>
            <td>N`+total_cost+`</td>
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


        // filter_drug_stocks.forEach((element, index) => {
        //     selected_drug_stocks.forEach(elem => {
        //         if (elem.sn == element.sn) {
        //             filter_drug_stocks.splice(index, 1);
        //         }
        //     })
        // })
        showDrugTable1(filter_drug_stocks);
        showDrugTable2(selected_drug_stocks);

        //  refreshDataTable('dataTables-example');
        //  refreshDataTable('med_datatale');

    }

    function addDrug(sn) {
      

        let obj = null;
        filter_drug_stocks.forEach((drug, index) => {
            if (drug.sn == sn) {
                obj = drug
            }
        });

        let med_frequency = $("#med_frequency_" + obj.sn).val();
        let med_dosage = $("#med_dosage_" + obj.sn).val();
        let med_dosage_unit = $("#med_dosage_unit_" + obj.sn).val();
        let med_duration = $("#med_duration_" + obj.sn).val();
        let med_duration_unit = $("#med_duration_unit_" + obj.sn).val();

        med_dosage = med_dosage < 1 ? 1 : med_dosage;
        med_duration = med_duration < 1 ? 1 : med_duration;

        if (med_dosage == "") {
            return alert("Select Dosage")
        }

        if (med_dosage_unit == "") {
            return alert("Select Dosage Unit")
        }



        if (med_frequency == "") {
            return alert("Select Frequency")
        }

        if (med_duration == "") {
            return alert("Select duration")
        }

        if (med_duration_unit == "") {
            return alert("Select duration unit")
        }


        let added = false;
        selected_drug_stocks.forEach((elem, index) => {
            if (elem.sn == obj.sn) {
                added = true
            }
        })

        if (added) {
            return alert('Selected already')
        } else {
            obj.med_frequency = med_frequency
            obj.med_dosage = med_dosage
            obj.med_dosage_unit = med_dosage_unit
            obj.med_duration = med_duration
            obj.med_duration_unit = med_duration_unit
            obj.remarks = med_dosage+''+med_dosage_unit+' '+med_frequency+' '+med_duration+''+med_duration_unit;
            obj.invoice_status = 0

            this.selected_drug_stocks.push(obj)
            $("#med_dosage_" + obj.sn).val("")
            $("#med_duration_" + obj.sn).val("")
        }

        let data = {
            appointment_number: appointment_number,
            hospital_no: hospital_no,
            drug: obj,
            action: 'add'
        };

        toastr.info('Saving,please wait..', 'Saving', {
            timeOut: 500
        })
        filterDrugStocks()

        $.ajax({
            url: 'controllers/_saveMedication.php',
            type: "POST",
            data: data,
            success: function(response) {
                console.log(response)
            },
            error: function(response) {
                console.log(response)
            }
        });
         checkMgtNotes(hospital_no,appointment_number);
    }



    function removeDrug(index) {

        
        let obj = selected_drug_stocks[index];
        filter_drug_stocks.push(obj)
        selected_drug_stocks.splice(index, 1)

        let data = {
            appointment_number: appointment_number,
            hospital_no: hospital_no,
            drug: obj,
            action: 'remove'
        };
        toastr.info('Removing,please wait..', 'Removing', {
            timeOut: 500
        })
        filterDrugStocks()
        $.ajax({
            url: 'controllers/_saveMedication.php',
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
         checkMgtNotes(hospital_no,appointment_number);
    }

    // function loadDosageForm(dosage, sn) {

    //     let opt = '<option value=""> Choose Times</option>';
    //     dosage_forms.forEach(elem => {
    //         if (elem.dosage == dosage) {
    //             opt += '<option value="' + elem.value + '">' + elem.value + '</option>';
    //         }

    //     })

    //     $('#med_times_' + sn).html(opt);
    // }






    var current_medication_group_id = null;

    function openAdd_medication_to_group_modal(group_id, modal) {
        current_medication_group_id = group_id;
        $('#' + modal).modal('show');
    }


    function addDrugToGroup() {
        let drug_ = $('#drug_').val();
        let dosage = $('#dosage').val();
        let dosage_unit = $('#dosage_unit').val();
        let frequency = $('#frequency').val();
        let duration = $('#duration').val();
        let duration_unit = $('#duration_unit').val();

        if (drug_ == '') {
            return alert('Select Drug')
        }

        if (dosage == '') {
            return alert('Enter Number of dosage')
        }

        if (frequency == '') {
            return alert('Select frequency ')
        }
        if (dosage_unit == '') {
            return alert('Select unit of dosage')
        }

        if (duration == '') {
            return alert('Enter Number of duration')
        }

        if (duration_unit == '') {
            return alert('Select unit of duration')
        }

        let prescription = dosage+''+dosage_unit+' '+frequency+' '+duration+''+duration_unit;
        $.ajax({
            url: 'controllers/management.php',
            type: "POST",
            data: {
                group_id: current_medication_group_id,
                drug_id: drug_,
                dosage: dosage,
                dosage_unit: dosage_unit,
                frequency: frequency,
                duration: duration,
                duration_unit: duration_unit,
                prescription: prescription,
                addDrugToGroup: true
            },
            success: function(response) {
                if (response.status == 200) {
                    toastr.success(response.message, 'Success', {
                        timeOut: 1000
                    })
                    load_add_to_group('drug')
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


    function pickDrugFromGroup(group_id, cat) {

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
                    pickDrugFromGroup: true
                },
                success: function(response) {
                    if (response.status == 200) {
                        toastr.success(response.message, 'Success', {
                            timeOut: 1000
                        })
                        selected_drug_stocks = response.data;
                        filterDrugStocks();
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




    $(document).ready(() => {

        // setTimeout(() => {
        //     $(".chosen-select").chosen({
        //         allow_single_deselect: true,
        //         enable_search_threshold: 10,
        //         no_results_text: 'Oops, nothing found!',
        //         width: "100%"
        //     });
        //     $('.chosen-drop').css({
        //         "width": "100%",
        //         "white-space": "nowrap"
        //     })

        // }, 3000);


    });

    $(document).on('change', '.represcribe-checkbox', function() {

        var numberOfChecked = $('.represcribe-checkbox:checked').length;

        if (numberOfChecked > 0) {

            $('#represcribe-btn').slideDown('fast');
        } else {
            $('#represcribe-btn').slideUp('fast');
        }


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
                url: 'controllers/_medication.php',
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
    
    // drug_stocks.forEach(elem => {
    //     let expire_date = elem.expire_date;

        
    //     let exp_date = '';
    //     if(expire_date){
    //         let exp_date_ar = expire_date.split('-');
        
    //         exp_date = exp_date_ar[2]+'-'+exp_date_ar[1]+'-'+exp_date_ar[0];
    //     }
    //     type_ahead_drugs.push({
    //         name: elem.product_name + ' - (Exp: ' + exp_date + ') - (Qty: ' + elem.qty + ') - (Price:  N' + elem.cash_price + ')',
    //         id: elem.sn

    //     })
    // });

    // var prescription_list = <?php //echo json_encode($prescription_list); ?>;

    // var $input = $("#typeahead1");
    // var $input2 = $("#typeahead2");
    // $input.typeahead({
    //     source: type_ahead_drugs,
    //     autoSelect: true
    // });

    // $input2.typeahead({
    //     source: prescription_list,
    //     autoSelect: true
    // });

    $(document).on('keyup', '.typeahead_elem', function() {
        let selected_drug_string = $('#typeahead1').val();
        let selected_drug_prescription = $('#typeahead2').val();

        if (selected_drug_string != '' && selected_drug_prescription != "") {
            $('#addSelectedDrugBtn').attr('disabled', false);
        } else {
            $('#addSelectedDrugBtn').attr('disabled', 'disabled');
        }

    });

    function addSelectedDrug() {
        let selected_drug_string = $('#typeahead1').val();
        let selected_drug_prescription = $('#typeahead2').val();

        let obj = null;
     

        if (selected_drug_string != '' && selected_drug_prescription != "") {
            
            type_ahead_drugs.forEach((type_ahead_drug) => {
              
                if (type_ahead_drug.name == selected_drug_string) {
                    obj = type_ahead_drug
                    // drug_stocks.forEach((drug, index) => {
                    //     if (drug.sn == type_ahead_drug.id) {
                    //         obj = drug
                    //     }
                    // });
                }
            });
            // console.log(obj)

            if(obj == null){
                return alert('Selected Drug is Already Prescrided.  Kindly Use the re-prescribe option')
            }else{
                obj.med_frequency = ""
            obj.med_dosage = ""
            obj.med_dosage_unit = ""
            obj.med_duration = ""
            obj.med_duration_unit = ""
            obj.remarks = selected_drug_prescription
            obj.invoice_status = 0
            this.selected_drug_stocks.push(obj)

            let data = {
                appointment_number: window.appointment_number,
                hospital_no: window.hospital_no,
                drug: obj,
                action: 'add'
            };
            toastr.info('Adding,please wait..', 'Requesting', {
                timeOut: 50000
            })

alert();

            $.ajax({
                url: 'controllers/_saveMedication.php',
                type: "POST",
                data: data,
                success: function(response) {
                    toastr.clear();
                    if (response.status == 200) {
                        toastr.success(response.message, 'Success', {
                            timeOut: 1000
                        })
                        $('#typeahead1').val("");
                        $('#typeahead2').val("");
                        filterDrugStocks()
                    } else {
                        toastr.error(response.message, 'Success', {
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

        checkMgtNotes(hospital_no,appointment_number);
    }

     
    //filterTbleFx('search-for-drug-input', 'med-datatale')"
</script>

