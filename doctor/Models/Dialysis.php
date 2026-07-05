<?php
class Dialysis
{
    private $dbCon;
    private $table = 'dialysis';
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

        $completed = 'no';
        $date_marked_completed = null;
        if (isset($data['completed'])) {
            $completed = $data['completed'];
            $date_marked_completed = date('Y-m-d H:i:s');
        }
        $update = $this->dbCon->prepare("UPDATE $this->table SET  uf = ?, blood_flow = ?, vp_pre_weight = ?, ap_post_weight = ?,ufr = ?, performed_date = ?,performed_time = ?, 
        hep = ?, bp = ?, pulse = ?, 
		fluid_loss = ?, 
		Diagnosis = ?, 
		Access = ?, 
		Dialyzer = ?, 
		PCV = ?, 
		Duration = ?, 
		SPO2 = ?, 
		complication = ?, 
		Name_Nurse = ?, 
		Duty_Shift = ?, 
		dialysis_note = ?, completed = ?, date_marked_completed=? WHERE id = ? ");
        return $update->execute(array(
            $data['uf'],
            $data['blood_flow'],
            $data['vp_pre_weight'],
            $data['ap_post_weight'],
            $data['ufr'],
            $data['performed_date'],
            $data['performed_time'],
            $data['hep'],
            $data['bp'],
            $data['pulse'],
            $data['fluid_loss'],
            $data['Diagnosis'],
            $data['Access'],
            $data['Dialyzer'],
            $data['PCV'],
            $data['Duration'],
            $data['SPO2'],
            $data['intradialysis_complication'],
            $data['Name_Nurse'],
            $data['Duty_Shift'],
            $data['dialysis_note'],
            $completed,
            $date_marked_completed,
            $id
        ));
    }


    public function save($data)
    {
        $check = $this->dbCon->prepare("SELECT id FROM $this->table WHERE app_no = ? AND hospital_no = ? and request_type = ? ");
        $check->execute(array(
            $data['appointment_number'],
            $data['hospital_no'],
            $data['request_type']
        ));

        if ($check->rowCount() == 0) {
            $stmt = $this->dbCon->prepare("INSERT INTO $this->table (app_no, hospital_no,patient_name, request_by, schedule_time, schedule_date, request_note, 
            amount, request_type,number_of_session, created_by, price_table_id, token) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ");
            return  $save =  $stmt->execute(array(
                $data['appointment_number'],
                $data['hospital_no'],
                $data['patient_name'],
                $data['request_by'],
                $data['schedule_time'],
                $data['schedule_date'],
                $data['request_note'],
                $data['amount'],
                $data['request_type'],
                $data['number_of_session'],
                $data['created_by'],
                $data['price_table_id'],
                $data['token']
            ));
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

        $sql = "SELECT * FROM $this->table WHERE status = '1' $where_clause  ";
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
