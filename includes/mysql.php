<?php

/**
 *	sCMS v.1.0
 * 	February 22, 2012 , 10:40:23 AM  
 *	Iwan Susyanto, S.Si - admin@auracms.org      - 081 327 575 145
 */
 
	if (preg_match("/".basename (__FILE__)."/", $_SERVER['PHP_SELF'])) {
	    header("HTTP/1.1 404 Not Found");
	    exit;
	}


	class sql_db {
		var $db_connect_id;
		var $query_result;
		var $row = array();
		var $rowset = array();
		var $num_queries = 0;
		var $total_time_db = 0;
		var $time_query = "";

		function sql_db($sqlserver, $sqluser, $sqlpassword, $database, $persistency = true) {
			$this->db_connect_id = ($persistency) ? @mysqli_connect("p:".$sqlserver, $sqluser, $sqlpassword, $database) : @mysqli_connect($sqlserver, $sqluser, $sqlpassword, $database);
			if ($this->db_connect_id) {
				if ($database != "" && !@mysqli_select_db($this->db_connect_id, $database)) {
					@mysqli_close($this->db_connect_id);
					$this->db_connect_id = false;
				}
				return $this->db_connect_id;
			} else {
				return false;
			}
		}
	
		function sql_close() {
			if ($this->db_connect_id) {
				if ($this->query_result) @mysqli_free_result($this->query_result);
				$result = @mysqli_close($this->db_connect_id);
				return $result;
			} else {
				return false;
			}
		}
	
		function sql_query($query = "", $transaction = false) {
			$key = print_r($this->query_result,true);
			//$myText = print_r($myVar,true)."foo bar";

			unset($key);
			if ($query != "") {
				$this->query_result = @mysqli_query($this->db_connect_id,$query);
			}
			if ($this->query_result) {
				$key = print_r($this->query_result,true);
				$this->num_queries += 1;
				unset($this->row[$key]);
				unset($this->rowset[$key]);
				return $this->query_result;
			} else {
				//return ($transaction == END_TRANSACTION) ? true : false;
			}
		}
	
		function sql_numrows($query_id = 0) {
			if (!$query_id) $query_id = $this->query_result;
			if ($query_id) {
				$result = @mysqli_num_rows($query_id);
				return $result;
			} else {
				return false;
			}
		}
	
		function sql_affectedrows() {
			if ($this->db_connect_id) {
				$result = @mysqli_affected_rows($this->db_connect_id);
				return $result;
			} else {
				return false;
			}
		}
	
		function sql_numfields($query_id = 0) {
			if (!$query_id) $query_id = $this->query_result;
			if ($query_id) {
				$result = @mysqli_field_count($query_id);
				return $result;
			} else {
				return false;
			}
		}
	
		function sql_fieldname($offset, $query_id = 0) {
			if (!$query_id) $query_id = $this->query_result;
			if ($query_id) {
				$fieldInfo = @mysqli_fetch_field_direct($query_id, $offset);
				$result = $fieldInfo->result;
				return $result;
			} else {
				return false;
			}
		}
	
		function sql_fieldtype($offset, $query_id = 0) {
			if (!$query_id) $query_id = $this->query_result;
			if($query_id) {
				/*$result = @mysql_field_type($query_id, $offset);
				return $result;*/

				$type_id = @mysqli_fetch_field_direct( $query_id, $offset)->type;
				$types = array();
				$constants = get_defined_constants(true);
				foreach ($constants['mysqli'] as $c => $n)
				 if (preg_match('/^MYSQLI_TYPE_(.*)/', $c, $m))
				  $types[$n] = $m[1];
				$resultType = array_key_exists( $type_id, $types ) ? $types[$type_id] : NULL;

				return $result;

			} else {
				return false;
			}
		}
	
		function sql_fetchrow($query_id = 0) {
			if (!$query_id) $query_id = $this->query_result;
			if ($query_id) {
				$key2 = print_r($query_id,true);
				$this->row[(int)$key2] = @mysqli_fetch_array($query_id, MYSQLI_BOTH);
				return $this->row[(int)$key2];
			} else {
				return false;
			}
		}
	
		function sql_fetchrowset($query_id = 0) {
	
			if (!$query_id) $query_id = $this->query_result;
			if ($query_id) {
				unset($this->rowset[$query_id]);
				unset($this->row[$query_id]);
				while ($this->rowset[$query_id] = @mysqli_fetch_array($query_id, MYSQLI_BOTH)) {
					$result[] = $this->rowset[$query_id];
				}
				return $result;
			} else {
				return false;
			}
		}
	
		function sql_fetchfield($field, $rownum = -1, $query_id = 0) {
			if (!$query_id) $query_id = $this->query_result;
			if ($query_id) {
				if ($rownum > -1) {
					//$result = @mysql_result($query_id, $rownum, $field);

					mysqli_data_seek($query_id, $rownum);
					if( !empty($field) ) {
					  while($finfo = mysqli_fetch_field( $query_id )) {
					    if( $field == $finfo->name ) {
					      $f = mysqli_fetch_assoc( $query_id );
					      $result =  $f[ $field ];
					    }
					  }
					} else {
					  $f = mysqli_fetch_array( $result );
					  $result = $f[0];
					}

				} else {
					if (empty($this->row[$query_id]) && empty($this->rowset[$query_id])) {
						if ($this->sql_fetchrow()) {
							$result = $this->row[$query_id][$field];
						}
					} else {
						if ($this->rowset[$query_id]) {
							$result = $this->rowset[$query_id][0][$field];
						} else if ($this->row[$query_id]) {
							$result = $this->row[$query_id][$field];
						}
					}
				}
				return $result;
			} else {
				return false;
			}
		}
	
		function sql_rowseek($rownum, $query_id = 0) {
			if (!$query_id) $query_id = $this->query_result;
			if ($query_id) {
				$result = @mysqli_data_seek($query_id, $rownum);
				return $result;
			} else {
				return false;
			}
		}
	
		function sql_nextid() {
			if ($this->db_connect_id) {
				$result = @mysqli_insert_id($this->db_connect_id);
				return $result;
			} else {
				return false;
			}
		}
	
		function sql_freeresult($query_id = 0){
			if (!$query_id) $query_id = $this->query_result;
			if ($query_id) {
				unset($this->row[$query_id]);
				unset($this->rowset[$query_id]);
				@mysqli_free_result($query_id);
				return true;
			} else {
				return false;
			}
		}
	
		function sql_error($query_id = 0) {
			$result["message"] = @mysqli_error($this->db_connect_id);
			$result["code"] = @mysqli_errno($this->db_connect_id);
			return $result;
		}
		
		
	}
	
	$db = new sql_db($mysql_host, $mysql_user, $mysql_password, $mysql_database, false);
	
	if (!$db->db_connect_id) {	
		die("<br /><br /><center><img src=\"images/under_construction.gif\"><br /><br /><b>There seems to be a problem with the MySQL server, sorry for the inconvenience.<br /><br />We should be back shortly.<br /><br /></center></b>");
	}