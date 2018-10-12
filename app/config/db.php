<?php  
    
    class DBConfig
    {

        public $data = "";
        /*
        // AMBIENTE PRODUCCION
        const DB_TYPE_CONNECTION = "mssqlnative";
        const DB_SERVER = "172.29.137.19";
        const DB_USER = "sifpima";
        const DB_PASSWORD = "ad2426";
        const DB = "SIFPIMA";
        */
        
        /*
        // AMBIENTE PRUEBAS (SQL 2000)
        const DB_TYPE_CONNECTION = "odbc_mssql";
        const DB_SERVER = "192.168.0.4";
        const DB_USER = "websifpima";
        const DB_PASSWORD = "ad2426";
        const DB = "Test";
        */

        
        // AMBIENTE LOCAL
        const DB_TYPE_CONNECTION = "mssqlnative";
        const DB_SERVER = "72.55.156.230";
        const DB_USER = "usuariomovil";
        const DB_PASSWORD = "android1793";
        const DB = "SIFPIMA";
        

        var $conn;
        private $table_name = '';
        private $table_alias = '';
        private $table_query = '';
        private $where_filter = '';
        private $query_string = '';

        function __construct() {            
            $this->conn = $this->dbConnect(); 
        }

        private function dbConnect()
        {
            include ("adodb5/adodb.inc.php");

            switch (self::DB_TYPE_CONNECTION) {
                case 'mssqlnative':
                    $db = ADONewConnection(self::DB_TYPE_CONNECTION);
                    $db->setConnectionParameter('characterSet','UTF-8');
                    $db->setFetchMode(ADODB_FETCH_ASSOC);
                    $db->connect(self::DB_SERVER,self::DB_USER,self::DB_PASSWORD,self::DB);
                    break;
                case 'odbc_mssql':
                    $db = ADONewConnection(self::DB_TYPE_CONNECTION);
                    $db->setConnectionParameter('characterSet','UTF-8');
                    $dsn = "Driver={SQL Server};Server=".self::DB_SERVER.";Database=".self::DB.";";
                    $db->setFetchMode(ADODB_FETCH_ASSOC);
                    $db->Connect($dsn, self::DB_USER, self::DB_PASSWORD);                
                    break;
                default:
                    # code...
                    break;
            }

            return $db;         
        }

        public function execute($query) {
            $this->query_string = $query;
            $rs = $this->conn->Execute($query);
            if (!$rs) {
                print 'error' . $this->conn->ErrorMsg() . '<br>';
            } else {
                return $rs; 
            }           
            
        }

        public function executeSecure($query, $values) {

            $stmt = $this->conn->Prepare($query);           
            $rs   = $this->conn->Execute($stmt, $values, 1);
            
            if (!$rs) {
                print 'error' . $this->conn->ErrorMsg() . '<br>';
            } else {
                return $rs; 
            }           
            
        }

        public function setTable($tbl_data, $relations=null) {

            $this->table_name  = $tbl_data[0];

            $relation_tables = "";
            if ($relations != null) {

                if (array_key_exists('alias', $tbl_data)) {
                    $this->table_alias = $tbl_data['alias'];
                } else {
                    $this->table_alias = $tbl_data[0];
                }

                $this->where_filter = "WHERE ";

                $relation_tables = ", ";
                $cR = 1;
                foreach ($relations['join'] as $relation) {
                    $table = $relation[0];
                    $field = $relation[1];
                    $alias = $relation[2];

                    $relation_tables .= $table." as ".$alias.", ";

                    if(is_array($field)) {

                        $nCR = 1;
                        foreach ($field as $f) {
                        
                            if ($nCR == 1 && $cR == 1) {
                                $this->where_filter .= $this->table_alias.".".$f." = ".$alias.".".$f. " ";
                            } else {
                                $this->where_filter .= "AND ".$this->table_alias.".".$f." = ".$alias.".".$f. " ";
                            }
                            $nCR++;

                        }

                    } else {
                        if ($cR == 1) {
                            $this->where_filter .= $this->table_alias.".".$field." = ".$alias.".".$field. " ";
                        } else {
                            $this->where_filter .= "AND ".$this->table_alias.".".$field." = ".$alias.".".$field. " ";
                        }                       
                    }

                    $cR++;
                }

                $relation_tables = substr_replace($relation_tables, "", -2);

                if (array_key_exists('alias', $tbl_data)) {
                    $this->table_alias = $tbl_data['alias'];
                    $queryRelations = $tbl_data[0]." as ". $tbl_data['alias'].$relation_tables;
                    $this->table_query = $queryRelations;
                } else {
                    $this->table_alias = $tbl_data[0];
                    $queryRelations = $tbl_data[0].$relation_tables;
                    $this->table_query = $queryRelations;
                }

            } else {

                if (array_key_exists('alias', $tbl_data)) {
                    $this->table_alias = $tbl_data['alias'];
                    $this->table_query = $tbl_data[0]." as ". $tbl_data['alias'];               
                } else {
                    $this->table_alias = $tbl_data[0];
                    $this->table_query = $tbl_data[0];
                }
            }               
        }

        public function getTable() {
            return $this->table_name;
        }

        public function prepareFields($columns, $relations=null) {
                        
            $fields = "";
            if($relations != null) {

                foreach ($columns as $key => $value) {

                    $coldata = explode(".", $key);

                    if(sizeof($coldata) == 2) {
                        $fields .= $coldata[0].".".$coldata[1]." as '$value', ";
                    } else {

                        if (preg_match('/\bCONCAT\b/',$key) || preg_match('/\b.\b/',$key)) {
                            $fields .= "$key as '$value', ";
                        } else {
                            $fields .= $this->table_alias.".$key as '$value', ";
                        }                       
                        
                    }
                }

            } else {

                foreach ($columns as $key => $value) {
                    $fields .= $this->table_alias.".$key as '$value', ";
                }               

            }
            
            $fields = substr_replace($fields, "", -2);

            return $fields;
        }

        public function getConditions($cond) {
            $conditions = [
                'limit' => ""
            ];

            if (array_key_exists('limit', $cond)) {
                $conditions['limit'] = "TOP ".$cond['limit'];
            }

            return $conditions;
        }

        public function setFilters($filters) {

            if (array_key_exists('where', $filters)) {

                if ($this->where_filter != "") {

                    foreach ($filters['where'] as $filter) {
                        $field = key($filter);
                        if ($field == "in") {                           
                            foreach ($filter as $inRow) {                               
                                $fieldIn = key($inRow);                             
                                $this->where_filter .= "AND ".$this->table_alias.".".$fieldIn." in (".$inRow[$fieldIn].") ";    
                            }
                        } else if ($field == "special_condition") { 
                            foreach ($filter as $inRow) {                               
                                $fieldIn = key($inRow);                             
                                $this->where_filter .= "AND ".$inRow[$fieldIn]." ";
                            }
                        } else {
                            $this->where_filter .= "AND ".$this->table_alias.".".$field." = '".$filter[$field]."' ";    
                        }
                        
                    }

                } else {
                    $this->where_filter = "WHERE ";
                    $nLine = 1;
                    foreach ($filters['where'] as $filter) {
                        $field = key($filter);

                        $andRow = " AND ";
                        if ($nLine == 1) {
                            $andRow = "";
                        }

                        $this->where_filter .= $andRow.$this->table_alias.".".$field." = '".$filter[$field]."' ";

                        $nLine++;
                    }
                }

            }
        }

        public function customExecute($tbl, $columns, $filters=null, $relations=null) {

            $this->setTable($tbl, $relations);
            $fields     = $this->prepareFields($columns, $relations);
            $conditions = $this->getConditions($filters);
            $this->setFilters($filters);

            $sql = "SELECT ".$conditions['limit']." $fields 
                    FROM ".$this->table_query." ".$this->where_filter;

            // Final Query String
            $this->query_string = $sql;

            $result = $this->execute($sql);
            //$result = "";

            return $result;
        }

        public function getArray($stmt) {
            
            $data = $stmt->getArray();
            return $data;
        }

        public function getQueryString() {
            return $this->query_string;
        }

    }


?>