<?php

?>


<div class="row">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Evaluation Report</h5>
            </div>
            <div class="ibox-content">
                <div class="form_sep">
                    <div class="row">
                        <form action="" method="GET">

                            <div class="col-sm-3">
                                <label class="req">Select Year</label>
                                <select name="eval_year" class="form-control" required>
                                    <option value="">Select year </option>
                                    <?php
                                    $year = date('Y');
                                    $years = range($year, $year - 50);
                                    foreach ($years as $y) {
                                    ?>
                                        <option value="<?php echo $y ?>" <?php if ($y == $_GET['eval_year']) {
                                                                                echo "selected";
                                                                            } ?>>
                                            <?php echo $y ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-sm-3">
                                <label>Select desired evaluation</label>
                                <select name="evaluation" class="form-control">
                                    <option value="">Select evaluation </option>
                                    <?php
                                    $stmt = $db->query(
                                        "SELECT * FROM eval_template"
                                    );
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    ?>
                                        <option value="<?php echo $row['id'] ?>" <?php if (@$_GET['evaluation'] == $row["id"]) {
                                                                                        echo "selected";
                                                                                    } ?>>
                                            <?php echo $row['name'] ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <label>Select evaluation period</label>
                                <select name="period" class="form-control">
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
                            <div class="col-sm-1">
                                <label for="">All staff in this category </label>
                                <input type="checkbox" name="all_staff" <?php if (isset($_GET['all_staff'])) {
                                                                            echo "checked";
                                                                        } ?>>
                            </div>
                            <div class="col-sm-2">
                                <br>
                                <button type="submit" class="btn btn-sm btn-primary">Continue</button>
                            </div>
                            <input type="hidden" name="rEval">
                        </form>
                    </div>
                </div>
                <hr>
                <?php
                if (isset($_GET['evaluation']) && isset($_GET['period']) && (!isset($_GET['all_staff']))) {
                    if ($_GET['evaluation'] != '') {
                        $evaluation = $_GET['evaluation'];
                    } else {
                        $evaluation = '';
                    }
                    if ($_GET['period'] != '') {
                        $period = $_GET['period'];
                    } else {
                        $period = '';
                    }

                    $year = $_GET['eval_year'];
                    $sql = "SELECT emp_code FROM eval_results 
                    WHERE 1 AND year = $year";
                    if ($period != '') {
                        $sql .= " AND period_id = $period";
                    }
                    if ($evaluation != '') {
                        $sql .= " AND evaluation_id = $evaluation";
                    }
                    //print_r($sql);
                    $stmt = $db->query($sql);
                    $evaluated = $stmt->fetch(PDO::FETCH_NUM);
                    //print_r($eval_result);
                ?>
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
                                                <?php echo $row["EmployeeCode"] . ' ' . $row["FirstName"]  . ', ' . $row["LastName"];
                                                if (in_array($row["EmployeeCode"], $evaluated)) {
                                                    echo "&nbsp;&nbsp;<i>(evaluated)</i>";
                                                }
                                                ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <?php
                                $evaluation = $_GET['evaluation'];
                                $period = $_GET['period'];
                                $year = $_GET['eval_year'];
                                ?>
                                <input type="hidden" name="rEval">
                                <input type="hidden" name="period" value="<?php echo $period; ?>">
                                <input type="hidden" name="evaluation" value="<?php echo $evaluation; ?>">
                                <input type="hidden" name="eval_year" value="<?php echo $year; ?>">
                                <div class="col-sm-2">
                                    <button type="submit" class="btn btn-sm btn-primary">Get details</button>
                                </div>
                            </div>
                        </form>
                        <br>

                    </div>
                <?php } else { ?>
                    <?php
                    if ($_GET['evaluation'] != '') {
                        $evaluation = $_GET['evaluation'];
                    } else {
                        $evaluation = '';
                    }
                    if ($_GET['period'] != '') {
                        $period = $_GET['period'];
                    } else {
                        $period = '';
                    }
                    $year = $_GET['eval_year'];
                    $sql = "SELECT eval_results.*, hremp.FirstName AS fname, hremp.LastName AS lname, template.name AS template_name,
                        eval_period.name as eval_period_name, department_tbl.department, 
                        hremp2.FirstName AS evaluated_firstname, hremp2.LastName AS evaluated_lastname
                        FROM eval_results 
                        INNER JOIN hremp ON eval_results.evaluator_id = hremp.EmployeeCode
                        INNER JOIN hremp AS hremp2 ON eval_results.emp_code = hremp2.EmployeeCode
                        INNER JOIN eval_template AS template ON eval_results.evaluation_id = template.id
                        INNER JOIN eval_period ON eval_results.period_id = eval_period.id
                        INNER JOIN department AS department_tbl ON hremp2.Department = department_tbl.sn
                        WHERE 1 AND year = :year";

                    if ($period != '') {
                        $sql .= " AND period_id = :period";
                    }
                    if ($evaluation != '') {
                        $sql .= " AND evaluation_id = :evaluation";
                    }
                    $sql .= " ORDER BY eval_results.score DESC, eval_results.year DESC";

                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':year', $year, PDO::PARAM_INT);
                    if ($period != '') {
                        $stmt->bindParam(':period', $period, PDO::PARAM_INT);
                    }
                    if ($evaluation != '') {
                        $stmt->bindParam(':evaluation', $evaluation, PDO::PARAM_INT);
                    }
                    $stmt->execute();

                    if ($stmt->rowCount() > 0) {
                    ?>
                        <table class="table table-stripped table-sm text-sm datatables-example">
                            <thead>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Evaluation</th>
                                <th>Evaluated Staff</th>
                                <th>Period</th>
                                <th>Evaluated by</th>
                                <th>Year</th>
                                <th>Score</th>
                                <th>Average</th>
                                <th>Remark</th>
                                <th>Manage</th>
                            </thead>
                            <tbody>
                                <?php while ($eval_result = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
                                    <tr>
                                        <td><?php echo $eval_result['fname'] . " " . $eval_result['lname']; ?></td>
                                        <td><?php echo $eval_result['department'] ?></td>
                                        <td><?php echo $eval_result['template_name'] ?></td>
                                        <td><?= $eval_result['evaluated_firstname'] . " " . $eval_result['evaluated_lastname'] ?></td>
                                        <td><?= $eval_result['eval_period_name'] ?></td>
                                        <td><?php echo $eval_result['fname'] . " " . $eval_result['lname'] ?></td>
                                        <td><?php echo $eval_result['year'] ?></td>
                                        <td><?php echo $eval_result['score'] ?></td>
                                        <td>
                                            <?php
                                            $sql = "SELECT AVG(score) AS avgr FROM eval_results 
                            WHERE 1 AND emp_code = :emp_code AND year = :year";
                                            if ($period != '') {
                                                $sql .= " AND period_id = :period";
                                            }
                                            if ($evaluation != '') {
                                                $sql .= " AND evaluation_id = :evaluation";
                                            }
                                            $avgr_stmt = $db->prepare($sql);
                                            $avgr_stmt->bindParam(':emp_code', $eval_result['emp_code'], PDO::PARAM_STR);
                                            $avgr_stmt->bindParam(':year', $year, PDO::PARAM_INT);
                                            if ($period != '') {
                                                $avgr_stmt->bindParam(':period', $period, PDO::PARAM_INT);
                                            }
                                            if ($evaluation != '') {
                                                $avgr_stmt->bindParam(':evaluation', $evaluation, PDO::PARAM_INT);
                                            }
                                            $avgr_stmt->execute();
                                            $avgr = $avgr_stmt->fetch(PDO::FETCH_ASSOC);
                                            echo ($avgr['avgr']);
                                            ?>
                                        </td>
                                        <td><small><?php echo $eval_result['remark'] ?></small></td>
                                        <td>
                                            <?php
                                            $evaluation = $_GET['evaluation'];
                                            $period = $_GET['period'];
                                            $year = $_GET['eval_year'];
                                            ?>
                                            <a href="<?php echo 'index.php?rEval&evaluation=' . $evaluation . '&period=' . $period . '&EmployeeCode=' . $eval_result['EmployeeCode'] . '&eval_year=' . $year; ?>">[Details]</a>
                                        </td>
                                    </tr>
                                <?php endwhile ?>
                            </tbody>
                        </table>
                    <?php } else { ?>
                        No data to show
                    <?php } ?>
                <?php } ?>
                <?php
                if (isset($_GET['EmployeeCode']) && isset($_GET['evaluation']) && isset($_GET['period']) && (!isset($_GET['all_staff']))) {
                    $evaluation = $_GET['evaluation'] != '' ? $_GET['evaluation'] : null;
                    $period = $_GET['period'] != '' ? $_GET['period'] : null;
                    $year = $_GET['eval_year'];
                    $EmployeeCode = $_GET['EmployeeCode'];

                    $sql = "SELECT eval_results.*, eval_template.name as eval_name, 
                            hremp.FirstName AS evaluator_firstname, hremp.LastName AS evaluator_lastname, 
                            eval_period.name as eval_period_name, 
                            hremp2.FirstName AS evaluated_firstname, hremp2.LastName AS evaluated_lastname
                            FROM eval_results
                            INNER JOIN hremp ON eval_results.evaluator_id = hremp.EmployeeCode
                            INNER JOIN hremp AS hremp2 ON eval_results.emp_code = hremp2.EmployeeCode  -- For the evaluated staff
                            INNER JOIN eval_template ON eval_template.id = eval_results.evaluation_id
                            INNER JOIN eval_period ON eval_period.id = eval_results.period_id
                            WHERE eval_results.emp_code = :EmployeeCode";

                    $params = [':EmployeeCode' => $EmployeeCode];

                    if ($evaluation) {
                        $sql .= " AND eval_results.evaluation_id = :evaluation";
                        $params[':evaluation'] = $evaluation;
                    }

                    if ($period) {
                        $sql .= " AND eval_results.period_id = :period";
                        $params[':period'] = $period;
                    }

                    $stmt = $db->prepare($sql);
                    $stmt->execute($params);

                    if ($stmt->rowCount() > 0) {
                        $total_score = 0;
                ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover ">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Evaluation Name</th>
                                        <th>Evaluator</th>
                                        <th>Evaluated Staff</th>
                                        <th>Period</th>
                                        <th>Date</th>
                                        <th>Evaluation Content</th>
                                        <th>Score</th>
                                        <th>Remark</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while ($eval_result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        $total_score += (int)$eval_result['score'];
                                    ?>
                                        <tr>
                                            <td><?= $eval_result['eval_name'] ?></td>
                                            <td><?= $eval_result['evaluator_firstname'] . " " . $eval_result['evaluator_lastname'] ?></td>
                                            <td><?= $eval_result['evaluated_firstname'] . " " . $eval_result['evaluated_lastname'] ?></td>
                                            <td><?= $eval_result['eval_period_name'] ?></td>
                                            <td><?= date('D jS M, Y', strtotime($eval_result['created_at'])) ?></td>
                                            <td><?= nl2br(($eval_result['evaluation'])) ?></td>
                                            <td><b><?= $eval_result['score'] ?></b></td>
                                            <td><?= htmlspecialchars($eval_result['remark']) ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <p>
                        <h3>Average score: <?php echo $total_score / $stmt->rowCount() ?></h3>
                        </p>
                    <?php } else { ?>
                        No evaluation data to show
                    <?php } ?>
                <?php } ?>

                <div class="form_sep">
                    <a href="index.php?rEval" class="btn btn-warning">Reset form</a>
                </div>

            </div>
        </div>
    </div>