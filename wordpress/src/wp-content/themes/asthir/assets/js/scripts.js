(function ($) {
  "use strict";
	
	$('.mini-toggle').on('click', function(){
	   $(this).parent().toggleClass('menushow');
	});
	$('.header-top-search i').on('click', function(){
	   $('.header-top-search form').toggleClass('sbar-show');

	});

	$('#masthead').on('click', function(){
	   $('.header-top-search form').removeClass('sbar-show');
	});
	$('.mmenu-hide').on('click', function(){
	   $('#site-navigation').removeClass('toggled');
	});

    $.fn.asthirAccessibleDropDown = function () {
        var el = $(this);

        $("a", el)
            .on("focus", function () {
                $(this).parents("li").addClass("focus");
            })
            .on("blur", function () {
                var that = this;
                setTimeout(function () {
                    if (!$(that).parents("li").find("a:focus").length) {
                        $(that).parents("li").removeClass("focus");
                    }
                }, 10);
            })
            .on("keydown", function (e) {
                var parentLi = $(this).parent("li");

                // Detect Shift + Tab
                if (e.shiftKey && e.key === "Tab") {
                    var prevElement = $(this).parent("li").prev().find("a").last();
                    if (prevElement.length) {
                        prevElement.focus();
                        e.preventDefault();
                    }
                }
            });
    };

	 $("#primary-menu").asthirAccessibleDropDown();
	


})(jQuery);