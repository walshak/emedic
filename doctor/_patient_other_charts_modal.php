
<div class = 'modal inmodal fade' id = 'patient-other-charts-modal' tabindex = '-1' role = 'dialog' aria-hidden = 'true' data-keyboard = 'false' >
    <div class = 'modal-dialog modal-lg' style = 'width: 900px;'>
        <div class = 'modal-content'>
            <div class = 'modal-header'>
            <button type = 'button' class = 'close' data-dismiss = 'modal' aria-hidden = 'true'>×</button>
            <h4 class = 'modal-title' id = ''>Patient Charts</h4>
            </div>
            <div class = 'modal-body' style = 'min-height: 300px'>

                <?php include_once('_patient_other_charts.php');?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>