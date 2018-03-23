<?php
require "PdoDb.php";

class DB
{
    protected $pdo   = null;
    protected $trans = false;

    private function pdodb($persistent = false)
    {
        if ($this->pdo === null) {
            global $config;
            $db_config               = $config['db'];
            $db_config['persistent'] = $persistent;
            $this->pdo               = new PdoDb($db_config);
        }
        return $this->pdo;
    }

    protected function beginTransaction()
    {
        $this->pdodb()
            ->BeginTransaction();
        $this->trans = true;
    }

    protected function commit()
    {
        $this->pdodb()
            ->Commit();
        if (empty($config['db']['persistent'])) {
            $this->close();
        }
        $this->trans = false;
    }

    protected function close()
    {
        $this->pdodb()
            ->Close();
        $this->trans = false;
    }

    protected function rollBack()
    {
        $this->pdodb()
            ->RollBack();
        if (empty($config['db']['persistent'])) {
            $this->close();
        }
        $this->trans = false;
    }

    private function throwException($e, $sql)
    {
        $msg  = $e->getMessage() . ',' . $sql;
        $code = $e->getCode();
        if (is_numeric($code)) {
            throw new Exception($msg, $code);
        } else {
            throw new Exception($msg . ',' . $code);
        }
    }

    public function insert($dsnType, $sql, $params=[])
    {
        try {
            $pdo = $this->pdodb();
            $pdo->Prepare($dsnType, $sql)
                ->BindParams($params)
                ->Execute()
                ->LastInsertId($lastid);
            if (!$this->trans) {
                $pdo->Close();
            }
        } catch (Exception $e) {
            $this->throwException($e, $sql);
        }

        return $lastid;
    }
    public function status(){
        return $this->pdodb()->Status();
    }

    public function update($dsnType, $sql, $params=[])
    {
        try {
            $pdo = $this->pdodb();
            $pdo->Prepare($dsnType, $sql)
                ->BindParams($params)
                ->Execute()
                ->AffectRows($affected);
            if (!$this->trans) {
                $pdo->Close();
            }
        } catch (Exception $e) {
            $this->throwException($e, $sql);
        }

        return $affected;
    }

    /**
     * 更新一条数据
     */
    public function updateOne($where,$data,$table){
        $wherstr = $this->formatWhere($where);
        $sql = "update " . $table . " set ";

        foreach ($data as $key=>$val){
            $sql .= $key.'=?,';
        }
        $sql = trim($sql,',') . $wherstr;
        $params = is_array($where) ? array_values($where) : [];
        $params2 = is_array($data) ? array_values($data) : [];
        
        return $this->update(PdoDb::DSN_TYPE_MASTER,$sql,array_merge($params,$params2));
    }


    /**
     * 批量更新
     *
     */
    public function updateBatch($table,$values,$index=''){
        $ids = array();
        foreach ($values as $key => $val)
        {
            $ids[] = $val[$index];

            foreach (array_keys($val) as $field)
            {
                if ($field !== $index)
                {
                    $value = is_numeric($val[$field]) ? $val[$field] : "'".$val[$field]."'";
                    $final[$field][] = 'WHEN ' . $val[$index].' THEN '.$value;
                }
            }
        }

        $sql ="update " . $table . " set ";
        foreach ($final as $k => $v)
        {
            $sql .= $k." = CASE $index \n"
                .implode("\n", $v)."\n"
                .'END, ' . "\n";
        }

        $sql = substr($sql,0,-3) . "\n WHERE ". $index." IN (" . implode(',',$ids) . ')';
        $res = $this->update(PdoDb::DSN_TYPE_MASTER,$sql);
        if(!$res){
            return false;
        }
        return true;
    }

    //获取单条数据
    public function getSingle($dsnType, $sql, $params=[])
    {
        try {
            $pdo = $this->pdodb();
            $pdo->Prepare($dsnType, $sql)
                ->BindParams($params)
                ->Execute()
                ->FetchSingle($result);
            if (!$this->trans) {
                $pdo->Close();
            }
            $result = ($result === false) ? array() : $result;
        } catch (Exception $e) {
            $this->throwException($e, $sql);
        }

        return $result;
    }

    //获取全部数据
    public function getAll($dsnType, $sql, $params=[], $page = 0, $size = 20, $persistent = false)
    {
        try {
            if ($page > 0) {
                // 如果参数是字符key
                $params_keys = array_keys($params);
                if (isset($params_keys[0]) && is_numeric($params_keys[0])) {
                    $sql .= ' LIMIT ?, ?';
                    $params[] = ($page - 1) * $size;
                    $params[] = (int) $size;
                } else {
                    $sql .= ' LIMIT :offset, :size';
                    $params['offset'] = ($page - 1) * $size;
                    $params['size']   = (int) $size;
                }
            }
            $pdo = $this->pdodb($persistent);
            $pdo->Prepare($dsnType, $sql)
                ->BindParams($params)
                ->Execute()
                ->FetchAll($result);
            if (!$this->trans && !$persistent) {
                $pdo->Close();
            }
            $result = ($result === false) ? array() : $result;
        } catch (Exception $e) {
            $this->throwException($e, $sql);
        }

        return $result;
    }

    public function featchAll($sql,$params=[],$dsnType=PdoDb::DSN_TYPE_MASTER, $persistent = false){
        try {
            $pdo = $this->pdodb($persistent);
            $pdo->Prepare($dsnType, $sql)
                ->BindParams($params)
                ->Execute()
                ->FetchAll($result);
            if (!$this->trans && !$persistent) {
                $pdo->Close();
            }
            $result = ($result === false) ? array() : $result;
        } catch (Exception $e) {
            $this->throwException($e, $sql);
        }
        return $result;
    }

    //删除某一个建
    public function delete($dsnType, $sql, $params=[])
    {
        try {
            $pdo = $this->pdodb();
            $pdo->Prepare($dsnType, $sql)
                ->BindParams($params)
                ->Execute()
                ->AffectRows($affected);
            if (!$this->trans) {
                $pdo->Close();
            }
        } catch (Exception $e) {
            $this->throwException($e, $sql);
        }

        return $affected;
    }

    /**
     * 插入
     */
    public function insertOne($data,$table){
        if(empty($data) || !$table){
            return false;
        }
        if(is_array($data)){
            $sql = 'insert into ' . $table . "(";
            foreach ($data as $key=>$v){
                $sql .= '`' .$key . '`,';
            }
            $sql = trim($sql,',') . ') values(';
            foreach ($data as $v){
                $sql .= '?,';
            }
            $sql = trim($sql,',') . ')';
        }else{
            $sql = $data;
        }
        $params = array_values($data);
        $res = $this->insert(PdoDb::DSN_TYPE_MASTER,$sql,$params);
        if(!$res){
            return false;
        }
        return $res;
    }

    /**
     * info 批量插入
     */
    public function insertBatch($data,$table){
        if(empty($data) || !$table){
            return false;
        }
        if(is_array($data)){
            $sql  = 'insert into ' . $table . "(";
            foreach ($data[0] as $key=>$v){
                $sql .= '`' .$key . '`,';
            }
            $sql = trim($sql,',') . ') values';
            foreach ($data as $item){
                $valstr = '("';
                $values = array_values($item);
                $valstr .= implode('","',$values);
                $valstr .= '"),';
                $sql .=$valstr;
            }
            $sql = trim($sql,',');
        }else{
            $sql = $data;
        }
        $res = $this->insert(PdoDb::DSN_TYPE_MASTER,$sql);
        if(!$res){
            return false;
        }
        return $res;
    }


    /**
     * 格式化
     * @param $where
     * @return string
     */
    protected function formatWhere($where){
        if(!$where){
            return '';
        }
        $wherestr = " where ";
        if(is_array($where)){
            foreach ($where as $key=>$value){
                $wherestr .= $key . '=? and ';
            }
            return substr($wherestr,0,-4);
        }else{
            return $wherestr . $where;
        }
    }

    /**
     * 获取单条记录
     * @param $where
     * @param $table
     * @param string $feild
     * @return bool
     */
    public function rowGet($where,$table,$feild='*'){

        if(!$where || !$table){
            return false;
        }
        $wherstr = $this->formatWhere($where);
        $sql = "select " .$feild . " from " . $table . $wherstr;
        $params = is_array($where) ? array_values($where) : [];
        return $this->getSingle(PdoDb::DSN_TYPE_MASTER,$sql,$params);
    }

    //safe
    public function featchSingle($sql,$params=[]){
        return $this->getSingle(PdoDb::DSN_TYPE_MASTER,$sql,$params);
    }


    /**
     * info
     * $order = ['Xxx','desc']
     */
    public function get_all($where,$table,$feild='*',$order=false){
        if(!$where || !$table){
            return false;
        }
        $wherstr = $this->formatWhere($where);
        $sql = "select " .$feild . " from " . $table . $wherstr;
        if($order){
            $sql .=   " order by $order[0] $order[1]";
        }
        $params = is_array($where) ? array_values($where) : [];
        return $this->getAll(PdoDb::DSN_TYPE_MASTER,$sql,$params);
    }

    /**
     * set 更新
     */
    public function setUpdate($where,$data,$table){
        if(!$where || !$table) {
            return false;
        }
        $wherstr = $this->formatWhere($where);
        $sql = "update " . $table . " set ";
        if(is_array($data)){
            foreach ($data as $key=>$v){
                $sql .= $key ."=?,";
            }
        }elseif(is_string($data)){
            $sql .=$data;
        }

        $sql = trim($sql,',') . $wherstr;
        $params = is_array($where) ? array_values($where) : [];
        $params2 = is_array($data) ? array_values($data) : [];
        return $this->update(PdoDb::DSN_TYPE_MASTER,$sql,array_merge($params,$params2));
    }

    protected function sendnotice($content, $title){
        $Helper_Sms = new Helper_Sms();
        $Helper_Sms->send_sms($content);
        $Helper_Sms->send_email($title,$content);
    }



}
