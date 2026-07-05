<?php
class LabGroup
{
    private $dbCon;
    private $table = 'custom_groups_labs';


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
        return $this->fetch(['id' => $id]);
    }



    public function get($data, $all = false)
    {
        return $this->fetch($data, $all);
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



    public function delete($data)
    {
        $stmt = $this->dbCon->prepare("DELETE FROM   $this->table WHERE id = ? ");
        return $stmt->execute(array(
            $data['id']
        ));
    }


    public function update($request)
    {
    }

    public function save($data)
    {
        $stmt = $this->dbCon->prepare("INSERT INTO $this->table (group_id, lab_id, created_by, specimen, request_note) VALUES (?, ?, ?, ?, ?) ");
        $save =  $stmt->execute(array(
            $data['group_id'],
            $data['lab_id'],
            $data['created_by'],
            $data['specimen'],
            $data['request_note']
        ));

        if($save){
            return $this->dbCon->lastInsertId();
        }

        return false;
    }

    private function fetch($arr = [], $all = false)
    {
        if ($arr == []) {
            $all = true;
        }
        $where_clause = '';
        foreach ($arr as $key => $value) {
            $where_clause .= " AND $key = '$value'  ";
        }

        $sql = "SELECT * FROM $this->table WHERE 1 $where_clause AND status = '1' ";
        $stmt = $this->dbCon->prepare($sql);
        $stmt->execute();
        return json_decode(json_encode($all ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC)));
    }
}
