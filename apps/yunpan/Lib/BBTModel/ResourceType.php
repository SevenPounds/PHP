<?php
class ResourceType{
	public static $Undefined = 0;
	public static $Package = 8;
	public static $Other = 999;
	
	public static $Text = 1;
	public static $Image = 2;
	public static $Video = 3;
	public static $Audio = 4;
	public static $Flash = 5;
	public static $WebPage = 16;
	public static $Zip = 17;
	
	public static $Document=6;
	public static $Poem = 7;
	public static $Doc = 9;
	public static $PPT = 10;
	public static $Excel = 11;
	public static $Pdf = 12;
	public static $iFLy = 13;
	public static $EBookSlice = 15;
	public static $Gallery=20;
	public static $ClassTest=21;
	
	public static $Manuscript = 30;//文稿
	public static $TeachingPlan = 31;//教案
	public static $ExamQuestion = 32;//试题
	public static $Thesis = 33;//论文
	public static $Program = 23;//程序
	

	public static $EBook = 40;
	
	/// <summary>
	/// 授课记录
	/// </summary>
	public static $Pages = 41;
	
	
	public static $EBookRes = 42;  //iflybook
}
?>