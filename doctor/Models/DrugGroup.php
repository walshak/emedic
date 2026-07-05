<?php
class DrugGroup
{
    private $dbCon;
    private $table = 'custom_groups_drugs';


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



    public function update($request)
    {
    }

    public function save($data)
    {
        $stmt = $this->dbCon->prepare("INSERT INTO $this->table (group_id, drug_id, dosage, dosage_unit, frequency, duration, duration_unit, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ");
        $save =  $stmt->execute(array(
            $data['group_id'],
            $data['drug_id'],
            $data['dosage'],
            $data['dosage_unit'],
            $data['frequency'],
            $data['duration'],
            $data['duration_unit'],
            $data['created_by'],
        ));

        if ($save) {
            return $this->dbCon->lastInsertId();
        }

        return false;
    }


    public function delete($data)
    {
        $stmt = $this->dbCon->prepare("DELETE FROM   $this->table WHERE id = ? ");
        return $stmt->execute(array(
            $data['id']
        ));
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

        $sql = "SELECT * FROM $this->table WHERE 1 $where_clause ";
        $stmt = $this->dbCon->prepare($sql);
        $stmt->execute();
        return json_decode(json_encode($all ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC)));
    }
}
