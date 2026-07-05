<?php include("../Connections/Conn.php"); ?>

<?php
session_start();
include('../inc/header.php');

// PERFORMANCE NOTE: Using optimized single-query approach instead of nested loops
// get income and expense classes (legacy - kept for compatibility, not used in main display)
$classes = $db->query('SELECT * FROM chart_class WHERE 1');
$classes = $classes->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['start']) && isset($_GET['end'])) {
	$the_stetment = 1;
}
include 'inc/functions.php';
redirect_to_active_day();
if (isset($_SESSION['h_code']) && $_SESSION['h_code'] == 'mluth') {
	$trial_balance_mluth_2024 = [
		'period' => [
			'start_date' => '2024-01-01',
			'end_date' => '2024-12-31'
		],
		'accounts' => [
			'assets' => [
				'fixed_assets' => [
					[
						'code' => '1212',
						'name' => 'Building',
						'debit' => 414030375.57,
						'credit' => 0.00,
						'balance' => 414030375.57,
						'balance_type' => 'DR'
					],
					[
						'code' => '1213',
						'name' => 'Furniture and Fittings',
						'debit' => 63070800.00,
						'credit' => 0.00,
						'balance' => 63070800.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '1214',
						'name' => 'Medical Equipment',
						'debit' => 596568968.46,
						'credit' => 7902500.00,
						'balance' => 588666468.46,
						'balance_type' => 'DR'
					],
					[
						'code' => '1215',
						'name' => 'Office Equipment',
						'debit' => 176996231.15,
						'credit' => 0.00,
						'balance' => 176996231.15,
						'balance_type' => 'DR'
					],
					[
						'code' => '1216',
						'name' => 'Plant and Machinery',
						'debit' => 460000.00,
						'credit' => 0.00,
						'balance' => 460000.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '1217',
						'name' => 'Motor Vehicle',
						'debit' => 126990000.00,
						'credit' => 0.00,
						'balance' => 126990000.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '1219',
						'name' => 'Computer Software',
						'debit' => 15000000.00,
						'credit' => 0.00,
						'balance' => 15000000.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '1220',
						'name' => 'Computer Equipment',
						'debit' => 45797500.00,
						'credit' => 0.00,
						'balance' => 45797500.00,
						'balance_type' => 'DR'
					]
				],
				'current_assets' => [
					[
						'code' => '1502',
						'name' => 'Patient Bill Receivable',
						'debit' => 5233977.56,
						'credit' => 4982200.24,
						'balance' => 251777.32,
						'balance_type' => 'DR'
					],
					[
						'code' => '1509',
						'name' => 'Bill to MD Account',
						'debit' => 5987973.45,
						'credit' => 1908494.00,
						'balance' => 4079479.45,
						'balance_type' => 'DR'
					],
					[
						'code' => '1510',
						'name' => 'Write Off',
						'debit' => 60880.00,
						'credit' => 0.00,
						'balance' => 60880.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '1513',
						'name' => 'Account Receivable-MLU',
						'debit' => 13616296.00,
						'credit' => 7291194.00,
						'balance' => 6325102.00,
						'balance_type' => 'DR'
					]
				],
				'cash_and_bank' => [
					[
						'code' => '1601',
						'name' => 'Main Cash',
						'debit' => 2383084.00,
						'credit' => 2342710.00,
						'balance' => 40374.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '1604',
						'name' => 'TAJ BANK',
						'debit' => 101679134.65,
						'credit' => 81268306.96,
						'balance' => 20410827.69,
						'balance_type' => 'DR'
					],
					[
						'code' => '1607',
						'name' => 'Zenith Bank-Salary Acct',
						'debit' => 312285769.87,
						'credit' => 303805254.07,
						'balance' => 8480515.80,
						'balance_type' => 'DR'
					],
					[
						'code' => '1608',
						'name' => 'Zenith Bank',
						'debit' => 133257975.56,
						'credit' => 127272346.76,
						'balance' => 5985628.80,
						'balance_type' => 'DR'
					],
					[
						'code' => '1610',
						'name' => 'Zenith Bank- Dollar Operations',
						'debit' => 6072500.00,
						'credit' => 0.00,
						'balance' => 6072500.00,
						'balance_type' => 'DR'
					]
				],
				'supplies_inventory' => [
					[
						'code' => '1401',
						'name' => 'Drug Inventory',
						'debit' => 81416062.22,
						'credit' => 0.00,
						'balance' => 81416062.22,
						'balance_type' => 'DR'
					]
				]
			],
			'liabilities' => [
				'other_current_liabilities' => [
					[
						'code' => '2121',
						'name' => 'Patient Deposit',
						'debit' => 171441208.46,
						'credit' => 175595230.87,
						'balance' => 4154022.41,
						'balance_type' => 'CR'
					],
					[
						'code' => '2124',
						'name' => 'Account Payable',
						'debit' => 35993870.40,
						'credit' => 44142213.00,
						'balance' => 8148342.60,
						'balance_type' => 'CR'
					],
					[
						'code' => '2128',
						'name' => 'Account Payable (Pharmacy)',
						'debit' => 13908314.00,
						'credit' => 22596702.02,
						'balance' => 8688388.02,
						'balance_type' => 'CR'
					]
				],
				'longterm_liabilities' => [
					[
						'code' => '2162',
						'name' => 'INTER-COMPANY WITH PHL',
						'debit' => 54506800.00,
						'credit' => 2214583625.92,
						'balance' => 2160076825.92,
						'balance_type' => 'CR'
					]
				],
				'taxation' => [
					[
						'code' => '2112',
						'name' => 'Pay As You Earn (PAYE)',
						'debit' => 14166288.08,
						'credit' => 18788099.23,
						'balance' => 4621811.15,
						'balance_type' => 'CR'
					],
					[
						'code' => '2114',
						'name' => 'WHT on Payables',
						'debit' => 3215189.36,
						'credit' => 4254259.88,
						'balance' => 1039070.52,
						'balance_type' => 'CR'
					]
				]
			],
			'income' => [
				'revenues' => [
					[
						'code' => '3101',
						'name' => 'Registration',
						'debit' => 30000.00,
						'credit' => 11033300.00,
						'balance' => 11003300.00,
						'balance_type' => 'CR'
					],
					[
						'code' => '3102',
						'name' => 'Consultation',
						'debit' => 290000.00,
						'credit' => 21820101.00,
						'balance' => 21530101.00,
						'balance_type' => 'CR'
					],
					[
						'code' => '3103',
						'name' => 'Pharmacy',
						'debit' => 1640356.06,
						'credit' => 36690027.12,
						'balance' => 35049671.05,
						'balance_type' => 'CR'
					],
					[
						'code' => '3104',
						'name' => 'Dialysis',
						'debit' => 0.00,
						'credit' => 2680000.00,
						'balance' => 2680000.00,
						'balance_type' => 'CR'
					],
					[
						'code' => '3105',
						'name' => 'Bed Space/Accommodation',
						'debit' => 88450.00,
						'credit' => 8981815.40,
						'balance' => 8893365.40,
						'balance_type' => 'CR'
					],
					[
						'code' => '3107',
						'name' => 'Scan/Imaging',
						'debit' => 409280.00,
						'credit' => 19081960.00,
						'balance' => 18672680.00,
						'balance_type' => 'CR'
					],
					[
						'code' => '3108',
						'name' => 'Laboratory Test',
						'debit' => 309595.00,
						'credit' => 45298665.00,
						'balance' => 44989070.00,
						'balance_type' => 'CR'
					],
					[
						'code' => '3109',
						'name' => 'Medical Services',
						'debit' => 7979246.70,
						'credit' => 42264083.26,
						'balance' => 34284836.56,
						'balance_type' => 'CR'
					],
					[
						'code' => '3111',
						'name' => 'Ambulance Hire',
						'debit' => 0.00,
						'credit' => 294117.00,
						'balance' => 294117.00,
						'balance_type' => 'CR'
					],
					[
						'code' => '3114',
						'name' => 'Medical Report',
						'debit' => 0.00,
						'credit' => 42000.00,
						'balance' => 42000.00,
						'balance_type' => 'CR'
					],
					[
						'code' => '3121',
						'name' => 'Nursing Services',
						'debit' => 10000.00,
						'credit' => 5283430.50,
						'balance' => 5273430.50,
						'balance_type' => 'CR'
					],
					[
						'code' => '3122',
						'name' => 'External Services',
						'debit' => 305015.10,
						'credit' => 575015.10,
						'balance' => 270000.00,
						'balance_type' => 'CR'
					],
					[
						'code' => '3123',
						'name' => 'Vendor Bidding Fees(pharm)',
						'debit' => 0.00,
						'credit' => 649950.00,
						'balance' => 649950.00,
						'balance_type' => 'CR'
					],
					[
						'code' => '3124',
						'name' => 'Mortuary Services',
						'debit' => 50000.00,
						'credit' => 1920000.00,
						'balance' => 1870000.00,
						'balance_type' => 'CR'
					],
					[
						'code' => '3126',
						'name' => 'Vendor Bidding Fees(General)',
						'debit' => 0.00,
						'credit' => 350000.00,
						'balance' => 350000.00,
						'balance_type' => 'CR'
					]
				],
				'other_revenues' => [
					[
						'code' => '3205',
						'name' => 'Other Income',
						'debit' => 0.00,
						'credit' => 49100.00,
						'balance' => 49100.00,
						'balance_type' => 'CR'
					],
					[
						'code' => '3207',
						'name' => 'Unearned Income',
						'debit' => 200000.00,
						'credit' => 212988.00,
						'balance' => 12988.00,
						'balance_type' => 'CR'
					]
				]
			],
			'expenses' => [
				'cost_of_sales' => [
					[
						'code' => '2186',
						'name' => 'Consumables-Plastic Surgery',
						'debit' => 3526358.75,
						'credit' => 0.00,
						'balance' => 3526358.75,
						'balance_type' => 'DR'
					],
					[
						'code' => '4103',
						'name' => 'Consult fee',
						'debit' => 125367382.67,
						'credit' => 0.00,
						'balance' => 125367382.67,
						'balance_type' => 'DR'
					],
					[
						'code' => '4105',
						'name' => 'Referals',
						'debit' => 1604200.00,
						'credit' => 0.00,
						'balance' => 1604200.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '4106',
						'name' => 'Oxygen Gas',
						'debit' => 1228240.00,
						'credit' => 0.00,
						'balance' => 1228240.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '4108',
						'name' => 'Other Cost of Sales',
						'debit' => 880000.00,
						'credit' => 0.00,
						'balance' => 880000.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '4109',
						'name' => 'Lab/Blood',
						'debit' => 561500.00,
						'credit' => 0.00,
						'balance' => 561500.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '4110',
						'name' => 'Consumables-General',
						'debit' => 92971783.35,
						'credit' => 30621800.00,
						'balance' => 62349983.35,
						'balance_type' => 'DR'
					],
					[
						'code' => '4111',
						'name' => 'Consumables-LAB',
						'debit' => 24198278.63,
						'credit' => 181319.69,
						'balance' => 24016958.94,
						'balance_type' => 'DR'
					],
					[
						'code' => '4112',
						'name' => 'Consumables-Radiology',
						'debit' => 31463000.00,
						'credit' => 12137500.00,
						'balance' => 19325500.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '4113',
						'name' => 'Consumables-Dialysis',
						'debit' => 5781000.00,
						'credit' => 0.00,
						'balance' => 5781000.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '4114',
						'name' => 'Consumables-NEONAT/O&G',
						'debit' => 514000.00,
						'credit' => 0.00,
						'balance' => 514000.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '4115',
						'name' => 'Laundry Expenses',
						'debit' => 1024800.00,
						'credit' => 0.00,
						'balance' => 1024800.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '4116',
						'name' => 'Consumables-MORGUE',
						'debit' => 5928434.20,
						'credit' => 2841970.00,
						'balance' => 3086464.20,
						'balance_type' => 'DR'
					],
					[
						'code' => '4117',
						'name' => 'Consumables -Pharmacy',
						'debit' => 245280.00,
						'credit' => 0.00,
						'balance' => 245280.00,
						'balance_type' => 'DR'
					]
				],
				'overhead_administrative' => [
					[
						'code' => '1511',
						'name' => 'DISCOUNT TO PATIENTS',
						'debit' => 2653488.99,
						'credit' => 0.00,
						'balance' => 2653488.99,
						'balance_type' => 'DR'
					],
					[
						'code' => '4201',
						'name' => 'Entertainment',
						'debit' => 1312550.00,
						'credit' => 0.00,
						'balance' => 1312550.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '4203',
						'name' => 'Refund',
						'debit' => 470240.10,
						'credit' => 0.00,
						'balance' => 470240.10,
						'balance_type' => 'DR'
					],
					[
						'code' => '4204',
						'name' => 'Uniforms',
						'debit' => 3159000.00,
						'credit' => 0.00,
						'balance' => 3159000.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '4206',
						'name' => 'Repair/Maintenance-AC',
						'debit' => 3234000.00,
						'credit' => 0.00,
						'balance' => 3234000.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '4208',
						'name' => 'Pay As You Earn (PAYE)',
						'debit' => 24618416.67,
						'credit' => 0.00,
						'balance' => 24618416.67,
						'balance_type' => 'DR'
					],
					[
						'code' => '4209',
						'name' => 'Water Expenses',
						'debit' => 36900.00,
						'credit' => 0.00,
						'balance' => 36900.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '4211',
						'name' => 'Cleaning Material/Expenses',
						'debit' => 17638669.67,
						'credit' => 0.00,
						'balance' => 17638669.67,
						'balance_type' => 'DR'
					],
					[
						'code' => '4212',
						'name' => 'Telephone/Communication',
						'debit' => 2800780.33,
						'credit' => 0.00,
						'balance' => 2800780.33,
						'balance_type' => 'DR'
					],
					[
						'code' => '4213',
						'name' => 'Staff Accommodation Allowance',
						'debit' => 1365000.00,
						'credit' => 0.00,
						'balance' => 1365000.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '4214',
						'name' => 'Repair/Maintenance-Building',
						'debit' => 13969850.00,
						'credit' => 0.00,
						'balance' => 13969850.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '4215',
						'name' => 'Advertisement/Publicity',
						'debit' => 10600971.87,
						'credit' => 0.00,
						'balance' => 10600971.87,
						'balance_type' => 'DR'
					],
					[
						'code' => '4216',
						'name' => 'Office Equipment',
						'debit' => 6218600.00,
						'credit' => 30000.00,
						'balance' => 6188600.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '4217',
						'name' => 'Fueling',
						'debit' => 1837712.00,
						'credit' => 0.00,
						'balance' => 1837712.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '4219',
						'name' => 'Salaries and Wages',
						'debit' => 239780626.42,
						'credit' => 285769.87,
						'balance' => 239494856.55,
						'balance_type' => 'DR'
					],
					[
						'code' => '4220',
						'name' => 'Office Expenses',
						'debit' => 4100642.00,
						'credit' => 0.00,
						'balance' => 4100642.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '4221',
						'name' => 'Repair/Maintenance-Mortor Vehicle',
						'debit' => 3474020.00,
						'credit' => 0.00,
						'balance' => 3474020.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '4222',
						'name' => 'Dues and Subsription',
						'debit' => 22831645.00,
						'credit' => 0.00,
						'balance' => 22831645.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '4223',
						'name' => 'Printing and Stationeries',
						'debit' => 11913700.00,
						'credit' => 0.00,
						'balance' => 11913700.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '4224',
						'name' => 'Electricals /Lighting',
						'debit' => 89022655.61,
						'credit' => 0.00,
						'balance' => 89022655.61,
						'balance_type' => 'DR'
					],
					[
						'code' => '4225',
						'name' => 'Internet/Web Expenses',
						'debit' => 1773440.00,
						'credit' => 0.00,
						'balance' => 1773440.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '4226',
						'name' => 'Trainning/Seminars',
						'debit' => 80000.00,
						'credit' => 0.00,
						'balance' => 80000.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '4227',
						'name' => 'Transport and Travel',
						'debit' => 34895126.67,
						'credit' => 0.00,
						'balance' => 34895126.67,
						'balance_type' => 'DR'
					],
					[
						'code' => '4228',
						'name' => 'Diesel',
						'debit' => 37756040.00,
						'credit' => 0.00,
						'balance' => 37756040.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '4229',
						'name' => 'Security/Safety',
						'debit' => 1679000.00,
						'credit' => 0.00,
						'balance' => 1679000.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '4230',
						'name' => 'Computer Accessories/Phones',
						'debit' => 1183500.00,
						'credit' => 0.00,
						'balance' => 1183500.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '4231',
						'name' => 'Amac/Enviromental/ Signpost',
						'debit' => 210000.00,
						'credit' => 0.00,
						'balance' => 210000.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '4234',
						'name' => 'Repair/Maintenance-Equipment',
						'debit' => 7881500.00,
						'credit' => 0.00,
						'balance' => 7881500.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '4237',
						'name' => 'Drug',
						'debit' => 170800.00,
						'credit' => 0.00,
						'balance' => 170800.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '4239',
						'name' => 'Staff Welfare',
						'debit' => 16068172.33,
						'credit' => 0.00,
						'balance' => 16068172.33,
						'balance_type' => 'DR'
					],
					[
						'code' => '4240',
						'name' => 'Mortuary expenses',
						'debit' => 196650.00,
						'credit' => 0.00,
						'balance' => 196650.00,
						'balance_type' => 'DR'
					],
					[
						'code' => '4241',
						'name' => 'Withholding Tax(WHT)',
						'debit' => 5316312.41,
						'credit' => 0.00,
						'balance' => 5316312.41,
						'balance_type' => 'DR'
					],
					[
						'code' => '4243',
						'name' => 'AUDIT /TAX Expenses',
						'debit' => 100000.00,
						'credit' => 0.00,
						'balance' => 100000.00,
						'balance_type' => 'DR'
					]
				],
				'bank_charges' => [
					[
						'code' => '4401',
						'name' => 'Taj Bank',
						'debit' => 366365.79,
						'credit' => 0.00,
						'balance' => 366365.79,
						'balance_type' => 'DR'
					],
					[
						'code' => '4405',
						'name' => 'Zenith Bank (Bank Charges)',
						'debit' => 176049.85,
						'credit' => 0.00,
						'balance' => 176049.85,
						'balance_type' => 'DR'
					]
				]
			]
		],
		'totals' => [
			'total_debits' => 3255466539.69,
			'total_credits' => 3255466539.69,
			'is_balanced' => true
		],
		'changes_summary' => [
			'patient_bill_receivable_reduction' => 1684647.54,
			'patient_deposit_increase' => 1422554.11,
			'consumables_general_increase' => 13041500.00,
			'intercompany_phl_increase' => 13303253.43
		],
		'journal_entry' => [
			'debits' => [
				[
					'account_code' => '4110',
					'account_name' => 'Consumables-General',
					'amount' => 13041500.00
				],
				[
					'account_code' => '2121',
					'account_name' => 'Patient Deposit',
					'amount' => 1422554.11
				]
			],
			'credits' => [
				[
					'account_code' => '1502',
					'account_name' => 'Patient Bill Receivable',
					'amount' => 1684647.54
				],
				[
					'account_code' => '2162',
					'account_name' => 'Inter-company with PHL',
					'amount' => 13303253.43
				]
			],
			'total_debits' => 14464054.11,
			'total_credits' => 14987900.97,
			'difference' => 0.00
		]
	];
}

?>
<style>
	.trial-table {
		width: 100%;
		border-collapse: collapse;
		margin-bottom: 1.5rem;
	}

	.trial-table th,
	.trial-table td {
		padding: 0.35rem 0.6rem;
		font-size: 0.97em;
		border: none !important;
		background: none !important;
	}

	.trial-class-row {
		font-weight: 700;
		color: #007bff;
		font-size: 1.18em;
		background: #f8f9fa;
	}

	.trial-group-row {
		font-weight: 600;
		color: #17a2b8;
		font-size: 1.07em;
		background: #f8f9fa;
	}

	.trial-account-row {
		font-weight: 500;
		color: #6c757d;
		font-size: 1em;
	}

	.trial-class-row td {
		padding-left: 0.2em;
	}

	.trial-group-row td {
		padding-left: 2.5em;
	}

	.trial-account-row td {
		padding-left: 4.5em;
	}

	.amount-cell,
	.balance-cell {
		text-align: right;
	}

	.toggle-link {
		cursor: pointer;
		color: #007bff;
		font-size: 1.1em;
		margin-right: 0.5em;
	}

	.summary-top,
	.section-summary {
		background: #e9ecef;
		border-radius: 4px;
		padding: 0.5em 1em;
		margin-bottom: 0.7em;
		font-size: 1em;
		display: flex;
		flex-wrap: wrap;
		gap: 2em;
		align-items: center;
	}

	.summary-top strong,
	.section-summary strong {
		color: #007bff;
	}

	.section-summary {
		background: #f6f8fa;
		margin-bottom: 0.3em;
		font-size: 0.97em;
	}
</style>
<script>
	function toggleTrialSection(id) {
		var toggleIcon = document.getElementById(id + "_toggle");
		var isExpanded = toggleIcon && toggleIcon.innerHTML === "&#9660;";
		if (isExpanded) {
			// Collapse: hide all descendants and set all icons to collapsed
			toggleChildren(id, false, true);
			toggleIcon.innerHTML = "&#9654;";
		} else {
			// Expand: show all descendants and set all icons to expanded
			toggleChildren(id, true, true);
			toggleIcon.innerHTML = "&#9660;";
		}
	}

	// show: true to show, false to hide
	// recursive: true to set all descendants, false for direct children only
	function toggleChildren(parentId, show, recursive) {
		var rows = document.querySelectorAll('[data-parent="' + parentId + '"]');
		for (var i = 0; i < rows.length; i++) {
			rows[i].style.display = show ? "" : "none";
			var childId = rows[i].id;
			var toggleIcon = document.getElementById(childId + "_toggle");
			if (toggleIcon) {
				toggleIcon.innerHTML = show ? "&#9660;" : "&#9654;";
			}
			if (recursive) {
				// Recursively show/hide all descendants
				toggleChildren(childId, show, true);
			}
		}
	}
</script>

<body class="fixed-navigation">
	<div id="wrapper">
		<?php include("nav_side.php"); ?>
		<div id="page-wrapper" class="gray-bg sidebar-content">
			<?php include '../../inc/nav_header.php'; ?>
			<div class="row">
				<div class="col-lg-12">
					<div class="ibox float-e-margins">
						<div class="ibox-title">
							<h5>Accounting Dashboard</h5>
						</div>
						<div class="ibox-content">
							<?php if (isset($the_stetment)) { ?>
								<?php if (isset($_SESSION['h_code']) && $_SESSION['h_code'] == 'mluth') { ?>
									<!-- <div class="alert alert-info">
										<strong>MLUTH Notice:</strong> 2024 dates are disabled. Use the button below to view the 2024 trial balance.
									</div> -->
									<div class="form_sep" style="margin-bottom: 15px;">
										<a href="trial_balance_2024.php" class="btn btn-warning btn-lg">
											<i class="fa fa-calendar"></i> Load 2024 Trial Balance <?php echo $_SESSION['h_code']; ?>
										</a>
									</div>
								<?php } ?>
								<form action="" method="get" class="form-inline">
									<div class="form_sep" align="">
										<label for="">Start Date</label>
										<input type="date" name="start" class="form-control" value="<?php echo isset($_GET['start']) ? $_GET['start'] : ''; ?>"
											<?php if (isset($_SESSION['h_code']) && $_SESSION['h_code'] == 'mluth') { ?>
											min="2025-01-01" max="2025-12-31"
											<?php } ?>
											required>
										&nbsp;
										<label for="">End Date</label>
										<input type="date" name="end" class="form-control" value="<?php echo isset($_GET['end']) ? $_GET['end'] : ''; ?>"
											<?php if (isset($_SESSION['h_code']) && $_SESSION['h_code'] == 'mluth') { ?>
											min="2025-01-01" max="2025-12-31"
											<?php } ?>
											required>
										&nbsp;
										<label for="">show cr/dr</label><input type="checkbox" name="show_crdr" <?php echo isset($_GET['show_crdr']) ? 'checked' : '' ?>>
										<button type="submit" class="btn btn-primary">Display </button>
										<button type="button" class="btn btn-success" onclick="exportTrialBalanceDirectly()" title="Export to CSV without loading data">
											<i class="fa fa-file-excel-o"></i> Export CSV
										</button>
									</div>
									<br>
								</form>
								<div class="summary-top">
									<strong>Period:</strong> <?php echo htmlspecialchars($_GET['start']); ?> to <?php echo htmlspecialchars($_GET['end']); ?>
								</div>
								<div id="chart_groups">
									<h1>Trial Balance</h1>
									<h3>For the period starting <?php echo date('d M Y', strtotime($_GET['start'])); ?> and ending
										<?php echo date('d M Y', strtotime($_GET['end'])); ?></h3>
									<?php
									$totals__ = getTotalDebitAndCredit($_GET['start'], $_GET['end']);

									// OPTIMIZED APPROACH: Single query instead of nested loops
									$sql = "
									SELECT 
										cl.class_name,
										cl.cid as class_id,
										cg.name as group_name,
										cg.id as group_id,
										ca.account_code,
										ca.account_name,
										ch.transc_type,
										ch.dr_amt,
										ch.cr_amt
									FROM chart_class cl
									LEFT JOIN chart_groups cg ON cg.class_id = cl.cid
									LEFT JOIN chart_accounts ca ON ca.account_group = cg.id
									LEFT JOIN chart_ledger ch ON ch.account_no = ca.account_code 
										AND DATE(ch.date_entry2) BETWEEN ? AND ?
									WHERE ca.account_code IS NOT NULL
									ORDER BY cl.cid, cg.id, ca.account_code
									";

									$stmt = $db->prepare($sql);
									$stmt->execute([$_GET['start'], $_GET['end']]);
									$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

									// PHP arrays to store account summations
									$account_data = [];
									$class_order = [];
									$group_order = [];

									// Process raw data and calculate summations in PHP
									foreach ($results as $row) {
										$account_code = $row['account_code'];

										// Initialize account if not exists
										if (!isset($account_data[$account_code])) {
											$account_data[$account_code] = [
												'class_name' => $row['class_name'],
												'class_id' => $row['class_id'],
												'group_name' => $row['group_name'],
												'group_id' => $row['group_id'],
												'account_name' => $row['account_name'],
												'total_debit' => 0,
												'total_credit' => 0
											];

											// Store ordering information
											if (!in_array($row['class_id'], $class_order)) {
												$class_order[] = $row['class_id'];
											}
											if (!in_array($row['group_id'], $group_order)) {
												$group_order[] = $row['group_id'];
											}
										}

										// Sum up debits and credits in PHP - CORRECTED LOGIC
										if ($row['transc_type'] == 'DEBIT') {
											$account_data[$account_code]['total_debit'] += (floatval($row['dr_amt']) + floatval($row['cr_amt']));
										} elseif ($row['transc_type'] == 'CREDIT') {
											$account_data[$account_code]['total_credit'] += (floatval($row['dr_amt']) + floatval($row['cr_amt']));
										}
									}

									// Filter out accounts with no activity
									$account_data = array_filter($account_data, function ($account) {
										return $account['total_debit'] > 0 || $account['total_credit'] > 0;
									});

									// Sort accounts by class and group order
									uasort($account_data, function ($a, $b) {
										if ($a['class_id'] != $b['class_id']) {
											return $a['class_id'] - $b['class_id'];
										}
										if ($a['group_id'] != $b['group_id']) {
											return $a['group_id'] - $b['group_id'];
										}
										return strcmp($a['account_code'], $b['account_code']);
									});

									// Group accounts by class and group for display
									$display_structure = [];
									foreach ($account_data as $account_code => $account) {
										$class_id = $account['class_id'];
										$group_id = $account['group_id'];

										if (!isset($display_structure[$class_id])) {
											$display_structure[$class_id] = [
												'class_name' => $account['class_name'],
												'groups' => []
											];
										}

										if (!isset($display_structure[$class_id]['groups'][$group_id])) {
											$display_structure[$class_id]['groups'][$group_id] = [
												'group_name' => $account['group_name'],
												'accounts' => []
											];
										}

										$display_structure[$class_id]['groups'][$group_id]['accounts'][$account_code] = $account;
									}
									?>
									<table class="trial-table">
										<thead>
											<tr>
												<th></th>
												<?php if (isset($_GET['show_crdr'])): ?>
													<th class="amount-cell">Debit</th>
													<th class="amount-cell">Credit</th>
												<?php endif ?>
												<th class="balance-cell">Balance</th>
											</tr>
										</thead>
										<tbody>
											<?php
											$class_idx = 0;
											foreach ($display_structure as $class_id => $class_info):
												$class_idx++;
											?>
												<tr class="trial-class-row" id="trial_class_<?php echo $class_idx; ?>">
													<td>
														<span id="trial_class_<?php echo $class_idx; ?>_toggle" class="toggle-link" onclick="toggleTrialSection('trial_class_<?php echo $class_idx; ?>'); event.stopPropagation();">&#9660;</span>
														<a href="ledger_entries.php?level=class&id=<?php echo $class_id; ?>&start=<?php echo urlencode($_GET['start']); ?>&end=<?php echo urlencode($_GET['end']); ?>" style="color:#007bff; text-decoration:underline;" title="View Ledger Entries for Class" target="_blank">
															<?php echo htmlspecialchars($class_info['class_name']); ?>
														</a>
													</td>
													<?php if (isset($_GET['show_crdr'])): ?>
														<td></td>
														<td></td>
													<?php endif ?>
													<td></td>
												</tr>
												<?php
												$group_idx = 0;
												foreach ($class_info['groups'] as $group_id => $group_info):
													$group_idx++;
												?>
													<tr class="trial-group-row" id="trial_group_<?php echo $class_idx; ?>_<?php echo $group_idx; ?>" data-parent="trial_class_<?php echo $class_idx; ?>">
														<td>
															<span id="trial_group_<?php echo $class_idx; ?>_<?php echo $group_idx; ?>_toggle" class="toggle-link" onclick="toggleTrialSection('trial_group_<?php echo $class_idx; ?>_<?php echo $group_idx; ?>'); event.stopPropagation();">&#9660;</span>
															<a href="ledger_entries.php?level=group&id=<?php echo $group_id; ?>&start=<?php echo urlencode($_GET['start']); ?>&end=<?php echo urlencode($_GET['end']); ?>" style="color:#17a2b8; text-decoration:underline;" title="View Ledger Entries for Group" target="_blank">
																<?php echo htmlspecialchars($group_info['group_name']); ?>
															</a>
														</td>
														<?php if (isset($_GET['show_crdr'])): ?>
															<td></td>
															<td></td>
														<?php endif ?>
														<td></td>
													</tr>
													<?php
													$acc_idx = 0;
													foreach ($group_info['accounts'] as $account_code => $account):
														$acc_idx++;
														$acc_dr = $account['total_debit'];
														$acc_cr = $account['total_credit'];
													?>
														<tr class="trial-account-row" id="trial_account_<?php echo $class_idx; ?>_<?php echo $group_idx; ?>_<?php echo $acc_idx; ?>" data-parent="trial_group_<?php echo $class_idx; ?>_<?php echo $group_idx; ?>">
															<td>
																<a href="ledger_entries.php?level=account&id=<?php echo $account_code; ?>&start=<?php echo $_GET['start']; ?>&end=<?php echo $_GET['end']; ?>" style="color:#6c757d; text-decoration:underline;" title="View Ledger Entries for Account" target="_blank">
																	[<?php echo $account_code; ?>] <?php echo htmlspecialchars($account['account_name']); ?>
																</a>
															</td>
															<?php if (isset($_GET['show_crdr'])): ?>
																<td class="amount-cell"><?php echo $acc_dr > 0 ? format_accounting($acc_dr, '') : ''; ?></td>
																<td class="amount-cell"><?php echo $acc_cr > 0 ? format_accounting($acc_cr, '') : ''; ?></td>
															<?php endif ?>
															<td class="balance-cell">
																<?php
																$balance = $acc_dr - $acc_cr;
																if ($balance > 0) {
																	echo number_format($balance, 2) . " DR";
																} elseif ($balance < 0) {
																	echo number_format(abs($balance), 2) . " CR";
																} else {
																	echo "0.00";
																}
																?>
															</td>
														</tr>
													<?php endforeach; ?>
												<?php endforeach; ?>
											<?php endforeach; ?>
										</tbody>
									</table>
									<div class="summary-top" style="margin-top:2em;">
										<strong>Total Debits:</strong> <?php echo format_accounting($totals__['total_debit']); ?> DR
										<strong>Total Credits:</strong> <?php echo format_accounting($totals__['total_credit']); ?> CR
									</div>
								</div>
								<div style="margin-top: 20px;">
									<button class="btn btn-success" onclick="printTrialDiv('chart_groups')"><i class="fa fa-print">&nbsp; Print Trial Balance</i></button>
									&nbsp;&nbsp;
									<button class="btn btn-info" onclick="exportTrialBalanceToCSV()"><i class="fa fa-file-text-o">&nbsp; Export to CSV</i></button>
								</div>
							<?php } ?>

						</div>
					</div>
				</div>
			</div>
			<?php include '../../inc/footer.php'; ?>

		</div>

		<?php include('../modal_lock.php'); ?>
		<?php include '/inc/footer_scripts.php'; ?>

		<script>
			function printTrialDiv(divId) {
				var content = document.getElementById(divId).innerHTML;
				var popupWindow = window.open('', '_blank', 'width=900,height=900');
				popupWindow.document.open();
				popupWindow.document.write('<html><head><title>' + document.title + '</title>');
				popupWindow.document.write('<link rel="stylesheet" type="text/css" href="../../css/bootstrap.min.css">');
				popupWindow.document.write(`
                    <style>
                        body {
                            font-size: 12px;
                            color: #222;
                            background: #fff;
                        }
                        .trial-table {
                            width: 100%;
                            border-collapse: collapse !important;
                        }
                        .trial-table th, .trial-table td {
                            border: 1px solid #bbb !important;
                            background: none !important;
                            padding: 0.35rem 0.6rem;
                        }
                        .trial-class-row { font-weight: 700; color: #007bff; font-size: 1.18em; background: #f8f9fa; }
                        .trial-group-row { font-weight: 600; color: #17a2b8; font-size: 1.07em; background: #f8f9fa; }
                        .trial-account-row { font-weight: 500; color: #6c757d; font-size: 1em; }
                        .trial-class-row td { padding-left: 0.2em; }
                        .trial-group-row td { padding-left: 2.5em; }
                        .trial-account-row td { padding-left: 4.5em; }
                        .amount-cell, .balance-cell { text-align: right; }
                        .toggle-link { display: none !important; }
                        .summary-top, .section-summary {
                            background: #e9ecef !important;
                            border-radius: 4px !important;
                            padding: 0.5em 1em !important;
                            margin-bottom: 0.7em !important;
                            font-size: 1em !important;
                            display: flex !important;
                            flex-wrap: wrap !important;
                            gap: 2em !important;
                            align-items: center !important;
                        }
                        .summary-top strong, .section-summary strong { color: #007bff !important; }
                        .btn, .fa-print, a[onclick*="printTrialDiv"], a[onclick*="printDiv"] {
                            display: none !important;
                        }
                                                /* Hide original headings in the print popup to prevent duplication */
                        body > h1, body > h3, .text-center.mb-4 { display: none !important; }
                        @media print {
                            a[href]:after { content: none !important; }
                        }
                    </style>
                `);
				popupWindow.document.write('</head><body>');
				popupWindow.document.write(content);
				popupWindow.document.write('</body></html>');
				popupWindow.document.close();
				setTimeout(function() {
				    popupWindow.focus();
				    popupWindow.print();
				}, 1000);
			}

			function exportTrialBalanceToCSV() {
				// Get URL parameters for direct database query
				var startDate = "<?php echo isset($_GET['start']) ? $_GET['start'] : ''; ?>";
				var endDate = "<?php echo isset($_GET['end']) ? $_GET['end'] : ''; ?>";
				var showCrDr = "<?php echo isset($_GET['show_crdr']) ? '1' : '0'; ?>";

				// Validate dates
				if (!startDate || !endDate) {
					alert('Please select a date range first.');
					return;
				}

				// Create download URL with parameters
				var exportUrl = 'trial_balance_export_csv.php?start=' + encodeURIComponent(startDate) +
					'&end=' + encodeURIComponent(endDate) +
					'&show_crdr=' + showCrDr;

				// Create temporary download link
				var link = document.createElement('a');
				link.href = exportUrl;
				link.download = 'Trial_Balance_' + startDate + '_to_' + endDate + '.csv';
				link.style.display = 'none';

				// Trigger download
				document.body.appendChild(link);
				link.click();
				document.body.removeChild(link);
			}

			function exportTrialBalanceDirectly() {
				// Get form values directly from the form inputs
				var startDate = document.querySelector('input[name="start"]').value;
				var endDate = document.querySelector('input[name="end"]').value;
				var showCrDr = document.querySelector('input[name="show_crdr"]').checked ? '1' : '0';

				// Validate dates
				if (!startDate || !endDate) {
					alert('Please select start and end dates first.');
					return;
				}

				// Create download URL with parameters
				var exportUrl = 'trial_balance_export_csv.php?start=' + encodeURIComponent(startDate) +
					'&end=' + encodeURIComponent(endDate) +
					'&show_crdr=' + showCrDr;

				// Create temporary download link
				var link = document.createElement('a');
				link.href = exportUrl;
				link.download = 'Trial_Balance_' + startDate + '_to_' + endDate + '.csv';
				link.style.display = 'none';

				// Trigger download
				document.body.appendChild(link);
				link.click();
				document.body.removeChild(link);
			}
		</script>

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
		<script src="../js/idle.js"></script>
</body>

</html>