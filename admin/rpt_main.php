<div class="row">

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Report Manager</h5>
                </div>


                <div class="ibox-content">

                    <a href="transc.php" class="btn btn-app"><i class="fa fa-money"></i>Transactions</a>
                    <a href="patients_balance2.php?owing" class="btn btn-app"><i class="fa fa-money"></i>
                        <strong style="color: red;">Patient DEBT</strong></a>

                    <a href="patients_balance2.php?deposit" class="btn btn-app"><i class="fa fa-money"></i>
                        <strong style="color: blue;">Patient Deposits</strong></a>
                    <!--  <a href="../accounts/index.php" class="btn btn-app"><i class="fa fa-money"></i>GL</a> -->
                    <a href="discount.php" class="btn btn-app"><i class="fa fa-money"></i>
                        <strong style="color: blue;">Discount Reports</strong></a>
                    <a href="index.php?updown" class="btn btn-app"><i class="fa fa-database"></i>Upload & Download</a>


                    <?php if ($_SESSION['see_statistic_rpt'] == 1) { ?>
                        <hr>
                        <a href="index.php?datab" class="btn btn-app"><i class="fa fa-database"></i>Statistic/Data Bank</a>


                    <?php } ?>
                    <br>
                    <br>
                </div>

            </div>
        </div>

    </div>
</div>