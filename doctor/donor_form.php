<?php
include("../Connections/Conn.php");
include('objects.php');
include('helpers.php');
$error_status = null;



if (isset($_POST["loadDonorForm"])) {


    $hospital_no = $_POST["hospital_no"];
    $transplant_id = $_POST["transplant_id"];
    $patient_info = $Patient->get(["hospital_no" => $hospital_no]);

    if (!empty($patient_info)) {
        $name = $patient_info->surname . ' ' . $patient_info->fname . ' ' . $patient_info->oname;
?>


        <div>
            <label for="reg_input_no" class="req">Name:</label>
            <input type="text" name="name" id="name" value="<?= $name; ?>" placeholder="Full name" maxlength="100" class="form-control" readonly>
        </div>
        <div>
            <label for="reg_input_no" class="req">Blood Group </label>
            <select class="form-control" name="blood_group" required>
                <option value=""> </option>
                <option value="A+" <?= $patient_info->blood_g == "A+" ? "selected" : "" ?>> A+ </option>
                <option value="O+" <?= $patient_info->blood_g == "O+" ? "selected" : "" ?>> O+</option>
                <option value="B+" <?= $patient_info->blood_g == "B+" ? "selected" : "" ?>> B+</option>
                <option value="AB+" <?= $patient_info->blood_g == "AB+" ? "selected" : "" ?>> AB+</option>
                <option value="A-" <?= $patient_info->blood_g == "A-" ? "selected" : "" ?>> A-</option>
                <option value="O-" <?= $patient_info->blood_g == "O-" ? "selected" : "" ?>> O-</option>
                <option value="B-" <?= $patient_info->blood_g == "B" ? "selected" : "" ?>> B-</option>
                <option value="AB-" <?= $patient_info->blood_g == "AB" ? "selected" : "" ?>> AB-</option>
            </select>
        </div>

        <br>

        <div>
            <label for="reg_input_no" class="req">Genotype </label>
            <select class="form-control" name="genotype" required>
                <option value=""> </option>
                <option value="AA" <?= $patient_info->geno_type == "AA" ? "selected" : "" ?>> AA </option>
                <option value="AS" <?= $patient_info->geno_type == "AS" ? "selected" : "" ?>> AS </option>
                <option value="SS" <?= $patient_info->geno_type == "SS" ? "selected" : "" ?>> SS </option>
                <option value="AC" <?= $patient_info->geno_type == "AC" ? "selected" : "" ?>> AC </option>
            </select>
        </div>
        <div>
            <label for="reg_input_no" class="req">Phone Number</label>
            <input type="text" name="phone_number" id="phone_number" value="<?= $patient_info->phone; ?>" maxlength="14" placeholder="Phone Number" class="form-control" required>
        </div>
        <br>
        <div>
            <label for="reg_input_no" class="req">Address </label>
            <input type="text" name="address" id="address" value="<?= $patient_info->addr; ?>" placeholder="" class="form-control" required>
        </div>
        <br>
        <div>
            <label for="reg_input_no" class="">NOK Name:</label>
            <input type="text" name="nok_name" id="nok_name" placeholder="" class="form-control">
        </div>
        <br>
        <div>
            <label for="reg_input_no" class="">NOK Phone number</label>
            <input type="text" name="nok_phone_number" id="nok_phone_number" maxlength="14" placeholder="" class="form-control">
        </div>
        <br>
        <div>
            <label for="reg_input_no" class="">NOK Address</label>
            <input type="text" name="nok_address" id="nok_address" placeholder="" class="form-control">
        </div>
        <br>
        <p id="addDonorStatusArea"></p>
        <p>
            <input type="hidden" name="transplant_id" value="<?= $transplant_id; ?>" placeholder="" class="form-control" required>
            <input type="hidden" name="addDonor" value="true" placeholder="" class="form-control" required>
            <input type="hidden" name="hospital_no" value="<?= $hospital_no; ?>" placeholder="" class="form-control" required>
            <button type="submit" class="btn btn-primary" name="addDonorBtn" style="display: block; width:100%;">Submit</button>
        </p>


<?php

    }
}
