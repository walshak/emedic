<?php
class Insurance
{
    private $dbCon;
    private $table = 'insurance_tbl';


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





    public function update($request)
    {
    }

    public function save($request)
    {
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
