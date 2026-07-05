<?php
class Investigation
{
    private $dbCon;
    private $table = 'lab_scan';


    function set_id($id)
    {
        $this->id = $id;
    }

    public function __construct($db)
    {
        $this->dbCon = $db;
    }

    public function all()
    {
        return $this->fetch();
    }

    public function find($id)
    {
        return $this->fetch(['sn' => $id]);
    }



    public function get($data, $all = false)
    {
        return $this->fetch($data, $all);
    }


    public function getPatientLabs($hospital_no, $all = false, $limit = null)
    {
        $limit_string = ' ';
        if ($limit != null) {
            $limit_string = " LIMIT $limit ";
        }

        $stmt = $this->dbCon->prepare("SELECT *  FROM lab_manage WHERE  patient = ? AND labrequest_no like 'LB%' ORDER BY request_date DESC $limit_string");
        $stmt->execute(array($hospital_no));
        return json_decode(json_encode($all ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC)));
    }

    public function getPatientRads($hospital_no, $all = false, $limit = null)
    {
        $limit_string = ' ';
        if ($limit != null) {
            $limit_string = " LIMIT $limit ";
        }

        $stmt = $this->dbCon->prepare("SELECT *  FROM lab_manage WHERE  patient = ? AND labrequest_no like 'RD%' ORDER BY request_date DESC $limit_string");
        $stmt->execute(array($hospital_no));
        return json_decode(json_encode($all ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC)));
    }



    public function count($arr = [], $all = false)
    {

        if ($arr == []) {
            $all = true;
        }
        $where_clause = '';
        foreach ($arr as $key => $value) {
            $where_clause .= " AND $key = '$value'  ";
        }
        $stmt = $this->dbCon->prepare("SELECT COUNT(*) count FROM $this->table  WHERE 1 $where_clause ");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }



    public function update($request) {}

    public function save($request)
    {
        $stmt = $this->dbCon->prepare("INSERT INTO  lab_manage(app_no, labrequest_no,patient,patient_name,test_id,test_name,lab_cat,section,group_id,business_service_center,preferred_specimen,request_note,
        request_date,request_date2,request_by,lab_combos, created_by, doctor_result_status,amount) 
        VALUES (?, ?,?,?,?,?,?,?,?,?,?,?, ?,?,?,?,?,?,?)");
        return $stmt->execute(array(
            $request->app_no,
            $request->labrequest_no,
            $request->patient,
            $request->patient_name,
            $request->test_id,
            $request->test_name,
            $request->lab_cat,
            $request->section,
            $request->app_no,
            "IN",
            $request->preferred_specimen,
            $request->request_note,
            $request->request_date,
            $request->request_date2,
            $request->request_by,
            $request->lab_combos,
            $request->created_by,
            $request->doctor_result_status,
            $request->amount_paying
        ));
    }


    private function fetch($arr = [], $all = false)
    {

        ///arr exp. ['id' = 1, 'name' => 'Abdul']
        if ($arr == []) {
            $all = true;
        }
        $where_clause = '';
        foreach ($arr as $key => $value) {
            $where_clause .= " AND $key = '$value'  ";
        }

        $sql = "SELECT *, hosp_price as cash_price FROM $this->table WHERE test != ''  $where_clause AND (hosp_price > 0 OR nhis_price > 0)";
        $stmt = $this->dbCon->prepare($sql);
        $stmt->execute();
        return json_decode(json_encode($all ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC)));
    }
}
