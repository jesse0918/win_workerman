<?php
namespace Service\Message;

use \Workerman\MySQL;

class message_select extends message_common
{	
		private $city_info    = null;
		
		private $site_info    = null;

		public function __construct()
		{
				parent::__construct();
				
				$this->city_info  = $this->get_city();
				
				$this->site_info  = $this->get_site();
		}

		/**
		 *
		 */
		public function send_main($id,$send_id)
		{
				$insert_info = array();
				
				for($i=0 ;$i<10;$i++)
				{
						$insert_info[$i] = "insert into ".$this->tbl_prefix."user_message_list_".$i." (uid,send_uid,msg_id,status)";
				}
				
				$message_info = $this -> get_message_info($id);
				
				$user_info = $this -> get_message_user($message_info);

				$info = array();
				foreach($user_info as $k => $value)
				{
						$tmp = $k % 10;
						
						$info[$tmp][] = "(".$k.",".$send_id.",".$id.",0)";
				}
				
				if(!empty($info))
				{
						foreach($info as $key => $value)
						{
								if(empty($value)) continue;
								
								$inset_sql = $insert_info[$key] ." values ". implode(",",$value);
								$this->db_conn -> query($inset_sql);
						}
				}
		}
		
		
		private function get_message_info($id)
		{
				$sql ="select s_city,
											s_site,
											b_bu,
											b_div,
											b_dept,
											t_type
									from ".$this->tbl_prefix."message_list
									where id = ".$id;
				$q = $this->db_conn -> query($sql);
				
				return $this->db_conn -> fetch_array($q);
		}
		
		private function get_message_user($chk_info)
		{
				$sql = "select a.id,
												case when b.city_code is null then e.city_id else  b.city_code end city_code,
												case when b.area is null then auth_area_apply else  b.area  end area,
												trim(d.bu) bu,
												trim(d.headquarters) divi,
												trim(d.department) dept,
												a.job_type
									from ".$this->tbl_prefix."employee a
									left join ".$this->tbl_prefix."door_auth_formal b on a.email = b.email
									left join ".$this->tbl_prefix."employee_org c on a.id = c.userid and c.active = 1
									left join ".$this->tbl_prefix."org_orginfo d on right(c.cost_code,6) = d.finance_code
									left join ".$this->tbl_prefix."door_auth_apply e on e.empcode = a.empcode and e.condition = 1 and e.apply_status = 2
									where a.isleave = 0";

				$q = $this->db_conn -> query($sql);
				
				$rtn_info = array();

				while($r = $this->db_conn -> fetch_array($q))
				{
						if(!empty($chk_info['s_city']))
						{
								if(empty($r['city_code'])) continue;
								
								if(is_numeric($r['city_code']))
								{
										if($chk_info['s_city'] != $r['city_code']) continue;
								}
								else
								{
										if($this->city_info[$chk_info['s_city']] != $r['city_code']) continue;
								}
						}
						
						if(!empty($chk_info['s_site']))
						{
								if(empty($r['area'])) continue;
								
								$tmp1 = explode(",",$r['area']);
								$tmp2 = explode(",",$this->site_info[$chk_info['s_site']]);
								
								if(empty(array_intersect($tmp1,$tmp2))) continue;
						}
						
						if(!empty($chk_info['b_bu']))
						{
								if($chk_info['b_bu'] != $r['bu']) continue;
						}
						
						if(!empty($chk_info['b_div']))
						{
								if($chk_info['b_div'] != $r['divi']) continue;
						}
						
						if(!empty($chk_info['b_dept']))
						{
								if($chk_info['b_dept'] != $r['dept']) continue;
						}
						
						if(!empty($chk_info['t_type']))
						{
								if($chk_info['t_type'] != $r['job_type']) continue;
						}
						
						$rtn_info[] = $r['id'];
				}
				
				return array_flip($rtn_info);
		}
		
		private function get_city()
		{
				$sql = "select distinct id,code from tcitrn_door_configure where category = 1";
				
				$q = $this->db_conn -> query($sql);
				
				$rtn_info = array();
				
				while($r = $this->db_conn -> fetch_array($q))
				{
						$rtn_info[$r['id']] = $r['code'];
				}
				
				return $rtn_info;
		}
		
		private function get_site()
		{
				$sql = "select site_id,group_concat(area_id) area_id from vw_office_address where site is not null group by site";
				
				$q = $this->db_conn -> query($sql);
				
				$rtn_info = array();
				
				while($r = $this->db_conn -> fetch_array($q))
				{
						$rtn_info[$r['site_id']] = $r['area_id'];
				}
				
				return $rtn_info;
		}
}
?>