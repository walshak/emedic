<?php
class Transplant
{
    private $dbCon;
    private $table = 'transplants';
    private $service_table = 'prices_table';
    private $donor_table = 'transplants_donors';


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

    public function getDonor($data, $all = false)
    {
        return $this->fetch_donor($data, $all);
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




    public function addDonor($data)
    {

        $check = $this->dbCon->prepare("SELECT id FROM $this->donor_table WHERE hospital_no = ? AND transplant_id = ? ");
        $check->execute(array(
            $data['hospital_no'],
            $data['transplant_id']
        ));

        if ($check->rowCount() == 0) {
            $insert = $this->dbCon->prepare("INSERT INTO $this->donor_table 
            (transplant_id,hospital_no,name,phone_number,address,nok_name,nok_phone_number,nok_address,comment,percentage,is_matched,blood_group,gender,genotype)  VALUES  (?,?,?,?,?,?,?,?,?,?, ?, ?, ?, ?)   ");
            $save = $insert->execute(array(
                $data['transplant_id'],
                $data['hospital_no'],
                $data['name'],
                $data['phone_number'],
                $data['address'],
                $data['nok_name'],
                $data['nok_phone_number'],
                $data['nok_address'],
                $data['comment'],
                $data['percentage'],
                $data['is_matched'],
                $data['blood_group'],
                $data['gender'],
                $data['genotype']
            ));

            if ($save) {
                return true;
            } else {
                return $this->dbCon->errorInfo();
            }
        } else {
            return 'Already added';
        }
    }
    public function update($data, $id)
    {

        $update = $this->dbCon->prepare("UPDATE  $this->table SET  	hla_machine = ?, dsa_findings = ?, plasma_exchange = ?, surgical_notes = ?,
        warm_ischemic_time = ?, cold_ischemic_time = ? WHERE id = ? ");
        return $update->execute(array(
            $data['hla_machine'],
            $data['dsa_findings'],
            $data['plasma_exchange'],
            $data['surgical_notes'],
            $data['warm_ischemic_time'],
            $data['cold_ischemic_time'],
            $id
        ));
    }


    public function save($data)
    {

        try{
            $check = $this->dbCon->prepare("SELECT id FROM $this->table WHERE app_no = ? AND	hospital_no = ? and transplant_type = ? ");
        $check->execute(array(
            $data['appointment_number'],
            $data['hospital_no'],
            $data['request_type']
        ));

        if ($check->rowCount() == 0) {
            $stmt = $this->dbCon->prepare("INSERT INTO $this->table (app_no, hospital_no,patient_name, created_by, price_table_id,amount,  transplant_type, done_transplant_before, number_of_transplant) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ");
            $save =  $stmt->execute(array(
                $data['appointment_number'],
                $data['hospital_no'],
                $data['patient_name'],
                $data['created_by'],
                $data['price_table_id'],
                $data['amount'],
                $data['request_type'],
                $data['done_transplant_before'],
                $data['number_of_transplant']
            ));
            return ['message' => ($save ? '<h4>Request Saved Successfully!</h4>' : 'Request Not Saved'),  'save' => $save, "modify" => false, 'error'=> false];
        } else {
            $stmt = $this->dbCon->prepare("UPDATE $this->table SET price_table_id = ?, amount = ?, transplant_type = ?, done_transplant_before = ?, number_of_transplant = ? 
             WHERE app_no = ? AND	hospital_no = ? and transplant_type = ? ");
            $save =  $stmt->execute(array(
                $data['price_table_id'],
                $data['amount'],
                $data['request_type'],
                $data['done_transplant_before'],
                $data['number_of_transplant'],
                $data['appointment_number'],
                $data['hospital_no'],
                $data['request_type']
            ));
            return ['message' => 'Already Requested', 'save' => false, "modify" => true, 'error'=> false];
        }

        return ['message' => 'Failed', 'save' => false, "modify" => false, 'error'=> true];
        }
        catch (Exception $e) {
            // return $e;
             return ['message' => 'Error as occured...', 'save' => false, "modify" => false, 'error'=> true];
        }
        
    }


    public function delete($data)
    {
    }


    public function transplantWithNote()
    {
        $sql = "SELECT * FROM $this->table WHERE surgical_notes IS NOT NULL AND surgical_notes != '' ";
        $stmt = $this->dbCon->prepare($sql);
        $stmt->execute();
        return json_decode(json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)));
    }


    public function transplantWithOutNote()
    {
        $sql = "SELECT * FROM $this->table WHERE surgical_notes IS  NULL OR surgical_notes = '' ";
        $stmt = $this->dbCon->prepare($sql);
        $stmt->execute();
        return json_decode(json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)));
    }


    private function fetch($arr = [], $all = false, $limit = null)
    {
        if ($arr == []) {
            $all = true;
        }
        $where_clause = '';
        foreach ($arr as $key => $value) {
            $where_clause .= " AND $key = '$value'  ";
        }

        $LIMIT_ = '';
        if ($limit != null) {
            $LIMIT_ = ' LIMIT ' . $limit . ' ';
        }
        $sql = "SELECT * FROM $this->table WHERE 1 $where_clause $LIMIT_ ";
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

        $sql = "SELECT * FROM $this->service_table WHERE 1 AND category = 'Transplant' $where_clause ";
        $stmt = $this->dbCon->prepare($sql);
        $stmt->execute();
        return json_decode(json_encode($all ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC)));
    }



    private function fetch_donor($arr = [], $all = false, $limit = null)
    {
        if ($arr == []) {
            $all = true;
        }
        $where_clause = '';
        foreach ($arr as $key => $value) {
            $where_clause .= " AND $key = '$value'  ";
        }

        $LIMIT_ = '';
        if ($limit != null) {
            $LIMIT_ = ' LIMIT ' . $limit . ' ';
        }
        $sql = "SELECT * FROM $this->donor_table WHERE 1 $where_clause $LIMIT_ ";
        $stmt = $this->dbCon->prepare($sql);
        $stmt->execute();
        return json_decode(json_encode($all ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC)));
    }
}
