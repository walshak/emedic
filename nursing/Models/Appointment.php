<?php
class Appointment
{
    private $dbCon;
    private $table = 'apptm';


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

    public function genAppNo()
    {
        $sql = "SELECT MAX(CAST(appt_no AS UNSIGNED)) AS max_appt FROM apptm WHERE appt_no REGEXP '^[0-9]+$'";
        $stmt = $this->dbCon->prepare($sql);
        $stmt->execute();
        $rwx = $stmt->fetch(PDO::FETCH_ASSOC);
        $last_appt = isset($rwx['max_appt']) ? (int)$rwx['max_appt'] : 0;
        $app_no = sprintf('%06d', $last_appt + 1);

        return $app_no;
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



    public function update($arr, $id)
    {
        $set_clause = '';
        $sn = 1;
        foreach ($arr as $key => $value) {

            if ($sn == 1) {
                $set_clause .= "  $key = '$value'  ";
            } else {
                $set_clause .= " , $key = '$value'  ";
            }
            $sn++;
        }

        $sql = "UPDATE $this->table SET $set_clause WHERE sn = $id  ";
        $stmt = $this->dbCon->prepare($sql);
        return $stmt->execute();
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
