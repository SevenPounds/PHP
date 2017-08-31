<?php
/**
 * 
 * @author "trwang"
 *
 */
class ELibraryModel extends Model{
	
	/**
	 * 获取老师版的电子图书
	 */
	function getLibraryForTeacher(){
		$librarys=array(
				array("author"=>"林乾","title"=>"正能量@曾国藩：一个做大事不做大官的典范","img"=>"dzts_js1.jpg","url"=>"http://www.apabi.com/tiyan/?pid=book.detail&metaid=m.20131009-YY-889-0136&cult=CN&wd="),
				array("author"=>"张秀奇","title"=>"走向辉煌：莫言记录","img"=>"dzts_js2.jpg","url"=>"http://www.apabi.com/tiyan/?pid=book.detail&metaid=m.20130930-XRT-889-0282&cult=CN&wd="),
				array("author"=>"赵学勤, 马国川","title"=>"中国教育问题求解","img"=>"dzts_js3.jpg","url"=>"http://www.apabi.com/tiyan/?pid=book.detail&metaid=m.20130123-YPT-889-0009&cult=CN&wd="),
				array("author"=>"余力，倩娜","title"=>"孙道临传：梦之岛的菩提树","img"=>"dzts_js4.jpg","url"=>"http://www.apabi.com/tiyan/?pid=book.detail&metaid=m.20140526-YKYD-888-0506&cult=CN&wd="),
				array("author"=>"张新欣","title"=>"IQ·教会孩子辨别是非","img"=>"dzts_js5.jpg","url"=>"http://www.apabi.com/tiyan/?pid=book.detail&metaid=m.20121030-YPT-889-0638&cult=CN&wd="),
				array("author"=>"翁肇桢","title"=>"书海扬帆：阅读指导课程建设的实践研究","img"=>"dzts_js6.jpg","url"=>"http://www.apabi.com/tiyan/?pid=book.detail&metaid=m.20140526-YKYD-888-0575&cult=CN&wd="),
				array("author"=>"王叶婷","title"=>"幸福园中的“和乐”校本课程","img"=>"dzts_js7.jpg","url"=>"http://www.apabi.com/tiyan/?pid=book.detail&metaid=m.20140526-YKYD-888-0583&cult=CN&wd="),
				array("author"=>"樊小蒲, 赵强, 苏婕","title"=>"科学名著与科学精神","img"=>"dzts_js8.jpg","url"=>"http://www.apabi.com/tiyan/?pid=book.detail&metaid=m.20140506-XRT-888-0424&cult=CN&wd="),
				array("author"=>"俞晓群","title"=>"自然数中的明珠","img"=>"dzts_js9.jpg","url"=>"http://www.apabi.com/tiyan/?pid=book.detail&metaid=m.20140526-YKYD-888-0576&cult=CN&wd="),
				array("author"=>"郭静云","title"=>"夏商周","img"=>"dzts_js10.jpg","url"=>"http://www.apabi.com/tiyan/?pid=book.detail&metaid=m.20140526-YKYD-888-0513&cult=CN&wd=")
		);
		return $librarys;
	}
	
	/**
	 * 获取学生版版的电子图书
	 */
	function getLibraryForStudent(){
		$librarys=array(
				array("author"=>"王星凡","title"=>"学生一定要知道的80位名人","img"=>"dzts_xs1.jpg","url"=>"http://www.apabi.com/tiyan/?pid=book.detail&metaid=m.20131030-XRT-889-0092&cult=CN&wd="),
				array("author"=>"沈从文","title"=>"边城","img"=>"dzts_xs2.jpg","url"=>"http://www.apabi.com/tiyan/?pid=book.detail&metaid=m.20100930-YPT-889-0003&cult=CN&wd=%E8%BE%B9%E5%9F%8E"),
				array("author"=>"唐巨南","title"=>"笔尖上的灵感：特级教师教你如何写作文","img"=>"dzts_xs3.jpg","url"=>"http://www.apabi.com/tiyan/?pid=book.detail&metaid=m.20140506-XRT-888-0250&cult=CN&wd="),
				array("author"=>"黑洲非人","title"=>"我和我的小伙伴们都笑了","img"=>"dzts_xs4.jpg","url"=>"http://www.apabi.com/tiyan/?pid=book.detail&metaid=m.20131113-XRT-889-0056&cult=CN&wd="),
				array("author"=>"陶雷","title"=>"让进取成为你的习惯","img"=>"dzts_xs5.jpg","url"=>"http://www.apabi.com/tiyan/?pid=book.detail&metaid=m.20130812-WXNX-889-0160&cult=CN&wd="),
				array("author"=>"刘薇","title"=>"早晚读英文.Ⅰ","img"=>"dzts_xs6.jpg","url"=>"http://www.apabi.com/tiyan/?pid=book.detail&metaid=m.20130326-JZ-889-0005&cult=CN&wd="),
				array("author"=>"陈大为","title"=>"我是小小礼仪家","img"=>"dzts_xs7.jpg","url"=>"http://www.apabi.com/tiyan/?pid=book.detail&metaid=m.20131030-XRT-889-0082&cult=CN&wd="),
				array("author"=>"伊记","title"=>"十万个为什么：那些你所不知的囧问题","img"=>"dzts_xs8.jpg","url"=>"http://www.apabi.com/tiyan/?pid=book.detail&metaid=m.20130925-WXNX-889-0133&cult=CN&wd="),
				array("author"=>"黄凤池","title"=>"唐诗画谱","img"=>"dzts_xs9.jpg","url"=>"http://www.apabi.com/tiyan/?pid=book.detail&metaid=m.20140526-YKYD-888-0529&cult=CN&wd="),
				array("author"=>"丁睿","title"=>"中华民族传统节日文化读本","img"=>"dzts_xs10.jpg","url"=>"http://www.apabi.com/tiyan/?pid=book.detail&metaid=m.20140506-XRT-888-0419&cult=CN&wd=")
		);
		return $librarys;
	}
	
	/**
	 * 获取教师版的工具书
	 */
	function getRefBookForTeacher(){
		$refBooks=array(
				array("author"=>"柳斌","title"=>"中国教师新百科 小学教育卷","img"=>"gjs_js1.png","url"=>"http://www.apabi.com/tiyan/?pid=refbook.book&bi=m.20091013-m300-w001-032&cult=CN&wd="),
				array("author"=>"梁忠义, 车文博","title"=>"实用教育辞典","img"=>"gjs_js2.png","url"=>"http://www.apabi.com/tiyan/?pid=refbook.book&bi=m.20090305-m300-w011-086&cult=CN"),
				array("author"=>"刘福光,马凤藻等","title"=>"简明文化知识辞典","img"=>"gjs_js3.jpg","url"=>"http://www.apabi.com/tiyan/?pid=refbook.book&bi=m.20100302-m300-w001-015&cult=CN")
		);
		return $refBooks;
	}
	
	/**
	 * 获取学生版的工具书
	 */
	function getRefBookForStudent(){
		$refBooks=array(
				array("author"=>"王质和","title"=>"中学生应读古诗词入境与赏析","img"=>"gjs_xs1.png","url"=>"http://www.apabi.com/tiyan/?pid=refbook.book&bi=m.20090106-m300-w011-028&cult=CN&wd="),
				array("author"=>"邵延淼","title"=>"古今中外人物传记指南录：补编","img"=>"gjs_xs2.png","url"=>"http://www.apabi.com/tiyan/?pid=refbook.book&bi=m.20100716-m300-w001-001&cult=CN&wd=&username=huali&ug=%E5%9B%BD%E5%86%85%E4%BD%93%E9%AA%8C%E6%9C%BA%E6%9E%84%E6%9C%89%E5%AF%86%E7%A0%81%E7%94%A8%E6%88%B7%E7%BB%84"),
				array("author"=>"刁绍华","title"=>"外国文学大词典","img"=>"gjs_xs3.png","url"=>"http://www.apabi.com/tiyan/?pid=refbook.book&bi=m.20090305-m300-w011-089&cult=CN")
		);
		return $refBooks;
	}
	
	/**
	 * 获取板块信息
	 */
	function getModInfo(){
		$mods=array(
				array("mod"=>1,"title"=>"电子图书","url"=>"http://www.apabi.com/tiyan/?pid=dlib.index&cult=CN"),
				array("mod"=>2,"title"=>"工具书库","url"=>"http://www.apabi.com/tiyan/?pid=refbook.index&cult=CN"),
				array("mod"=>3,"title"=>"数字报纸","url"=>"http://www.apabi.com/tiyan/?pid=newspaper.index&cult=CN"),
				array("mod"=>4,"title"=>"图片库","url"=>"http://www.apabi.com/tiyan/?pid=picture.index&cult=CN")
		);
		return $mods;
	}
	
	/**
	 * 获取专题信息
	 */
	function getSpecialTopic(){
		 $topics=array(
				array("title"=>"小说阅读","img"=>"zt_1.jpg","url"=>"http://www.apabi.com/tiyan/?pid=topics.standard&topicid=XiaoShuoYueDu&cult=CN"),
				array("title"=>"科普知识","img"=>"zt_2.jpg","url"=>"http://www.apabi.com/tiyan/?pid=topics.standard&topicid=KePuZhiShi&cult=CN"),
				array("title"=>"计算机","img"=>"zt_3.jpg","url"=>"http://www.apabi.com/tiyan/?pid=topics.standard&topicid=JiSuanJi&cult=CN"),
				array("title"=>"人文社科","img"=>"zt_4.jpg","url"=>"http://www.apabi.com/tiyan/?pid=topics.standard&topicid=RenWenSheKe&cult=CN")
		);
		return $topics;
	}
	
	/**
	 * 获取数字报纸
	 */
	function getNewspaper(){
		$news=array(
				array("title"=>"人民日报","img"=>"bz_1.jpg","url"=>"http://www.apabi.com/tiyan/?pid=newspaper.page&paperid=n.D110000renmrb&wd=&cult=CN"),
				array("title"=>"环球时报","img"=>"bz_2.jpg","url"=>"http://www.apabi.com/tiyan/?pid=newspaper.page&paperid=n.D110000huanqiusb&wd=&cult=CN"),
				array("title"=>"东方早报","img"=>"bz_3.jpg","url"=>"http://www.apabi.com/tiyan/?pid=newspaper.page&paperid=n.D310000dfzb&wd=&cult=CN"),
				array("title"=>"北京青年报","img"=>"bz_4.jpg","url"=>"http://www.apabi.com/tiyan/?pid=newspaper.page&articleid=nw.D110000bjqingnianb_20140904_7-C04&cult=CN"),
				array("title"=>"都市快报","img"=>"bz_5.jpg","url"=>"http://www.apabi.com/tiyan/?pid=newspaper.page&paperid=n.D330100dskb&wd=&cult=CN"),
				array("title"=>"新民晚报","img"=>"bz_6.jpg","url"=>"http://www.apabi.com/tiyan/?pid=newspaper.page&paperid=n.D310000xmwb&wd=&cult=CN"),
				array("title"=>"21世纪经济报道","img"=>"bz_7.jpg","url"=>"http://www.apabi.com/tiyan/?pid=newspaper.page&paperid=n.D44010021sjjjbd&wd=&cult=CN"),
				array("title"=>"京华时报","img"=>"bz_8.jpg","url"=>"http://www.apabi.com/tiyan/?pid=newspaper.page&paperid=n.D110000jhsb&wd=&cult=CN")
		);
		return $news;
	}
	
	/**
	 * 获取图库
	 */
	function getGallery(){
		$pics=array(
				array("title"=>"中国乡村风景","img"=>"tpk_1.jpeg","url"=>"http://www.apabi.com/tiyan/?pid=picture.picinfo&sys=picture&metaid=sa.00000000000070000784&cult=CN"),
				array("title"=>"冲峦夕照","img"=>"tpk_2.jpeg","url"=>"http://www.apabi.com/tiyan/?pid=picture.picinfo&sys=picture&metaid=sa.00000000000020003842-ZXX&cult=CN"),
				array("title"=>"金玉满堂","img"=>"tpk_3.jpeg","url"=>"http://www.apabi.com/tiyan/?pid=picture.picinfo&sys=picture&metaid=sf.00000000000060000609&cult=CN"),
				array("title"=>"牡丹缠枝花纹","img"=>"tpk_4.jpeg","url"=>"http://www.apabi.com/tiyan/?pid=picture.picinfo&sys=picture&metaid=sf.00000000000060000670&cult=CN"),
				array("title"=>"王维诗意图之九","img"=>"tpk_5.jpeg","url"=>"http://www.apabi.com/tiyan/?pid=picture.picinfo&sys=picture&metaid=sa.00000000000020003427&cult=CN"),
				array("title"=>"春花","img"=>"tpk_6.jpeg","url"=>"http://www.apabi.com/tiyan/?pid=picture.picinfo&sys=picture&metaid=sa.00000000000070000177&cult=CN"),
				array("title"=>"陶行知书录荀子语","img"=>"tpk_7.jpeg","url"=>"http://www.apabi.com/tiyan/?pid=picture.picinfo&sys=picture&metaid=sc.00000000000010061284&cult=CN"),
				array("title"=>"参议员","img"=>"tpk_8.jpeg","url"=>"http://www.apabi.com/tiyan/?pid=picture.picinfo&metaid=so.20110120010000301631&db=picture&cult=CN&wd=&ct=CAT_SJJDSYYSBWG$$")
		);
		return $pics;
	}
}