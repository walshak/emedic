<style>
    /* Custom CSS */
    @media (max-width: 576px) { /* Extra small devices (portrait phones) */
      .custom-padding {
        padding: 10px;
      }
    }

    @media (min-width: 577px) and (max-width: 768px) { /* Small devices (landscape phones) */
      .custom-padding {
        padding: 20px;
      }
    }

    @media (min-width: 769px) and (max-width: 992px) { /* Medium devices (tablets) */
      .custom-padding {
        padding: 30px;
      }
    }

    @media (min-width: 993px) { /* Large devices (desktops) */
      .custom-padding {
        padding: 40px;
      }
    }
  </style>
    <div class="ibox float-e-margins">
            <div class="ibox-title">
    
                <h5 class="pull-right">
            <a href="<?= $folder; ?>" class="text-danger "><strong>[ Back ]</strong></a>
                </h5>
            </div>
            <div class="ibox-content">
            <div class="row">
                
                        <div class="col-lg-4">
                            <?php include_once('_gen_services_hx.php');?>
                            
                            </div>
                            <div class="col-lg-8 custom-padding ">
                                <div class="" style="padding: 20px; border: 2px dashed #08c">
                                    <div class="form-group">
                                        <div></div>
                                        <div class="row">
                                            <div class="col-md-2">Search For Patient:</div>
                                            <div class="col-md-7">
                                            <div>
                                                <input type="text"  class="typeahead form-control "
                                                placeholder='Search for patient'
                                                data-provide="typeahead" id="typeahead_search_patient" placeholder="" autocomplete="off" required>
                                                <input type="hidden" name="consultant_id" id="typeahead_search_patient_hospital_no" required>
                                                <input type="hidden" name="consultant_id" id="typeahead_search_patient_id" required>
                                                <input type="hidden" name="consultant_name" id="typeahead_search_patient_name" required>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                               <button class="btn btn-sm btn-primary" id="openDocumentationForm">Open Documentation</button>
                                            </div>
                                        </div>
                                    </div>
                                    </div>
                            </div>
                    
                
            </div>
        </div>
    </div>
