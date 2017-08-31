<?php
class PraiseModel extends Model{
	protected $tableName = 'praise';
	protected $fields = array(
			0=>'praise_id',
			1=>'praise_name',
			2=>'icon',
			3=>'founderuid',
			4=>'founername',
			5=>'membernum',
			6=>'ctime',
			7=>'updatetime',
			8=>'is_del',
			9=>'logo',
			'_autoinc' => false,
			'_pk' => 'praise_id'
			);
	
}