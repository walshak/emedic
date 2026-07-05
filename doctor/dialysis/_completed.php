<div class="row">
    <div class="col-lg-12">
        <div class="ibox ">
            <div class="ibox-title">
                <h5>Dialysis Panel</h5>
            </div>
            <div class="ibox-content">
                <div class="row">
                    <div class="col-md-9 b-r">
                        <div class="card feature wow fadeInRight animated animated" style="visibility: visible; animation-name: fadeInRight; position: relative;">
                            <form method="GET" class="form-inline mb-3">
                                <input type="hidden" name="p" value="<?php echo $_GET['p']; ?>">
                                <input type="text" name="search" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>" placeholder="Name or Hospital No." class="form-control mx-1">

                                <input type="date" name="start_date" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>" class="form-control mx-1">

                                <input type="date" name="end_date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>" class="form-control mx-1">
                                <?php
                                // Example of dynamically getting distinct request types
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

                                <button class="btn btn-sm btn-primary mx-1">Search</button>

                                <a href="export_dialysis_excel.php?<?php echo http_build_query($_GET); ?>" class="btn btn-sm btn-success mx-1">
                                    Export to Excel
                                </a>
                            </form>

                            <hr>

                            <?php

                            $limit = 10;
                            $page  = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                            $offset = ($page - 1) * $limit;

                            // Default dates = today
                            $start_date = date('Y-m-d');
                            $end_date   = date('Y-m-d');

                            $show_message = false;

                            // If user provides dates
                            if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {

                                $user_start = $_GET['start_date'];
                                $user_end   = $_GET['end_date'];

                                // Ensure correct order (PHP 5 safe)
                                if ($user_start > $user_end) {
                                    $temp = $user_start;
                                    $user_start = $user_end;
                                    $user_end = $temp;
                                }

                                // Enforce max 3 months
                                $max_end = date('Y-m-d', strtotime('+3 months', strtotime($user_start)));

                                if ($user_end > $max_end) {
                                    // ❌ User exceeded 3 months → show warning and use default dates
                                    $show_message = true;
                                    $start_date = date('Y-m-d');
                                    $end_date   = date('Y-m-d');
                                } else {
                                    // ✅ User dates within 3 months → use them
                                    $start_date = $user_start;
                                    $end_date   = $user_end;
                                }
                            }

                            // Guidance message
                            if ($show_message) {
                                echo '<div class="alert alert-warning text-center">
            <strong>Date Range Not Allowed:</strong>  
            You can only view records for a maximum of <b>3 months</b> at a time.
            Showing records for <b>today</b> instead.
          </div>';
                            }

                            // Filter Setup
                            $where = ["completed = 'yes'"];
                            $params = [];

                            // Optimized date filter
                            $where[] = "request_date >= :start AND request_date < DATE_ADD(:end, INTERVAL 1 DAY)";
                            $params[':start'] = $start_date;
                            $params[':end']   = $end_date;

                            if (!empty($_GET['request_type'])) {
                                $where[] = "request_type = :type";
                                $params[':type'] = $_GET['request_type'];
                            }

                            if (!empty($_GET['search'])) {
                                $where[] = "(hospital_no LIKE :search OR patient_name LIKE :search)";
                                $params[':search'] = '%' . $_GET['search'] . '%';
                            }

                            $where_clause = implode(" AND ", $where);

                            // Get distinct tokens
                            $sql = "SELECT DISTINCT token FROM dialysis WHERE $where_clause ORDER BY id DESC LIMIT $limit OFFSET $offset";
                            $stmt = $db->prepare($sql);
                            $stmt->execute($params);
                            $tokens = $stmt->fetchAll(PDO::FETCH_COLUMN);

                            $sn = $offset + 1;
                            ?>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-center">SN</th>
                                        <th class="text-center">Name / Hospital No.</th>
                                        <th class="text-center">Request Type</th>
                                        <th class="text-center">Number of Session</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($tokens) {
                                        $placeholders = implode(',', array_fill(0, count($tokens), '?'));
                                        $query = "SELECT * FROM dialysis WHERE token IN ($placeholders) AND status = '1' GROUP BY token";
                                        $stmt = $db->prepare($query);
                                        $stmt->execute($tokens);
                                        $dialysis_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                        foreach ($dialysis_records as $dialysis):
                                    ?>
                                            <tr>
                                                <td class="text-center"><?= $sn++ ?></td>
                                                <td><?= $dialysis["patient_name"]; ?> - <b>[<?= $dialysis["hospital_no"]; ?>]</b></td>
                                                <td class="text-center"><?= $dialysis["request_type"]; ?></td>
                                                <td class="text-center"><?= $dialysis["number_of_session"]; ?></td>
                                                <td class="text-center">
                                                    <a href="dialysis.php?p=<?= base64_encode('Open_dialysis') ?>&completed&d=<?= $dialysis['token']; ?>" class="btn btn-sm btn-primary">
                                                        Open
                                                    </a>
                                                </td>
                                            </tr>
                                    <?php
                                        endforeach;
                                    } else {
                                        echo '<tr><td colspan="5" class="text-center">No records found.</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>


                            <?php
                            // Count total
                            $count_sql = "SELECT COUNT(DISTINCT token) as total FROM dialysis WHERE $where_clause";
                            $count_stmt = $db->prepare($count_sql);
                            $count_stmt->execute($params);
                            $total_tokens = $count_stmt->fetchColumn();
                            $total_pages = ceil($total_tokens / $limit);


                            if ($total_pages > 1):

                                $range = 1; // pages before & after current
                            ?>
                                <nav>
                                    <ul class="pagination justify-content-center">

                                        <!-- Previous -->
                                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                                &lt;
                                            </a>
                                        </li>

                                        <!-- First page -->
                                        <li class="page-item <?= ($page == 1) ? 'active' : '' ?>">
                                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">1</a>
                                        </li>

                                        <!-- Dots before -->
                                        <?php if ($page > 3): ?>
                                            <li class="page-item disabled"><span class="page-link">...</span></li>
                                        <?php endif; ?>

                                        <!-- Middle pages -->
                                        <?php
                                        for ($i = max(2, $page - $range); $i <= min($total_pages - 1, $page + $range); $i++):
                                            if ($i == 1 || $i == $total_pages) continue;
                                        ?>
                                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                                    <?= $i ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <!-- Dots after -->
                                        <?php if ($page < $total_pages - 2): ?>
                                            <li class="page-item disabled"><span class="page-link">...</span></li>
                                        <?php endif; ?>

                                        <!-- Last page -->
                                        <?php if ($total_pages > 1): ?>
                                            <li class="page-item <?= ($page == $total_pages) ? 'active' : '' ?>">
                                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>">
                                                    <?= $total_pages ?>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <!-- Next -->
                                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                                &gt;
                                            </a>
                                        </li>

                                    </ul>
                                </nav>
                            <?php endif; ?>



                        </div>


                    </div>
                    <div class="col-md-3">
                        <div class=" wow fadeInLeft animated animated">
                            <?php include_once('_links.php'); ?>
                        </div>
                        <hr>
                        <?php
                        include_once('dialysis/_dialysis_reminder.php');

                        ?>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>