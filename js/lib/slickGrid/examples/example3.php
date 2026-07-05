<?php require_once('class.batch.php'); 
$Vzukb1aif34b=new batch;  
$V2kfyndm3ugj=$Vzukb1aif34b->selectall('','','');
?>
<!DOCTYPE HTML>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <title>SlickGrid example 3: Editing</title>
  <link rel="stylesheet" href="../slick.grid.css" type="text/css"/>
  <link rel="stylesheet" href="../css/smoothness/jquery-ui-1.8.16.custom.css" type="text/css"/>
  <link rel="stylesheet" href="examples.css" type="text/css"/>
  <style>
    .cell-title {
      font-weight: bold;
    }

    .cell-effort-driven {
      text-align: center;
    }
  </style>
</head>
<body>
<div style="position:relative">
  <div style="width:100%;">
    <div id="myGrid" style="width:100%;height:500px;"></div>
  </div>

  <div class="options-panel">
    <h2>Demonstrates:</h2>
    <ul>
      <li>adding basic keyboard navigation and editing</li>
      <li>custom editors and validators</li>
      <li>auto-edit settings</li>
    </ul>

    <h2>Options:</h2>
    <button onclick="grid.setOptions({autoEdit:true})">Auto-edit ON</button>
    &nbsp;
    <button onclick="grid.setOptions({autoEdit:false})">Auto-edit OFF</button>
      <h2>View Source:</h2>
      <ul>
          <li><A href="https://github.com/mleibman/SlickGrid/blob/gh-pages/examples/example3-editing.html" target="_sourcewindow"> View the source for this example on Github</a></li>
      </ul>
  </div>
</div>

<script src="../lib/firebugx.js"></script>

<script src="../lib/jquery-1.7.min.js"></script>
<script src="../lib/jquery-ui-1.8.16.custom.min.js"></script>
<script src="../lib/jquery.event.drag-2.2.js"></script>

<script src="../slick.core.js"></script>
<script src="../plugins/slick.cellrangedecorator.js"></script>
<script src="../plugins/slick.cellrangeselector.js"></script>
<script src="../plugins/slick.cellselectionmodel.js"></script>
<script src="../slick.formatters.js"></script>
<script src="../slick.editors.js"></script>
<script src="../slick.grid.js"></script>

<script>
  function requiredFieldValidator(value) {
    if (value == null || value == undefined || !value.length) {
      return {valid: false, msg: "This is a required field"};
    } else {
      return {valid: true, msg: null};
    }
  }

  var grid;
  var data = [];
  var columns = [
    {id: "title", name: "Title", field: "title", width: 120, cssClass: "cell-title", editor: Slick.Editors.Text, validator: requiredFieldValidator},
	<?php for($Vm05opbhpmrp=1;$Vm05opbhpmrp<=10;$Vm05opbhpmrp++) { ?>
    {id: "desc", name: "<?php echo $Vm05opbhpmrp; ?>", field: "description-<?php echo $Vm05opbhpmrp; ?>", width: 100, editor: Slick.Editors.LongText},
	<?php } ?>
	
    
  ];
  var options = {
    editable: true,
    enableAddRow: true,
    enableCellNavigation: true,
    asyncEditorLoading: false,
    autoEdit: false
  };

  $(function () {
    <?php $V4jxpnh1o213=1; foreach($V2kfyndm3ugj as $Vabhoja0jzon) { ?>
      var d = (data[<?php echo $V4jxpnh1o213 ?>] = {});

      d["title"] = "Task " + <?php echo $V4jxpnh1o213 ?>;
	 <?php for($Vm05opbhpmrp=0;$Vm05opbhpmrp<=10;$Vm05opbhpmrp++) { ?>
      d["description-<?php echo $Vm05opbhpmrp; ?>"] = "X";
	<?php } ?>
      
	  <?php $V4jxpnh1o213++; } ?>
    

    grid = new Slick.Grid("#myGrid", data, columns, options);

    grid.setSelectionModel(new Slick.CellSelectionModel());

    grid.onAddNewRow.subscribe(function (e, args) {
      var item = args.item;
      grid.invalidateRow(data.length);
      data.push(item);
      grid.updateRowCount();
      grid.render();
    });
  })
</script>
</body>
</html>
