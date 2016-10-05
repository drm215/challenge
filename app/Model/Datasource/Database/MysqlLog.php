<?php

  App::uses('Mysql', 'Model/Datasource/Database');
  
  class MysqlLog extends Mysql {
    
    function logQuery($sql, $params = array()) {
      parent::logQuery($sql, $params);
      CakeLog::write('sql', 'Took: ' . $this->took . ', numRows: ' . $this->numRows . ', affected: ' . $this->affected . ', sql: ' . $sql);
    }
    
  }

?>