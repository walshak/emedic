<div class="row">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h2>Filter Admin Users Rights Log</h2>
            </div>

            <div class="ibox-content">

                <?php
                $n = 1;
                $tableNames   = $db->query("SELECT DISTINCT table_name FROM admin_users_rights_log ORDER BY table_name")->fetchAll(PDO::FETCH_COLUMN);
                $usernames    = $db->query("SELECT DISTINCT username FROM admin_users_rights_log ORDER BY username")->fetchAll(PDO::FETCH_COLUMN);
                $changedBys   = $db->query("SELECT DISTINCT changed_by FROM admin_users_rights_log ORDER BY changed_by")->fetchAll(PDO::FETCH_COLUMN);

                $results = array();
                $error = '';

                $username   = isset($_GET['username']) ? trim($_GET['username']) : '';
                $changed_by = isset($_GET['changed_by']) ? trim($_GET['changed_by']) : '';
                $table_name = isset($_GET['table_name']) ? trim($_GET['table_name']) : '';
                $date_from  = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
                $date_to    = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';

                if (isset($_GET['search'])) {
                    // --- Validate mandatory filters ---
                    if ($date_from === '' || $date_to === '' || $table_name === '') {
                        $error = "Please select a date range and a table name.";
                    } else {
                        $sql = "SELECT * FROM admin_users_rights_log 
                WHERE table_name = :table_name 
                AND DATE(changed_at) BETWEEN :date_from AND :date_to";
                        $params = array(
                            ':table_name' => $table_name,
                            ':date_from'  => $date_from,
                            ':date_to'    => $date_to
                        );

                        if ($username !== '') {
                            $sql .= " AND username = :username";
                            $params[':username'] = $username;
                        }
                        if ($changed_by !== '') {
                            $sql .= " AND changed_by = :changed_by";
                            $params[':changed_by'] = $changed_by;
                        }

                        $sql .= " ORDER BY changed_at DESC";
                        $stmt = $db->prepare($sql);
                        $stmt->execute($params);
                        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    }
                }
                ?>




                <?php if ($error !== ''): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="get">
                    <label>Username:
                        <select name="username" class="form-control">
                            <option value="">--All--</option>
                            <?php foreach ($usernames as $u): ?>
                                <option value="<?php echo htmlspecialchars($u); ?>" <?php if ($username === $u) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($u); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Changed By:
                        <select name="changed_by" class="form-control">
                            <option value="">--All--</option>
                            <?php foreach ($changedBys as $cb): ?>
                                <option value="<?php echo htmlspecialchars($cb); ?>" <?php if ($changed_by === $cb) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($cb); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>From Date:
                        <input type="date" name="date_from" required value="<?php echo htmlspecialchars($date_from); ?>" class="form-control">
                    </label>
                    <label>To Date:
                        <input type="date" name="date_to" required value="<?php echo htmlspecialchars($date_to); ?>" class="form-control">
                    </label>
                    <label>Table Name:
                        <select name="table_name" class="form-control" required>
                            <option value="">--Select--</option>
                            <?php foreach ($tableNames as $t): ?>
                                <option value="<?php echo htmlspecialchars($t); ?>" <?php if ($table_name === $t) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($t); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>



                    <button type="submit" name="search" value="1" class="btn btn-success btn btn-sm">Search</button>

                    &nbsp;:&nbsp;

                    <a rel="" href="index.php?rit" class="btn btn-danger btn btn-sm"><i class="fa fa-times"></i>&nbsp;Close</a>

                    <input type="hidden" name="right_report" value="">
                </form>

                <?php if (!empty($results)): ?>
                    <table class="table table-striped table-bordered">
                        <tr>
                            <th>Log ID</th>
                            <th>Username</th>
                            <th>Changed Field</th>
                            <th>Old Value</th>
                            <th>New Value</th>
                            <th>Changed At</th>
                            <th>Changed By</th>
                            <th>Table Name</th>
                        </tr>
                        <?php foreach ($results as $row): ?>
                            <tr>
                                <td><?php echo $n++; ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['changed_field']); ?></td>
                                <td><?php echo htmlspecialchars($row['old_value']); ?></td>
                                <td><?php echo htmlspecialchars($row['new_value']); ?></td>
                                <td><?php echo htmlspecialchars($row['changed_at']); ?></td>
                                <td><?php echo htmlspecialchars($row['changed_by']); ?></td>
                                <td><?php echo htmlspecialchars($row['table_name']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php elseif (isset($_GET['search']) && $error === ''): ?>
                    <p>No records found.</p>
                <?php endif; ?>




            </div>

        </div>
    </div>
</div>