<div class="modal inmodal fade" id="convert_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">

            </div>
            <div class="modal-body" id="edit_patient_data_body">

                <?php
                $stmt = $db->prepare("SELECT COUNT(*) AS total FROM patient_ap_services WHERE hospital_no = :hosp_no");
                $stmt->execute([':hosp_no' => $hosp_no]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                $total_rows = (int) $row['total'];
                ?>


                <form method="POST" id="insurance_form">

                    <?php if ($total_rows >= 2): ?>
                        <div class="form_sep">
                            <h2>Existing transactions found on patient's account. Would like you to add comment before insurance conversion.</h2>
                            <h3>Are you sure you want to change patient insurance status?</h3>

                            <label>Enter Reason Conversion (Required) <b style="color: red;">Remarks must be at least 50 characters.</b></label>
                            <textarea class="input-sm form-control" cols="5" rows="2" name="payment_remarks" id="payment_remarks" maxlength="100"></textarea>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="payment_remarks" id="payment_remarks" value="">
                    <?php endif; ?>

                    <div class="form_sep">
                        <label for="reg_select" class="req"><strong style="font-size:14px; color:#00F">[ Select NEW Insurance Status ]</strong></label>
                        <br>
                        <select name="Insurance_Type" id="Insurance_Type" class="form-control" data-required="true">
                            <option selected="selected" value="">Select...</option>
                            <option value="NHIS">NHIS</option>
                            <option value="PHIS">PHIS(Private HMO)</option>
                            <option value="Corporate">Corporate</option>
                            <option value="Family">Family</option>
                            <option value="Private">Private(Self)</option>
                        </select>
                    </div>

                    <input type="hidden" name="total_rows" id="total_rows" value="<?= $total_rows; ?>">
                    <br />



                    <div id="NHIS_S">
                        <?php
                        // Fetch only required columns for active NHIS insurance types, ordered by insurance_no
                        $stmt = $db->query("SELECT insurance_no, insurance_name FROM insurance_tbl WHERE status='Active' AND insurance_type='NHIS' ORDER BY insurance_no");
                        ?>

                        <div class="form_sep">
                            <label for="insurance_list" class="req">Select HMO</label>
                            <select name="insurance_list" id="insurance_list" class="form-control" data-required="true">
                                <option selected="selected" value="">Select...</option>
                                <?php while ($row_emp = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<option value="' . htmlspecialchars($row_emp["insurance_no"]) . '">'
                                        . htmlspecialchars($row_emp["insurance_no"] . ': ' . $row_emp["insurance_name"])
                                        . '</option>';
                                } ?>
                            </select>
                        </div>



                        <div class="form_sep">
                            <label for="reg_input_name" class="req">Enter NHIS Number:</label>
                            <input type="text" id="nhis_no" name="nhis_no" class="form-control" data-required="true">
                        </div>

                        <hr>
                        <div class="pull-left">
                            <button class="btn btn-success btn-sm" type="submit" name="confirm_nhis" id="confirm_nhis">Assign NHIS</button>
                        </div>

                        <div class="pull-right">
                            <?php if ($ptm_2 == '') { ?>
                                <a href="index.php?ptm=<?php echo 'all/' . $hosp_no; ?>" class="btn btn-danger btn-sm">Close</a>
                            <?php } else { ?>
                                <a href="index.php?ptm=<?php echo $ptm . '/' . $ptm_2 . '/' . $hosp_no; ?>" class="btn btn-danger btn-sm">Close</a>
                            <?php } ?>
                        </div>

                    </div>

                    <div id="PHIS_S">
                        <?php
                        // Query only needed columns for active PHIS insurance types, ordered by insurance_no
                        $stmt = $db->query("SELECT insurance_no, insurance_name FROM insurance_tbl WHERE status='Active' AND insurance_type='PHIS' ORDER BY insurance_no");
                        ?>

                        <div class="form_sep">
                            <label for="insurance_list2" class="req">Select Private HMO & Apply Below</label>
                            <select name="insurance_list2" id="insurance_list2" class="form-control" data-required="true">
                                <option selected="selected" value="">Select...</option>
                                <?php while ($row_emp = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<option value="' . htmlspecialchars($row_emp["insurance_no"]) . '">'
                                        . htmlspecialchars($row_emp["insurance_no"] . ': ' . $row_emp["insurance_name"])
                                        . '</option>';
                                } ?>
                            </select>
                        </div>


                        <hr>

                        <div class="pull-left">
                            <button class="btn btn-success btn-sm" type="submit" name="start_phis" id="start_phis">Assign PHIS</button>
                        </div>

                        <div class="pull-right">
                            <?php if ($ptm_2 == '') { ?>
                                <a href="index.php?ptm=<?php echo 'all/' . $hosp_no; ?>" class="btn btn-danger btn-sm">Close</a>
                            <?php } else { ?>
                                <a href="index.php?ptm=<?php echo $ptm . '/' . $ptm_2 . '/' . $hosp_no; ?>" class="btn btn-danger btn-sm">Close</a>
                            <?php } ?>
                        </div>

                    </div>



                    <div id="Corporate">
                        <?php
                        // Use LOWER() to handle case-insensitive search for 'corporate' in insurance_type
                        $stmt = $db->query("SELECT insurance_no, insurance_name FROM insurance_tbl WHERE status = 'Active' AND LOWER(insurance_type) = 'Corporate' ORDER BY insurance_name");
                        ?>

                        <div class="form_sep">
                            <label for="insurance_list33" class="req">Select Corporate Name & Click Apply Below</label>
                            <select name="insurance_list33" id="insurance_list33" class="form-control" data-required="true">
                                <option selected="selected" value="">Select...</option>
                                <?php while ($row_emp = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <option value="<?= htmlspecialchars($row_emp['insurance_no']); ?>">
                                        <?= htmlspecialchars($row_emp['insurance_no'] . ': ' . $row_emp['insurance_name']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <hr>

                        <div class="pull-left">
                            <button class="btn btn-success btn-sm" type="submit" name="coop_apply" id="coop_apply">Assign Corporate</button>
                        </div>

                        <div class="pull-right">
                            <?php if ($ptm_2 == '') { ?>
                                <a href="index.php?ptm=<?php echo 'all/' . $hosp_no; ?>" class="btn btn-danger btn-sm">Close</a>
                            <?php } else { ?>
                                <a href="index.php?ptm=<?php echo $ptm . '/' . $ptm_2 . '/' . $hosp_no; ?>" class="btn btn-danger btn-sm">Close</a>
                            <?php } ?>
                        </div>

                    </div>



                    <div id="Family">
                        <?php
                        // Case-insensitive search for insurance_type 'family'
                        $stmt = $db->query("SELECT insurance_no, insurance_name FROM insurance_tbl WHERE status = 'Active' AND LOWER(insurance_type) = 'family' ORDER BY insurance_name");
                        ?>

                        <div class="form_sep">
                            <label for="insurance_list4" class="req">Select Family</label>
                            <select name="insurance_list4" id="insurance_list4" class="form-control" data-required="true">
                                <option selected="selected" value="">Select ...</option>
                                <?php while ($row_emp = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <option value="<?= htmlspecialchars($row_emp['insurance_no'] . ':' . $row_emp['insurance_name']); ?>">
                                        <?= htmlspecialchars($row_emp['insurance_no'] . ': ' . $row_emp['insurance_name']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>


                        <div class="form_sep">
                            <label for="reg_select" class="">Select Patient Membership and Click Add Button</label>
                            <select name="g_relation2" id="g_relation2" class="form-control" data-required="true">
                                <option selected="selected" value="">Select ...</option>
                                <option value="father">father</option>
                                <option value="son">son</option>
                                <option value="husband">husband</option>
                                <option value="brother">brother</option>
                                <option value="grandfather">grandfather</option>
                                <option value="grandson">grandson</option>
                                <option value="uncle">uncle</option>
                                <option value="nephew">nephew</option>
                                <option value="cousin">cousin</option>
                                <option value="mother">mother</option>
                                <option value="daughter">daughter</option>
                                <option value="wife">wife</option>
                                <option value="sister">sister</option>
                                <option value="grandmother">grandmother</option>
                                <option value="granddaughter">granddaughter</option>
                                <option value="aunt">aunt</option>
                                <option value="niece">niece</option>
                                <option value="parent">parent</option>
                                <option value="child">child</option>
                                <option value="spouse">spouse</option>
                                <option value="sibling">sibling</option>
                                <option value="grandparents">grandparents</option>
                                <option value="grandchild">grandchild</option>
                                <option value="friend">friend</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>

                        <hr>

                        <div class="pull-left">
                            <button class="btn btn-success btn-sm" type="submit" name="assignFamily" id="assignFamily">Add to Family Folder</button>
                        </div>

                        <div class="pull-right">
                            <?php if ($ptm_2 == '') { ?>
                                <a href="index.php?ptm=<?php echo 'all/' . $hosp_no; ?>" class="btn btn-danger btn-sm">Close</a>
                            <?php } else { ?>
                                <a href="index.php?ptm=<?php echo $ptm . '/' . $ptm_2 . '/' . $hosp_no; ?>" class="btn btn-danger btn-sm">Close</a>
                            <?php } ?>
                        </div>

                    </div>


                    <div id="Private">

                        <div class="pull-left">
                            <button class="btn btn-success btn-sm" type="submit" name="assign_private" id="assign_private">Assign Private</button>
                        </div>

                        <div class="pull-right">
                            <?php if ($ptm_2 == '') { ?>
                                <a href="index.php?ptm=<?php echo 'all/' . $hosp_no; ?>" class="btn btn-danger btn-sm">Close</a>
                            <?php } else { ?>
                                <a href="index.php?ptm=<?php echo $ptm . '/' . $ptm_2 . '/' . $hosp_no; ?>" class="btn btn-danger btn-sm">Close</a>
                            <?php } ?>
                        </div>

                    </div>

                    <input type="hidden" value="<?php echo $item_sn; ?>" name="item_sn" />
                </form>

                <div id="close">
                    <div class="pull-right">
                        <?php if ($ptm_2 == '') { ?>
                            <a href="index.php?ptm=<?php echo 'all/' . $hosp_no; ?>" class="btn btn-danger btn-sm">Close</a>
                        <?php } else { ?>
                            <a href="index.php?ptm=<?php echo $ptm . '/' . $ptm_2 . '/' . $hosp_no; ?>" class="btn btn-danger btn-sm">Close</a>
                        <?php } ?>
                    </div>
                </div>

                <input type="hidden" name="hos_no" id="hos_no" value="<?php echo $hosp_no; ?>" />


            </div>

        </div>
    </div>
</div>