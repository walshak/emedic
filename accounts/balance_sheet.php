<?php include("../Connections/Conn.php");?>

<?php
session_start();
include('../inc/header.php');

// Fetch hospital details from the database
$hospital_details_query = 'SELECT * FROM hospital_details LIMIT 1';
$hospital_details_stmt = $db->prepare($hospital_details_query);
$hospital_details_stmt->execute();
$hospital_details = $hospital_details_stmt->fetch(PDO::FETCH_ASSOC);

// Construct the hospital table HTML
$hospital_table = '<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width:100%;">';
$hospital_table .= '<tr><td width="50%" align="left"><img src="../img/logo.png" width="196" height="111"></td>';
$hospital_table .= '<td width="50%" align="right"><div style="font-size:18px; font:Verdana, Geneva, sans-serif"><strong>' . $hospital_details['name'] . '</strong></div>';
$hospital_table .= '<br><div style="font-size:14px">' . $hospital_details['address'] . '<br><br>' . $hospital_details['phones'] . '</div></td></tr>';
$hospital_table .= '</table><br>';

//get income and expense classes
$classes = $db->query("SELECT * FROM chart_class WHERE cid = 4 OR cid = 5");

$classes = $classes->fetchAll(PDO::FETCH_ASSOC);

// Get parameters
$fiscal_year_id = isset($_GET['fiscal_year']) && $_GET['fiscal_year'] != '' ? $_GET['fiscal_year'] : null;

if ($fiscal_year_id) {
    $stmt = $db->prepare("SELECT begin, end FROM chart_fiscal_year WHERE id = ?");
    $stmt->execute([$fiscal_year_id]);
    $fy = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $stmt = $db->query("SELECT id, begin, end FROM chart_fiscal_year WHERE closed = 0 LIMIT 1");
    $fy = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($fy) {
        $fiscal_year_id = $fy['id'];
    }
}

if (isset($fy) && $fy) {
    $start_date = $fy['begin'];
    $end_date = $fy['end'];
    $_GET['start'] = $start_date;
    $_GET['end'] = $end_date;
} else {
    $start_date = date('Y-01-01');
    $end_date = date('Y-12-31');
    $_GET['start'] = $start_date;
    $_GET['end'] = $end_date;
}

$the_stetment = 1;
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

									<!-- Filter Form -->
									<div class="row">
										<div class="col-md-12">
											<form method="GET" class="well" style="background: #f8f9fa; border: 1px solid #e9ecef; padding: 20px; border-radius: 8px;">

												<!-- Date Selection Row -->
				<div class="row mb-3">
					<?php
						$active_fy = get_active_year();
					?>
					<?php if ($active_fy): ?>
					<div class="col-md-12" style="margin-bottom: 10px;">
						<div class="alert alert-info" style="padding: 8px 14px; margin-bottom: 0;">
							<i class="fa fa-info-circle"></i>
							<strong>Active Fiscal Year:</strong>
							<?= date('M d, Y', strtotime($active_fy['begin'])) ?> &ndash; <?= date('M d, Y', strtotime($active_fy['end'])) ?>
							&nbsp;&mdash;&nbsp;<small class="text-muted">Select a fiscal year below to scope this report to that accounting period.</small>
						</div>
					</div>
					<?php endif; ?>

					<div class="col-md-6">
						<?php echo render_fiscal_year_filter($fiscal_year_id); ?>
						<input type="hidden" id="start" name="start" value="<?php echo $start_date; ?>">
						<input type="hidden" id="end" name="end" value="<?php echo $end_date; ?>">
					</div>

					<div class="col-md-6">
						<div class="form-group">
							<label class="control-label"><strong>Actions</strong></label>
							<div style="margin-top: 8px;">
								<button type="submit" class="btn btn-primary" title="Click to fetch data">
									<i class="fa fa-search"></i> <strong>DISPLAY</strong>
								</button>
								<button type="button" class="btn btn-success" onclick="exportToCSV()" title="Export to CSV">
									<i class="fa fa-download"></i> Export CSV
								</button>
								<button type="button" class="btn btn-primary" onclick="printDiv('chart_groups')" title="Print Report">
									<i class="fa fa-print"></i> Print
								</button>
							</div>
						</div>
					</div>
				</div>

											</form>
										</div>
									</div>
								
								
								<?php if (isset($the_stetment)) : ?>
								<div class="table-responsive" id="chart_groups">
	
											<h1>Balance Sheet</h1>
											<h3>For the period starting <?php echo date('d M Y', strtotime($_GET['start'])); ?> and ending 
												<?php echo date('d M Y', strtotime($_GET['end']));//echo $_GET['end']; ?></h3>
								
								
								
                                    <hr>
									
									
                                    
									
									
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
											
												
													<?php if($gTotal != 0){ ?>
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
			// Hospital header HTML for printouts
			var hospitalHeader = `<?php echo addslashes($hospital_table); ?>`;

			// Quick date shortcut functions removed as Balance Sheet uses Fiscal Bounds

			function exportToCSV() {
				var startDate = document.querySelector('input[name="start"]').value;
				var endDate = document.querySelector('input[name="end"]').value;

				if (!startDate || !endDate) {
					alert('Please select start and end dates first.');
					return;
				}

				var exportUrl = 'balance_sheet_export_csv.php?start=' + encodeURIComponent(startDate) +
					'&end=' + encodeURIComponent(endDate);

				var link = document.createElement('a');
				link.href = exportUrl;
				link.download = 'Balance_Sheet_' + startDate + '_to_' + endDate + '.csv';
				link.style.display = 'none';

				document.body.appendChild(link);
				link.click();
				document.body.removeChild(link);
			}

            function printDiv(divId) {
                var content = document.getElementById(divId).innerHTML;
                var startDate = document.getElementById('start') ? document.getElementById('start').value : '';
                var endDate = document.getElementById('end') ? document.getElementById('end').value : '';
                
                var filterSummary = '<div style="text-align:center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; font-family: arial;">' +
                    '<h2 style="margin:0; padding:0; color: #333;">Balance Sheet</h2>' +
                    '<p style="margin:5px 0 0 0; font-size: 14px; color: #555;">' +
                    '<strong>Date Range:</strong> ' + startDate + ' to ' + endDate +
                    '</p></div>';

                var popupWindow = window.open('', '_blank', 'width=900,height=900');
                popupWindow.document.open();
                popupWindow.document.write('<html><head><title>Balance Sheet Report</title>');
                // Reference the external stylesheet from the main page
                popupWindow.document.write('<link rel="stylesheet" type="text/css" href="../../css/bootstrap.min.css">');
				popupWindow.document.write(`
                    <style>
                        body { font-size: 14px; color: #222; background: #fff; }
                        .table { width: 100%; border-collapse: collapse !important; margin-bottom: 20px;}
                        .table td { padding: 5px; border: none !important; }
                        a { text-decoration: none !important; color: inherit !important; }
                                                /* Hide original headings in the print popup to prevent duplication */
                        body > h1, body > h3, .text-center.mb-4 { display: none !important; }
                        @media print {
                            a[href]:after { content: none !important; }
                        }
                    </style>
                `);
                popupWindow.document.write('</head><body>');
				popupWindow.document.write(hospitalHeader);
                popupWindow.document.write(filterSummary);
                popupWindow.document.write(content);
                popupWindow.document.write('</body></html>');
                popupWindow.document.close();
                setTimeout(function() {
                    popupWindow.focus();
                    popupWindow.print();
                }, 1000);
            }
        </script>
<script src="../js/idle.js"></script>
</body>

</html>