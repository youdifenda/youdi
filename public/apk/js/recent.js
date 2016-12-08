function add() {
    $.ajax({
        type: 'POST',
        url: '/question/recent',
        data: {},
        dataType: 'JSON',
        success: function (data) {
            var result = $.parseJSON(data);
            if (result.code == 0) {
                alert(result.message);

                var json = result.data;
                for (var i = 0; i < json.length; i++) {
                    //问题状态，是否有权限听，多少钱能听
                    //是否登录 1，登录。0未登录
                    var tip = '';
                    var action = '';
                    //play： 已付钱可以播放
                    //info：已付钱可以查看
                    //out： 未登录
                    //noPay：未付钱

                    if (json[i].isLogin == 1) {
                        //是否已经付钱
                        // 1 已经付过钱了  0：还没付钱
                        if (json[i].payment == 1) {
                            //是文字还是录音
                            //type:0 图文 ，1：录音
                            if (json[i].type == 1) {
                                tip = '点击播放';
                                action = 'play';
                            }
                            else {
                                tip = '点击查看';
                                action = 'info';
                            }
                        }
                        //未付钱
                        else {
                            action = 'noPay';
                            if (json[i].type == 1) {
                                tip = json[i].listenprice + 'U币点击播放';
                            }
                            else {
                                tip = json[i].listenprice + 'U币点击查看';
                            }
                        }
                    }
                    //未登录
                    else {
                        if (json[i].type == 1) {
                            tip = json[i].listenprice + 'U币点击播放';
                        }
                        else {
                            tip = json[i].listenprice + 'U币点击查看';
                        }
                        action = 'out';
                    }

                    var html = "";

                    if(action != 'play') {
                        html += '<li data="'+json[i].id+'" class="myli">';
                        html += '<span>';
                        //问题内容
                        html += '<p class="pram">' + json[i].questioncontent + '</p>';
                        html += '<div class="personInfo">';
                        //图片的点击效果
                        html += '<a href= "/apk/ask.html?id=' + json[i].ut + '" class="img"><img src=' + json[i].fileurl + '></a>';
                        html += '<div class="des">';
                        //用户名称
                        html += '<h3>' + json[i].user_name + '</h3>';
                        //时间   用户职位名称
                        html += '<p class="identity"><span><i></i>' + json[i].time + '</span>' + json[i].user_name + '</p>';
                        //问题状态，是否有权限听，多少钱能听
                        html += '<div class="status">';
                        html += '<a href="javascript:;" class="vbtn ' + action + ' " id = ' + json[i].id + '>' + tip + '</a>';
                        html += '</div>';
                        html += '<div class="act">';
                        //旁听数
                        html += '<a href="#" class="icon_hear"><i></i>' + '旁听' + json[i].listennum + '</a>';
                        html += '<a href="#" class="icon_share"><i></i>分享</a>';
                        html += '<a href="#" class="icon_fav" id="'+json[i].id+'" onclick="collect('+json[i].id+')" ><i></i>收藏</a>';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                        html += '</span>';
                        html += '</li>';
                    }
                    else{
                        html += '<li class="myli" data="'+json[i].id+'">';
                        html += '<span>';
                        html += '<p class="pram">'+json[i].questioncontent +'</p>';
                        html += '<div class="personInfo">';
                        html += '<a href="/apk/ask.html?id='+json[i].ut+'" class="img"><img src="'+json[i].fileurl+'"/></a>';
                        html += '<div class="des">';
                        //姓名
                        html += '<h3>'+json[i].user_name+'</h3>';
                        //时间  职位
                        html += '<p class="identity"><span><i></i>'+json[i].time+'</span>'+json[i].user_name+'</p>';
                        html += '<div class="status">';
                        html += '<a href="javascript:;" class="vbtn play"><i></i>点击播放'+ 14+" ''"+'</a>';
                        html += '</div>';
                        html += '<div class="act">';
                        html += '<a href="#" class="icon_hear"><i></i>'+'旁听'+json[i].listennum+'</a>'
                        html += '<a href="#" class="icon_share"><i></i>分享</a>';
                        html += '<a href="#" class="icon_fav" id="'+json[i].id+'" onclick="collect('+json[i].id+')"><i></i>收藏</a>'
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                        html += '</span>';
                        html += '</li>';
                    }
                    $("#myul").append(html);
                }
            } else {
                alert(result.message);
            }
        },
        error: function (xhr, type) {
            alert('Ajax error!')
        }
    })
};







// function showmDetail(answerId) {
//     var id = answerId;
//     var e = event || window.event;
//     var dom = e.srcElement || e.target;
//
//     console.log(event.srcElement);
//     // window.location.href ='/apk/questionDetail.html?answerId='+id;
//     return false;
// }

