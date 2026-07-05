<?php
// echo("<script>alert('$patient_info->fname')</script>");
?>

<div class="modal inmodal fade" id="bookProcedureModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">IVF Details</h4>
            </div>

            <div class="modal-body">
                <form method="post" id="subject" action="">
                    <div class="row">
                        <div class="col-sm-12">
                            <table class="table">
                                <tr>
                                    <td>Date</td>
                                    <td><input type="date" class="form-control" name="ivf_date"></td>
                                    <td>Hospital No</td>
                                    <td><input type="text" class="form-control" name="ivf_hosp_no" readonly value="<?= cleanInput($_GET['patient']); ?>"></td>
                                </tr>
                                <tr>
                                    <td>Husband Name</td>
                                    <td colspan="3">
                                        <input type="text" name="ivf_husband_name" class="form-control">
                                    </td>
                                </tr>
                                <tr>
                                    <td>Wife Name</td>
                                    <td colspan="3">
                                        <?php  $name_wife = $patient_info->surname . ' ' . $patient_info->fname. ' ' . (($patient_info->oname) ? $patient_info->oname : ""); ?>
                                        <input type="text" name="ivf_wife_name" value="<?php echo $name_wife; ?>" class="form-control" readonly>
                                    </td>

                                </tr>
                                <tr>
                                    <?php
                                        if($patient_info->dob){
                                            $ddd = date_diff(date_create($patient_info->dob), date_create('now'));
                                            $wife_age = $ddd->y;
                                        }else{
                                            $wife_age = "";
                                        }
                                    ?>
                                    <td>Age</td>
                                    <td><input type="text" class="form-control" name="ivf_age" value="<?=$wife_age?>" <?=($wife_age == "") ? "": 'readonly'; ?>></td>
                                    <td>Tel</td>
                                    <td><input type="text" class="form-control" name="ivf_tel" readonly value="<?= $patient_info->phone ?>"></td>
                                </tr>
                                <tr>
                                    <td>
                                        Treatment Program
                                    </td>
                                    <td colspan="3">
                                        <select name="ivf_treat_plan" class="form-control">
                                            <option value="">-- Selectplan--</option>
                                            <option value="IVF">IVF</option>
                                            <option value="ICSI">ICSI</option>
                                            <option value="IVF/ICSI">IVF/ICSI</option>
                                            <option value="ER">ER</option>
                                            <option value="IVF/TESA">IVF/TESA</option>
                                            <option value="IVF/PESA">IVF/PESA</option>
                                            <option value="TESE">TESE</option>
                                            <option value="ES">ES</option>
                                            <option value="FET">FET</option>
                                            <option value="FOT">FOT</option>
                                            <option value="IVF/PGD/XY">IVF/PGD/XY</option>
                                            <option value="IVF/PGD/XY/HBSS">IVF/PGD/XY/HBSS</option>
                                            <option value="IVF/PGD/HBSS">IVF/PGD/HBSS</option>
                                            <option value="IVF/TESA/PESA">IVF/TESA/PESA</option>
                                            <option value="IVF/SD">IVF/SD</option>
                                            <option value="ER/SD">ER/SD</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="background-color: gray; color:white">Treatment Details</td>
                                </tr>
                                <tr>
                                    <td>
                                        Protocol
                                    </td>
                                    <td>
                                        <select name="ivf_protocol" class="form-control">
                                            <option value="">-- Choose Protocol --</option>
                                            <option value="LONG">LONG</option>
                                            <option value="SHORT AGONIST">SHORT AGONIST</option>
                                            <option value="SHORT ANTAGONIST">SHORT ANTAGONIST</option>
                                            <option value="OTHERS">OTHERS</option>
                                        </select>
                                    </td>
                                    <td>Start date</td>
                                    <td><input type="date" name="start_date" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>GnRH-a</td>
                                    <td colspan="3"><input type="text" name="ivf_gnrha" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>Gonadotrophin</td>
                                    <td colspan="3">
                                        <select name="ivf_gonadotrophin" class="form-control">
                                            <option value="">-- Choose Gonadotrophin --</option>
                                            <option value="Recombinant FSH">Recombinant FSH</option>
                                            <option value="Pure FSH">Pure FSH</option>
                                            <option value="hMG">hMG</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Days of Stimulation</td>
                                    <td colspan="3"><input type="text" name="ivf_days_of_stimulation" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>hGG</td>
                                    <td><input type="text" class="form-control" name="ivf_hcg"></td>
                                    <td>Dose</td>
                                    <td><input type="text" class="form-control" name="ivf_dose"></td>
                                </tr>
                                <tr>
                                    <td>Date Administered</td>
                                    <td colspan="3"><input type="text" name="ivf_date_administered" class="form-control"></td>
                                </tr>

                                <tr>
                                    <td colspan="4" style="background-color: gray; color:white">Semen Preparation Details</td>
                                </tr>
                                <tr>
                                    <td>
                                        Sample Type
                                    </td>
                                    <td>
                                        <select name="ivf_sample_type" class="form-control">
                                            <option value="">-- Choose Sample Type --</option>
                                            <option value="Fresh">Fresh</option>
                                            <option value="Frozen">Frozen</option>
                                        </select>
                                    </td>
                                    <td>Date of Analysis</td>
                                    <td><input type="date" name="ivf_date_of_analysis" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>Volume</td>
                                    <td><input type="text" name="ivf_volume" class="form-control"></td>
                                    <td>Viscosity</td>
                                    <td><input type="text" name="ivf_vicosity" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>Conc. Count</td>
                                    <td><input type="text" class="form-control" name="ivf_conc_count"></td>
                                    <td>Motile Count</td>
                                    <td><input type="text" class="form-control" name="ivf_mortile_count"></td>
                                </tr>
                                <tr>
                                    <td>Morphology</td>
                                    <td colspan="3"><input type="text" name="ivf_morphology" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>Remarks </td>
                                    <td colspan="3"><input type="text" name="ivf_remarks" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="background-color: gray; color:white">Egg retrieval and Fertilization</td>
                                </tr>
                                <tr>
                                    <td>
                                        No. of Follicles
                                    </td>
                                    <td><input type="date" name="ivf_no_of_folicles" class="form-control"></td>
                                    <td>Retrieval Date</td>
                                    <td><input type="date" name="ivf_retrival_date" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>No. of Eggs</td>
                                    <td><input type="text" name="ivf_no_of_eggs" class="form-control"></td>
                                    <td>No. Fertilized</td>
                                    <td><input type="text" name="ivf_no_fertilized" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>Fertilization Method</td>
                                    <td colspan="3">
                                        <select name="ivf_fertiliztion_method" class="form-control">
                                            <option value="">-- Selectferilization Method--</option>
                                            <option value="IVF">IVF</option>
                                            <option value="ICSI">ICSI</option>
                                            <option value="IVF/ICSI">IVF/ICSI</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>No Cleaved</td>
                                    <td><input type="text" name="ivf_no_cleaved" class="form-control"></td>
                                    <td>No. Frozen</td>
                                    <td><input type="text" name="ivf_no_frozen" class="form-control"></td>
                                </tr>

                                <tr>
                                    <td colspan="4" style="background-color: gray; color:white">Embryo Transfer</td>
                                </tr>
                                <tr>
                                    <td>
                                        No. Transfered
                                    </td>
                                    <td><input type="text" name="ivf_no_transferd" class="form-control"></td>
                                    <td>Transfer Date</td>
                                    <td><input type="date" name="ivf_transfer_date" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>Cleaving/Embryo Grade</td>
                                    <td colspan="3"><input type="text" name="ivf_embrayo_grade" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>Blastocyst</td>
                                    <td><input type="text" name="ivf_blastocyst" class="form-control"></td>
                                    <td>No. Frozen</td>
                                    <td><input type="text" name="ivf_blastocyst_no_frozen" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>Comment</td>
                                    <td colspan="3"><input type="text" name="ivf_no_cleaved" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="background-color: gray; color:white">Conclusion</td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        After your embryo transfer, the pregnancy test should be done on
                                    </td>
                                    <td>
                                        <input type="date" name="ivf_pregnancy_test_date" class="form-control">
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        While awaiting pregnancy test, kindly continue taking the drugs for luteal support. These are
                                    </td>
                                    <td colspan="3">
                                        <input type="text" name="ivf_support_drugs" class="form-control">
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Embryologist
                                    </td>
                                    <td colspan="3">
                                        <input type="text" name="ivf_embryologist" class="form-control">
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Fertility Specialist
                                    </td>
                                    <td colspan="3">
                                        <input type="text" name="ivf_fertility_specialist" class="form-control">
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        IVF Nurse
                                    </td>
                                    <td colspan="3">
                                        <input type="text" name="ivf_IVF_nurse" class="form-control">
                                    </td>
                                </tr>
                            </table>
                            <div class="form_sep">

                                <div class="pull-left">
                                    <button type="submit" class="btn btn-success btn btn-sm" name="save_ivf_from">Save Details</button>
                                </div>

                                <div class="pull-right">
                                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>

                                </div>
                            </div>
                        </div>

                    </div>
                </form>

                <hr>


            </div>
        </div>
    </div>
</div>