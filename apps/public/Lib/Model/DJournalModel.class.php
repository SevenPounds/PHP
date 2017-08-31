<?php
/**
 * 
 * @author "trwang"
 *
 */
class DJournalModel extends Model{
	
	/**
	 * 获取老师版的数字期刊
	 */
	function getJournalForTeacher(){
		$journals=array(
				array("mod"=>1,"title"=>"地理教育","img"=>"jxll.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=4AEB3FCB-E490-4615-9B2C-CE3E57E80D8F&year=2015&periodnum=4"),
				array("mod"=>1,"title"=>"课程教育研究·新教师教学","img"=>"kcjyyj.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=A2FCC828-52DA-42DC-A9FE-F73FF2C7637F&year=2014&periodnum=26"),
				array("mod"=>1,"title"=>"考试周刊","img"=>"zgjsyjyjy.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=3F542E0C-9B57-4864-94A3-F8173CC844B9&year=2015&periodnum=20"),
				array("mod"=>1,"title"=>"班主任之友·小学","img"=>"jyx.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=B33E7CFE-7B5B-4441-9CD6-B64547145BAE&year=2015&periodnum=3"),
				array("mod"=>1,"title"=>"语文世界(教师版)","img"=>"jyyzy.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=EB5BF42B-B62E-4439-8320-D3492C426AEE&year=2015&periodnum=3"),
				array("mod"=>2,"title"=>"中学课程辅导·教学研究","img"=>"zxkcfdjxyj.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=130A8B3C-8DB9-405C-B3A4-10057BAB5E80&year=2014&periodnum=27"),
				array("mod"=>2,"title"=>"中学生数理化·教与学","img"=>"zxsslh.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=82B0ADDE-8ABA-49B1-9BC1-99248879AD10&year=2014&periodnum=8"),
				array("mod"=>2,"title"=>"教学月刊·中学版（教学管理）","img"=>"zxsyy.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=37095BE5-2FA6-4023-B6A4-98477C2E02BB&year=2015&periodnum=3"),
				array("mod"=>2,"title"=>"理科考试研究·初中","img"=>"lkksyj.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=B1734184-EEFE-4FA9-876B-CE7854C32D0D&year=2014&periodnum=7"),
				array("mod"=>2,"title"=>"中学生英语·外语教学与研究","img"=>"zxkcfdjsjy.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=6E5BC781-455A-4C5C-AD5C-3FAD9735A35A&year=2015&periodnum=3"),
				array("mod"=>3,"title"=>"中国教师","img"=>"zgjs.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=DCF54CAA-67B0-4715-8165-8170C8169725&year=2014&periodnum=16"),
				array("mod"=>3,"title"=>"中小学心理健康教育","img"=>"zxxxljkjy.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=4258405C-5CFB-4CAD-8839-4B62D7E70F6E&year=2014&periodnum=16"),
				array("mod"=>3,"title"=>"教书育人：教师新概念","img"=>"jsyr.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=62B6C16B-E0D8-4923-9E19-5D3847C33ED3&year=2014&periodnum=7"),	
				array("mod"=>3,"title"=>"中国德育","img"=>"sdjy.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=560448B2-77CB-4A1B-8CA5-62BDF110B02B&year=2015&periodnum=5"),
				array("mod"=>3,"title"=>"中小学心理健康教育","img"=>"jyys.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=4258405C-5CFB-4CAD-8839-4B62D7E70F6E&year=2015&periodnum=4"),
				array("mod"=>4,"title"=>"文化交流","img"=>"sjwh.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=C6A5A516-5054-4118-B409-8D651BA5D793&year=2015&periodnum=4"),
				array("mod"=>4,"title"=>"道德与文明","img"=>"jrzg.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=640C2F52-DB17-4407-8F13-2E68F1523540&year=2014&periodnum=3"),
				array("mod"=>4,"title"=>"做人与处世","img"=>"rwzk.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=DCFEF44B-70ED-4D84-89B4-A436D8CCE782&year=2015&periodnum=4"),
				array("mod"=>4,"title"=>"大众文化","img"=>"dfwhzk.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=17E5884E-7269-4D9A-9015-0B8C933C37AC&year=2014&periodnum=4"),
				array("mod"=>4,"title"=>"农家书屋","img"=>"whcy.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=19E9F2AF-09FE-49FA-8A5A-A6D0FD7FE7B3&year=2015&periodnum=3")
		);
		return $journals;
	}
	
	/**
	 * 获取学生版版的数字期刊
	 */
	function getJournalForStudent(){
		$journals=array(
				array("mod"=>1,"title"=>"百科知识","img"=>"bkzs.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=270F74BE-DFD9-483B-9634-D1CDA82918D0&year=2014&periodnum=18"),
				array("mod"=>1,"title"=>"大科技·科学之谜","img"=>"dkjkxzm.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=F4A95B5A-F529-4E0A-923B-9B1EC8DA20E0&year=2014&periodnum=8"),
				array("mod"=>1,"title"=>"奥秘","img"=>"zgkjbl.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=EBD28B3C-9972-4048-B289-2AE4572FA2E1&year=2015&periodnum=3"),
				array("mod"=>1,"title"=>"电脑爱好者","img"=>"dnahz.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=7045D2F9-A27D-4601-9DF3-D63C143048AD&year=2014&periodnum=16"),
				array("mod"=>1,"title"=>"百科探秘·海底世界","img"=>"kxsh.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=B77C7BC2-3762-469B-AFA5-8EE7A0E39E8B&year=2013&periodnum=11"),
				array("mod"=>2,"title"=>"意林","img"=>"zwsz.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=7E788766-77A0-4093-B1F1-E3F55DD55137&year=2015&periodnum=6"),
				array("mod"=>2,"title"=>"海外文摘","img"=>"gw.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=10EE35E6-FF3F-46A8-A141-78B01D0123FB&year=2015&periodnum=4"),
				array("mod"=>2,"title"=>"读者·校园版","img"=>"dzxyb.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=9E236705-8F62-4007-9DC6-0CE02AE32A4C&year=2014&periodnum=17"),
				array("mod"=>2,"title"=>"读书文摘","img"=>"dswz.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=3C6F6841-C0FD-4E7C-9F2E-D83501D0774A&year=2014&periodnum=9"),
				array("mod"=>2,"title"=>"散文百家","img"=>"swbj.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=9C467659-127A-4129-8139-CD99D36D8011&year=2014&periodnum=8"),
				array("mod"=>3,"title"=>"阅读与作文","img"=>"ydyzw.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=A3E447DA-1160-423A-901B-DE26D436CBC4&year=2014&periodnum=9"),
				array("mod"=>3,"title"=>"初中生世界·七年级","img"=>"zxsbl.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=02DD86A4-DCB2-4A33-9A4E-ACE6462C9924&year=2015&periodnum=4"),
				array("mod"=>3,"title"=>"少年文艺","img"=>"snwy.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=191495DD-6020-4FEB-815D-9581C84E2894&year=2015&periodnum=3"),
				array("mod"=>3,"title"=>"少年文摘","img"=>"qsnky.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=62FFE72B-D96F-46B5-89DA-FC1D9C161A3B&year=2015&periodnum=3"),
				array("mod"=>3,"title"=>"全国优秀作文选(美文精粹)","img"=>"yxzw.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=EE621693-EBAA-46B9-90F5-CA19D740F116&year=2014&periodnum=8"),
				array("mod"=>4,"title"=>"少年交际与口才","img"=>"snjjykc.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=51DF31E0-11A2-405E-8B2B-C65AA84C17B3&year=2014&periodnum=6"),
				array("mod"=>4,"title"=>"青年时代·中学生读本","img"=>"qnsd.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=0A11D6D2-D9E8-4B7E-9E1B-CAC196782D89&year=2014&periodnum=8"),
				array("mod"=>4,"title"=>"青年博览","img"=>"qnbl.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=5A1113AF-96CA-4668-9245-DAC0390F9843&year=2014&periodnum=15"),
				array("mod"=>4,"title"=>"青年时代","img"=>"dxs.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=2CC8D287-7D54-4115-8338-8ACB56C5EB9E&year=2015&periodnum=3"),
				array("mod"=>4,"title"=>"新青年","img"=>"xtd.jpg","url"=>"http://changyan.vip.qikan.com/Text/TextMag.aspx?issn=F4874CD0-238C-4E01-9295-E5A5DC1E8C6B&year=2015&periodnum=1")
		);
		return $journals;
	}
	
	/**
	 * 获取教师版的板块信息
	 */
	function getModForTeacher(){
		$mods=array(
				array("mod"=>1,"title"=>"教育研究","url"=>"http://changyan.vip.qikan.com/Text/TextList.aspx?cid=F5C2AFA8-19D0-4ECF-B036-83F287D5AD52&cname=%bd%cc%d3%fd%d1%d0%be%bf&pid=DD420E05-37EC-4D76-9393-9E1435FAB3BD&pname=%bd%cc%d3%fd%bd%cc%d1%a7"),
				array("mod"=>2,"title"=>"教学参考","url"=>"http://changyan.vip.qikan.com/Text/TextList.aspx?cid=6D7F609D-B1D6-42FF-8953-4189FB0D1E80&cname=%bd%cc%d1%a7%b2%ce%bf%bc&pid=05993F46-E3AD-4156-83F4-542DAC959D8C&pname=%d6%d0%d1%a7%bd%cc%d3%fd"),
				array("mod"=>3,"title"=>"教书育人","url"=>"http://changyan.vip.qikan.com/Text/TextList.aspx?cid=7DEB1B4C-6F00-4F35-9471-42BDE0ACD6EA&cname=%bd%cc%ca%e9%d3%fd%c8%cb&pid=DD420E05-37EC-4D76-9393-9E1435FAB3BD&pname=%bd%cc%d3%fd%bd%cc%d1%a7"),
				array("mod"=>4,"title"=>"综合文化","url"=>"http://changyan.vip.qikan.com/Text/TextList.aspx?cid=9F63AD5B-FA22-495E-AE72-7FC6475052CC&cname=%d7%db%ba%cf%ce%c4%bb%af&pid=0E5A3C17-7ED8-4420-8336-E5BB5B5AC7FF&pname=%ce%c4%bb%af%d2%d5%ca%f5")
		);
		return $mods;
	}
	
	/**
	 * 获取学生版的板块信息
	 */
	function getModForStudent(){
		$mods=array(
				array("mod"=>1,"title"=>"科普知识","url"=>"http://changyan.vip.qikan.com/Text/TextList.aspx?cid=C0F64DAD-C0FC-428C-BB40-43C06C851197&cname=%bf%c6%c6%d5%d6%aa%ca%b6&pid=35AAFDBC-CD80-4F0D-A2E4-4F5F7F63CDB6&pname=%bf%c6%bc%bc%bf%c6%c6%d5"),
				array("mod"=>2,"title"=>"文摘文萃","url"=>"http://changyan.vip.qikan.com/Text/TextList.aspx?cid=34524753-B071-4F01-A0B2-640408975B18&cname=%ce%c4%d5%aa%ce%c4%dd%cd&pid=C0B327A5-7C85-4C0F-8C8F-3BE4099C47FE&pname=%ce%c4%d1%a7%ce%c4%dd%cd"),
				array("mod"=>3,"title"=>"学习指导","url"=>"http://changyan.vip.qikan.com/Text/TextList.aspx?cid=95208265-F05D-4C65-A195-FF92706981B3&cname=%d1%a7%cf%b0%d6%b8%b5%bc&pid=05993F46-E3AD-4156-83F4-542DAC959D8C&pname=%d6%d0%d1%a7%bd%cc%d3%fd"),
				array("mod"=>4,"title"=>"青年视野","url"=>"http://changyan.vip.qikan.com/Text/TextList.aspx?cid=293B8FE8-2767-4483-BBAE-428DB39C467E&cname=%c7%e0%c4%ea%ca%d3%d2%b0&pid=0E5A3C17-7ED8-4420-8336-E5BB5B5AC7FF&pname=%ce%c4%bb%af%d2%d5%ca%f5")

		);
		return $mods;
	}
}