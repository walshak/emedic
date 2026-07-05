<?php
class Document
{
    private $dbCon;
    private $table = 'patients_documents';

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
        $stmt = $this->dbCon->prepare("SELECT COUNT(*) count FROM $this->table  WHERE 1 $where_clause AND status='1'");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }



    public function update($data, $id) {}


    public function save($hospital_no, $title = "Un-named File", $link, $file_type = null, $module = null,  $related_table = null, $related_table_id = null, $created_by)
    {
        $today_date = date('Y-m-d');
        $stmt = $this->dbCon->prepare("SELECT id FROM $this->table  WHERE hospital_no = ? AND title = ? AND created_at LIKE '$today_date%' AND status = '1' ");
        $stmt->execute(array($hospital_no, $title));

        if ($stmt->rowCount() == 0) {
            $stmt = $this->dbCon->prepare("INSERT INTO $this->table  (hospital_no, title, link, file_type, module, related_table, related_table_id, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ");
            if ($stmt->execute(array($hospital_no, $title, $link, $file_type, $module, $related_table, $related_table_id, $created_by))) {
                return true;
            } else {
                print_r($this->dbCon->errorInfo());
            }
        }
        return false;
    }


    public function delete($data) {}



    private function fetch($arr = [], $all = false)
    {
        if ($arr == []) {
            $all = true;
        }
        $where_clause = '';
        foreach ($arr as $key => $value) {
            $where_clause .= " AND $key = '$value'  ";
        }

        $sql = "SELECT * FROM $this->table WHERE 1 $where_clause AND status='1' ORDER BY id DESC ";
        $stmt = $this->dbCon->prepare($sql);
        $stmt->execute();
        return json_decode(json_encode($all ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC)));
    }
}
