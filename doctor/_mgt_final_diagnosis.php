<div class="row">
    <div class="col-md-6">
        <form action="index.php" method="POST" id="subject" name="subject" enctype="multipart/form-data">

            <label for="reg_input_no" class="req">Diagnosis</label>
         

            <input type="text" class="typeahead form-control" data-provide="typeahead" id="typeahead" placeholder="Enter First 3 Letters of  Diagnosis Name or ICD-10/ICPC-2 code" autocomplete="off">
                             

            <div id="diagnosis_text"></div>

        </form>
         <br>
        <div>
            <select name="comment2" id="comment2" class="form-control" style="font-size:14px;">
                <option value="">Not Applicable</option>
                <option value="Acute">Acute</option>
                <option value="Chronic">Chronic</option>
                <option value="Recurrent">Recurrent</option>


            </select>
        </div>
        <br>
        <div>
            <select name="comment" id="comment" class="form-control" style="font-size:14px;">
                <option value="">Not Applicable</option>
                <option value="Query">Query</option>
                <option value="Differential">Differential</option>
                <option value="Confirmed">Confirmed</option>

            </select>
        </div>
        <br>
        <div class="row">
            <div class="col-md-12">
                <div class="form_sep">
                    <label for="reg_textarea_message" class="">Diagnosis Description [optional]</label>
                    <textarea name="comment3" id="comment3" cols="15" rows="2" class="form-control" data-minlength="10" value="" style="font-size:18px"></textarea>
                    <br>
                    <!-- <button class="btn btn-sm btn-success">Add Final Diagnosis</button> -->
                </div>
            </div>
        </div>

    </div>
    <div class="col-md-6">
        <br>
    <h4 class="text-center">Diagnosis - History</h4>
<table class="table table-responsive" border="2">
    <thead>
        <tr>
            <td>SN</td>
            <td>COMPLAINS</td>
            <td>DATE</td>
        </tr>
    </thead>
    <tbody>
        <?php
        $sn = 1;
        //// presenting complaints
        $complains_stmt = $db->prepare("SELECT * FROM notes WHERE  hospital_no = ? AND notes_type = 'D' AND tag = 'DR' ORDER BY sn DESC LIMIT 15");
        $complains_stmt->execute(array($hospital_no));
        $complains = $complains_stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($complains as $key => $complain) {
        ?>

            <tr>
                <td class="p-10"><?= $sn++; ?></td>
                <td class="p-10"><?= $complain["notes"]; ?></td>
                <td class="p-10"><?= $complain["date_entry"]; ?></td>
            </tr>
        <?php
        }
        ?>
    </tbody>
</table>
    </div>

</div>


<br>

<script>



</script>

