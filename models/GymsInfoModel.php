<?php
class GymsInfoModel extends BaseModel{

    private $table = 'gyms_info';

    public function checkField($name,$field){
        $sql = 'select * from gyms_info where ?=?';
        return $this->db->featchSingle($sql,[$name,$field]);

    }
}