!function(e){"use strict";var t=function(t,s){e.fn.typeahead.defaults;s.scrollBar&&(s.items=100,s.menu='<ul class="typeahead arfdropdown-menu" style="max-height:220px;overflow:auto;"></ul>');var i=this;if(i.$element=e(t),i.options=e.extend({},e.fn.typeahead.defaults,s),i.$menu=e(i.options.menu).insertAfter(i.$element),i.eventSupported=i.options.eventSupported||i.eventSupported,i.grepper=i.options.grepper||i.grepper,i.highlighter=i.options.highlighter||i.highlighter,i.lookup=i.options.lookup||i.lookup,i.matcher=i.options.matcher||i.matcher,i.render=i.options.render||i.render,i.onSelect=i.options.onSelect||null,i.sorter=i.options.sorter||i.sorter,i.source=i.options.source||i.source,i.displayField=i.options.displayField||i.displayField,i.valueField=i.options.valueField||i.valueField,i.options.ajax){var a=i.options.ajax;"string"==typeof a?i.ajax=e.extend({},e.fn.typeahead.defaults.ajax,{url:a}):("string"==typeof a.displayField&&(i.displayField=i.options.displayField=a.displayField),"string"==typeof a.valueField&&(i.valueField=i.options.valueField=a.valueField),i.ajax=e.extend({},e.fn.typeahead.defaults.ajax,a)),i.ajax.url||(i.ajax=null),i.query=""}else i.source=i.options.source,i.ajax=null;i.shown=!1,i.listen()};t.prototype={constructor:t,eventSupported:function(e){var t=e in this.$element;return t||(this.$element.setAttribute(e,"return;"),t="function"==typeof this.$element[e]),t},select:function(){var e=this.$menu.find(".active"),t=e.attr("data-value"),s=this.$menu.find(".active a").text();return this.options.onSelect&&this.options.onSelect({value:t,text:s}),this.$element.val(this.updater(s)).change(),this.hide()},updater:function(e){return e},show:function(){var t=e.extend({},this.$element.position(),{height:this.$element[0].offsetHeight});if(jQuery(this.$menu).parents(".arf_material_outline_form").length>0?this.$menu.css({cssText:"top: "+(t.top+t.height)+"px !important; left: "+t.left+";"}):this.$menu.css({top:t.top+t.height,left:t.left}),this.options.alignWidth){var s=e(this.$element[0]).outerWidth();this.$menu.css({width:s})}(jQuery(this.$element).hasClass("rounded")||jQuery(this.$element).hasClass("standard"))&&jQuery(this.$element).addClass("arfautocompleterounded");var i=this,a=i.$element,o=i.$menu,r=a.offset(),n=r.top,h=n+a.outerHeight();n=Math.round(n),h=Math.round(h);var u=o.offset(),l=o.outerHeight(),p=u.top,d=a.parents(".arf_form_outer_wrapper"),f=d.offset(),c=f.top,m=d.outerHeight(),y=Math.round(c)+Math.round(m),v=(Math.round(p)+Math.round(l),o.find("li").first().outerHeight()?o.find("li").first().outerHeight():40),x="";return x=y-(h+v),x+="px",i.$menu.css({"max-height":x,overflow:"auto"}),this.$menu.show(),this.shown=!0,this},hide:function(){var e=jQuery(this.$element).parents(".arf_field_type_arf_autocomplete");return e.removeClass("arf_active_autocomplete"),(jQuery(this.$element).hasClass("rounded")||jQuery(this.$element).hasClass("standard"))&&jQuery(this.$element).removeClass("arfautocompleterounded"),this.$menu.hide(),this.shown=!1,this},ajaxLookup:function(){function t(){this.ajaxToggleLoadClass(!0),this.ajax.xhr&&this.ajax.xhr.abort();var t=this.ajax.preDispatch?this.ajax.preDispatch(s):{query:s};this.ajax.xhr=e.ajax({url:this.ajax.url,data:t,success:e.proxy(this.ajaxSource,this),type:this.ajax.method||"get",dataType:"json"}),this.ajax.timerId=null}var s=e.trim(this.$element.val());return s===this.query?this:(this.query=s,this.ajax.timerId&&(clearTimeout(this.ajax.timerId),this.ajax.timerId=null),!s||s.length<this.ajax.triggerLength?(this.ajax.xhr&&(this.ajax.xhr.abort(),this.ajax.xhr=null,this.ajaxToggleLoadClass(!1)),this.shown?this.hide():this):(this.ajax.timerId=setTimeout(e.proxy(t,this),this.ajax.timeout),this))},ajaxSource:function(e){this.ajaxToggleLoadClass(!1);var t,s=this;if(s.ajax.xhr)return s.ajax.preProcess&&(e=s.ajax.preProcess(e)),s.ajax.data=e,t=s.grepper(s.ajax.data)||[],t.length?(s.ajax.xhr=null,s.render(t).show()):s.shown?s.hide():s},ajaxToggleLoadClass:function(e){this.ajax.loadingClass&&this.$element.toggleClass(this.ajax.loadingClass,e)},lookup:function(){var e,t=this;return t.ajax?void t.ajaxer():(t.query=t.$element.val(),t.query&&(e=t.grepper(t.source))?(0==e.length&&(e[0]={id:-21,name:"Result not Found"}),t.render(e).show()):t.shown?t.hide():t)},matcher:function(e){var t=navigator.language;return~e.toLocaleLowerCase(t).indexOf(this.query.toLocaleLowerCase(t))},sorter:function(e){if(this.options.ajax)return e;for(var t,s=[],i=[],a=[];t=e.shift();)t.toLocaleLowerCase().indexOf(this.query.toLocaleLowerCase())?~t.indexOf(this.query)?i.push(t):a.push(t):s.push(t);return s.concat(i,a)},highlighter:function(e){var t=this.query.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g,"\\$&");return e.replace(new RegExp("("+t+")","ig"),function(e,t){return"<strong>"+t+"</strong>"})},render:function(t){var s=jQuery(this.$menu).parents(".arf_field_type_arf_autocomplete");s.addClass("arf_active_autocomplete");var i,a=this,o="string"==typeof a.options.displayField;return t=e(t).map(function(t,s){return"object"==typeof s?(i=o?s[a.options.displayField]:a.options.displayField(s),t=e(a.options.item).attr("data-value",s[a.options.valueField])):(i=s,t=e(a.options.item).attr("data-value",s)),t.find("a").html(a.highlighter(i)),t[0]}),t.first().addClass("active"),this.$menu.html(t),this},grepper:function(t){var s,i,a=this,o="string"==typeof a.options.displayField;if(!(o&&t&&t.length))return null;if(t[0].hasOwnProperty(a.options.displayField))s=e.grep(t,function(e){return i=o?e[a.options.displayField]:a.options.displayField(e),a.matcher(i)});else{if("string"!=typeof t[0])return null;s=e.grep(t,function(e){return a.matcher(e)})}return this.sorter(s)},next:function(){var t=this.$menu.find(".active").removeClass("active"),s=t.next();s.length||(s=e(this.$menu.find("li")[0]));var i=this.$menu.children("li").index(s);this.$menu.scrollTop(29*i),s.addClass("active")},prev:function(){var e=this.$menu.find(".active").removeClass("active"),t=e.prev();t.length||(t=this.$menu.find("li").last());var s=this.$menu.children("li"),i=(s.length-1,s.index(t));this.$menu.scrollTop(29*i),t.addClass("active")},listen:function(){this.$element.on("focus",e.proxy(this.focus,this)).on("blur",e.proxy(this.blur,this)).on("keypress",e.proxy(this.keypress,this)).on("keyup",e.proxy(this.keyup,this)),this.eventSupported("keydown")&&this.$element.on("keydown",e.proxy(this.keydown,this)),this.$menu.on("click",e.proxy(this.click,this)).on("mouseenter","li",e.proxy(this.mouseenter,this)).on("mouseleave","li",e.proxy(this.mouseleave,this))},move:function(e){if(this.shown){switch(e.keyCode){case 9:case 13:case 27:e.preventDefault();break;case 38:e.preventDefault(),this.prev();break;case 40:e.preventDefault(),this.next()}e.stopPropagation()}},keydown:function(t){this.suppressKeyPressRepeat=~e.inArray(t.keyCode,[40,38,9,13,27]),this.move(t)},keypress:function(e){if(!this.suppressKeyPressRepeat){this.move(e);var t=this;setTimeout(function(){var e=t.$element,s=t.$menu,i=e.offset(),a=i.top,o=a+e.outerHeight();a=Math.round(a),o=Math.round(o);var r=s.offset(),n=s.outerHeight(),h=r.top,u=e.parents(".arf_form_outer_wrapper"),l=u.offset(),p=l.top,d=u.outerHeight(),f=Math.round(p)+Math.round(d),c=Math.round(h)+Math.round(n),m=s.find("li").first().outerHeight()?s.find("li").first().outerHeight():40,y="";c>f?(y=f-(o+m),y+="px"):y="auto",t.$menu.css({"max-height":y,overflow:"auto"})},100)}},keyup:function(e){switch(e.keyCode){case 40:case 38:case 16:case 17:case 18:break;case 9:case 13:if(!this.shown)return;this.select();break;case 27:if(!this.shown)return;this.hide();break;default:this.ajax?this.ajaxLookup():this.lookup()}e.stopPropagation(),e.preventDefault()},focus:function(){this.focused=!0},blur:function(){this.focused=!1,!this.mousedover&&this.shown&&this.hide()},click:function(e){e.stopPropagation(),e.preventDefault(),this.select(),this.$element.focus()},mouseenter:function(t){this.mousedover=!0,this.$menu.find(".active").removeClass("active"),e(t.currentTarget).addClass("active")},mouseleave:function(){this.mousedover=!1,!this.focused&&this.shown&&this.hide()},destroy:function(){this.$element.off("focus",e.proxy(this.focus,this)).off("blur",e.proxy(this.blur,this)).off("keypress",e.proxy(this.keypress,this)).off("keyup",e.proxy(this.keyup,this)),this.eventSupported("keydown")&&this.$element.off("keydown",e.proxy(this.keydown,this)),this.$menu.off("click",e.proxy(this.click,this)).off("mouseenter","li",e.proxy(this.mouseenter,this)).off("mouseleave","li",e.proxy(this.mouseleave,this)),this.$element.removeData("typeahead")}},e.fn.typeahead=function(s){return this.each(function(){var i=e(this),a=i.data("typeahead"),o="object"==typeof s&&s;a||i.data("typeahead",a=new t(this,o)),"string"==typeof s&&a[s]()})},e.fn.typeahead.defaults={source:[],items:10,scrollBar:!1,alignWidth:!0,menu:'<ul class="typeahead arfdropdown-menu"></ul>',item:'<li><a href="#"></a></li>',valueField:"id",displayField:"name",onSelect:function(){},ajax:{url:null,timeout:300,method:"get",triggerLength:1,loadingClass:null,preDispatch:null,preProcess:null}},e.fn.typeahead.Constructor=t,e(function(){e("body").on("focus.typeahead.data-api",'[data-provide="typeahead"]',function(t){var s=e(this);s.data("typeahead")||(t.preventDefault(),s.typeahead(s.data()))})})}(window.jQuery);