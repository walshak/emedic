<div class="footer">
     <div class="pull-right">
          <!-- 10GB of <strong>250GB</strong> Free.-->
     </div>
     <div>
          <?php if ($_SESSION['h_code'] == 'xyz') { ?>
          <?php } elseif ($_SESSION['h_code'] == 'police') { ?>
               <strong>Powered by HEALTH BRIDGE</strong> <?php /// echo date("Y")
                                                            ?>
          <?php } else { ?>


               <strong>Copyright</strong> Prosoft Systems &copy; <?php echo date("Y") ?>
          <?php } ?>
     </div>
</div>

<?php
