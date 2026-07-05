<?php
class Patient
{
    private $dbCon;
    private $table = 'enrollee';


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

    public function getByHospitalNo($hospital_no){
        $sql = "SELECT * FROM enrollee WHERE hospital_no = ? LIMIT 1";
        $stmt = $this->dbCon->prepare($sql);
        $stmt->execute([$hospital_no]);
        $enrolee = json_decode(json_encode( $stmt->fetch(PDO::FETCH_ASSOC) ));

        if(!empty($enrolee)){
            $sql = "SELECT insurance_name, interest,insurance_type,services_access,payment_mode FROM insurance_tbl WHERE insurance_no = ? LIMIT 1";
            $stmt = $this->dbCon->prepare($sql);
            $stmt->execute([$enrolee->hmo_no]);
            $hmo_no_info = json_decode(json_encode( $stmt->fetch(PDO::FETCH_ASSOC) ));

            if(!empty($enrolee)){
                $enrolee->insurance_name = $hmo_no_info->insurance_name;
                $enrolee->interest = $hmo_no_info->interest;
                $enrolee->insurance_type = $hmo_no_info->insurance_type;
                $enrolee->services_access = $hmo_no_info->services_access;
                $enrolee->insurance_no = $hmo_no_info->insurance_no;
                $enrolee->payment_mode = $hmo_no_info->payment_mode;

                return $enrolee; 
            }
        }

        return null;
    }


    public function getByHospitalNo2($hospital_no){
        $sql = "SELECT e.*,i.insurance_name,i.interest,i.insurance_type FROM enrollee as e INNER JOIN insurance_tbl as i ON e.hmo_no=i.insurance_no WHERE hospital_no = ? ";
        $stmt = $this->dbCon->prepare($sql);
        $stmt->execute([$hospital_no]);
        return json_decode(json_encode( $stmt->fetch(PDO::FETCH_ASSOC) ));
    }

    public function getLight($data, $all = false)
    {
        return $this->fetch_light($data, $all);
    }

    public function patientSeenToday($doctor_id)
    {
        $setdate = date("Y-m-d");
        $stmt = $this->dbCon->prepare("SELECT * FROM apptm  WHERE doctor_id = ? AND  date_ap = ? ");
        $stmt->execute(array($doctor_id, $setdate));
        return $stmt->rowCount();
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



    public function update($data)
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
            $where_clause .= " AND e.$key = '$value'  ";
        }


        $sql = "SELECT e.*,i.insurance_name,i.interest,i.insurance_type FROM enrollee as e INNER JOIN insurance_tbl as i ON e.hmo_no=i.insurance_no WHERE 1 $where_clause ";
        $stmt = $this->dbCon->prepare($sql);
        $stmt->execute();
        return json_decode(json_encode($all ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC)));
    }

    private function fetch_light($arr = [], $all = false)
    {
        if ($arr == []) {
            $all = true;
        }
        $where_clause = '';
        foreach ($arr as $key => $value) {
            $where_clause .= " AND $key = '$value'  ";
        }

        $sql = "SELECT sn, hospital_no, surname, fname, oname other_name, token,hmo_no, insurance FROM enrollee  WHERE 1 $where_clause ";
        $stmt = $this->dbCon->prepare($sql);
        $stmt->execute();
        return json_decode(json_encode($all ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC)));
    }
}
