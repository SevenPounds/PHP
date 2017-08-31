<?php
class TodayModel extends Model{
	protected $tableName = 'today';
	
	public function getTodayContent($date){
		return $this->where("date=$date")->select();
	}
	
}