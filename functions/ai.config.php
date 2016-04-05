<?php
return array(
    'levels'=>array(
        '1'=>'0',
        '2'=>'150',
        '3'=>'350',
        '4'=>'500',
        '5'=>'1000',
        '6'=>'2000',
        '7'=>'3500',
        '8'=>'5000',
        '9'=>'7000',
        '10'=>'10000'
    ),
    'learn_times'=>array(
        '1'=>0,
        '2'=>1,
        '3'=>2,
        '4'=>3,
        '5'=>5,
        '6'=>7,
        '7'=>10,
        '8'=>13,
        '9'=>16,
        '10'=>20
    ),
    'skills'=>array(
        '1'=>array(
            array(
                'skill_name'=>'天气通',
                'skill_trigger'=>'WEATHER_NOW',
                'skill_description'=>'输入『今天天气』，即可查询你所在的城市的实时天气了呢~'
            ),
            array(
                'skill_name'=>'天气先知',
                'skill_trigger'=>'WEATHER_FORECAST',
                'skill_description'=>'输入『天气预报』，即可得到一星期内你所在城市的天气预报了哦~'
            )
        ),
        '2'=>array(
            array(
                'skill_name'=>'幽默达人',
                'skill_trigger'=>'JOKE',
                'skill_description'=>'输入『讲个笑话/笑话』，即可让我给你讲一个文字笑话哦~'
            ),
            array(
                'skill_name'=>'新闻达人',
                'skill_trigger'=>'NEWS',
                'skill_description'=>'输入『最新新闻』，我就会告诉你今天最新的新闻哦~'
            )
        ),
        '3'=>array(
            array(
                'skill_name'=>'好图分享',
                'skill_trigger'=>'HILARIOUS_PICS',
                'skill_description'=>'输入『搞笑图片/搞笑一下』，我就会随机发给你一个网络搞笑图文呢~'
            ),
            array(
                'skill_name'=>'回望历史',
                'skill_trigger'=>'HISTORY_TODAY',
                'skill_description'=>'输入『历史今天』，我就会告诉你过去的今天发生了些什么事情噢~'
            ),
            array(
                'skill_name'=>'猜一猜',
                'skill_trigger'=>'GUESS',
                'skill_description'=>'输入『猜一猜』，我就会告诉你一个谜语噢~'
            )
        ),
        '4'=>array(
            array(
                'skill_name'=>'人工词典',
                'skill_trigger'=>'TRANSLATE',
                'skill_description'=>'输入『翻译+待翻译的中英文』即可得到小A的专属翻译咯哈哈~'
            ),
            array(
                'skill_name'=>'微信精选',
                'skill_trigger'=>'WECHAT',
                'skill_description'=>'输入『微信精选』即可查看微信精选文章啦~'
            )
        ),
        '5'=>array(
            array(
                'skill_name'=>'本月票房',
                'skill_trigger'=>'MONTH_MOVIE',
                'skill_description'=>'输入『本月票房』，小A就会把当月的国内票房反馈给你了哦~'
            ),
            array(
                'skill_name'=>'全球票房',
                'skill_trigger'=>'GLOBAL_MOVIE',
                'skill_description'=>'输入『全球票房』，小A就会把全世界的票房信息反馈给你了呢 ~'
            )
        ),
        '6'=>array(
            array(
                'skill_name'=>'视频分享',
                'skill_trigger'=>'VIDEO',
                'skill_description'=>'输入『每日视频』，小A就会把好玩的视频发给你娱乐一下呢 ~'
            )
        ),
        '7'=>array(
            array(
                'skill_name'=>'音频分享',
                'skill_trigger'=>'AUDIO',
                'skill_description'=>'输入『每日音频』，小A就会把好玩的音频发给你娱乐一下呢 ~'
            )
        ),
        '8'=>array(
            array(
                'skill_name'=>'深夜福利',
                'skill_trigger'=>'NIGHT_PICS',
                'skill_description'=>'输入『深夜福利』，小A就会发给你一些只适合你在夜晚静悄悄地只有你一个人的时候看的图片~ 嘿嘿'
            )
        ),
        '9'=>array(
            array(
                'skill_name'=>'养眼福利',
                'skill_trigger'=>'CHICK_PICS',
                'skill_description'=>'输入『美女图片』，小A就会发给你一些精选的美女图片哦 ~'
            )
        ),
        '10'=>array(
            array(
                'skill_name'=>'音乐百科',
                'skill_trigger'=>'MUSIC',
                'skill_description'=>'输入『搜乐+关键字』，小A就会把相关的音乐试听，图片与高质下载链接一齐发给你噢 ~'
            )
        )
    )
);