<?php
class Opthalmology
{
    private $dbCon;
    private $table = 'ocular_list_his';
    private $table_list = 'ocular_list';


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
        return $this->fetch_list();
    }

    public function find($id)
    {
        return $this->fetch(['sn' => $id]);
    }

    public function find_list($id)
    {
        return $this->fetch_list(['sn' => $id]);
    }



    public function getList($data, $all = false){
        return $this->fetch_list($data, $all);
    }

    public function get($data, $all = false){
        return $this->fetch($data, $all);
    }


    public function count($arr = [], $all = false){

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


    public function saveResult($data)
    {
        $stmt = $this->dbCon->prepare("INSERT INTO $this->table  (`app_no`, `req_No`, `hospital_no`, `ocular_list`, `editorr`, `view_mode`, `result`, `left`, `right`, `entered_by`, `ocular_sn`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ");
        return $stmt->execute(array(
            $data['app_no'],
            0,
            $data['hospital_no'],
            $data['ocular_list'],
            $data['editorr'],
            $data['view_mode'],
            $data['ocular_result'],
            $data['left'],
            $data['right'],
            $data['entered_by'],
            $data['sn']
        ));
    }

    public function updateResult($data){
        $stmt = $this->dbCon->prepare("UPDATE $this->table  SET  `result` = ?, `left` = ?, `right` = ? WHERE  hospital_no = ? AND ocular_sn = ?");
        return $stmt->execute(array(
            $data['ocular_result'],
            $data['left'],
            $data['right'],
            $data['hospital_no'],
            $data['sn']
        ));
    }


    private function fetch($arr = [], $all = false) {
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

    private function fetch_list($arr = [], $all = false) {
        if ($arr == []) {
            $all = true;
        }
        $where_clause = '';
        foreach ($arr as $key => $value) {
            $where_clause .= " AND $key = '$value'  ";
        }

         $sql = "SELECT * FROM $this->table_list WHERE 1 $where_clause ";
        $stmt = $this->dbCon->prepare($sql);
        $stmt->execute();
        return json_decode(json_encode($all ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC)));
    }
}
