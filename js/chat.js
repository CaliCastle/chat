var KEY_ENTER = 13;

Date.prototype.Format = function (fmt) {
    var o = {
        "M+": this.getMonth() + 1, //月份   
        "d+": this.getDate(), //日   
        "h+": this.getHours(), //小时   
        "m+": this.getMinutes(), //分   
        "s+": this.getSeconds(), //秒   
        "q+": Math.floor((this.getMonth() + 3) / 3), //季度   
        "S": this.getMilliseconds() //毫秒   
    };
    if (/(y+)/.test(fmt))
        fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o)
        if (new RegExp("(" + k + ")").test(fmt))
            fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
    return fmt;
}

$(document).ready(function () {
    var $header = $('.codrops-demos'),
        $input = $(".chat-input"),
        $sendButton = $(".chat-send"),
        $messagesContainer = $(".chat-messages"),
        $messagesList = $(".chat-messages-list"),
        $effectContainer = $(".chat-effect-container"),
        $infoContainer = $(".chat-info-container"),
        $loginForm = $("form#actions"),
        $pullButton = $('#pull-button'),
        messages = 0,
        bleeding = 100,
        isFriendTyping = false,
        incomingMessages = 0,
        lastMessage = "",
        xiaoAAvatar = "images/a-avatar.png",
        hasMore = false,
        display = 15,
        current = 1,
        messageRepeat = 0,
        keepDelay = true;
    //        pulled;

    var lipsum = '<img src="https://dn-abletive.qbox.me/chat/pic/ifiweredjwillyouloveme.gif" />|我听不懂你在讲什么不过好像很厉害的样子呢<img src="https://dn-abletive.qbox.me/chat/pic/高兴.png" />|说人话<img src="https://dn-abletive.qbox.me/chat/pic/出拳.gif" />|<img src="https://dn-abletive.qbox.me/chat/pic/mikucry.jpg" />什么意思|什么鬼<img src="https://dn-abletive.qbox.me/chat/pic/PBQ0W.gif" />|<img src="https://dn-abletive.qbox.me/chat/pic/XEAXKQ.jpg" />...|<img src="https://dn-abletive.qbox.me/chat/pic/XK95LC.jpg" />呀|<img src="https://dn-abletive.qbox.me/chat/pic/diulaunchpad.jpg" />我丢|不是很明白，那我就卖个萌好了~<(▰˘◡˘▰)>';
    var repeats = '呃。。请问一下，你是复读机么？(」ﾟヘﾟ)」|。。。。什么鬼|为何老是发一样的东西(゜-゜)|你受到什么刺激了么ヾ(´･ ･｀｡)ノ"|能不重复么..?|你说什么就是什么咯~';

    // Pull chat logs from database
    if (USER_ID) {
        addMessage('正在加载中...&nbsp;<i class="fa fa-spin fa-spinner"></i>', false, false);
        jQuery.ajax({
            url: "functions/get_avatar.php?user_id=" + USER_ID,
            dataType: "json",
            type: "GET",
            success: function (results) {
                if (results['status'] == "ok") {
                    USER_AVATAR = results['avatar'];
                    jQuery.ajax({
                        url: "functions/pull_chat_logs.php?user_id=" + USER_ID,
                        dataType: "json",
                        success: function (results) {
                            if (results['status'] == 'ok') {
                                $messagesList.children('li').last().remove();
                                var $userDetail = $('<div/>').addClass('user-info').appendTo($header);
                                var $userDetailAvatar = $('<img/>').addClass('user-info-avatar').attr('src', USER_AVATAR).appendTo($userDetail);
                                var $userLogout = $('<a id="logout-button" href="javascript:void(0)" onclick="logout()">点击注销</a>').appendTo($userDetail);
                                for (var $i in results['data']) {
                                    addMessage(results['data'][$i]['chat_message'], results['data'][$i]['from'] == "1" ? true : false, false, false, results['data'][$i]['chat_time']);
                                    if ($i == display - 1) {
                                        hasMore = true;
                                    }
                                }
                                if (hasMore) {
                                    $pullButton.css('display', 'block');
                                } else
                                    $pullButton.css('display', 'none');
                            } else {
                                $messagesList.children('li').last().remove();
                                addMessage("嗨~主淫你好~初次见面请多指教，我是小 A，有什么问题或者想找我聊天的话我会一直在这的哦 <br />(＾ｖ＾).", false, false);
                                $pullButton.remove();
                            }
                        }
                    });
                }
            }
        });
    }

    function gooOn() {
        setFilter('url(#goo)');
    }

    function gooOff() {
        setFilter('none');
    }

    function setFilter(value) {
        $effectContainer.css({
            webkitFilter: value,
            mozFilter: value,
            filter: value,
        });
    }

    function addMessage(message, self, insert, prepend, time) {
        if (prepend) {
            var $messageContainer = $("<li/>")
                .addClass('chat-message ' + (self ? 'chat-message-self' : 'chat-message-friend'))
                .prependTo($messagesList);
            var $messageAvatar = $("<div/>")
                .addClass('chat-message-avatar')
                .appendTo($messageContainer);
            var $avatarSource = $("<img/>")
                .appendTo($messageAvatar);
            var $messageBubble = $("<div/>")
                .addClass('chat-message-bubble ' + (self ? 'chat-bubble-self' : ''))
                .appendTo($messageContainer);
            var $messageTime = $("<div/>")
                .addClass('chat-message-datetime')
                .appendTo($messageContainer);
            var $messageArrow = $("<div/>")
                .addClass('chat-message-arrow')
                .appendTo($messageContainer);
        } else {
            var $messageContainer = $("<li/>")
                .addClass('chat-message ' + (self ? 'chat-message-self' : 'chat-message-friend'))
                .appendTo($messagesList);
            var $messageAvatar = $("<div/>")
                .addClass('chat-message-avatar')
                .appendTo($messageContainer);
            var $avatarSource = $("<img/>")
                .appendTo($messageAvatar);
            var $messageBubble = $("<div/>")
                .addClass('chat-message-bubble ' + (self ? 'chat-bubble-self' : ''))
                .appendTo($messageContainer);
            var $messageTime = $("<div/>")
                .addClass('chat-message-datetime')
                .appendTo($messageContainer);
            var $messageArrow = $("<div/>")
                .addClass('chat-message-arrow')
                .appendTo($messageContainer);
        }
        $avatarSource.attr('src', self ? (USER_AVATAR == "" ? "images/default-avatar.png" : USER_AVATAR) : xiaoAAvatar);
        var timeString = "";
        if (time) {
            timeString = time.substr(0, time.indexOf(' '));
            var today = new Date().Format('yyyy-MM-dd');
            var dateOffset = dateDiff(timeString, today);
            if (dateOffset) {
                switch (dateOffset) {
                case 1:
                    timeString = "昨天";
                    timeString += time.substr(time.indexOf(' '), time.substr(time.indexOf(' ')).length - 3);
                    break;
                case 2:
                    timeString = "前天";
                    timeString += time.substr(time.indexOf(' '), time.substr(time.indexOf(' ')).length - 3);
                    break;
                case 3:
                case 4:
                case 5:
                case 6:
                case 7:
                    timeString = dateOffset + "天前";
                    timeString += time.substr(time.indexOf(' '), time.substr(time.indexOf(' ')).length - 3);
                default:
                    timeString = dateOffset >= 365 ? time : time.substr(time.indexOf('-') + 1, time.length - 5);
                    break;
                }
            } else {
                timeString = "今天";
                timeString += time.substr(time.indexOf(' '), time.substr(time.indexOf(' ')).length - 3);
            }
        }
        $messageTime.text(timeString);

        if (insert) {
            $messageBubble.html('<i class="fa fa-spin fa-spinner"></i>&nbsp;' + message);

            jQuery.ajax({
                url: "functions/send_message.php",
                data: {
                    'user_id': USER_ID,
                    'chat_message': message,
                    'from': self ? 1 : 0
                },
                dataType: "text",
                type: "POST",
                success: function (result) {
                    if (result == '1') {
                        $messageBubble.html(message);
                    } else if (result == '2') {
                        $messageBubble.html(message);
                        setTimeout(function () {
                            receiveMessage("哈哈 ヽ(^0^)ﾉ 托你的福我又升级了呢~ 新技能√get, 感谢你的陪伴，问我：『查看等级/我的小A/你几级了』都可以查看详情噢~<br />输入『查看技能』就可查看我已经掌握了的技能了哦");
                        }, 1000);
                    } else {
                        $messageBubble.html('<i class="fa fa-times"></i> 发送失败');
                    }
                }
            });
        } else {
            $messageBubble.html(message);
        }

        if (!prepend) {
            var oldScroll = $messagesContainer.scrollTop();
            $messagesContainer.scrollTop(9999999);
            var newScroll = $messagesContainer.scrollTop();
            var scrollDiff = newScroll - oldScroll;
        }
        TweenMax.fromTo(
            $messagesList, 0.4, {
                y: scrollDiff
            }, {
                y: 0,
                ease: Quint.easeOut
            }
        );

        return {
            $container: $messageContainer,
            $bubble: $messageBubble
        };
    }

    function pullOldMessages() {
        hasMore = false;
        $pullButton.html('<i class="fa fa-spin fa-spinner"></i> 正在读取中...');
        jQuery.ajax({
            url: "functions/pull_chat_logs.php?user_id=" + USER_ID + "&offset=" + current,
            dataType: "json",
            success: function (results) {
                $pullButton.html('点击加载更多');
                hasMore = false;
                if (results['status'] == 'ok') {
                    current++;
                    for (var $i in results['data']) {
                        addMessage(results['data'][$i]['chat_message'], results['data'][$i]['from'] == "1" ? true : false, false, true, results['data'][$i]['chat_time']);
                        if ($i == display - 1) {
                            hasMore = true;
                        }
                    }
                    if (hasMore) {
                        $pullButton.css('display', 'block');
                    } else {
                        $pullButton.css('display', 'none');
                    }
                } else {
                    $messagesList.children('li').last().remove();
                    addMessage("加载失败，请重试&nbsp;<i class='fa fa-times'></i>", false, false);
                    $pullButton.remove();
                }
            }
        });
    }

    function timeToString(date) {
        var today = new Date();
        year = today.getFullYear();
        month = today.getMonth() + 1;
        day = today.getDate();
        hour = today.getHours() >= 10 ? today.getHours() : "0" + today.getHours();
        minute = today.getMinutes() >= 10 ? today.getMinutes() : "0" + today.getMinutes();
        second = today.getSeconds();

        var string = "";
        var yearOffset = year - date.getFullYear();
        var monthOffset = month - date.getMonth() + 1;
        var dayOffset = day - date.getDate();
        if (date.getFullYear() != year) {
            string += yearOffset > 1 ? yearOffset + "年前" : "去年";
            string += month + "月" + day + "日";
        } else {
            string += month + "月" + day + "号 " + hour + ":" + minute;
        }
        return string;
    }

    function sendMessage() {
        var message = $input.text().trim();

        if (message == "") return;
        if (message.indexOf('搜乐') == 0 || message.indexOf('天气预报') == 0 || message.indexOf('最新新闻') == 0 || message.indexOf('微信精选') == 0 || message.indexOf('历史今天') == 0 || message.indexOf('票房') > 0) {
            keepDelay = false;
        }

        if (lastMessage == message) {
            messageRepeat++;
        } else {
            messageRepeat = 0;
        }

        lastMessage = message;

        var insert = USER_ID != 0 ? true : false;
        var messageElements = addMessage(message, true, insert),
            $messageContainer = messageElements.$container,
            $messageBubble = messageElements.$bubble;

        var oldInputHeight = $(".chat-input-bar").height();
        $input.text('');
        updateChatHeight();
        var newInputHeight = $(".chat-input-bar").height();
        var inputHeightDiff = newInputHeight - oldInputHeight

        var $messageEffect = $("<div/>")
            .addClass('chat-message-effect')
            .append($messageBubble.clone())
            .appendTo($effectContainer)
            .css({
                left: $input.position().left - 12,
                top: $input.position().top + bleeding + inputHeightDiff
            });


        var messagePos = $messageBubble.offset();
        var effectPos = $messageEffect.offset();
        var pos = {
            x: messagePos.left - effectPos.left - 16,
            y: messagePos.top - effectPos.top
        }

        var $sendIcon = $sendButton.children("i");
        TweenMax.to(
            $sendIcon, 0.15, {
                x: 30,
                y: -30,
                force3D: true,
                ease: Quad.easeOut,
                onComplete: function () {
                    TweenMax.fromTo(
                        $sendIcon, 0.15, {
                            x: -30,
                            y: 30
                        }, {
                            x: 0,
                            y: 0,
                            force3D: true,
                            ease: Quad.easeOut
                        }
                    );
                }
            }
        );

        gooOn();


        TweenMax.from(
            $messageBubble, 0.8, {
                y: -pos.y,
                ease: Sine.easeInOut,
                force3D: true
            }
        );

        var startingScroll = $messagesContainer.scrollTop();
        var curScrollDiff = 0;
        var effectYTransition;
        var setEffectYTransition = function (dest, dur, ease) {
            return TweenMax.to(
                $messageEffect, dur, {
                    y: dest,
                    ease: ease,
                    force3D: true,
                    onUpdate: function () {
                        var curScroll = $messagesContainer.scrollTop();
                        var scrollDiff = curScroll - startingScroll;
                        if (scrollDiff > 0) {
                            curScrollDiff += scrollDiff;
                            startingScroll = curScroll;

                            var time = effectYTransition.time();
                            effectYTransition.kill();
                            effectYTransition = setEffectYTransition(pos.y - curScrollDiff, 0.8 - time, Sine.easeOut);
                        }
                    }
                }
            );
        }

        effectYTransition = setEffectYTransition(pos.y, 0.8, Sine.easeInOut);

        // effectYTransition.updateTo({y:800});

        TweenMax.from(
            $messageBubble, 0.6, {
                delay: 0.2,
                x: -pos.x,
                ease: Quad.easeInOut,
                force3D: true
            }
        );
        TweenMax.to(
            $messageEffect, 0.6, {
                delay: 0.2,
                x: pos.x,
                ease: Quad.easeInOut,
                force3D: true
            }
        );

        TweenMax.from(
            $messageBubble, 0.2, {
                delay: 0.65,
                opacity: 0,
                ease: Quad.easeInOut,
                onComplete: function () {
                    TweenMax.killTweensOf($messageEffect);
                    $messageEffect.remove();
                    if (!isFriendTyping)
                        gooOff();
                }
            }
        );

        messages++;

        //		if(Math.random()<0.65 || lastMessage.indexOf("?")>-1 || messages==1) getReply();
        getReply();
    }

    function getReply() {
        if (incomingMessages > 2) return;
        incomingMessages++;

        var message = "";

        if (messageRepeat >= 2) {
            message = getRepeatReply();

            var typeStartDelay = 100 + (lastMessage.length * 20) + (Math.random() * 500);
            setTimeout(friendIsTyping, typeStartDelay);

            var typeDelay = 300 + (message.length * 50) + (Math.random() * 500);

            setTimeout(function () {
                receiveMessage(message);
            }, typeDelay + typeStartDelay);

            setTimeout(function () {
                incomingMessages--;
                if (Math.random() < 0.05) {
                    getReply();
                }
                if (incomingMessages <= 0) {
                    friendStoppedTyping();
                }
            }, typeDelay + typeStartDelay);
        } else {
            var userid = USER_ID;

            jQuery.ajax({
                url: "functions/get_reply.php",
                type: "POST",
                data: {
                    'message': lastMessage,
                    'user_id': userid
                },
                dataType: "text",
                success: function (result) {
                    if (result != "") {
                        if (result.indexOf('图灵') >= 0 || result.indexOf('`') == 0) {
                            message = defaultReply();
                        } else {
                            switch (result) {
                            case "LEARNED":
                                message = "小A学话成功啦~ 赶快试试？(●'◡'●)ﾉ♥";
                                break;
                            case "LEARN_TIMES_LIMIT":
                                message = "小A学话失败 (>_<｡), 次数已达上限";
                                break;
                            default:
                                message = result;
                                break;
                            }
                        }
                    } else {
                        message = defaultReply();
                    }
                    var typeStartDelay = 100 + (lastMessage.length * 5) + (Math.random() * 500);
                    setTimeout(friendIsTyping, typeStartDelay);
                    if (keepDelay) {
                        var typeDelay = 300 + (message.length * 5) + (Math.random() * 500);
                        setTimeout(function () {
                            receiveMessage(message);
                        }, typeDelay + typeStartDelay);
                        setTimeout(function () {
                            incomingMessages--;
                            if (Math.random() < 0.05) {
                                getReply();
                            }
                            if (incomingMessages <= 0) {
                                friendStoppedTyping();
                            }
                        }, typeDelay + typeStartDelay);
                    } else {
                        keepDelay = true;
                        setTimeout(function () {
                            receiveMessage(message);
                        }, typeStartDelay + 500);
                        setTimeout(function () {
                            incomingMessages--;
                            if (incomingMessages <= 0) {
                                friendStoppedTyping();
                            }
                        }, 400 + typeStartDelay);
                    }
                }
            });
        }
    }

    function defaultReply() {
        var replies = lipsum.split('|');
        var reply = "";
        reply = replies[Math.floor(Math.random() * replies.length)];
        return reply;
    }

    function getRepeatReply() {
        var replies = repeats.split('|');
        var reply = "";
        reply = replies[Math.floor(Math.random() * replies.length)];
        return reply;
    }

    function friendIsTyping() {
        if (isFriendTyping) return;

        isFriendTyping = true;

        var $dots = $("<div/>")
            .addClass('chat-effect-dots')
            .css({
                top: -30 + bleeding,
                left: 10
            })
            .appendTo($effectContainer);
        for (var i = 0; i < 3; i++) {
            var $dot = $("<div/>")
                .addClass("chat-effect-dot")
                .css({
                    left: i * 20
                })
                .appendTo($dots);
            TweenMax.to($dot, 0.3, {
                delay: -i * 0.1,
                y: 30,
                yoyo: true,
                repeat: -1,
                ease: Quad.easeInOut
            })
        };

        var $info = $("<div/>")
            .addClass("chat-info-typing")
            .text("小A正在输入中...")
            .css({
                transform: "translate3d(0,30px,0)"
            })
            .appendTo($infoContainer)

        TweenMax.to($info, 0.3, {
            y: 0,
            force3D: true
        });

        gooOn();
    }

    function friendStoppedTyping() {
        if (!isFriendTyping) return

        isFriendTyping = false;

        var $dots = $effectContainer.find(".chat-effect-dots");
        TweenMax.to($dots, 0.3, {
            y: 40,
            force3D: true,
            ease: Quad.easeIn,
        });

        var $info = $infoContainer.find(".chat-info-typing");
        TweenMax.to($info, 0.3, {
            y: 30,
            force3D: true,
            ease: Quad.easeIn,
            onComplete: function () {
                $dots.remove();
                $info.remove();

                gooOff();
            }
        });
    }

    function receiveMessage(message) {
        var messageElements = addMessage(message, false, true),
            $messageContainer = messageElements.$container,
            $messageBubble = messageElements.$bubble;

        TweenMax.set($messageBubble, {
            transformOrigin: "60px 50%"
        })
        TweenMax.from($messageBubble, 0.4, {
            scale: 0,
            force3D: true,
            ease: Back.easeOut
        })
        TweenMax.from($messageBubble, 0.4, {
            x: -100,
            force3D: true,
            ease: Quint.easeOut
        })
    }

    function updateChatHeight() {
        $messagesContainer.css({
            height: window.innerHeight - 10 - $(".chat-input-bar").height()
        });
    }

    $input.keydown(function (event) {
        if (event.keyCode == KEY_ENTER) {
            event.preventDefault();
            sendMessage();
        }
    });
    $sendButton.click(function (event) {
        event.preventDefault();
        sendMessage();
        // $input.focus();
    });
    $sendButton.on("touchstart", function (event) {
        event.preventDefault();
        sendMessage();
        // $input.focus();
    });

    $input.on("input", function () {
        updateChatHeight();
    });

    $pullButton.on('click', function () {
        pullOldMessages();
    });

    gooOff();
    updateChatHeight();

    function dateDiff(date1, date2) {
        var type1 = typeof date1,
            type2 = typeof date2;
        if (type1 == 'string')
            date1 = stringToTime(date1);
        else if (date1.getTime)
            date1 = date1.getTime();
        if (type2 == 'string')
            date2 = stringToTime(date2);
        else if (date2.getTime)
            date2 = date2.getTime();
        return (date2 - date1) / 1000 / 60 / 60 / 24; //除1000是毫秒，不加是秒   
    }

    function stringToTime(string) {
        var f = string.split(' ', 2);
        var d = (f[0] ? f[0] : '').split('-', 3);
        var t = (f[1] ? f[1] : '').split(':', 3);
        return (new Date(
            parseInt(d[0], 10) || null, (parseInt(d[1], 10) || 1) - 1,
            parseInt(d[2], 10) || null,
            parseInt(t[0], 10) || null,
            parseInt(t[1], 10) || null,
            parseInt(t[2], 10) || null)).getTime();
    }
});