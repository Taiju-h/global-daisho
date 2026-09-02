

// スムーススクロール
$(document).ready(function(){
	$("a[href^=#]").click(function() {
		var myHref= $(this).attr("href");
		var myPos = $(myHref).offset().top;
		$("html,body").animate({scrollTop : myPos}, 'swing');
		return false;
	});
});


// ページ上部へ戻る

$(function() {
	var pageTop = $('#top_btn');
	pageTop.hide();
	$(window).scroll(function () {
		if ($(this).scrollTop() > 600) {
			pageTop.fadeIn();
		} else {
			pageTop.fadeOut();
		}
	});
	pageTop.click(function () {
		$('body, html').animate({scrollTop:0}, 500, 'swing');
		return false;
	});
});