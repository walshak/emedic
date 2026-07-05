<?php include("../Connections/Conn.php");?>

<?php
session_start();
include('../inc/header.php');

//get income and expense classes
$classes = $db->query("SELECT * FROM chart_class WHERE cid = 4 OR cid = 5");

$classes = $classes->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['start']) && isset($_GET['end'])) {
    $the_stetment = 1;
}
include_once('inc/functions.php');
redirect_to_active_year();
?>

<body class="fixed-navigation">
	<div id="wrapper">
<?php include("nav_side.php"); ?>
		
 <?php $netTotal = 0; ?>
		
		
        <div id="page-wrapper" class="gray-bg sidebar-content">

            <?php include '../../inc/nav_header.php'; ?>
            <div class="">
                <div class="row">
                    <div class="col-lg-12">
          					<div class="ibox float-e-margins">
								<div class="ibox-title">

									<h5>Accounts Dashboard</h5> 
									
									
								</div>
                            <div class="ibox-content">

									<form action="" method="get" class="form-inline">
                                        <div class="form_sep" align="">
										<label for="">Start Date</label>
                                        <input type="date" name="start" class="form-control" value="<?php echo isset($_GET['start']) ? $_GET['start'] : ''; ?>" required>
									
										&nbsp; 
											
										<label for="">End Date</label>
                                        <input type="date" name="end" class="form-control" value="<?php echo isset($_GET['end']) ? $_GET['end'] : ''; ?>" required>
										&nbsp; 
											
										<button type="submit" class="btn btn-primary">Display </button>
										</div>
										<br>
										
                                    </form>
								
								
<div class="table-responsive" id="chart_groups">
	
											<h1>Balance Sheet</h1>
											<h3>For the period starting <?php echo date('d M Y', strtotime($_GET['start'])); ?> and ending 
												<?php echo date('d M Y', strtotime($_GET['end']));//echo $_GET['end']; ?></h3>
								
								
								
                                    <hr>
									
									
                                    <?php if (isset($the_stetment)) : ?>
									
									
									<table width="100%" style="font-size: 14px; " >
										<tr>
											
											<td width="40%">
											<h2><strong><i>Assets</i></strong></h2>
												
	                                                    <?php
                                                        $groups = $db->prepare("SELECT * FROM chart_groups WHERE class_id = ?");
                                                        $groups->execute([1]);
                                                        $groups = $groups->fetchAll(PDO::FETCH_ASSOC);
                                                        ?>
                                                        <table class="table" style="font-size: 16px; font-style: italic; ">
														<?php foreach ($groups as $group) :
														 $gTotal = getGroupTotal($group['id'], $_GET['start'], $_GET['end'])['bal'];	
															
															?>										
											
												
													<?php if($gTotal > 0){ ?>
															<tr>
																<td>&nbsp;&nbsp;</td>
																<td><?= $group['name'] ?></td>
																<td>
																	<div align="right">		
																	<?php
																	echo format_accounting($gTotal, '');
																	?></div>
																</td>
															</tr>												
													<?php } ?>
											
														<?php endforeach ?>
														</table>
												
								<table width="100%">
									<tr>
										<td><h2><strong><i>TOTAL OF ASSETS:</i></strong></h2></td>
										<td><div align="right"><h2><strong><i>	
	<span style="text-decoration: underline double; border-top: 1px solid #000; display: inline-block;  padding: 5px;">											
	<?php $cTotal = getClassTotal(1, $_GET['start'], $_GET['end'])['bal'];
	echo format_accounting($cTotal, ''); 
															
?></span>	</i></strong></h2></div></td>
									</tr>				
								</table>				
											</td>
										
											
											<td width="5%">&nbsp;</td>
											
											
											<td width="40%">
											<h2><strong><i>Liabilities</i></strong></h2>
												
												
	                                                    <?php
															$groups = $db->prepare("SELECT * FROM chart_groups WHERE class_id = ?");
															$groups->execute([2]);
															$groups = $groups->fetchAll(PDO::FETCH_ASSOC);
                                                        ?>
                                                        <table class="table" style="font-size: 16px; font-style: italic; ">
														<?php foreach ($groups as $group) :
														 $gTotal = getGroupTotal($group['id'], $_GET['start'], $_GET['end'])['bal'];	
															
															?>										
											
												
													<?php if($gTotal > 0 or $gTotal < 0){ ?>
															<tr>
																<td>&nbsp;&nbsp;</td>
																<td><?= $group['name'] ?></td>
																<td>
																	<div align="right">		
																	<?php
																	echo format_accounting($gTotal, '');
																	?></div>
																</td>
															</tr>												
													<?php } ?>
											
														<?php endforeach ?>
														</table>
												
								<table width="100%">
									<tr>
										<td><h2><strong><i>TOTAL OF LIABILITIES:</i></strong></h2></td>
										<td><div align="right"><h2><strong><i>	<?php $cTotal = getClassTotal(2, $_GET['start'], $_GET['end'])['bal'];
	echo format_accounting($cTotal, ''); 
?>	</i></strong></h2></div></td>
									</tr>				
								</table>															
											
										
	<br>											
	<br>											
	<br>
												
												
	<h2><strong><i>Equity</i></strong></h2>											
										
	
	                                       <?php
												$Over_total_Equity=0;
															$groups = $db->prepare("SELECT * FROM chart_groups WHERE class_id = ?");
															$groups->execute([3]);
															$groups = $groups->fetchAll(PDO::FETCH_ASSOC);
                                                        ?>
                                                        <table class="table" style="font-size: 16px; font-style: italic; ">
														<?php 
														
														 foreach ($groups as $group) :
														 $gTotal = getGroupTotal($group['id'], $_GET['start'], $_GET['end'])['bal'];	
														 $Over_total_Equity = $Over_total_Equity  + $gTotal;
															
														?>										
											
												
													<?php if($gTotal > 0 or $gTotal < 0){ ?>
															<tr>
																<td>&nbsp;&nbsp;</td>
																<td><?= $group['name'] ?></td>
																<td>
																	<div align="right">		
																	<?php
																	echo number_format(abs($gTotal),2), '';
																	$Over_total_Equity = $Over_total_Equity  + abs($gTotal);
																	?></div>
																</td>
															</tr>												
													<?php } ?>
											
															
									<tr>
															
																<td>&nbsp;&nbsp;</td>
																<td>Retained Earning</td>
																<td>
																	<div align="right">		
																	<?php
																	
																		
$net = (makePositive(getClassTotal('1', $_GET['start'], $_GET['end'])['bal']) - 
								 makePositive(getClassTotal('2', $_GET['start'], $_GET['end'])['bal'] + 
											  getClassTotal(3, $_GET['start'], $_GET['end'])['bal']));																	
												echo number_format($net,2);						
																		
																	?></div>
																</td>															
									</tr>						
															

															
															
															
														<?php endforeach ?>
														</table>											
												
								<table width="100%">
									<tr>
										<td><h2><strong><i>TOTAL OF EQUITY:</i></strong></h2></td>
										<td><div align="right"><h2><strong><i>	<?php 
	
	$cTotal = getClassTotal(3, $_GET['start'], $_GET['end'])['bal'];
		echo number_format(abs($cTotal) + $net ,2); 
															
?>	</i></strong></h2></div></td>
									</tr>				
								</table>								
												
												
								
												
							<hr>					
												
							<table width="100%">
									<tr>
										<td><h2><strong><i>TOTAL OF LIABILITIES + EQUITY:</i></strong></h2></td>
										<td><div align="right"><h2><strong><i><br>	
											
<span style="text-decoration: underline double; border-top: 1px solid #000; display: inline-block;  padding: 5px;">	<?php 
											
	$liabilty = getClassTotal('2', $_GET['start'], $_GET['end'])['bal'];										
	echo number_format(abs($cTotal) + $net + abs($liabilty), 2);
?></span>	</i></strong></h2></div></td>
									</tr>				
								</table>															
																						
												
												
											
											</td>
										
										</tr>
									</table>
	
                                    <?php endif ?>
                            </div>
								
								<button class="btn btn-success"  onclick="printDiv('chart_groups')"><i class="fa fa-print">&nbsp; Print Report</i></button>
								
                            </div>
                        </div>
                    </div>
                </div>


                <?php include '../../inc/footer.php'; ?>

            </div>
        </div>


        <?php 		 include('../modal_lock.php'); ?>
        <?php include '/inc/footer_scripts.php'; ?>


        <script>
            <?php
            if ($error_status == 1) { ?>toastr.error('<?php echo $error_msg; ?>', 'Error', {
                timeOut: 5000
            })
            <?php } elseif ($error_status == 2) { ?>toastr.success(' <?php echo $error_msg; ?> ', 'Success', {
                timeOut: 5000
            })
            <?php } ?>
        </script>

        <script src="../../js/plugins/dataTables/jquery.dataTables.js"></script>
        <script src="../../js/plugins/dataTables/dataTables.bootstrap.js"></script>
        <script src="../../js/plugins/dataTables/dataTables.responsive.js"></script>
        <script src="../../js/plugins/dataTables/dataTables.tableTools.min.js"></script>
        <script>
            function printDiv(divId) {
                var content = document.getElementById(divId).innerHTML;
                var popupWindow = window.open('', '_blank', 'width=600,height=600');
                popupWindow.document.open();
                popupWindow.document.write('<html><head><title>' + document.title + '</title>');
                // Reference the external stylesheet from the main page
                popupWindow.document.write('<link rel="stylesheet" type="text/css" href="../../css/bootstrap.min.css">');
                popupWindow.document.write('</head><body>');
                popupWindow.document.write(content);
                popupWindow.document.write('</body></html>');
                popupWindow.document.close();
                popupWindow.print();
            }
        </script>
<script src="../js/idle.js"></script>
</body>

</html>