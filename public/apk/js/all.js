//show or hide
function animateshowHide (_this){
	var $fixbBox = $(_this).parents('.fixbottomBox');
	var $cont = $fixbBox.find('.cont')
	var pheight = $cont.height();
	if(!$fixbBox.is(':visible')){
		$(_this).parents('.fixbottomBox').show();
		$cont.animate({bottom:0},400);
	}else{
		$cont.animate({bottom:-pheight},400,function(){
			$(_this).parents('.fixbottomBox').hide();
		})
	}
}

$(function(){
	$('body').css('minHeight',$(window).height());
	$('html').height($(window).height());

	//关闭
	$('.fixbottomBox .closed, .fixbottomBox .bg').tap(function(){
		var _this = this;
		setTimeout(function(){
			animateshowHide(_this);
		},320)
		return false;
	})
})


function getArgsFromHref(sArgName) {
	var sHref = window.location.href;
	var args = sHref.split("?");
	var retval = "";
	if (args[0] == sHref) /*参数为空*/
	{
		return retval;
	}
	var str = args[1];
	args = str.split("&");
	for (var i = 0; i < args.length; i++) {
		str = args[i];
		var arg = str.split("=");
		if (arg.length <= 1) continue;
		if (arg[0] == sArgName) retval = arg[1];
	}
	return retval;
}


