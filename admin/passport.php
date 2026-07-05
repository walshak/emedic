<?php
if (isset($_POST["upload_pass"])) {
    session_start();

    // Config
    define('MAXFILESIZE', 300000); // 300KB max
    define('UPLOAD_DIR', '../uploads/enrollee/');
    define('PASSPORT_WIDTH', 150);  // target passport width
    define('PASSPORT_HEIGHT', 150); // target passport height

    $hosp_no = isset($_POST["hosp_no"]) ? trim($_POST["hosp_no"]) : '';
    $url = isset($_POST["url"]) ? trim($_POST["url"]) : '';
    $complain = "";

    if (!empty($_FILES['file_foto']['tmp_name'])) {
        $file_size = $_FILES['file_foto']['size'];
        $file_tmp  = $_FILES['file_foto']['tmp_name'];

        // Check file size
        if ($file_size <= 0 || $file_size > MAXFILESIZE) {
            $complain = "The passport size should not be more than 300KB";
        } else {
            // Verify image type using getimagesize
            $img_info = @getimagesize($file_tmp);
            if ($img_info === false) {
                $complain = "Invalid image file.";
            } else {
                $mime = $img_info['mime'];
                if ($mime != 'image/jpeg' && $mime != 'image/jpg') {
                    $complain = "Only JPG images are allowed.";
                } else {
                    // Generate filename
                    $pixid = $hosp_no . '.jpg';
                    $target = UPLOAD_DIR . $pixid;

                    // Resize to passport size
                    $src_img = imagecreatefromjpeg($file_tmp);
                    $dst_img = imagecreatetruecolor(PASSPORT_WIDTH, PASSPORT_HEIGHT);

                    // Maintain aspect ratio by fitting & centering
                    $src_w = $img_info[0];
                    $src_h = $img_info[1];
                    $scale = min(PASSPORT_WIDTH / $src_w, PASSPORT_HEIGHT / $src_h);
                    $new_w = (int)($src_w * $scale);
                    $new_h = (int)($src_h * $scale);
                    $dst_x = (PASSPORT_WIDTH - $new_w) / 2;
                    $dst_y = (PASSPORT_HEIGHT - $new_h) / 2;

                    // Fill white background
                    $white = imagecolorallocate($dst_img, 255, 255, 255);
                    imagefill($dst_img, 0, 0, $white);

                    // Copy resized
                    imagecopyresampled($dst_img, $src_img, $dst_x, $dst_y, 0, 0, $new_w, $new_h, $src_w, $src_h);

                    // Save as JPG
                    imagejpeg($dst_img, $target, 90);

                    // Free memory
                    imagedestroy($src_img);
                    imagedestroy($dst_img);

                    // Log action
                    $desc = 'Patient Passport/Image Updated';
                    $staff = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Unknown';
                    $pid = $hosp_no;
                    $pname = '';
                    $action = 'Patient Passport/Updated';
                    // include_once("../logs.php");

                    $complain = "Passport uploaded successfully.";
                }
            }
        }
    } else {
        $complain = "No passport selected.";
    }

    echo $complain;
}


echo '<a href="index.php?ptm=all/' . htmlspecialchars($_POST["hosp_no"]) . '&app=" class="btn btn-xs btn-danger"><i class="fa fa-arrow"></i> Go Back</a>';
