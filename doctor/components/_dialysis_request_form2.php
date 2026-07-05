<form action="<?= $editFormAction; ?>" method="post">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="wow fadeRight animated light-card">


                <?php
                for ($i = 1; $i <= $number_of_session; $i++) {
                ?>
                    <div>
                        <h4 for="schedule_date_<?= $i; ?>"> <u>Session <?= $i; ?>: </u> Date Time:</h4>

                        <p><input type="datetime-local" id="schedule_date" min="<?= date('Y-m') . '-01'; ?>T08:30" max="<?= date('Y') + (1); ?>-01-30T16:30" name="schedule_date_<?= $i; ?>" value="<?= date('Y-m-d') . 'T' . date('h:i'); ?>" class="form-control"></p>
                    </div>
                    <br>
                <?php
                }

                ?>
                <hr>
                <h3 class="text-right"> Amount Payable: &#8358; <?= number_format($dialysis_amount, 2); ?></h3>
                <hr>
                <p>
                    <input type="hidden" name="hospital_no" value="<?= $hospital_no; ?>">
                    <input type="hidden" name="dept_price_id" value="<?= $dept_price_id; ?>">
                    <input type="hidden" name="patient_name" value="<?= $patient_name; ?>">
                    <input type="hidden" name="insurance_no" value="<?= $insurance_no ?>">
                    <input type="hidden" name="insurance_type" value="<?= $insurance_type; ?>">
                    <input type="hidden" name="interest" value="<?= $insurance_info->interest; ?>">
                    <input type="hidden" name="hosp_price" value="<?= $item_amt; ?>">
                    <input type="hidden" name="claim_amt" value="<?= $claim_amt; ?>">
                    <input type="hidden" name="ccop_int_charge" value="<?= $ccop_int_charge; ?>">
                    <input type="hidden" name="pay_mode" value="<?= $pay_mode; ?>">
                    <input type="hidden" name="invoice_no" value="<?= $invoice_no; ?>">
                    <input type="hidden" name="token" value="<?= $token; ?>">
                    <input type="hidden" name="service_id" value="<?= $request_type_info->sn; ?>">
                    <input type="hidden" name="amount" value="<?= $amount; ?>">
                    <input type="hidden" name="service_name" value="<?= $request_type_info->item_service; ?>">
                    <input type="hidden" name="request_note" value="<?= $request_note; ?>">
                    <input type="hidden" name="number_of_session" value="<?= $number_of_session; ?>">


                </p>
                <?php
                if ($dialysis_amount > 0) {
                ?>
                    <button class="btn btn-card btn-full btn-card-active" name="submitSessionDateTimeBtn"><i class="fa fa-forward"></i> Save Booking and Bill Patient </button>
                <?php
                }
                ?>



            </div>
        </div>
    </div>

</form>

<script>
    $(document).ready(function() {
        $("#dialysis-request-modal").modal('show');
    });
</script>