<?php
namespace Service\Finance;

use \Workerman\MySQL;

/**
 *   合计信息的同步
 */

class sync_finance_data
{
		//数据库连接
		Private $db_conn    = null;
		
		Private $tbl_prefix = null;
		
		//初始化信息,(DB连接)
		public function __construct()
		{
				if(is_null($this->db_conn))
				{
						$this->db_conn = new \Service\Common\Db\db_mysql();
						
						$this->db_conn ->connect(\Service\Common\Config\config::db_host,
																		 \Service\Common\Config\config::db_user,
																		 \Service\Common\Config\config::db_pass,
																		 \Service\Common\Config\config::db_name,
																		 0,
																		 'utf8');
				}
				
				$this->tbl_prefix = \Service\Common\Config\config::g_tbl_prefix;
		}
		
		/**
		 * 合计的再计算
		 */
		public function cost_count($def_no)
		{
  			//mysql_query('START TRANSACTION');
  	
  			$upd_feilds = array();
  			//oth
  			$upd_feilds['tel_total_cost'] = $this->cost_oth_recount($def_no);
  			//ent
  			$upd_feilds['ent_total_cost'] = $this->cost_ent_recount($def_no);
  			//bus
  			$upd_feilds['bus_total_cost'] = $this->cost_bus_recount($def_no);
  			//tra
  			$upd_feilds['tra_total_cost'] = $this->cost_tra_recount($def_no);
  			//bro
  			$upd_feilds['bor_total_cost'] = $this->cost_bor_recount($def_no);
  			//tb
  			$upd_feilds['tb_total_cost']  = $this->cost_tb_recount($def_no);
  			//list
  			$upd_feilds['total_cost']     = $upd_feilds['tel_total_cost'] +
  	                             			  $upd_feilds['ent_total_cost'] +
  	                               			$upd_feilds['bus_total_cost'] +
  	                               			$upd_feilds['tra_total_cost'] +
  	                               			$upd_feilds['tb_total_cost'] -
  	                            		   	$upd_feilds['bor_total_cost'] ;
  	
  			$upd_feilds['tel_total_cost'] = ($upd_feilds['tel_total_cost'] == 0 ? null : $upd_feilds['tel_total_cost']);
  			$upd_feilds['ent_total_cost'] = ($upd_feilds['ent_total_cost'] == 0 ? null : $upd_feilds['ent_total_cost']);
  			$upd_feilds['bus_total_cost'] = ($upd_feilds['bus_total_cost'] == 0 ? null : $upd_feilds['bus_total_cost']);
  			$upd_feilds['tra_total_cost'] = ($upd_feilds['tra_total_cost'] == 0 ? null : $upd_feilds['tra_total_cost']);
  			$upd_feilds['tb_total_cost']  = ($upd_feilds['tb_total_cost']  == 0 ? null : $upd_feilds['tb_total_cost']);
  			$upd_feilds['bor_total_cost'] = ($upd_feilds['bor_total_cost'] == 0 ? null : $upd_feilds['bor_total_cost']);
  			$upd_feilds['total_cost']     = ($upd_feilds['total_cost']     <  0 ? 0    : $upd_feilds['total_cost']);
  	
  			$this->db_conn -> update($this->tbl_prefix."finance_list",$upd_feilds,"finance_def_no = '".$def_no."'");
  	
  			//mysql_query('COMMIT');
		}
  
		/**
		 * 其它费用再计算
		 */
		private function cost_oth_recount($def_no)
		{
				$oth_total = 0;
  	
				$sql = "select a.finance_def_no,
												a.apply_type,
												a.apply_no,
												a.cost,
												d.cost meisei_cost
										from ".$this->tbl_prefix."finance_telephone a
										left join (select b.finance_def_no,
																			b.apply_type,
																			b.apply_no,
																			sum(b.cost) cost
																	from ".$this->tbl_prefix."finance_tel_meisei b
																	right join ".$this->tbl_prefix."finance_telephone c on b.finance_def_no = c.finance_def_no
																																								and b.apply_type = c.apply_type
																																								and b.apply_no = c.apply_no
																where c.finance_def_no = '".$def_no."'
																and c.pro_sts <> '98'
																group by b.finance_def_no,b.apply_type,b.apply_no) d on a.finance_def_no = d.finance_def_no
																																										and a.apply_type = d.apply_type
																																										and a.apply_no = d.apply_no
							where a.finance_def_no = '".$def_no."'
								and a.pro_sts <> '98'
								order by a.finance_def_no asc,a.apply_type asc,a.apply_no asc";
				$mod = $this->db_conn -> query($sql);
		
				while($r = $this->db_conn -> fetch_array($mod))
				{
						if($r['cost'] != $r['meisei_cost'])
						{
								$upd_info    = array();
								$upd_info['cost'] = $r['meisei_cost'];
								$this->db_conn -> update($this->tbl_prefix."finance_telephone",$upd_info,"finance_def_no = '".$def_no."' and apply_type = '0' and apply_no = ".$r['apply_no']);
						}
						
						$oth_total = $oth_total + $r['meisei_cost'];
				}
				
				return $oth_total;
		}

		/**
		 * 业务招待费再计算
		 */
		private function cost_ent_recount($def_no)
		{
  			$ent_total = 0;
  	
				$sql = "select cost
									from ".$this->tbl_prefix."finance_entertain
									where finance_def_no = '".$def_no."' 
										and pro_sts <> 98
										order by finance_def_no asc,apply_type asc, apply_no asc";
				$mod = $this->db_conn -> query($sql);
		
				while($r = $this->db_conn -> fetch_array($mod))
				{
						$ent_total = $ent_total + $r['cost'];
				}
		
				return $ent_total;
 		}

		/**
		 *交通费再计算
		 */
		private function cost_tra_recount($def_no)
		{
				$tra_total = 0;
  	
  			$sql = "select a.finance_def_no,
												a.apply_type,
												a.apply_no,
												a.cost,
												d.cost meisei_cost
										from ".$this->tbl_prefix."finance_traffice a
										left join (select b.finance_def_no,
																			b.apply_type,
																			b.apply_no,
																			sum(b.cost) cost
																from ".$this->tbl_prefix."finance_tra_meisei b
																right join ".$this->tbl_prefix."finance_traffice c on b.finance_def_no = c.finance_def_no
																																							and b.apply_type = c.apply_type
																																							and b.apply_no = c.apply_no
																where c.finance_def_no = '".$def_no."'
																	and c.pro_sts <> '98'
																group by b.finance_def_no,b.apply_type,b.apply_no) d on a.finance_def_no = d.finance_def_no
																																										and a.apply_type = d.apply_type
																																										and a.apply_no = d.apply_no
									where a.finance_def_no = '".$def_no."'
										and a.pro_sts <> '98'
										order by a.finance_def_no asc,a.apply_type asc,a.apply_no asc";
				$mod = $this->db_conn -> query($sql);
		
				while($r = $this->db_conn -> fetch_array($mod))
				{
						if($r['cost'] != $r['meisei_cost'])
						{
								$upd_info    = array();
								$upd_info['cost'] = $r['meisei_cost'];

								$this->db_conn -> update($this->tbl_prefix."finance_traffice",$upd_info,"finance_def_no = '".$def_no."' and apply_type = '1' and apply_no = ".$r['apply_no']);
						}
			
						$tra_total = $tra_total + $r['meisei_cost'];
				}
		
				return $tra_total;
		}

		/**
		 * 暂支费再计算
		 */
		private function cost_bor_recount($def_no)
  	{
  			$bor_total = 0;
  	
  			$sql = "select borrow_ofset cost
									from ".$this->tbl_prefix."finance_borrow
									where finance_def_no = '".$def_no."' 
										and pro_sts <> 98
										order by finance_def_no asc,apply_type asc, apply_no asc";

				$mod = $this->db_conn -> query($sql);
		
				while($r = $this->db_conn -> fetch_array($mod))
				{
						$bor_total = $bor_total + $r['cost'];
				}
		
				return $bor_total;
		}
		
		/**
		 *
		 */
		private function cost_tb_recount($def_no)
		{
				$tb_total = 0;
				
				$sql = "select cost
									from ".$this->tbl_prefix."finance_team_building
									where finance_def_no = '".$def_no."'
										and pro_sts <> 98
										order by finance_def_no asc,apply_type asc, apply_no asc";
				$mod = $this->db_conn -> query($sql);
		
				while($r = $this->db_conn -> fetch_array($mod))
				{
						$tb_total = $tb_total + $r['cost'];
				}
		
				return $tb_total;
		}

		/**
		 *差旅费再计算
		 */
		private function cost_bus_recount($def_no)
		{
  			$bus_total = 0;
				$apply_no_bak = null;
  	
  			$sql ="select	a.finance_def_no,
											a.apply_type,
											a.apply_no,
											a.tra_charge,
											a.hot_charge,
											a.bus_charge,
											a.mea_charge_i,
											a.mea_charge_l,
											a.oth_charge,
											a.tic_charge,
											a.cost,
											d.wiped_type,
											d.cost meisei_cost
									from ".$this->tbl_prefix."finance_business a
									left join (select b.finance_def_no,
																		b.apply_type,
																		b.apply_no,
																		b.wiped_type,
																		sum(b.cost) cost
																from ".$this->tbl_prefix."finance_bus_meisei b
																right join ".$this->tbl_prefix."finance_business c on b.finance_def_no = c.finance_def_no
																																							and b.apply_type = c.apply_type
																																							and b.apply_no = c.apply_no
																where c.finance_def_no = '".$def_no."'
																	and c.pro_sts <> '98'
																	and ifnull(b.admin_charge,'') <> '1'
																group by b.finance_def_no,b.apply_type,b.apply_no,b.wiped_type) d on a.finance_def_no = d.finance_def_no
																																																	and a.apply_type = d.apply_type
																																																	and a.apply_no = d.apply_no
									where a.finance_def_no = '".$def_no."'
										and a.pro_sts <> '98'
										order by a.finance_def_no asc,a.apply_type asc,a.apply_no asc,d.wiped_type asc";
				
				$mod = $this->db_conn -> query($sql);
		
				while($r = $this->db_conn -> fetch_array($mod))
				{
						if(empty($apply_no_bak))
						{
								$apply_no_bak = $r['apply_no'];
								$upd_info     = array('tic_charge'   => 0,
																			'tra_charge'   => 0,
																			'hot_charge'   => 0,
																			'bus_charge'   => 0,
																			'mea_charge_i' => 0,
																			'mea_charge_l' => 0,
																			'oth_charge'   => 0,
																			'cost'         => 0,
																			);
						}
			
						if($apply_no_bak != $r['apply_no'] )
						{
								$upd_info['cost'] = $upd_info['tic_charge'] + 
																		$upd_info['tra_charge'] + 
																		$upd_info['hot_charge'] + 
																		$upd_info['bus_charge'] + 
																		$upd_info['mea_charge_i'] + 
																		$upd_info['mea_charge_l'] +
																		$upd_info['oth_charge'];

								$this->db_conn -> update($this->tbl_prefix."finance_business",$upd_info,"finance_def_no = '".$def_no."' and apply_type = '2' and apply_no = ".$apply_no_bak);
				
								$bus_total    = $bus_total + $upd_info['cost'];
				
								$upd_info     = array('tic_charge'   => 0,
																			'tra_charge'   => 0,
																			'hot_charge'   => 0,
																			'bus_charge'   => 0,
																			'mea_charge_i' => 0,
																			'mea_charge_l' => 0,
																			'oth_charge'   => 0,
																			'cost'         => 0,
																			);
								$apply_no_bak = $r['apply_no'];
						}
			
						switch($r['wiped_type'])
						{
								case '01':
										$upd_info['tic_charge'] = $r['meisei_cost'];
										break;
								case '02':
										$upd_info['tra_charge'] = $r['meisei_cost'];
										break;
								case '03':
										$upd_info['hot_charge'] = $r['meisei_cost'];
										break;
								case '04':
										$upd_info['bus_charge'] = $r['meisei_cost'];
										break;
								case '05':
										break;
								case '06':
										$upd_info['oth_charge'] = $r['oth_charge'];
										break;
						}
						
						$upd_info['mea_charge_i']   = $r['mea_charge_i'];
						$upd_info['mea_charge_l']   = $r['mea_charge_l'];
				}
		
				//
				if(!is_null($apply_no_bak))
				{		
						$upd_info['cost'] = $upd_info['tic_charge'] + 
																$upd_info['tra_charge'] + 
																$upd_info['hot_charge'] + 
																$upd_info['bus_charge'] + 
																$upd_info['mea_charge_i'] + 
																$upd_info['mea_charge_l'] +
																$upd_info['oth_charge'];

						$this->db_conn -> update($this->tbl_prefix."finance_business",$upd_info,"finance_def_no = '".$def_no."' and apply_type = '2' and apply_no = ".$apply_no_bak);
						
						$bus_total    = $bus_total + $upd_info['cost'];
				}
		
				return $bus_total;
 		}
}