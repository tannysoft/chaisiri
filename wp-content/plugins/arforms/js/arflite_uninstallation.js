"use strict";jQuery(document).on("click",".arflite_delete_note",function(){var e=jQuery("#wp_admin_url").val(),t=jQuery(this).attr("href");return jQuery("#arflite_confirm_delete").attr("data-url",e+t),jQuery(".arflite_uninstallation_note_wrapper").addClass("arfactive"),!1}),jQuery(document).on("click",".arflite_uninstallation_popup_close",function(){jQuery(".arflite_uninstallation_note_wrapper").removeClass("arfactive")}),jQuery(document).on("click","#arflite_cancel_delete",function(){jQuery(".arflite_uninstallation_note_wrapper").removeClass("arfactive")}),jQuery(document).on("click","#arflite_confirm_delete",function(){var e=jQuery(this).attr("data-url");window.location.href=e}),jQuery(document).on("keydown",function(e){jQuery(".arflite_uninstallation_note_wrapper.arfactive").length>0&&(27!=e.keyCode&&"Escape"!=e.key||jQuery(".arflite_uninstallation_note_wrapper").removeClass("arfactive"))});