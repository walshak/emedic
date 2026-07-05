<?php
class PatientApService{
    private $dbCon;
	private $table = 'patient_ap_services';


	function set_id($id){ $this->id = $id;}

	public function __construct($db){
        $this->dbCon = $db;
	}

	public function all(){
      return $this->fetch();
    }

    public function find($id){
        return $this->fetch(['sn' => $id]);
    }

    

    public function get($data, $all = false){
        return $this->fetch($data, $all);
    }


    public function count($arr = [], $all = false){

        if($arr == []){ $all = true;}
        $where_clause = '';
        foreach ($arr as $key => $value) {
           $where_clause .= " AND $key = '$value'  ";
        }
        $stmt = $this->dbCon->prepare("SELECT COUNT(*) count FROM $this->table  WHERE 1 $where_clause ");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }



 public function update($request){
        

    }

    public function delete($arr){
        $where_clause = 'WHERE paystatus = 0 ';
        foreach ($arr as $key => $value) {
           $where_clause .= " AND $key = '$value'  ";
        }
        $stmt = $this->dbCon->prepare("DELETE FROM patient_ap_services  $where_clause");
        return $delete = $stmt->execute();
    }

    public function save($request){
       

    }


	private function fetch($arr = [], $all = false)
    {

        ///arr exp. ['id' = 1, 'name' => 'Abdul']
        if($arr == []){ $all = true;}
        $where_clause = '';
        foreach ($arr as $key => $value) {
           $where_clause .= " AND $key = '$value'  ";
        }

        $sql= "SELECT * FROM $this->table WHERE 1 $where_clause ";
        $stmt = $this->dbCon->prepare($sql);
        $stmt->execute();
        return json_decode(json_encode($all ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC)));
    }



}
