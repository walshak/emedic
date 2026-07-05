<?php
include("../Connections/Conn.php");
session_start();
?>
<!DOCTYPE html>
<html>
<?php include("../inc/header.php"); ?>

</head>
<?php
if (strtoupper($_SESSION['password']) == 'STAFF123') {
  header("location:profile/index.php?profile=$uname&changepassword");
}


$setdate = date("Y-m-d");
$year = date("Y");
$patient_acct_status = 1;

?>

<title>WebMedic | <?php echo $_SESSION['Designation']; ?></title>

</head>

<body class="fixed-navigation">
  <div id="wrapper">

    <nav class="navbar-default navbar-static-side" role="navigation">
      <div class="sidebar-collapse">
        <ul class="nav" id="side-menu">
          <li class="nav-header">
            <div class="dropdown profile-element"> <span>
                <img alt="image" class="img-circle" src="<?php if (file_exists(staff_p . 'port_' . $uname . '.' . 'jpg')) {
                                                            echo staff_p . 'port_' . $uname . '.' . 'jpg';
                                                          } else {
                                                            echo '../img/user_avatar_lg.png';
                                                          } ?>" height="50" width="50">

              </span>
              <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                <span class="clear"> <span class="block m-t-xs"> <strong class="font-bold"><?php echo $fullname ?></strong>
                  </span> <span class="text-muted text-xs block"><?php echo $_SESSION['Designation'] ?> <b class="caret"></b></span> </span> </a>
              <ul class="dropdown-menu animated fadeInRight m-t-xs">
                <li><a href="index.php?profile=<?php echo $_SESSION['username'] ?>">Profile</a></li>
                <li><a href="mailbox.php">Mailbox</a></li>
                <li class="divider"></li>
                <li><a href="../index.php">Logout</a></li>
              </ul>
            </div>
            <div class="logo-element">
              IN+
            </div>
          </li>

          <li>
            <a href="../<?php echo $_SESSION['navigate']; ?>/index.php"><i class="fa fa-dashboard"></i> <span class="nav-label">Main Dashboard</span> </a>
          </li>

          <?php include("../inc/nav_side_profile.php"); ?>

        </ul>

      </div>
    </nav>


    <div id="page-wrapper" class="gray-bg sidebar-content">
      <div class="">
        <?php
        $isAdmin = $_SESSION['rights'] == 'MD' || $_SESSION['rights'] == 'RE'
          || $_SESSION['rights'] == 'US' || $_SESSION['rights'] == 'GM' || $_SESSION['rights'] == 'AO';

        $sql = $isAdmin ? "SELECT sn,department FROM department order by department" : "SELECT * FROM department WHERE sn = ?";
        $params = $isAdmin ? [] : [$_SESSION['dept_id']];

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        // Fetch results
        $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        include_once('templates_page.php');

        ?>
      </div>
    </div>

    <?php include('../modal_lock.php'); ?>
    <?php include("../inc/footer_scripts.php"); ?>


    <script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
    <script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
    <script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>
    <script src="../js/vendors/editor/dist/trumbowyg.js"></script>
    <script src="../js/vendors/editor/plugins/fontsize/trumbowyg.fontsize.js"></script>
    <script src="../js/vendors/editor/plugins/colors/trumbowyg.colors.js"></script>
    <script>
      $(document).ready(function() {
        $('.dataTables-example').dataTable({
          pageLength: 20,
          responsive: true,
          "dom": 'T<"clear">lfrtip',
          "tableTools": {
            "sSwfPath": "js/plugins/dataTables/swf/copy_csv_xls_pdf.swf"
          }
        });

        $('.trumbowygEditor').trumbowyg({
          btns: [
            ['viewHTML'],
            ['undo', 'redo'], // Only supported in Blink browsers
            ['formatting'],
            ['strong', 'em', 'del'],
            ['superscript', 'subscript'],
            ['fontsize'],
            ['foreColor', 'backColor'],
            ['link'],
            ['insertImage'],
            ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
            ['unorderedList', 'orderedList'],
            ['horizontalRule'],
            ['removeformat'],
            ['fullscreen']
          ],
          plugins: {
            fontsize: {
              sizeList: [
                '12px',
                '14px',
                '16px',
                '18px',
                '20px',
                '24px',
                '32px',
                '48px',
              ]
            }
          }
        });

        $('#trumbowygEditor')
          .trumbowyg()
          .on('tbwchange', function() {
            $('#trumbowygEditor').val($('#trumbowygEditor').html())
          });

      });
    </script>
    <script src="../js/idle.js"></script>
</body>

</html>