<?php
namespace Service\Common\Db;
/**
 *	@desc:数据连接
 */
class db_mysql
{
	var $connid;
	var $dbname;
	var $querynum = 0;
	var $debug = 1;
	var $search = array('/union(\s*(\/\*.*\*\/)?\s*)+select/i', '/load_file(\s*(\/\*.*\*\/)?\s*)+\(/i', '/into(\s*(\/\*.*\*\/)?\s*)+outfile/i');
	var $replace = array('union &nbsp; select', 'load_file &nbsp; (', 'into &nbsp; outfile');
	var $doescape = true;

	function connect($dbhost, $dbuser, $dbpw, $dbname = '', $pconnect = 0, $charset = 'utf8')
	{
		$func = 'mysqli_connect';
		if(!$this->connid = @$func($dbhost, $dbuser, $dbpw))
		{
			if(DB_NAME == '' && file_exists(PHPCMS_ROOT.'install.php'))
			{
				header('location:./install.php');
				exit;
			}
			$this->halt('Can not connect to MySQL server');
			return false;
		}

		mysqli_query($this->connid, "SET NAMES '$charset'");

		if($dbname && !@mysqli_select_db($this->connid, $dbname))
		{
			$this->halt('Cannot use database '.$dbname);
			return false;
		}
		$this->dbname = $dbname;
		return $this->connid;
	}

	function select_db($dbname)
	{
		if(!@mysqli_select_db($this->connid, $dbname)) return false;
		$this->dbname = $dbname;
		return true;
    }
	
	function query($sql , $type = '')
	{
		//$func = $type == 'UNBUFFERED' ? 'mysql_unbuffered_query' : 'mysqli_query';
		$func = $type == 'UNBUFFERED' ? 'mysqli_query' : 'mysqli_query';
		if(!($query = @$func($this->connid, $sql)) && $type != 'SILENT')
		{
			$this->halt('MySQL Query Error', $sql);
			return false;
		}
		$this->querynum++;
		return $query;
	}

	//多结果集查询
	function multi_query($sql,$result_type = MYSQLI_ASSOC)
	{
		$query = mysqli_multi_query($this->connid, $sql);

		//多个结果集，按数组存放
		if($query)
		{
			$i=0;
			do{
				if($result = mysqli_store_result($this->connid)){
					while($r = mysqli_fetch_array($result,$result_type)){
						$datas[$i][] = $r;
					};
					@mysqli_free_result($this->connid);

				}

				if (mysqli_more_results($this->connid)){
					//printf("-----------------\n");
				}

				$i++;

			}while(mysqli_next_result($this->connid));
		}

		return $datas;

	}

	function select($sql, $keyfield = '')
	{
		$result = $this->query($sql);
		while($r = $this->fetch_array($result))
		{
			if($keyfield)
			{
				$key = $r[$keyfield];
				$array[$key] = $r;
			}
			else
			{
				$array[] = $r;
			}
		}
		$this->free_result($result);
		return $array;
	}

	function insert($tablename, $array)
	{
		if($this -> doescape)$array = $this -> escape($array);
		return $this->query("INSERT INTO `$tablename`(`".implode('`,`', array_keys($array))."`) VALUES('".implode("','", $array)."')");
	}

	function update($tablename, $array, $where = '')
	{
		if($this -> doescape)$array = $this -> escape($array);
		if($where)
		{
			$sql = '';
			foreach($array as $k=>$v)
			{
				$sql .= ", `$k`='$v'";
			}
			$sql = substr($sql, 1);
			$sql = "UPDATE `$tablename` SET $sql WHERE $where";
		}
		else
		{
			$sql = "REPLACE INTO `$tablename`(`".implode('`,`', array_keys($array))."`) VALUES('".implode("','", $array)."')";
		}
		return $this->query($sql);
	}

	function get_primary($table)
	{
		$result = $this->query("SHOW COLUMNS FROM $table");
		while($r = $this->fetch_array($result))
		{
			if($r['Key'] == 'PRI') break;
		}
		$this->free_result($result);
		return $r['Field'];
	}

	function get_fields($table)
	{
		$fields = array();
		$result = $this->query("SHOW COLUMNS FROM $table");
		while($r = $this->fetch_array($result))
		{
			$fields[] = $r['Field'];
		}
		$this->free_result($result);
		return $fields;
	}

	function get_one($sql, $type = '', $expires = 3600, $dbname = '')
	{
		$query = $this->query($sql, $type, $expires, $dbname);
		$rs = $this->fetch_array($query);
		$this->free_result($query);
		return $rs ;
	}

	function data_seek($query,$i)
	{
		return mysqli_data_seek($query,$i);
	}

	function fetch_array($query, $result_type = MYSQLI_ASSOC)
	{
		return mysqli_fetch_array($query, $result_type);
	}

	function affected_rows()
	{
		return mysqli_affected_rows($this->connid);
	}

	function num_rows($query)
	{
		return mysqli_num_rows($query);
	}

	function num_fields($query)
	{
		return mysqli_num_fields($query);
	}

	function result($query, $row=0)
	{

		//return @mysql_result($query, $row);
		//mysqli没有以上函数，所以采用下面替代方法

		$result = array();
		$i = 0;
		while($r=mysqli_fetch_array($query))
		{
			if($row == $i){
				$result = $r;
				break;
			}
			$i++;
		}
		return $result;
	}

	function free_result(&$query)
	{
		return mysqli_free_result($query);
	}

	function insert_id()
	{
		return mysqli_insert_id($this->connid);
	}

	function fetch_row($query)
	{
		return mysqli_fetch_row($query);
	}
	
	function bound_autocommit()
	{
		mysqli_autocommit($this->connid,TRUE);
	}
	
	function unbound_autocommit()
	{
		mysqli_autocommit($this->connid,False);
	}
	
	function rollback()
	{
		mysqli_rollback($this->connid);
	}
	
	function commit()
	{
		mysqli_commit($this->connid);
	}
	
	function escape($string)
	{
		if(!is_array($string)) return str_replace(array('\n', '\r'), array(chr(10), chr(13)), mysqli_real_escape_string($this->connid , preg_replace($this->search, $this->replace, $string)));
		foreach($string as $key=>$val) $string[$key] = $this->escape($val);
		return $string;
	}

	function table_status($table)
	{
		return $this->get_one("SHOW TABLE STATUS LIKE '$table'");
	}

	function tables()
	{
		$tables = array();
		$result = $this->query("SHOW TABLES");
		while($r = $this->fetch_array($result))
		{
			$tables[] = $r['Tables_in_'.$this->dbname];
		}
		$this->free_result($result);
		return $tables;
	}

	function table_exists($table)
	{
		$tables = $this->tables($table);
		return in_array($table, $tables);
	}

	function field_exists($table, $field)
	{
		$fields = $this->get_fields($table);
		return in_array($field, $fields);
	}

	function version()
	{
		return mysqli_get_server_info($this->connid);
	}

	function close()
	{
		return mysqli_close($this->connid);
	}

	function error()
	{
		return @mysqli_error($this->connid);
	}

	function errno()
	{
		return intval(@mysqli_errno($this->connid)) ;
	}

	function halt($message = '', $sql = '')
	{
		$this->errormsg = "<b>MySQL Query : </b>$sql <br /><b> MySQL Error : </b>".$this->error()." <br /> <b>MySQL Errno : </b>".$this->errno()." <br /><b> Message : </b> $message";
		if($this->debug)
		{
			$msg = (DEBUG) ? $this->errormsg : "Bad Request.";
			echo '<div style="font-size:12px;text-align:left; border:1px solid #9cc9e0; padding:1px 4px;color:#000000;font-family:Arial, Helvetica,sans-serif;"><span>'.$msg.'</span></div>';
			
			$obg_exception = new CosException();
			$obg_exception->set_ERR_MOD("MYSQLI");
			$obg_exception->set_ERR_TASK("QUERY") ;
			$obg_exception->set_ERR_CODE($this->errno());
			$obg_exception->set_ERR_FILE($obg_exception->getFile());
			$obg_exception->set_ERR_LINE($obg_exception->getLine());
			$obg_exception->set_ERR_MESSAGE($this->error());
			$obg_exception->set_ERR_MESSAGE_INFO("MySQL Query :".$sql."\r\n                    MySQL Error :". $this->error());
			
			throw $obg_exception;
			//exit;
		}
	}
}
?>