     <?php
        session_start();
        include("../Connections/Conn.php");

        $post_keys = array_keys(array_filter($_POST));
        $hospital_no = $_POST['hospital_no'];
        $date_ap = $_POST['date_ap'];
        /// $vital_lock = $_POST['vital_lock'];


        $columns = '';
        $col_values = '';

        $keys = [];

        $height = floatval($_POST['height']);
        $weight = floatval($_POST['weight']);

        $bmi = '';
        if ($height != 0 && $weight != 0) {
            $bmi = $weight / ($height * $height);
            $bmi = round($bmi, 2);
            // BMI = weight (in kilograms) / (height (in meters))^2
        }

        $col_value_arr = [$hospital_no, $date_ap, $_SESSION["fullname"], $bmi];
        foreach ($post_keys as $key => $_key_) {
            if ($_key_ === 'hospital_no' || $_key_ === 'vistal_hx' || $_key_ === 'date_ap') {
                continue;
            }

            $value = $_POST[$_key_];


            if ($_key_ === 'bp' || $_key_ === 'bp2') {
                $_key_ = 'bp';
                $value1 = isset($_POST['bp']) ? $_POST['bp'] : "";
                $value2 = isset($_POST['bp2']) ? $_POST['bp2'] : "";
                $value = $value1 . '/' . $value2;
            }

            if (!in_array($_key_, $keys)) {
                array_push($col_value_arr, $value);
                array_push($keys, $_key_);
                $columns .= " $_key_ ";
                $col_values .= " ? ";
                if ($key < count($post_keys) - 1) {
                    $columns .= ",";
                    $col_values .= ",";
                }
            }
        }

        $columns = rtrim($columns, ','); // Remove the last comma from columns
        $col_values = rtrim($col_values, ','); // Remove the last comma from column values
        if (!empty($col_values)) {
            $placeholders = rtrim(str_repeat('?,', count($col_value_arr)), ',');
            $sql = "INSERT INTO `vital_sign` (`hospital_no`, `date_ap`, `prepared_by`,`bmi`, $columns) VALUES ( $placeholders)";
            $stmt = $db->prepare($sql);
            $saved = $stmt->execute($col_value_arr);
            if ($saved) {

                $updateSQL = "UPDATE apptm SET vital_lock=1 WHERE hospital_no=:hospital_no and vital_lock=0";
                $sql = $db->prepare($updateSQL);
                $sql->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
                $sql->execute();

                echo 'Success: Vitals Saved Successfully!';
            } else {
                echo 'Error: Vitals could not be saved';
            }
        }

        exit;
        ?>