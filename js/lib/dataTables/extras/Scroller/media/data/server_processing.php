<?php
	
	
	
	$Vxnttlwaqil2 = array( 'id', 'firstname', 'surname', 'zip', 'country' );
	
	
	$Vr2kzemvwvx1 = "id";
	
	
	$V1sefvpnx0kb = "massive";
	
	
	$Vnlrsngknoho['user']       = "";
	$Vnlrsngknoho['password']   = "";
	$Vnlrsngknoho['db']         = "";
	$Vnlrsngknoho['server']     = "localhost";
	
	
	include( $_SERVER['DOCUMENT_ROOT']."/datatables/mysql.php" );
	
	
	
	
	
	$Vnlrsngknoho['link'] =  mysql_pconnect( $Vnlrsngknoho['server'], $Vnlrsngknoho['user'], $Vnlrsngknoho['password']  ) or
		die( 'Could not open connection to server' );
	
	mysql_select_db( $Vnlrsngknoho['db'], $Vnlrsngknoho['link'] ) or 
		die( 'Could not select database '. $Vnlrsngknoho['db'] );
	
	
	
	$Vfcyeppe4jwd = "";
	if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
	{
		$Vfcyeppe4jwd = "LIMIT ".mysql_real_escape_string( $_GET['iDisplayStart'] ).", ".
			mysql_real_escape_string( $_GET['iDisplayLength'] );
	}
	
	
	
	$Va0x0ubrmc3g = "";
	if ( isset( $_GET['iSortCol_0'] ) )
	{
		$Va0x0ubrmc3g = "ORDER BY  ";
		for ( $V4jxpnh1o213=0 ; $V4jxpnh1o213<intval( $_GET['iSortingCols'] ) ; $V4jxpnh1o213++ )
		{
			if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$V4jxpnh1o213]) ] == "true" )
			{
				$Va0x0ubrmc3g .= $Vxnttlwaqil2[ intval( $_GET['iSortCol_'.$V4jxpnh1o213] ) ]."
				 	".mysql_real_escape_string( $_GET['sSortDir_'.$V4jxpnh1o213] ) .", ";
			}
		}
		
		$Va0x0ubrmc3g = substr_replace( $Va0x0ubrmc3g, "", -2 );
		if ( $Va0x0ubrmc3g == "ORDER BY" )
		{
			$Va0x0ubrmc3g = "";
		}
	}
	
	
	
	$Vl0vuemp40kl = "";
	if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
	{
		$Vl0vuemp40kl = "WHERE (";
		for ( $V4jxpnh1o213=0 ; $V4jxpnh1o213<count($Vxnttlwaqil2) ; $V4jxpnh1o213++ )
		{
			$Vl0vuemp40kl .= $Vxnttlwaqil2[$V4jxpnh1o213]." LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' OR ";
		}
		$Vl0vuemp40kl = substr_replace( $Vl0vuemp40kl, "", -3 );
		$Vl0vuemp40kl .= ')';
	}
	
	
	for ( $V4jxpnh1o213=0 ; $V4jxpnh1o213<count($Vxnttlwaqil2) ; $V4jxpnh1o213++ )
	{
		if ( isset($_GET['bSearchable_'.$V4jxpnh1o213]) && $_GET['bSearchable_'.$V4jxpnh1o213] == "true" && $_GET['sSearch_'.$V4jxpnh1o213] != '' )
		{
			if ( $Vl0vuemp40kl == "" )
			{
				$Vl0vuemp40kl = "WHERE ";
			}
			else
			{
				$Vl0vuemp40kl .= " AND ";
			}
			$Vl0vuemp40kl .= $Vxnttlwaqil2[$V4jxpnh1o213]." LIKE '%".mysql_real_escape_string($_GET['sSearch_'.$V4jxpnh1o213])."%' ";
		}
	}
	
	
	
	$Viu13fvmgfst = "
		SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $Vxnttlwaqil2))."
		FROM   $V1sefvpnx0kb
		$Vl0vuemp40kl
		$Va0x0ubrmc3g
		$Vfcyeppe4jwd
	";
	$Vkqnu4waplss = mysql_query( $Viu13fvmgfst, $Vnlrsngknoho['link'] ) or die(mysql_error());
	
	
	$Viu13fvmgfst = "
		SELECT FOUND_ROWS()
	";
	$Vkqnu4waplssFilterTotal = mysql_query( $Viu13fvmgfst, $Vnlrsngknoho['link'] ) or die(mysql_error());
	$Vfvpflwh3ggn = mysql_fetch_array($Vkqnu4waplssFilterTotal);
	$V4jxpnh1o213FilteredTotal = $Vfvpflwh3ggn[0];
	
	
	$Viu13fvmgfst = "
		SELECT COUNT(".$Vr2kzemvwvx1.")
		FROM   $V1sefvpnx0kb
	";
	$Vkqnu4waplssTotal = mysql_query( $Viu13fvmgfst, $Vnlrsngknoho['link'] ) or die(mysql_error());
	$V04zcczuxwan = mysql_fetch_array($Vkqnu4waplssTotal);
	$V4jxpnh1o213Total = $V04zcczuxwan[0];
	
	
	
	$Vs2fotmixfd5 = array(
		"sEcho" => intval($_GET['sEcho']),
		"iTotalRecords" => $V4jxpnh1o213Total,
		"iTotalDisplayRecords" => $V4jxpnh1o213FilteredTotal,
		"aaData" => array()
	);
	
	while ( $Vdiyixb1e3ji = mysql_fetch_array( $Vkqnu4waplss ) )
	{
		$Vjnupbcucmqt = array();
		for ( $V4jxpnh1o213=0 ; $V4jxpnh1o213<count($Vxnttlwaqil2) ; $V4jxpnh1o213++ )
		{
			if ( $Vxnttlwaqil2[$V4jxpnh1o213] == "version" )
			{
				
				$Vjnupbcucmqt[] = ($Vdiyixb1e3ji[ $Vxnttlwaqil2[$V4jxpnh1o213] ]=="0") ? '-' : $Vdiyixb1e3ji[ $Vxnttlwaqil2[$V4jxpnh1o213] ];
			}
			else if ( $Vxnttlwaqil2[$V4jxpnh1o213] != ' ' )
			{
				
				$Vjnupbcucmqt[] = $Vdiyixb1e3ji[ $Vxnttlwaqil2[$V4jxpnh1o213] ];
			}
		}
		$Vs2fotmixfd5['aaData'][] = $Vjnupbcucmqt;
	}
	
	echo json_encode( $Vs2fotmixfd5 );
?>