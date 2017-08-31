<?php
/**
 * PHP SDK For ResourceService
 *
 * 资源分类定义，此处要考虑和班班通资源兼容
 *
 * BBT ResourceType 中 Doc(9), PPT(10), Excel(11), Pdf(12) 等类型
 * 将通过另一个维度 extension 文件扩展名进行标示。
 *
 * @version         1.0.0.0
 * @author          IFLYTEK Education Division Architecture Team
 * @copyright       © 2013, iFlyTEK CO.Ltd. All rights reserved.
*/

/**
 * 资源分类取值
 * @package         Model
 */
final class resource_type
{
    /**
     * 未定义，对应于 BBT: Usage.Undefined(0), Other(999)
     */
    const undefined = '0';

    /**
     * 导学案
     */
    const guidancecase = '0100';
    const guidancecase_general = '0101';

    /**
     * 教案, 对应于 BBT: ResourceType.TeachingPlan(31)
     */
    const teachingplan = '0200';
    const teachingplan_general = '0201';

    //// MEDIA ////
    //
    /**
     * 媒体素材, 对应于 BBT: Usage.TeachMaterial(3)
     */
    const media = '0300';

    /**
     * 一般类型
     */
    const media_general = '0301';

    /**
     * 文本类素材，对应于BBT: ResourceType.Text(1)
     */
    const media_txt = '0302';

    /**
     * 图形/图像类素材，对应于BBT: ResourceType.Image(2)
     */
    const media_image = '0303';

    /**
     * 音频类素材，对应于BBT: ResourceType.Audio(4)
     */
    const media_audio = '0304';

    /**
     * 视频类素材，对应于BBT: ResourceType.Video(3)
     */
    const media_video = '0305';

    /**
     * 动画类素材，对应于BBT: ResourceType.Flash(5)
     */
    const media_animation = '0306';

    /**
     * 网页素材，对应于BBT: ResourceType.Webpage(16)
     */
    const media_webpage = '0307';

    /**
     * 压缩包，对应于BBT: ResourceType.zip(17)
     */
    const media_zip = '0308';
    //// END of MEDIA ////

    /**
     * 试题, 对应于 BBT: ResourceType.ExamQuestion(32)
     */
    const testquestion = '0400';
    const testquestion_general = '0401';

    //试卷
    const testpaper = '0500';
    const testpaper_general = '0501';

    //// COURSEWARE ////

    /**
     * 课件，对应于 BBT: Usage.Courseware(4)
     */
    const courseware = '0600';

    /**
     * 一般课件
     */
    const courseware_general = '0601';

    /**
     * PPT
     */
    const courseware_ppt = '0602';

    /**
     * 电子白板记录 .page 文件，对应于 BBT: ResourceType.Pages (41)
     */
    const courseware_page = '0603';
    
    const courseware_zip = '0606';

    //// END of COURSEWARE ////

    /**
     * 案例
     */
    const cases = '0700';
    const cases_general = '0701';

    //// DOCUMENT ////

    /**
     * 文档，对应于 BBT: ResourceType.Document(6)
     */
    const document = '0800';

    /**
     * 一般文献
     */
    const document_general = '0801';

    /**
     * 文稿，对应于 BBT: ResourceType.Manuscript (30)
     */
    const document_manuscript = '0802';

    /**
     * 论文，对应于 BBT: ResourceType.Thesis (33)
     */
    const document_thesis = '0803';

    //// END of DOCUMENT ////

    /**
     * 网络课程
     */
    const onlinecourse = '0900';
    const onlinecourse_general = '0901';

    /**
     * 常见问题解答
     */
    const faq = '1000';
    const faq_general = '1001';

    /**
     * 资源目录索引
     */
    const resourceindex = '1100';
    const resourceindex_general = '1101';
    const resourceindex_book = '1102';

    //// VOICEAPP ////

    /**
     * 语音交互资源，对应于 BBT: ResourceType.iFly (13)
     */
    const voiceapp = '1200';

    /**
     * 其他类型
     */
    const voiceapp_general = '1201';

    /**
     * 画廊卡片，对应于 BBT: ResourceType.Gallery(20)
     */
    const voiceapp_gallery = '1202';

    /**
     * 媒体展示
     */
    const voiceapp_multimedia = '1203';

    /**
     * 课堂测验，对应于 BBT: ResourceType.ClassTest (21)
     */
    const voiceapp_classtest = '1204';

    /**
     * 卡片包，对应于 BBT: Usage.CardPackage (1), ResourceType.Package(8)
     */
    const voiceapp_cardpackage = '1205';

    /**
     * 卡片，对应于 BBT: Usage.Card (2)
     */
    const voiceapp_card = '1206';

    /**
     * 电子书，对应于 BBT：Usage.EBook (5)，ResourceType.EBook (40)
     */
    const voiceapp_ebook = '1207';

    /**
     * 电子书有声图片卡片，对应于 BBT: ResourceType.EBookSlice (15)
     */
    const voiceapp_ebookslice = '1208';

    /**
     * 诗词对答卡片，对应于 BBT: ResourceType.Poem (7)
     */
    const voiceapp_poem = '1209';

    /**
     * 评测卡片
     */
    const voiceapp_eval = '1210';

    /**
     * 合成卡片
     */
    const voiceapp_tts = '1211';

    /**
     * 情景对话
     */
    const voiceapp_dialog = '1212';
    //// END of VOICEAPP ////
    
    const ebook_iflybook = '1403';
}

?>