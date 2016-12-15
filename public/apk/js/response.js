$(function () {
    $('.personInfo .viewbtn1').tap(function () {
        setTimeout(function () {
            animateshowHide($('.fixbottomInfo .bg'));
        }, 320);
        return false;
    });

    $('.personInfo .viewbtn2').tap(function () {
        setTimeout(function () {
            animateshowHide($('.fixbottomLogin .bg'));
        }, 320);
        return false;
    });

    $('.personInfo .viewbtn3').tap(function () {
        setTimeout(function () {
            animateshowHide($('.fixbottomPay .bg'));
        }, 320);
        return false;
    });

    $('.dalist').on('click', '.personInfo .info', function () {

        var answerId = this.id;
        $.ajax({
            type: 'POST',
            url: '/question/detail',
            data: {answerId: answerId},
            dataType: 'json',
            timeout: 1000,
            success: function (data) {
                var json = data.data;
                for (var i = 0; i < json.length; i++) {
                    $('#infoName').html(json[i].user_name);
                    $('#infoTeam').html(json[i].user_name);
                    $('#infoContent').html(json[i].content);
                    $('#infohead').attr("src", json[i].imgs);
                    $('#infoimg').attr("src", json[i].imgs);
                }
                //返回的数据为空的情况
                //后台做验证
                //此时可能是未付款，或者未登录
                if (json.length == 0) {
                    //未登录
                    if (data.message == '未登录') {
                        animateshowHide($('.fixbottomLogin .bg'));
                    }
                    else if (data.message == '未付款') {
                        animateshowHide($('.fixbottomPay .bg'));
                    }
                }
                else {
                    animateshowHide($('.fixbottomInfo .bg'));
                }
            },
            error: function (xhr, type) {
                alert('Ajax error!')
            }
        });
        return false;
    });

    $('.dalist').on('click', '.personInfo .noPay', function () {
        animateshowHide($('.fixbottomPay .bg'));
        return false;
    });
    $('.dalist').on('click', '.personInfo .play', function () {
        alert('play');
        return false;
    });
    $('.dalist').on('click', '.myli', function () {
        var ev = event || window.event;
        var dom = ev.srcElement;
        if (!$(dom).hasClass("vbtn") &&!$(dom).hasClass("play") && $(dom)[0].localName != 'img') {
            var state = this.attributes.state.value;
            //有答案 查看问答详情
            if(state == 0){
                window.location.href = '/apk/questionDetail.html?answerId=' + this.attributes.data.value;
            }
            //没答案 提交答案
            if(state == 1){
                window.location.href = '/apk/submitanswer.html?id=' + this.attributes.data.value;
            }
            return false;
        }
        return true;
    });

    //请求数据，获得我答
    $.ajax({
        type: 'POST',
        url: '/user/iresponse',
        data: {},
        dataType: 'json',
        timeout: 2000,
        success: function (data) {
            var code = data.code;
            if (code == 0) {
                var result = data.data;
                var action = '';
                for (var i = 0; i < result.length; i++) {
                    //state:   0： 有答案
                    //         1： 未回答
                    //         2： 已过期

                    //play:  已付钱可以播放
                    //info:  已付钱可以查看
                    //noPay: 未付钱
                    var html = '';
                    if (result[i].state == 0) {
                        // 是否已经付钱
                        // 1: 已经付过钱了  0: 还没付钱
                        if (result[i].payment == 1) {
                            if (result[i].answerType == 1) {
                                action = 'play';
                            }
                            else {
                                action = 'info';
                            }
                        }
                        else {
                            action = 'noPay';
                        }
                        //是否是图文 type:0 图文 , 1: 录音
                        if (action == 'play') {
                            html += '<li class="myli" state="0" data="' + result[i].answerid + '">';
                            html += '<p class="pram">' + result[i].content + '</p>';
                            html += '<div class="personInfo">';
                            html += '<a href="#" class="img"><img src="' + result[i].userImg + '" /></a>';
                            html += '<div class="des">';
                            html += '<h3>' + result[i].userName + '</h3>';
                            html += '<p class="identity">巅峰财富|策略分析师</p>';
                            html += '<div class="status">';
                            html += '<span class="play" id="' + result[i].answerid + '"><i></i>点击播放</span> 14 \'\'';
                            html += '</div>';
                            html += '<p class="time">' + result[i].time + '</p>';
                            html += '</div>';
                            html += '</div>';
                            html += '</li>';
                        }
                        else {
                            html += '<li class="myli" state="0" data="' + result[i].answerid + '">';
                            html += '<p class="pram">' + result[i].content + '</p>';
                            html += '<div class="personInfo">';
                            html += '<a href="#" class="img"><img src="' + result[i].userImg + '"/></a>';
                            html += '<div class="des">';
                            html += '<h3>' + result[i].userName + '</h3>';
                            html += '<p class="identity">巅峰财富|策略分析师</p>';
                            html += '<div class="status">';
                            html += '<a href="javascript:;" class="vbtn ' + action + ' " id = ' + result[i].answerid + '">点击查看</a>';
                            html += '</div>';
                            html += '<p class="time">' + result[i].time + '</p>';
                            html += '</div>';
                            html += '</div>';
                            html += '</li>';
                        }
                    }
                    //未过期
                    else if (result[i].state == 1) {
                        html += '<li class="myli" state="1" data="' + result[i].id + '">';
                        html += '<div class="personInfo">';
                        html += '<a href="#" class="img"><img src="' + result[i].userImg + '"/></a>';
                        html += '<div class="des">';
                        html += '<h3>' + result[i].userName + '</h3>';
                        html += '<p class="identity"><span class="state">待回答</span>巅峰财富|策略分析师</p>';
                        html += '<p class="pram">' + result[i].content + '</p>';
                        html += '<p class="time">' + result[i].time + '</p>';
                        html += '</div>';
                        html += '</div>';
                        html += '</li>';
                    }
                    //已过期
                    else if (result[i].state == 2) {
                        html += '<li>';
                        html += '<div class="personInfo">';
                        html += '<a href="#" class="img"><img src="' + result[i].userImg + '"/></a>';
                        html += '<div class="des">';
                        html += '<h3>' + result[i].userName + '</h3>';
                        html += '<p class="identity"><span class="state overstate">已过期</span>巅峰财富|策略分析师</p>';
                        html += '<p class="pram">' + result[i].content + '</p>';
                        html += '<p class="time">' + result[i].time + '</p>';
                        html += '</div>';
                        html += '</div>';
                        html += '</li>';
                    }
                    $("#myul").append(html);
                }
            }
            else {
                alert(data.message);
            }
        },
        error: function (xhr, type) {
            alert('Ajax error!')
        }
    })
});

// function intent(id) {
//     window.location.href = '/apk/submitanswer.html?id=' + id;
// }