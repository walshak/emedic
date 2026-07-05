<div class="row">
    <div class="col-lg-12">
        <div class="ibox ">
            <div class="ibox-title">
                <h5>Dialysis Panel</h5>
            </div>
            <div class="ibox-content">
                <div class="row">
                    <div class="col-md-3 b-r">
                        <div class=" wow fadeInLeft animated animated">
                            <?php include_once('_links.php'); ?>
                        </div>
                    </div>


                    <div class="col-sm-6">
                        <H2>Dialysis Patients List </H2>
                        <form action="dialysis.php" method="POST" name="subject">
                            <table width="100%">
                                <tr>
                                    <td width="60%">
                                        <label for="search_patient_input">Search for Hospital Patient</label>
                                        <select id="search_for_patient" name="hospital_no" style="width:350px;">
                                        </select>
                                        <script>
                                            $('#search_for_patient').select2();
                                        </script>
                                    </td>
                                    <td width="40%">
                                        <label>.</label><br>
                                        &nbsp;&nbsp;<button type="submit" class="btn btn-primary btn-sm" name="patient_trs_display">Display</button>
                                        &nbsp; | &nbsp;
                                        <a href="dialysis.php" class="btn btn-default btn-sm">Refresh</a>
                                    </td>
                                </tr>
                            </table>


                        </form>

                    </div>


                    <div class="col-md-3">
                        <?php
                        include_once('dialysis/_dialysis_reminder.php');

                        ?>
                    </div>

                </div>


                <div class="row">
                    <div class="col-md-12">
                        <div class="card feature wow fadeInRight animated animated" style="visibility: visible; animation-name: fadeInRight; position: relative;">
                            <?php
                            // DB connection assumed in $db


                            $page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                            $limit = !empty($_GET['patient']) ? 30 : 20;
                            $offset = ($page - 1) * $limit;

                            $filters = [];
                            $params  = [];

                            /**
                             * PATIENT FILTER (NO DEFAULT DATE)
                             */
                            /*    if (isset($_POST['patient_trs_display'])) {

                                $filters[] = "hospital_no = ?";
                                $params[]  = $_POST['hospital_no'];
                            } else */

                            if (!empty($_GET['patient'])) {
                                $filters[] = "hospital_no = ?";
                                $params[]  = $_GET['patient'];
                            } else {

                                /**
                                 * DATE FILTER
                                 * If user provides date → use it
                                 * Else → default last 30 days
                                 */
                                if (!empty($_GET['from']) && !empty($_GET['to'])) {
                                    $filters[] = "DATE(request_date) BETWEEN ? AND ?";
                                    $params[]  = $_GET['from'];
                                    $params[]  = $_GET['to'];
                                } else {
                                    // ✅ DEFAULT LAST 30 DAYS
                                    $filters[] = "DATE(request_date) >= CURDATE() - INTERVAL 30 DAY";
                                }

                                /**
                                 * OPTIONAL FILTERS
                                 */
                                if (!empty($_GET['request_type'])) {
                                    $filters[] = "request_type = ?";
                                    $params[]  = $_GET['request_type'];
                                }

                                $filters[] = "completed = 'no'";
                            }

                            /**
                             * COMMON CONDITION
                             */
                            $filters[] = "status = '1'";

                            $whereSQL = implode(" AND ", $filters);

                            // Total records for pagination
                            $total_stmt = $db->prepare("SELECT COUNT(*) AS total FROM dialysis WHERE $whereSQL");
                            $total_stmt->execute($params);
                            $total = $total_stmt->fetch(PDO::FETCH_ASSOC)['total'];
                            $total_pages = ceil($total / $limit);

                            // Fetch data
                            $data_stmt = $db->prepare("
    SELECT * FROM dialysis 
    WHERE $whereSQL 
    ORDER BY id DESC 
    LIMIT $limit OFFSET $offset
");
                            $data_stmt->execute($params);
                            $rows = $data_stmt->fetchAll(PDO::FETCH_ASSOC);

                            ?>

                            <form method="get" class="row" style="margin-bottom:15px;">
                                <div class="col-md-3">
                                    <input type="date" name="from" class="form-control" value="<?php echo isset($_GET['from']) ? $_GET['from'] : ''; ?>" />
                                </div>
                                <div class="col-md-3">
                                    <input type="date" name="to" class="form-control" value="<?php echo isset($_GET['to']) ? $_GET['to'] : ''; ?>" />
                                </div>
                                <div class="col-md-3">
                                    <?php
                                    $request_types_stmt = $db->query("SELECT DISTINCT request_type FROM dialysis ORDER BY request_type ASC");
                                    $request_types = $request_types_stmt->fetchAll(PDO::FETCH_COLUMN);
                                    ?>
                                    <select name="request_type" class="form-control">
                                        <option value="">-- Request Type --</option>
                                        <?php foreach ($request_types as $type) { ?>
                                            <option value="<?php echo $type; ?>" <?php echo (@$_GET['request_type'] == $type) ? 'selected' : ''; ?>>
                                                <?php echo $type; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-primary" type="submit">Filter</button>
                                </div>
                            </form>

                            <table class="table table-striped table-bordered table-hover" style="font-size: 14px;">
                                <thead>
                                    <tr>
                                        <th>SN</th>
                                        <th>Hospital No.</th>
                                        <th>Name</th>
                                        <th>Request Type</th>
                                        <th>Requester</th>
                                        <th>Status</th>
                                        <th>Request Date</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sn = $offset + 1;

                                    if (empty($rows)) {
                                        echo '<tr><td colspan="8" class="text-center" style="color:red; font-weight:bold;">No records available</td></tr>';
                                    } else {

                                        foreach ($rows as $dialysis) {

                                            // Check admission status
                                            $adm_stmt = $db->prepare("SELECT COUNT(sn) AS total FROM admission WHERE hospital_no = ? AND adm_status = '3'");
                                            $adm_stmt->execute(array($dialysis['hospital_no']));
                                            $adm_data = $adm_stmt->fetch(PDO::FETCH_ASSOC);
                                            $onAdmission = ($adm_data['total'] > 0);

                                            $badge = $onAdmission ? 'label-success' : 'label-default';
                                            $statusText = $onAdmission ? 'On Admission' : 'Out Patient';
                                    ?>
                                            <tr>
                                                <td><?php echo $sn++; ?></td>
                                                <td><?php echo $dialysis["hospital_no"]; ?></td>
                                                <td><?php echo $dialysis["patient_name"]; ?></td>
                                                <td><?php echo $dialysis["request_type"]; ?></td>
                                                <td><?php echo $dialysis["request_by"]; ?></td>
                                                <td><span class="label <?php echo $badge; ?>"><?php echo $statusText; ?></span></td>
                                                <td><?php echo date('d M, Y h:i A', strtotime($dialysis["request_date"])); ?></td>
                                                <td>
                                                    <a href="dialysis.php?p=<?php echo base64_encode('Open_dialysis'); ?>&d=<?php echo $dialysis['token']; ?>" class="btn btn-sm btn-primary">
                                                        Open
                                                    </a>
                                                </td>
                                            </tr>
                                    <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>

                            <?php if ($total_pages > 1) { ?>
                                <nav>
                                    <ul class="pagination">

                                        <!-- First -->
                                        <?php if ($page > 1): ?>
                                            <li>
                                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>">
                                                    « First
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <!-- Previous -->
                                        <?php if ($page > 1): ?>
                                            <li>
                                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                                    ‹ Prev
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php
                                        // Number of pages to show around current page
                                        $range = 2;
                                        $start = max(1, $page - $range);
                                        $end   = min($total_pages, $page + $range);
                                        ?>

                                        <!-- Page Numbers -->
                                        <?php for ($p = $start; $p <= $end; $p++): ?>
                                            <li class="<?php echo ($page == $p) ? 'active' : ''; ?>">
                                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $p])); ?>">
                                                    <?php echo $p; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <!-- Next -->
                                        <?php if ($page < $total_pages): ?>
                                            <li>
                                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                                    Next ›
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <!-- Last -->
                                        <?php if ($page < $total_pages): ?>
                                            <li>
                                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>">
                                                    Last »
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                    </ul>
                                </nav>

                            <?php } ?>

                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>