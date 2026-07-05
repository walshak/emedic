<?php
class DialysisData
{
    private $dbCon;
    private $id;
    private $table = 'dialysis_data';
    private $service_table = 'prices_table';


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

    public function get_services($data = [], $all = false)
    {
        return $this->fetch_services($data, $all);
    }



    public function get($data, $all = false)
    {
        return $this->fetch($data, $all);
    }

    private function group_by($key, $data)
    {
        $result = array();
        foreach ($data as $val) {
            if (array_key_exists($val[$key], $result)) {
                array_push($result[$val[$key]], $val);
            } else {
                $result[$val[$key]] = array();
                array_push($result[$val[$key]], $val);
            }
        }
        return $result;
    }

    public function getByHosp($hosp_no, $all = false)
    {
        if ($hosp_no == "") {
            return [];
        }

        $sql = "SELECT id, hospital_no FROM dialysis WHERE status = '1' AND hospital_no = $hosp_no ";
        $stmt = $this->dbCon->prepare($sql);
        $stmt->execute();
        $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $values = array_values(array_column((array) $arr, 'id'));

        if (sizeof($values) > 0) {
            $sql = "SELECT * FROM $this->table WHERE status = '1' AND  dialysis_id IN (" . implode(',', $values) . ") ";
            $stmt = $this->dbCon->prepare($sql);
            $stmt->execute();
            $arr =  $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($arr != false) {
                return $this->group_by('dialysis_id', $arr);
            }
            return $arr;
        }
        return [];
    }

    public function getByDialysisId($id, $all = false)
    {
        if ($id == "") {
            return [];
        }

        $sql = "SELECT id, hospital_no FROM dialysis WHERE status = '1' AND dialysis_id = $id ";
        $stmt = $this->dbCon->prepare($sql);
        $stmt->execute();
        $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $values = array_values(array_column((array) $arr, 'id'));


        return [];
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



    public function update($data, $id)
    {



        if ($data['completed'] == 'yes') {
            $update_completed_stmt =  $this->dbCon->prepare("UPDATE  dialysis SET completed = 'yes', date_marked_completed=? WHERE token = ?");
            $update_completed_stmt->execute(array($data['date_marked_completed'], $data['token']));
        }


        $update = $this->dbCon->prepare("UPDATE  $this->table SET 
		performed_by =?, performed_date =?, performed_time =?, 
		dialysis_note =?, completed =?, date_marked_completed =?, uf =?,
        blood_flow =?, vp_pre_weight =?, ap_post_weight =?, ufr =?, hep =?, 
		bp =?, pulse =?, fluid_loss =?, 
		Diagnosis =?, Access =?, Dialyzer =?,
		PCV =?, Duration =?, SPO2 =?,complication =?, 
		Name_Nurse =?, Duty_Shift =? 
		WHERE id = ? ");
        return $update->execute(array(
            // $data['dialysis_id'],
            $data['performed_by'],
            $data['performed_date'],
            $data['performed_time'],
            $data['dialysis_note'],
            $data['completed'],
            $data['date_marked_completed'],
            $data['uf'],
            $data['blood_flow'],
            $data['vp_pre_weight'],
            $data['ap_post_weight'],
            $data['ufr'],
            $data['hep'],
            $data['bp'],
            $data['pulse'],
            $data['fluid_loss'],
            $data['diagnosis'],
            $data['access'],
            $data['dialyzer'],
            $data['pcv'],
            $data['duration'],
            $data['spo'],
            $data['intradialysis_complication'],
            $data['name_nurse'],
            $data['duty_shift'],
            $id
        ));
    }
    public function save($data)
    {

        $completed = 'no';
        $date_marked_completed = null;
        if (isset($data['completed'])) {
            $completed = $data['completed'];
            $date_marked_completed = date('Y-m-d H:i:s');

            if ($completed == 'yes') {
                $update_completed_stmt =  $this->dbCon->prepare("UPDATE  dialysis SET completed = 'yes', date_marked_completed=?,mark_completed_by=? WHERE token = ?");
                $update_completed_stmt->execute(array($date_marked_completed, $_SESSION['fullname'], $data['token']));
            }
        }

        $stmt = $this->dbCon->prepare("INSERT INTO $this->table (dialysis_id,performed_by,performed_date,performed_time,dialysis_note,completed,
		date_marked_completed,uf,blood_flow,vp_pre_weight,ap_post_weight,ufr,hep,bp,pulse,fluid_loss,Diagnosis,Access,Dialyzer,PCV,Duration,SPO2,complication,Name_Nurse,Duty_Shift,token,created_by,`status`) 
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) ");
        return  $save =  $stmt->execute(array(
            $data['dialysis_id'],
            $data['performed_by'],
            $data['performed_date'],
            $data['performed_time'],
            $data['dialysis_note'],
            $completed,
            $date_marked_completed,
            $data['uf'],
            $data['blood_flow'],
            $data['vp_pre_weight'],
            $data['ap_post_weight'],
            $data['ufr'],
            $data['hep'],
            $data['bp'],
            $data['pulse'],
            $data['fluid_loss'],
            $data['diagnosis'],
            $data['access'],
            $data['dialyzer'],
            $data['pcv'],
            $data['duration'],
            $data['spo'],
            $data['intradialysis_complication'],
            $data['name_nurse'],
            $data['duty_shift'],
            $data['token'],
            $_SESSION['id'],
            '1',
        ));
    }


    public function delete($data)
    {
        $stmt = $this->dbCon->prepare("UPDATE $this->table SET status=0 WHERE id=$data");
        $stmt->execute();
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

        $sql = "SELECT * FROM $this->table WHERE status = '1' $where_clause ";
        $stmt = $this->dbCon->prepare($sql);
        $stmt->execute();
        return json_decode(json_encode($all ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC)));
    }

    private function fetch_services($arr = [], $all = false)
    {
        if ($arr == []) {
            $all = true;
        }
        $where_clause = '';
        foreach ($arr as $key => $value) {
            $where_clause .= " AND $key = '$value'  ";
        }

        $sql = "SELECT * FROM $this->service_table WHERE 1 AND category = 'Dialysis' $where_clause ";
        $stmt = $this->dbCon->prepare($sql);
        $stmt->execute();
        return json_decode(json_encode($all ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC)));
    }
}
