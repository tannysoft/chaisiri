jQuery(window).on("load",function(){jQuery("div[data-dismissible] button.notice-dismiss").click(function(i){i.preventDefault();var s=jQuery(this).parent().attr("data-dismissible").split("-"),i=s.pop(),s=s.join("-");jQuery.post(ajaxurl,{action:"dismiss_admin_notice",option_name:s,dismissible_length:i})})}),jQuery(window).on("load",function(){jQuery("div[data-dismissible] .dismiss-this").on("click",function(i){i.preventDefault();var s=jQuery(this),t=s.closest("div[data-dismissible]").attr("data-dismissible").split("-"),i=t.pop(),t=t.join("-");jQuery.post(ajaxurl,{action:"dismiss_admin_notice",option_name:t,dismissible_length:i}),s.closest("div[data-dismissible]").parent().hide("slow")})});