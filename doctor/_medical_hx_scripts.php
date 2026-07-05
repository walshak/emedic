<script>
    var med_hx_url = "<?php echo $med_hx_url; ?>";
    $(document).ready(function() {
        jQuery.expr[':'].contains = function(a, i, m) {
            return jQuery(a).text().toUpperCase()
                .indexOf(m[3].toUpperCase()) >= 0;
        };

        setTimeout(function() {

            var records_per_page = document.getElementById("records_per_page").value;

            $.ajax({
                url: med_hx_url,
                method: "POST",
                data: {
                    loadMedicalHx: true,
                    hospital_no: "<?php echo $hospital_no; ?>",
                    records_per_page: records_per_page,
                    page_num: 1
                },
                success: function(response) {
                    $("#medical-history-content-area").html(response);

                },
                error: function(err) {
                    console.log(err)
                }
            });
        }, 500)

    })


    function records_per_page_() {

        toastr.info('Please Wait .... ', 'Processing', {
            timeOut: 500
        })
        var records_per_page = document.getElementById("records_per_page").value;
        var documentation = document.getElementById("documentation_type").value;
        var doctor_names = document.getElementById("doctor_names").value;
        var search_anything = document.getElementById("search_anything").value;
        var end_date = document.getElementById("end_date").value;
        var start_date = document.getElementById("start_date").value;

        $.ajax({
            url: med_hx_url,
            method: "POST",
            data: {
                loadMedicalHx: true,
                hospital_no: "<?php echo $hospital_no; ?>",
                records_per_page: records_per_page,
                documentation: documentation,
                search_anything: search_anything,
                end_date: end_date,
                start_date: start_date,
                doctor_names: doctor_names
            },
            success: function(response) {
                $("#medical-history-content-area").html(response);


                $('html, body').animate({
                    scrollTop: $('#scroll_here_hx').offset().top - 100
                }, 500);



            },
            error: function(err) {
                console.log(err)
            }
        });

    }


    function documentation() {
        toastr.info('Please Wait .... ', 'Processing', {
            timeOut: 500
        })
        var records_per_page = document.getElementById("records_per_page").value;
        var documentation = document.getElementById("documentation_type").value;
        var doctor_names = document.getElementById("doctor_names").value;
        var search_anything = document.getElementById("search_anything").value;
        var end_date = document.getElementById("end_date").value;
        var start_date = document.getElementById("start_date").value;

        $.ajax({
            url: med_hx_url,
            method: "POST",
            data: {
                loadMedicalHx: true,
                hospital_no: "<?php echo $hospital_no; ?>",
                records_per_page: records_per_page,
                documentation: documentation,
                search_anything: search_anything,
                end_date: end_date,
                start_date: start_date,
                doctor_names: doctor_names
            },
            success: function(response) {
                $("#medical-history-content-area").html(response);


                $('html, body').animate({
                    scrollTop: $('#scroll_here_hx').offset().top - 100
                }, 500);


            },
            error: function(err) {
                console.log(err)
            }
        });
    }

    function load_more_mx_hx(load_more_mx_hx) {
        toastr.info('Please Wait .... ', 'Processing', {
            timeOut: 500
        })
        var records_per_page = document.getElementById("records_per_page").value;
        var documentation = document.getElementById("documentation_type").value;
        var doctor_names = document.getElementById("doctor_names").value;
        var search_anything = document.getElementById("search_anything").value;
        var end_date = document.getElementById("end_date").value;
        var start_date = document.getElementById("start_date").value;

        $.ajax({
            url: med_hx_url,
            method: "POST",
            data: {
                loadMedicalHx: true,
                hospital_no: "<?php echo $hospital_no; ?>",
                records_per_page: records_per_page,
                page_num: load_more_mx_hx,
                documentation: documentation,
                doctor_names: doctor_names,
                search_anything: search_anything,
                end_date: end_date,
                start_date: start_date
            },
            success: function(response) {
                $("#medical-history-content-area").html(response);

                $('html, body').animate({
                    scrollTop: $('#scroll_here_hx').offset().top - 100
                }, 500);


            },
            error: function(err) {
                console.log(err)
            }
        });

    }


    $(document).ready(function() {
        $.ajax({
            url: 'sort.php',
            type: 'POST',
            data: {
                hospital_no: "<?php echo $hospital_no; ?>",
            },
            dataType: 'json',
            success: function(data) {
                const select = $('#documentation_type');
                select.find('option:gt(0)').remove(); // remove all except 'All'

                $.each(data, function(val, label) {
                    select.append(new Option(label, val));
                });
            },
            error: function() {
                console.error("Failed to load note types.");
            }
        });
    });



    function med_hx_refresh() {




        toastr.info('Please Wait .... ', 'Processing', {
            timeOut: 2000
        })
        $.ajax({
            url: "_medication_hx.php",
            method: "POST",
            data: {
                loadMedicalHx: true,
                hospital_no: "<?php echo $hospital_no; ?>"
            },
            success: function(response) {
                toastr.clear();
                $("#medical-history-content-area").html(response);

            },
            error: function(err) {
                console.log(err)
            }
        });

    }


    $(document).on('change', '#medication_hx_type', function() {
        if ($(this).val() == '') {
            $('.med-hx-ibox-content').slideDown('fast');
        } else {
            $('.med-hx-ibox-content').slideUp('fast');
            $('.med-hx-ibox-content-' + $(this).val()).slideDown('fast');
        }

    });

    $('#search_med_hx_input').keyup(function() {
        var valThis = this.value;
        var length = this.value.length;
        $('.med-hx-ibox-content').show();
        if (length < 4) {
            $('.med-hx-ibox-content').show();
            return 0;
        } else {
            $('.med-hx-ibox-content').hide();
            $(".med-hx-ibox-content").each(function() {
                var content_ = $(this).html();
                var remove1 = content_.replace(new RegExp('<mark>', 'ig'), '');
                var remove2 = remove1.replace(new RegExp('</mark>', 'ig'), '');
                $(this).html(remove2);

            })

            $(".med-hx-ibox-content:contains('" + valThis + "')").each(function() {
                var html = $(this).html();
                var textL = html.toLowerCase();
                var position = textL.indexOf(valThis.toLowerCase());

                if (position !== -1) {
                    $(this).show()
                    var matches = html.substring(position, (valThis.length + position));
                    console.log(matches)
                    var regex = new RegExp(matches, 'ig');
                    var highlighted = html.replace(regex, '<mark>' + matches + '</mark>');


                    $(this).html(highlighted);
                }
            });

        }

    });


    function nurse_progress_hx(appointment_number, hospital_no) {
        toastr.info('Please Wait .... ', 'Processing', {
            timeOut: 1000
        })

        $.ajax({
            url: "../inc/_progress_notes_hx.php",
            method: "POST",
            data: {
                loadProgressNote: true,
                appointment_number: appointment_number,
                hospital_no: "<?php echo $hospital_no; ?>"
            },
            success: function(response) {
                $("#_progress_notes_hx").html(response);

                $('html, body').animate({
                    scrollTop: $('#_progress_notes_hx').offset().top - 100
                }, 500);

                toastr.clear();
            },
            error: function(err) {
                console.log(err)
            }
        });

    }

    function ns_report_page(appointment_number, page_num) {
        toastr.info('Please Wait .... ', 'Processing', {
            timeOut: 1000
        })
        $.ajax({
            url: "../inc/_progress_notes_hx.php",
            method: "POST",
            data: {
                loadProgressNote: true,
                page_num: page_num,
                appointment_number: appointment_number,
                hospital_no: "<?php echo $hospital_no; ?>"
            },
            success: function(response) {

                $("#_progress_notes_hx").html(response);

                $('html, body').animate({
                    scrollTop: $('#_progress_notes_hx').offset().top - 100
                }, 500);

                toastr.clear();
            },
            error: function(err) {
                console.log(err)
            }
        });

    }


    function general_view(general_view, page_num) {
        toastr.info('Please Wait .... ', 'Processing', {
            timeOut: 5000
        })

        document.getElementById("switch_hx").innerHTML = 'please wait ... ';
        document.getElementById("switch_hx").disabled = true;
        $.ajax({
            url: med_hx_url,
            method: "POST",
            data: {
                loadMedicalHx_v2: true,
                general_view: general_view,
                page_num: page_num,
                hospital_no: "<?php echo $hospital_no; ?>"
            },
            success: function(response) {
                $("#medical-history-content-area").html(response);
                document.getElementById("switch_hx").innerHTML = 'All Notes';
                document.getElementById("switch_hx").disabled = false;

                toastr.clear();
            },
            error: function(err) {
                console.log(err)
            }
        });
    }


    function load_vistamedic_notes(notes_type) {
        //    toastr.info('Please Wait .... ', 'Processing', {
        //        timeOut: 5000
        //    })

        var old_hostpital_no = document.getElementById("old_hostpital_no").value;

        document.getElementById("switch_hx3").innerHTML = 'please wait ... ';
        document.getElementById("switch_hx3").disabled = true;


        $.ajax({
            url: med_hx_url,
            method: "POST",
            data: {
                load_vistamedic_notes: true,
                hospital_no: old_hostpital_no,
                notes_type: notes_type
            },
            success: function(response) {

                $("#medical-history-content-area").html(response);
                document.getElementById("switch_hx3").innerHTML = 'OLD EMR NOTES';
                document.getElementById("switch_hx3").disabled = false;

                toastr.clear();
            },
            error: function(err) {
                console.log(err)
            }
        });
    }


    function general_view2(major, page_num) {
        var title = '';
        toastr.info('Please Wait .... ', 'Processing', {
            timeOut: 5000
        })
        document.getElementById(major).innerHTML = 'please wait ... ';
        document.getElementById(major).disabled = true;

        if (major == 'serv_review') {
            title = "Doctor's Review";
        } else if (major == 'nurse_report') {
            title = "View Nursing/Midwifery Reports";
        } else if (major == 'procedure') {
            title = "Procedure Reports";
        } else if (major == 'plan') {
            title = "Doctor's Plan";
        } else if (major == 'ward_round') {
            title = "Doctor's Ward Rounds";
        } else if (major == 'physio') {
            title = "Physiotherapy Notes";
        }

        $.ajax({
            url: med_hx_url,
            method: "POST",
            data: {
                loadMedicalHx_v3: true,
                page_num: page_num,
                search_catera: major,
                hospital_no: "<?php echo $hospital_no; ?>"
            },
            success: function(response) {

                $("#medical-history-content-area").html(response);
                document.getElementById(major).innerHTML = title;
                document.getElementById(major).disabled = false;
                toastr.clear();
            },
            error: function(err) {
                console.log(err)
            }
        });
    }

    $.ajax({
        url: med_hx_url,
        method: "POST",
        data: {
            loadMedicalHx: true,
            hospital_no: "<?php echo $hospital_no; ?>"
        },
        success: function(response) {
            $("#medical-history-content-area").html(response);

        },
        error: function(err) {
            console.log(err)
        }
    });
</script>