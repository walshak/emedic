 <?php include("../Connections/Conn.php"); ?>
 <?php
  session_start();

  if (isset($_POST["labour_summary"])) {
    $hos_no = $_POST["labour_summary"];
    include("../inc/labour_summary.php");
  }


  if (isset($_POST["bio_data_id"])) {
    $hos_no = $_POST["bio_data_id"];
    include("../inc/bio_data.php");
  }

  if (isset($_POST["note_inv_id"])) {
    $note_inv_id = $_POST["note_inv_id"];
    $part = explode("__", $note_inv_id);
    $sn = $part[0];


    $stmt = $db->prepare('SELECT * FROM patient_ap_services WHERE sn = :sn');
    $stmt->bindParam(':sn', $sn);
    $stmt->execute();
    $row_d = $stmt->fetch(PDO::FETCH_ASSOC);
    $emr = $row_d['hospital_no'];
    $item_services = $row_d['item_services'];
    $pay = $row_d['pay'];
  ?>

   <table class="table table-bordered">
     <tbody>

       <tr>
         <td>Item Services: </td>
         <td><?php echo $row_d['item_services']; ?></td>
       </tr>
       <tr>
         <td>Hospital Price: </td>
         <td><?php echo $row_d['hosp_price']; ?></td>
       </tr>
       <tr>
         <td>Claim Price: </td>
         <td><?php echo $row_d['claim_amt']; ?></td>
       </tr>
       <tr>
         <td>Quantity: </td>
         <td><?php echo $row_d['qty']; ?></td>
       </tr>
       <tr>
         <td>Invoice Number: </td>
         <td><?php echo $row_d['invoice_no']; ?></td>
       </tr>
       <tr>
         <td>Invoice Date: </td>
         <td><?php if ($row_d['invoice_date'] == '' or $row_d['invoice_date'] == '0000-00-00') {
                echo '';
              } else {
                echo date("d M Y H:i:s a ", strtotime($row_d['invoice_date']));
              } ?></td>
       </tr>
       <tr>
         <td>Invoice Status: </td>
         <td><?php if ($row_d['invoice_status'] == 0) {
                echo 'Invoice Pending';
              } else {
                echo 'Invoice Ready';
              } ?></td>
       </tr>
       <tr>
         <td>Invoice By: </td>
         <td><?php echo $row_d['invoice_by']; ?></td>
       </tr>
       <tr>
         <td>Amount: </td>
         <td><?php echo $pay; ?></td>
       </tr>

       <tr>
         <td>Credit Status: </td>
         <td><?php if ($row_d['cr'] == 1) {
                echo 'Credit';
              } else {
                echo 'No';
              } ?></td>
       </tr>
       <tr>
         <td>Dispense Status: </td>
         <td><?php if ($row_d['dsp_by'] != '') {
                echo 'Delivered';
              } else {
                echo 'No';
              } ?></td>
       </tr>
       <tr>
         <td>Paid Status: </td>
         <td><?php if ($row_d['paystatus'] == 1) {
                echo 'Amount Paid';
              } else {
                echo 'Pending';
              } ?></td>
       </tr>

       </tr>

     </tbody>
   </table>

 <?php }

  if (isset($_POST["reverse_id"])) {
    include("../inc/dsp_rvs.php");
  }

  ?>



 <div class="modal inmodal fade" id="print_lab_mdl" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
   <div class="modal-dialog modal-lg">
     <div class="modal-content">
       <div class="modal-header">
         <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
         <h4 class="modal-title" id="">Investigation</h4>
       </div>
       <div class="modal-body" id="print_lab_body">
       </div>
     </div>
   </div>
 </div>


 <script>
   $(document).ready(function() {
     $('#div2').hide('fast');
     $('#div1').hide('fast');

     $('#drug_replace').click(function() {
       $('#div2').hide('fast');
       $('#div1').show('fast');
     });
     $('#drug_new').click(function() {
       $('#div1').hide('fast');
       $('#div2').show('fast');
     });
   });
 </script>