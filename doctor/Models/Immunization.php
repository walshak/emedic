<?php
class Immunization
{
    private $dbCon;
    private $table = 'immunization';


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

    public function raw($selectors, $arr = [], $all = false)
    {

        if ($arr == []) {
            $all = true;
        }
        $where_clause = '';
        foreach ($arr as $key => $value) {
            $where_clause .= " AND $key = '$value'  ";
        }
        $stmt = $this->dbCon->prepare("SELECT $selectors FROM immunization_vaccines  WHERE status = '1' $where_clause ");
        $stmt->execute();
        return json_decode(json_encode($all ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC)));
    }



    public function update($request)
    {
    }

    public function enrol($request)
    {
        $stmt = $this->dbCon->prepare("INSERT INTO  $this->table  (hospital_no, appointment_number, mother_name, father_name, gestation_at_birth, mode_of_delivery, neonatal_complication) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute(array(
            $request->hospital_no,
            $request->appointment_number,
            $request->mother_name,
            $request->father_name,
            $request->gestation_at_birth,
            $request->mode_of_delivery,
            $request->neonatal_complication
        ));
    }

    
    public function is_vaccine_given($data)
    {
        $stmt = $this->dbCon->prepare("SELECT * FROM  immunization_vaccines  WHERE hospital_no = ? and antigen = ?");
         $stmt->execute(array(
            $data["hospital_no"],
            $data["antigen"]
        ));
        return $stmt->rowCount() > 0 ? true : false;
    }

    public function addVaccine($data)
    {
        $stmt = $this->dbCon->prepare("INSERT INTO  immunization_vaccines  
        (hospital_no, appointment_number, antigen, date_given,comment,
         vaccine_name, patient_ap_services_id, given_by, created_by, next_vaccination_date) 
        VALUES (?, ?, ?, ?,?, ?, ?, ?,?, ?) ");
         return $stmt->execute(array(
            $data["hospital_no"],
            $data["appointment_number"],
            $data["antigen"],
            $data["date_given"],
            $data["comment"],
            $data["vaccine_name"],
            $data["patient_ap_services_id"],
            $data["given_by"],
            $data["created_by"],
            $data["next_vaccination_date"]
        ));
    }


    public function immunization_vaccine_given($hospital_no)
    {
        $stmt = $this->dbCon->prepare("SELECT iv.*, v.name, v.description FROM  immunization_vaccines iv inner join vaccines v  on iv.antigen = v.id WHERE iv.hospital_no = ? and iv.status = '1' ");
         $stmt->execute(array($hospital_no));
        return  json_decode(json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)));
    }



    private function fetch($arr = [], $all = false)
    {

        ///arr exp. ['id' = 1, 'name' => 'Abdul']
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
