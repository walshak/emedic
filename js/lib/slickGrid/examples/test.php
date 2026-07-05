<?php mysql_connect("localhost","root","");
mysql_select_db("web-school");
?>
<?php
$Vh0nq4iwfwkl = '';
$V4jxpnh1o213 = 0;

$V1jwyoxtrpq0 = "
    SELECT
        `course_id`, `batch`, `start_date`, `end_date`, `org_id`, `id`
    FROM
        `batch`
";
$Vjpggjp1rjjz = mysql_query($V1jwyoxtrpq0);
while($Vjnupbcucmqt = mysql_fetch_array($Vjpggjp1rjjz, MYSQL_ASSOC)){
    $Vh0nq4iwfwkl .= '
        data['.$V4jxpnh1o213.'] = {
            title: "'.$Vjnupbcucmqt['course_id'].'",
            duration: "'.$Vjnupbcucmqt['batch'].'",
            percentComplete: "'.$Vjnupbcucmqt['start_date'].'",
            start: "'.$Vjnupbcucmqt['end_date'].'",
            finish: "'.$Vjnupbcucmqt['org_id'].'",
            effortDriven: "'.$Vjnupbcucmqt['id'].'"
        };
    ';

    $V4jxpnh1o213++;
}
?>	
<script type="text/javascript">
    var grid;

    var columns = [
        {id:"title", name:"Title", field:"title"},
        {id:"duration", name:"Duration", field:"duration"},
        {id:"%", name:"% Complete", field:"percentComplete"},
        {id:"start", name:"Start", field:"start"},
        {id:"finish", name:"Finish", field:"finish"},
        {id:"effort-driven", name:"Effort Driven", field:"effortDriven"}
    ];

    var options = {
        enableCellNavigation: false,
        enableColumnReorder: false
    };

    $(function() {
        var data = [];
        <? echo $Vh0nq4iwfwkl; ?> 

        grid = new Slick.Grid($("#myGrid"), data, columns, options);
    })
</script>