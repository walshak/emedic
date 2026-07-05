<?php
class Diagnosis
{
    private $dbCon;
    private $table = 'diagnosis';


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


    private function fetch($item = "", $all = false)
    {
        $islike = "";
        if (!empty($item)) {
            $islike = " AND item LIKE '%$item%' ";
        } else {
            $all = true;
        }
        $sql = "SELECT item name FROM $this->table WHERE item != ''  $islike LIMIT 4000 ";
        $stmt = $this->dbCon->prepare($sql);
        $stmt->execute();
        return json_decode(json_encode($all ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC)));
    }
}
