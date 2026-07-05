<?php
class PatientAlert
{
    private $dbCon;
    private $table = 'tbl_patient_alerts';


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


    public function count($arr = [], $all = false) {}



    public function update($data)
    {
        $stmt = $this->dbCon->prepare("UPDATE $this->table SET alert = ? WHERE id=? ");
        return $stmt->execute(array($data['alert'], $data['id']));
    }

    /*  public function delete($data)
    {
        $stmt = $this->dbCon->prepare("DELETE FROM $this->table WHERE id=? ");
        return $stmt->execute(array($data['id']));
    }
 */

    public function delete($data)
    {
        $stmt = $this->dbCon->prepare("UPDATE $this->table SET status = '0' WHERE id = ?");
        return $stmt->execute(array($data['id']));
    }

    public function save($data)
    {
        $stmt = $this->dbCon->prepare("INSERT INTO $this->table (alert, hospital_no, created_by, where_to_show) VALUES (?, ?, ?, ?) ");
        return $stmt->execute(array($data['alert'], $data['hospital_no'], $data['created_by'], $data['who_should_see']));
    }


    private function fetch($arr = array(), $all = false)
    {
        if ($arr == array()) {
            $all = true;
        }

        $where_clause = "";

        foreach ($arr as $key => $value) {

            if ($key == 'where_to_show') {

                if ($value == 1) {
                    $where_clause .= " AND (where_to_show = '1' OR where_to_show = '2') ";
                }

                if ($value == 2) {
                    $where_clause .= " AND where_to_show = '2' ";
                }
            } else {

                $where_clause .= " AND $key = '$value' ";
            }
        }

        $where_clause .= " AND alert != '' AND status = '1'";

        $sql = "SELECT * FROM $this->table WHERE 1 $where_clause";

        $stmt = $this->dbCon->prepare($sql);
        $stmt->execute();

        return json_decode(json_encode($all ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC)));
    }
}
