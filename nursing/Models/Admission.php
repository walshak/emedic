<?php
class Admission
{
    private $dbCon;
    private $table = 'admission';


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


    
    public function admittedToday($arr = [])
    {

        $where_clause = '';
        foreach ($arr as $key => $value) {
            $where_clause .= " AND $key = '$value'  ";
        }
        $today_date = date("Y-m-d");
        $sql = "SELECT *  FROM $this->table  WHERE hospital_no IS NOT NULL $where_clause AND date_admit LIKE '$today_date%'";
        $stmt = $this->dbCon->prepare($sql);
        $stmt->execute();
        return json_decode(json_encode($stmt->fetchAll(PDO::FETCH_ASSOC) ));
    }

    public function admittedByDoc($arr = [])
    {

        $where_clause = '';
        foreach ($arr as $key => $value) {
            $where_clause .= " AND $key = '$value'  ";
        }
       
        $sql = "SELECT *  FROM $this->table  WHERE hospital_no IS NOT NULL $where_clause AND adm_status = 3 ";
        $stmt = $this->dbCon->prepare($sql);
        $stmt->execute();
        return json_decode(json_encode($stmt->fetchAll(PDO::FETCH_ASSOC) ));
    }

    public function dischargedToday($arr = [])
    {

        $where_clause = '';
        foreach ($arr as $key => $value) {
            $where_clause .= " AND $key = '$value'  ";
        }
        $today_date = date("Y-m-d");
        // $sql = "SELECT *  FROM $this->table  WHERE hospital_no IS NOT NULL $where_clause AND adm_status = 4 AND date_discharge LIKE '$today_date%'";
        $sql = "SELECT *  FROM $this->table  WHERE hospital_no IS NOT NULL $where_clause AND adm_status = 4 ";
        $stmt = $this->dbCon->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return json_decode(json_encode($stmt->fetchAll(PDO::FETCH_ASSOC) ));
    }

 

    public function get($data, $all = false)
    {
        return $this->fetch($data, $all);
    }


    public function count($arr = [])
    {

        $where_clause = '';
        foreach ($arr as $key => $value) {
            $where_clause .= " AND $key = '$value'  ";
        }

        $sql = "SELECT COUNT(*) count FROM $this->table  WHERE hospital_no IS NOT NULL $where_clause ";
        $stmt = $this->dbCon->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }



    public function update($request)
    {
    }

    public function save($request)
    {
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

        $sql = "SELECT * FROM $this->table WHERE 1 $where_clause ";
        $stmt = $this->dbCon->prepare($sql);
        $stmt->execute();
        return json_decode(json_encode($all ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC)));
    }
}
