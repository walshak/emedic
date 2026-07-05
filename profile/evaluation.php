<?php
if (isset($_POST['evaluation'])) {
    $EmployeeCode = $_GET['EmployeeCode'];
    $evaluation_template = $_GET['evaluation'];
    $evaluation = $_POST['evaluation'];
    $period = $_GET['period'];
    $year = date('Y');
    $mode = $_POST['edit_mode'];
    $evaluator_id = $_POST['evaluator_id'];
    $remark = $_POST['remark'];
    $score = $_POST['score'];

    if ($mode == 0) {
        // Insert mode
        $stmt = $db->prepare("
            INSERT INTO eval_results (emp_code, period_id, evaluation_id, year, evaluation, evaluator_id, remark, score)
            VALUES (:EmployeeCode, :period, :evaluation_template, :year, :evaluation, :evaluator_id, :remark, :score)
        ");
        $stmt->bindParam(':EmployeeCode', $EmployeeCode, PDO::PARAM_STR);
        $stmt->bindParam(':period', $period, PDO::PARAM_STR);
        $stmt->bindParam(':evaluation_template', $evaluation_template, PDO::PARAM_STR);
        $stmt->bindParam(':year', $year, PDO::PARAM_INT);
        $stmt->bindParam(':evaluation', $evaluation, PDO::PARAM_STR);
        $stmt->bindParam(':evaluator_id', $evaluator_id, PDO::PARAM_STR);
        $stmt->bindParam(':remark', $remark, PDO::PARAM_STR);
        $stmt->bindParam(':score', $score, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $sv = 1;
        } else {
            $errors = 1;
        }
    } else {
        // Update mode
        $result_id = $_POST['result_id'];
        $stmt = $db->prepare("
            UPDATE eval_results 
            SET period_id = :period, evaluation_id = :evaluation_template, evaluation = :evaluation, score = :score, remark = :remark 
            WHERE id = :result_id
        ");
        $stmt->bindParam(':period', $period, PDO::PARAM_STR);
        $stmt->bindParam(':evaluation_template', $evaluation_template, PDO::PARAM_STR);
        $stmt->bindParam(':evaluation', $evaluation, PDO::PARAM_STR);
        $stmt->bindParam(':score', $score, PDO::PARAM_STR);
        $stmt->bindParam(':remark', $remark, PDO::PARAM_STR);
        $stmt->bindParam(':result_id', $result_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $sv = 1;
        } else {
            $errors = 1;
        }
    }
}

?>


<div class="row">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Evaluation <i>by: <?php echo $_SESSION['fullname']; ?></i> </h5>
            </div>
            <div class="ibox-content">
                <div class="form_sep">
                    <form action="" method="get">
                        <label for="reg_input_no" class="req">Search Employee</label>
                        <div class="row">

                            <div class="col-sm-10">

                                <select name="EmployeeCode" class="input-sm chosen-select" id="employ_code" required>
                                    <option selected="selected" value="">Search and Select Staff</option>
                                    <?php $stmt = $db->query("SELECT EmployeeCode, FirstName, LastName FROM hremp WHERE status=0 order by FirstName");


                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <option value="<?php echo $row["EmployeeCode"]; ?>" <?php if (@$_GET['EmployeeCode'] == $row["EmployeeCode"]) {
                                                                                                echo "selected";
                                                                                            } ?>>
                                            <?php echo $row["EmployeeCode"] . ' ' . $row["FirstName"]  . ', ' . $row["LastName"]; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <input type="hidden" name="eval">
                            <div class="col-sm-2">
                                <button type="submit" class="btn btn-sm btn-primary">Get details</button>
                            </div>
                        </div>
                    </form>

                </div>
                <?php if (isset($_GET['EmployeeCode'])) : ?>
                    <div class="form_sep">
                        <div class="row">
                            <?php
                            $EmployeeCode = $_GET['EmployeeCode'];
                            $stmt = $db->prepare("SELECT hremp.*, department.sn as dept_idd, department.department  FROM hremp INNER JOIN department 
                                    ON hremp.Department = department.sn WHERE hremp.EmployeeCode = ?");
                            $stmt->execute([$EmployeeCode]);
                            $staff = $stmt->fetch();
                            $dept = $staff['dept_idd'];

                            // print_r($dept);
                            // die();
                            ?>
                            <form action="" method="GET">

                                <div class="col-sm-5">
                                    <label class="req">Select desired evaluation</label>
                                    <select name="evaluation" class="form-control" required>
                                        <option value="">Select evaluation </option>
                                        <?php
                                        $stmt = $db->prepare("SELECT * FROM eval_template WHERE dept_id = :dept");
                                        $stmt->bindParam(':dept', $dept, PDO::PARAM_STR);
                                        $stmt->execute();

                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        ?>
                                            <option value="<?php echo $row['id']; ?>" <?php if (@$_GET['evaluation'] == $row["id"]) {
                                                                                            echo "selected";
                                                                                        } ?>>
                                                <?php echo htmlspecialchars($row['name']); ?>
                                            </option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-sm-5">
                                    <label class="req">Select desired evaluation period</label>
                                    <select name="period" class="form-control" required>
                                        <option value="">Select evaluation period </option>
                                        <?php
                                        $stmt = $db->query("SELECT * FROM eval_period");
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        ?>
                                            <option value="<?php echo $row['id'] ?>" <?php if (@$_GET['period'] == $row["id"]) {
                                                                                            echo "selected";
                                                                                        } ?>>
                                                <?php echo $row['name'] ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <br>
                                    <button type="submit" class="btn btn-sm btn-primary">Continue</button>
                                </div>
                                <input type="hidden" name="eval">
                                <input type="hidden" name="EmployeeCode" value="<?php echo $EmployeeCode ?>">
                            </form>
                        </div>
                    </div>
                <?php endif ?>
                <hr>
                <?php
                if (isset($_GET['evaluation']) && isset($_GET['period'])) {
                    $EmployeeCode = $_GET['EmployeeCode'];
                    $evaluation = $_GET['evaluation'];
                    $period = $_GET['period'];
                    $year = date('Y');
                    $evaluator_id = $_SESSION['EmployeeCode'];

                    // Query for existing evaluation results
                    $stmt = $db->prepare("
                        SELECT * FROM eval_results 
                        WHERE emp_code = :EmployeeCode AND period_id = :period AND year = :year 
                          AND evaluation_id = :evaluation AND evaluator_id = :evaluator_id
                    ");
                    $stmt->execute([
                        ':EmployeeCode' => $EmployeeCode,
                        ':period' => $period,
                        ':year' => $year,
                        ':evaluation' => $evaluation,
                        ':evaluator_id' => $evaluator_id
                    ]);
                    $eval_result = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($stmt->rowCount() == 0) {
                        // Fetch template if no evaluation result exists
                        $stmt = $db->prepare("SELECT * FROM eval_template WHERE id = :evaluation");
                        $stmt->execute([':evaluation' => $evaluation]);
                        $template = $stmt->fetch(PDO::FETCH_ASSOC);
                ?>

                        <div class="form_sep">
                            <!-- <script>
                                function getContent() {
                                    document.getElementById("the-evaluation").value = document.getElementById("the-content").innerHTML;
                                }
                            </script> -->
                            <form action="" method="post" onsubmit="return getContent()">
                                <!-- <div class="form_sep" contenteditable="true" id="the-content" style="border:1px solid black;">
                                </div> -->

                                <div class="form_sep">
                                    <label class="req">Fill your validation</label>
                                    <textarea name="evaluation" id="evaluation_" class=" form-control trumbowygEditor">
                                    <?php echo htmlspecialchars($template['template']); ?>
                                    </textarea>
                                </div>
                                <div class="form_sep">
                                    <label class="req">Score (on a scale of 1-100)</label>
                                    <input type="number" name="score" class="form-control" min="1" max="100" required>
                                </div>
                                <div class="form_sep">
                                    <label for="">Remark (optional)</label>
                                    <input type="text" name="remark" class="form-control">
                                </div>
                                <div class="form_sep">
                                    <button class="btn btn-primary" type="submit">Finish</button>
                                    <a href="index.php?eval" class="btn btn-warning">Reset form</a>
                                </div>
                                <!-- <textarea name="evaluation" id="the-evaluation" style="display: none;"></textarea> -->
                                <input type="hidden" name="edit_mode" value="0">
                                <input type="hidden" name="evaluator_id" value="<?php echo htmlspecialchars($evaluator_id); ?>">
                            </form>
                        </div>

                    <?php } else { ?>

                        <script>
                            function getContent() {
                                document.getElementById("the-evaluation").value = document.getElementById("the-content").innerHTML;
                            }
                        </script>
                        <form action="" method="post" onsubmit="return getContent()">
                            <div class="form_sep">
                                <label class="req">Fill your validation</label>
                                <textarea name="evaluation" id="evaluation_" class=" form-control trumbowygEditor">
                                    <?php echo htmlspecialchars($eval_result['evaluation']); ?>
                                    </textarea>
                            </div>
                            <div class="form_sep">
                                <label class="req">Score (on a scale of 1-100)</label>
                                <input type="number" name="score" class="form-control" min="1" max="100"
                                    value="<?php echo htmlspecialchars($eval_result['score']); ?>" required>
                            </div>
                            <div class="form_sep">
                                <label for="">Remark (optional)</label>
                                <input type="text" name="remark" class="form-control" value="<?php echo htmlspecialchars($eval_result['remark']); ?>">
                            </div>
                            <div class="form_sep">
                                <button class="btn btn-primary" type="submit">Finish</button>
                                <a href="index.php?eval" class="btn btn-warning">Reset form</a>
                            </div>
                            <!-- <textarea name="evaluation" id="the-evaluation" style="display: none;"></textarea> -->
                            <input type="hidden" name="edit_mode" value="1">
                            <input type="hidden" name="evaluator_id" value="<?php echo htmlspecialchars($evaluator_id); ?>">
                            <input type="hidden" name="result_id" value="<?php echo htmlspecialchars($eval_result['id']); ?>">
                        </form>

                    <?php } ?>
                <?php } ?>

            </div>
        </div>
    </div>
</div>