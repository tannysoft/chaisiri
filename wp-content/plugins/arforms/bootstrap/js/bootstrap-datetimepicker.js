!function(e){"use strict";if("function"==typeof define&&define.amd)define(["jquery","moment"],e);else if("object"==typeof exports&&"undefined"!=typeof require)e(require("jquery"),require("moment"));else{if("undefined"==typeof jQuery)throw"bootstrap-datetimepicker requires jQuery to be loaded first";if("undefined"==typeof moment)throw"bootstrap-datetimepicker requires Moment.js to be loaded first";e(jQuery,moment)}}(function(e,t){"use strict";if(!t)throw new Error("bootstrap-datetimepicker requires Moment.js to be loaded first");var a=function(a,n){var r,i,o,d,s,l={},p=t().startOf("d"),c=p.clone(),f=!0,u=!1,h=!1,m=0,y=[{clsName:"days",navFnc:"M",navStep:1},{clsName:"months",navFnc:"y",navStep:1},{clsName:"years",navFnc:"y",navStep:10},{clsName:"decades",navFnc:"y",navStep:100}],b=["days","months","years","decades"],w=["top","bottom","auto"],g=["left","right","auto"],v=["default","top","bottom"],C={up:38,38:"up",down:40,40:"down",left:37,37:"left",right:39,39:"right",tab:9,9:"tab",escape:27,27:"escape",enter:13,13:"enter",pageUp:33,33:"pageUp",pageDown:34,34:"pageDown",shift:16,16:"shift",control:17,17:"control",space:32,32:"space",t:84,84:"t","delete":46,46:"delete"},k={},D=function(e){if("string"!=typeof e||e.length>1)throw new TypeError("isEnabled expects a single character string parameter");switch(e){case"y":return-1!==o.indexOf("Y");case"M":return-1!==o.indexOf("M");case"d":return-1!==o.toLowerCase().indexOf("d");case"h":case"H":return-1!==o.toLowerCase().indexOf("h");case"m":return-1!==o.indexOf("m");case"s":return-1!==o.indexOf("s");default:return!1}},x=function(){return D("h")||D("m")||D("s")},T=function(){return D("y")||D("M")||D("d")},M=function(){var a=t(p).format("MMM"),r=t(p).format("YYYY"),i=t(p).format("DD"),o=t(p).format("ddd"),d=e("<thead>").append(e('<tr class="arf_cal_header">').append(e("<th>").addClass("prev").attr("data-action","previous").append(e("<span>").addClass(n.icons.previous))).append(e("<th>").addClass("picker-switch").attr("data-action","pickerSwitch").attr("colspan",n.calendarWeeks?"6":"5")).append(e("<th>").addClass("next").attr("data-action","next").append(e("<span>").addClass(n.icons.next)))),s=e("<tbody>").append(e("<tr>").append(e("<td>").attr("colspan",n.calendarWeeks?"8":"7")));return[e("<div>").addClass("datepicker-days").append(e("<div>").addClass("topdateinfo").append(e("<p>").addClass("yearonly").append(r)).append(e("<div>").addClass("datetopcol").append(e("<p>").addClass("dayname").append(o+",")).append(e("<p>").addClass("monthonly").append(a)).append(e("<p>").addClass("dateonly").append(i)))).append(e("<table>").addClass("table-condensed").append(d).append(e("<tbody>"))),e("<div>").addClass("datepicker-months").append(e("<div>").addClass("topdateinfo").append(e("<p>").addClass("yearonly").append(r)).append(e("<div>").addClass("datetopcol").append(e("<p>").addClass("dayname").append(o+",")).append(e("<p>").addClass("monthonly").append(a)).append(e("<p>").addClass("dateonly").append(i)))).append(e("<table>").addClass("table-condensed").append(d.clone()).append(s.clone())),e("<div>").addClass("datepicker-years").append(e("<div>").addClass("topdateinfo").append(e("<p>").addClass("yearonly").append(r)).append(e("<div>").addClass("datetopcol").append(e("<p>").addClass("dayname").append(o+",")).append(e("<p>").addClass("monthonly").append(a)).append(e("<p>").addClass("dateonly").append(i)))).append(e("<table>").addClass("table-condensed").append(d.clone()).append(s.clone())),e("<div>").addClass("datepicker-decades").append(e("<div>").addClass("topdateinfo").append(e("<p>").addClass("yearonly").append(r)).append(e("<div>").addClass("datetopcol").append(e("<p>").addClass("dayname").append(o+",")).append(e("<p>").addClass("monthonly").append(a)).append(e("<p>").addClass("dateonly").append(i)))).append(e("<table>").addClass("table-condensed").append(d.clone()).append(s.clone()))]},O=function(){var t=e("<tr>"),a=e("<tr>"),r=e("<tr>");return D("h")&&(t.append(e("<td>").append(e("<a>").attr({href:"#",tabindex:"-1",title:"Increment Hour"}).addClass("btn").attr("data-action","incrementHours").append(e("<span>").addClass(n.icons.up)))),a.append(e("<td>").append(e("<span>").addClass("timepicker-hour").attr({"data-time-component":"hours",title:"Pick Hour"}).attr("data-action","showHours"))),r.append(e("<td>").append(e("<a>").attr({href:"#",tabindex:"-1",title:"Decrement Hour"}).addClass("btn").attr("data-action","decrementHours").append(e("<span>").addClass(n.icons.down))))),D("m")&&(D("h")&&(t.append(e("<td>").addClass("arf_separator")),a.append(e("<td>").addClass("arf_separator").html(":")),r.append(e("<td>").addClass("arf_separator"))),t.append(e("<td>").append(e("<a>").attr({href:"#",tabindex:"-1",title:"Increment Minute"}).addClass("btn").attr("data-action","incrementMinutes").append(e("<span>").addClass(n.icons.up)))),a.append(e("<td>").append(e("<span>").addClass("timepicker-minute").attr({"data-time-component":"minutes",title:"Pick Minute"}).attr("data-action","showMinutes"))),r.append(e("<td>").append(e("<a>").attr({href:"#",tabindex:"-1",title:"Decrement Minute"}).addClass("btn").attr("data-action","decrementMinutes").append(e("<span>").addClass(n.icons.down))))),D("s")&&(D("m")&&(t.append(e("<td>").addClass("arf_separator")),a.append(e("<td>").addClass("arf_separator").html(":")),r.append(e("<td>").addClass("arf_separator"))),t.append(e("<td>").append(e("<a>").attr({href:"#",tabindex:"-1",title:"Increment Second"}).addClass("btn").attr("data-action","incrementSeconds").append(e("<span>").addClass(n.icons.up)))),a.append(e("<td>").append(e("<span>").addClass("timepicker-second").attr({"data-time-component":"seconds",title:"Pick Second"}).attr("data-action","showSeconds"))),r.append(e("<td>").append(e("<a>").attr({href:"#",tabindex:"-1",title:"Decrement Second"}).addClass("btn").attr("data-action","decrementSeconds").append(e("<span>").addClass(n.icons.down))))),i||(t.append(e("<td>").addClass("arf_separator")),a.append(e("<td>").append(e("<button>").addClass("btn btn-primary").attr({"data-action":"togglePeriod",tabindex:"-1",title:"Toggle Period"}))),r.append(e("<td>").addClass("arf_separator"))),e("<div>").addClass("timepicker-picker").append(e("<table>").addClass("table-condensed").append([t,a,r]))},S=function(){var t=e("<div>").addClass("timepicker-hours").append(e("<table>").addClass("table-condensed")),a=e("<div>").addClass("timepicker-minutes").append(e("<table>").addClass("table-condensed")),n=e("<div>").addClass("timepicker-seconds").append(e("<table>").addClass("table-condensed")),r=[O()];return D("h")&&r.push(t),D("m")&&r.push(a),D("s")&&r.push(n),r},P=function(){var t=[];return n.showTodayButton&&t.push(e("<td>").append(e("<a>").attr({"data-action":"today",title:n.tooltips.today}).append(e("<span>").addClass(n.icons.today)))),!n.sideBySide&&T()&&x()&&t.push(e("<td>").append(e("<a>").attr({"data-action":"togglePicker",title:"Select Time"}).append(e("<span>").addClass(n.icons.time)))),n.showClear&&t.push(e("<td>").append(e("<a>").attr({"data-action":"clear",title:n.tooltips.clear}).append(e("<span>").addClass(n.icons.clear)))),n.showClose&&t.push(e("<td>").append(e("<a>").attr({"data-action":"close",title:n.tooltips.close}).append(e("<span>").addClass(n.icons.close)))),e("<table>").addClass("table-condensed").append(e("<tbody>").append(e("<tr>").append(t)))},E=function(){var t=e("<div>").addClass("bootstrap-datetimepicker-widget arfdate-dropdown-menu"),a=e("<div>").addClass("datepicker").append(M()),r=e("<div>").addClass("timepicker").append(S()),o=e("<ul>").addClass("list-unstyled"),d=e("<li>").addClass("picker-switch"+(n.collapse?" accordion-toggle":"")).append(P());return n.inline&&t.removeClass("arfdate-dropdown-menu"),t.css("display","block"),i&&t.addClass("usetwentyfour"),D("s")&&!i&&t.addClass("wider"),n.sideBySide&&T()&&x()?(t.addClass("timepicker-sbs"),"top"===n.toolbarPlacement&&t.append(d),t.append(e("<div>").addClass("row").append(a.addClass("col-md-6")).append(r.addClass("col-md-6"))),"bottom"===n.toolbarPlacement&&t.append(d),t):("top"===n.toolbarPlacement&&o.append(d),T()&&o.append(e("<li>").addClass(n.collapse&&x()?"collapse in":"").append(a)),"default"===n.toolbarPlacement&&o.append(d),x()&&o.append(e("<li>").addClass(n.collapse&&T()?"collapse":"").append(r)),"bottom"===n.toolbarPlacement&&o.append(d),t.append(o))},I=function(){var t,r={};return t=a.is("input")||n.inline?a.data():a.find("input").data(),t.dateOptions&&t.dateOptions instanceof Object&&(r=e.extend(!0,r,t.dateOptions)),e.each(n,function(e){var a="date"+e.charAt(0).toUpperCase()+e.slice(1);void 0!==t[a]&&(r[e]=t[a])}),r},H=function(){var t,r=(u||a).position(),i=(u||a).offset(),o=n.widgetPositioning.vertical,d=n.widgetPositioning.horizontal;if(n.widgetParent)t=n.widgetParent.append(h);else if(a.is("input"))t=a.after(h).parent();else{if(n.inline)return void(t=a.append(h));t=a,a.children().first().after(h)}if("auto"===o&&(o=i.top+1*h.height()>=e(window).height()+e(window).scrollTop()&&h.height()+a.outerHeight()<i.top?"top":"bottom"),"auto"===d&&(d=t.width()<i.left+h.outerWidth()/2&&i.left+h.outerWidth()>e(window).width()?"right":"left"),"top"===o?h.addClass("top").removeClass("bottom"):h.addClass("bottom").removeClass("top"),"right"===d?h.addClass("pull-right"):h.removeClass("pull-right"),"relative"!==t.css("position")&&(t=t.parents().filter(function(){return"relative"===e(this).css("position")}).first()),0===t.length)throw new Error("datetimepicker component should be placed within a relative positioned container");h.css({top:"top"===o?"auto":r.top+a.outerHeight(),bottom:"top"===o?r.top+a.outerHeight():"auto"})},Y=function(e){"dp.change"===e.type&&(e.date&&e.date.isSame(e.oldDate)||!e.date&&!e.oldDate)||a.trigger(e)},q=function(e){"y"===e&&(e="YYYY"),Y({type:"dp.update",change:e,viewDate:c.clone()})},B=function(e){h&&(e&&(s=Math.max(m,Math.min(3,s+e))),h.find(".datepicker > div").hide().filter(".datepicker-"+y[s].clsName).show())},j=function(){var t=e('<tr class="arf_cal_month">'),a=c.clone().startOf("w").startOf("d");for(n.calendarWeeks===!0&&t.append(e("<th>").addClass("cw").text("#"));a.isBefore(c.clone().endOf("w"));)t.append(e("<th>").addClass("dow").text(a.format("dd"))),a.add(1,"d");h.find(".datepicker-days thead").append(t)},F=function(e){return n.disabledDates[e.format("YYYY-MM-DD")]===!0},L=function(e){return n.enabledDates[e.format("YYYY-MM-DD")]===!0},W=function(e){return n.disabledHours[e.format("H")]===!0},A=function(e){return n.enabledHours[e.format("H")]===!0},z=function(t,a){if(!t.isValid())return!1;if(n.disabledDates&&"d"===a&&F(t))return!1;if(n.enabledDates&&"d"===a&&!L(t))return!1;if(n.minDate&&t.isBefore(n.minDate,a))return!1;if(n.maxDate&&t.isAfter(n.maxDate,a))return!1;if(n.daysOfWeekDisabled&&"d"===a&&-1!==n.daysOfWeekDisabled.indexOf(t.day()))return!1;if(n.disabledHours&&("h"===a||"m"===a||"s"===a)&&W(t))return!1;if(n.enabledHours&&("h"===a||"m"===a||"s"===a)&&!A(t))return!1;if(n.disabledTimeIntervals&&("h"===a||"m"===a||"s"===a)){var r=!1;if(e.each(n.disabledTimeIntervals,function(){return t.isBetween(this[0],this[1])?(r=!0,!1):void 0}),r)return!1}return!0},_=function(){for(var t=[],a=c.clone().startOf("y").startOf("d");a.isSame(c,"y");)t.push(e("<span>").attr("data-action","selectMonth").addClass("month").text(a.format("MMM"))),a.add(1,"M");h.find(".datepicker-months td").empty().append(t)},N=function(){var t=h.find(".datepicker-months"),a=t.find("th"),r=t.find("tbody").find("span");a.eq(0).find("span").attr("title",n.tooltips.prevYear),a.eq(1).attr("title",n.tooltips.selectYear),a.eq(2).find("span").attr("title",n.tooltips.nextYear),t.find(".disabled").removeClass("disabled"),z(c.clone().subtract(1,"y"),"y")||a.eq(0).addClass("disabled"),a.eq(1).text(c.year()),z(c.clone().add(1,"y"),"y")||a.eq(2).addClass("disabled"),r.removeClass("active"),p.isSame(c,"y")&&!f&&r.eq(p.month()).addClass("active"),r.each(function(t){z(c.clone().month(t),"M")||e(this).addClass("disabled")})},V=function(){var e=h.find(".datepicker-years"),t=e.find("th"),a=c.clone().subtract(5,"y"),r=c.clone().add(6,"y"),i="";for(t.eq(0).find("span").attr("title",n.tooltips.prevDecade),t.eq(1).attr("title",n.tooltips.selectDecade),t.eq(2).find("span").attr("title",n.tooltips.nextDecade),e.find(".disabled").removeClass("disabled"),n.minDate&&n.minDate.isAfter(a,"y")&&t.eq(0).addClass("disabled"),t.eq(1).text(a.year()+"-"+r.year()),n.maxDate&&n.maxDate.isBefore(r,"y")&&t.eq(2).addClass("disabled");!a.isAfter(r,"y");)i+='<span data-action="selectYear" class="year'+(a.isSame(p,"y")&&!f?" active":"")+(z(a,"y")?"":" disabled")+'">'+a.year()+"</span>",a.add(1,"y");e.find("td").html(i)},Q=function(){var e=h.find(".datepicker-decades"),a=e.find("th"),r=t(c.isBefore(t({y:1999}))?{y:1899}:{y:1999}),i=r.clone().add(100,"y"),o="";for(a.eq(0).find("span").attr("title",n.tooltips.prevCentury),a.eq(2).find("span").attr("title",n.tooltips.nextCentury),e.find(".disabled").removeClass("disabled"),(r.isSame(t({y:1900}))||n.minDate&&n.minDate.isAfter(r,"y"))&&a.eq(0).addClass("disabled"),a.eq(1).text(r.year()+"-"+i.year()),(r.isSame(t({y:2e3}))||n.maxDate&&n.maxDate.isBefore(i,"y"))&&a.eq(2).addClass("disabled");!r.isAfter(i,"y");)o+='<span data-action="selectDecade" class="decade'+(r.isSame(p,"y")?" active":"")+(z(r,"y")?"":" disabled")+'" data-selection="'+(r.year()+6)+'">'+(r.year()+1)+" - "+(r.year()+12)+"</span>",r.add(12,"y");o+="<span></span><span></span><span></span>",e.find("td").html(o)},R=function(){var a,r,i,o,d=h.find(".datepicker-days"),s=d.find("th"),l=[];if(T()){for(s.eq(0).find("span").attr("title",n.tooltips.prevMonth),s.eq(1).attr("title",n.tooltips.selectMonth),s.eq(2).find("span").attr("title",n.tooltips.nextMonth),d.find(".disabled").removeClass("disabled"),s.eq(1).text(c.format(n.dayViewHeaderFormat)),z(c.clone().subtract(1,"M"),"M")||s.eq(0).addClass("disabled"),z(c.clone().add(1,"M"),"M")||s.eq(2).addClass("disabled"),a=c.clone().startOf("M").startOf("w").startOf("d"),o=0;42>o;o++)0===a.weekday()&&(r=e("<tr>"),n.calendarWeeks&&r.append('<td class="cw">'+a.week()+"</td>"),l.push(r)),i="",a.isBefore(c,"M")&&(i+=" old"),a.isAfter(c,"M")&&(i+=" new"),a.isSame(p,"d")&&!f&&(i+=" active"),z(a,"d")||(i+=" disabled"),a.isSame(t(),"d")&&(i+=" today"),0!==a.day()&&6!==a.day()||(i+=" weekend"),r.append('<td data-action="selectDay" data-day="'+a.format("L")+'" class="day'+i+'">'+a.date()+"</td>"),a.add(1,"d");d.find("tbody").empty().append(l),N(),V(),Q()}},U=function(){var t=h.find(".timepicker-hours table"),a=c.clone().startOf("d"),n=[],r=e("<tr>");for(c.hour()>11&&!i&&a.hour(12);a.isSame(c,"d")&&(i||c.hour()<12&&a.hour()<12||c.hour()>11);)a.hour()%4===0&&(r=e("<tr>"),n.push(r)),r.append('<td data-action="selectHour" class="hour'+(z(a,"h")?"":" disabled")+'"><div class="arf_cal_hour">'+a.format(i?"HH":"hh")+'</div"></td>'),a.add(1,"h");t.empty().append(n)},G=function(){for(var t=h.find(".timepicker-minutes table"),a=c.clone().startOf("h"),r=[],i=e("<tr>"),o=1===n.stepping?5:n.stepping;c.isSame(a,"h");)a.minute()%(4*o)===0&&(i=e("<tr>"),r.push(i)),i.append('<td data-action="selectMinute" class="minute'+(z(a,"m")?"":" disabled")+'"><div class="arf_cal_minute">'+a.format("mm")+"</div></td>"),a.add(o,"m");t.empty().append(r)},J=function(){for(var t=h.find(".timepicker-seconds table"),a=c.clone().startOf("m"),n=[],r=e("<tr>");c.isSame(a,"m");)a.second()%20===0&&(r=e("<tr>"),n.push(r)),r.append('<td data-action="selectSecond" class="second'+(z(a,"s")?"":" disabled")+'">'+a.format("ss")+"</td>"),a.add(5,"s");t.empty().append(n)},K=function(){var e,t,a=h.find(".timepicker span[data-time-component]");i||(e=h.find(".timepicker [data-action=togglePeriod]"),t=p.clone().add(p.hours()>=12?-12:12,"h"),e.text(p.format("A")),z(t,"h")?e.removeClass("disabled"):e.addClass("disabled")),a.filter("[data-time-component=hours]").text(p.format(i?"HH":"hh")),a.filter("[data-time-component=minutes]").text(p.format("mm")),a.filter("[data-time-component=seconds]").text(p.format("ss")),U(),G(),J()},X=function(){h&&(R(),K())},Z=function(e){var t=f?null:p;return e?(e=e.clone().locale(n.locale),1!==n.stepping&&e.minutes(Math.round(e.minutes()/n.stepping)*n.stepping%60).seconds(0),void(z(e)?(p=e,c=p.clone(),r.val(p.format(o)),a.data("date",p.format(o)),f=!1,X(),Y({type:"dp.change",date:p.clone(),oldDate:t})):(n.keepInvalid||r.val(f?"":p.format(o)),Y({type:"dp.error",date:e})))):(f=!0,r.val(""),a.data("date",""),Y({type:"dp.change",date:!1,oldDate:t}),void X())},$=function(){var t=!1;return h?(h.find(".collapse").each(function(){var a=e(this).data("collapse");return a&&a.transitioning?(t=!0,!1):!0}),t?l:(u&&u.hasClass("btn")&&u.toggleClass("active"),h.hide(),e(window).off("resize",H),h.off("click","[data-action]"),h.off("mousedown",!1),h.remove(),h=!1,Y({type:"dp.hide",date:p.clone()}),r.blur(),l)):l},ee=function(){Z(null)},te={next:function(){var e=y[s].navFnc;c.add(y[s].navStep,e),R(),q(e)},previous:function(){var e=y[s].navFnc;c.subtract(y[s].navStep,e),R(),q(e)},pickerSwitch:function(){B(1)},selectMonth:function(t){var a=e(t.target).closest("tbody").find("span").index(e(t.target));c.month(a),s===m?(Z(p.clone().year(c.year()).month(c.month())),n.inline||$()):(B(-1),R()),q("M")},selectYear:function(t){var a=parseInt(e(t.target).text(),10)||0;c.year(a),s===m?(Z(p.clone().year(c.year())),n.inline||$()):(B(-1),R()),q("YYYY")},selectDecade:function(t){var a=parseInt(e(t.target).data("selection"),10)||0;c.year(a),s===m?(Z(p.clone().year(c.year())),n.inline||$()):(B(-1),R()),q("YYYY")},selectDay:function(t){var a=c.clone();e(t.target).is(".old")&&a.subtract(1,"M"),e(t.target).is(".new")&&a.add(1,"M"),Z(a.date(parseInt(e(t.target).text(),10))),x()||n.keepOpen||n.inline||$()},incrementHours:function(){var e=p.clone().add(1,"h");z(e,"h")&&Z(e)},incrementMinutes:function(){var e=p.clone().add(n.stepping,"m");z(e,"m")&&Z(e)},incrementSeconds:function(){var e=p.clone().add(1,"s");z(e,"s")&&Z(e)},decrementHours:function(){var e=p.clone().subtract(1,"h");z(e,"h")&&Z(e)},decrementMinutes:function(){var e=p.clone().subtract(n.stepping,"m");z(e,"m")&&Z(e)},decrementSeconds:function(){var e=p.clone().subtract(1,"s");z(e,"s")&&Z(e)},togglePeriod:function(){Z(p.clone().add(p.hours()>=12?-12:12,"h"))},togglePicker:function(t){var a,r=e(t.target),i=r.closest("ul"),o=i.find(".in"),d=i.find(".collapse:not(.in)");if(o&&o.length){if(a=o.data("collapse"),a&&a.transitioning)return;o.collapse?(o.collapse("hide"),d.collapse("show")):(o.removeClass("in"),d.addClass("in")),r.is("span")?r.toggleClass(n.icons.time+" "+n.icons.date):r.find("span").toggleClass(n.icons.time+" "+n.icons.date)}},showPicker:function(){h.find(".timepicker > div:not(.timepicker-picker)").hide(),h.find(".timepicker .timepicker-picker").show()},showHours:function(){h.find(".timepicker .timepicker-picker").hide(),h.find(".timepicker .timepicker-hours").show()},showMinutes:function(){h.find(".timepicker .timepicker-picker").hide(),h.find(".timepicker .timepicker-minutes").show()},showSeconds:function(){h.find(".timepicker .timepicker-picker").hide(),h.find(".timepicker .timepicker-seconds").show()},selectHour:function(t){var a=parseInt(e(t.target).text(),10);i||(p.hours()>=12?12!==a&&(a+=12):12===a&&(a=0)),Z(p.clone().hours(a)),te.showPicker.call(l)},selectMinute:function(t){Z(p.clone().minutes(parseInt(e(t.target).text(),10))),te.showPicker.call(l)},selectSecond:function(t){Z(p.clone().seconds(parseInt(e(t.target).text(),10))),te.showPicker.call(l)},clear:ee,today:function(){z(t(),"d")&&Z(t())},close:$},ae=function(t){return e(t.currentTarget).is(".disabled")?!1:(te[e(t.currentTarget).data("action")].apply(l,arguments),!1)},ne=function(){var a,i={year:function(e){return e.month(0).date(1).hours(0).seconds(0).minutes(0)},month:function(e){return e.date(1).hours(0).seconds(0).minutes(0)},day:function(e){return e.hours(0).seconds(0).minutes(0)},hour:function(e){return e.seconds(0).minutes(0)},minute:function(e){return e.seconds(0)}};return r.prop("disabled")||!n.ignoreReadonly&&r.prop("readonly")||h?l:(void 0!==r.val()&&0!==r.val().trim().length?Z(ie(r.val().trim())):n.useCurrent&&f&&(r.is("input")&&0===r.val().trim().length||n.inline)&&(a=t(),"string"==typeof n.useCurrent&&(a=i[n.useCurrent](a)),Z(a)),h=E(),j(),_(),h.find(".timepicker-hours").hide(),h.find(".timepicker-minutes").hide(),h.find(".timepicker-seconds").hide(),X(),B(),e(window).on("resize",H),h.on("click","[data-action]",ae),h.on("mousedown",!1),u&&u.hasClass("btn")&&u.toggleClass("active"),h.show(),H(),n.focusOnShow&&!r.is(":focus")&&r.focus(),Y({type:"dp.show"}),l)},re=function(){return h?$():ne()},ie=function(e){return e=void 0===n.parseInputDate?t.isMoment(e)||e instanceof Date?t(e):t(e,d,n.useStrict):n.parseInputDate(e),e.locale(n.locale),e},oe=function(e){var t,a,r,i,o=null,d=[],s={},p=e.which,c="p";k[p]=c;for(t in k)k.hasOwnProperty(t)&&k[t]===c&&(d.push(t),parseInt(t,10)!==p&&(s[t]=!0));for(t in n.keyBinds)if(n.keyBinds.hasOwnProperty(t)&&"function"==typeof n.keyBinds[t]&&(r=t.split(" "),r.length===d.length&&C[p]===r[r.length-1])){for(i=!0,a=r.length-2;a>=0;a--)if(!(C[r[a]]in s)){i=!1;break}if(i){o=n.keyBinds[t];break}}o&&(o.call(l,h),e.stopPropagation(),e.preventDefault())},de=function(e){k[e.which]="r",e.stopPropagation(),e.preventDefault()},se=function(t){var a=e(t.target).val().trim(),n=a?ie(a):null;return Z(n),t.stopImmediatePropagation(),!1},le=function(){r.on({change:se,blur:n.debug?"":$,keydown:oe,keyup:de,focus:n.allowInputToggle?ne:""}),a.is("input")?r.on({focus:ne}):u&&(u.on("click",re),u.on("mousedown",!1))},pe=function(){r.off({change:se,blur:blur,keydown:oe,keyup:de,focus:n.allowInputToggle?$:""}),a.is("input")?r.off({focus:ne}):u&&(u.off("click",re),u.off("mousedown",!1))},ce=function(t){var a={};return e.each(t,function(){var e=ie(this);e.isValid()&&(a[e.format("YYYY-MM-DD")]=!0)}),Object.keys(a).length?a:!1},fe=function(t){var a={};return e.each(t,function(){a[this]=!0}),Object.keys(a).length?a:!1},ue=function(){var e=n.format||"L LT";o=e.replace(/(\[[^\[]*\])|(\\)?(LTS|LT|LL?L?L?|l{1,4})/g,function(e){var t=p.localeData().longDateFormat(e)||e;return t.replace(/(\[[^\[]*\])|(\\)?(LTS|LT|LL?L?L?|l{1,4})/g,function(e){return p.localeData().longDateFormat(e)||e})}),d=n.extraFormats?n.extraFormats.slice():[],d.indexOf(e)<0&&d.indexOf(o)<0&&d.push(o),i=o.toLowerCase().indexOf("a")<1&&o.replace(/\[.*?\]/g,"").indexOf("h")<1,D("y")&&(m=2),D("M")&&(m=1),D("d")&&(m=0),s=Math.max(m,s),f||Z(p)};if(l.destroy=function(){$(),pe(),a.removeData("DateTimePicker"),a.removeData("date")},l.toggle=re,l.show=ne,l.hide=$,l.disable=function(){return $(),u&&u.hasClass("btn")&&u.addClass("disabled"),r.prop("disabled",!0),l},l.enable=function(){return u&&u.hasClass("btn")&&u.removeClass("disabled"),r.prop("disabled",!1),l},l.ignoreReadonly=function(e){if(0===arguments.length)return n.ignoreReadonly;if("boolean"!=typeof e)throw new TypeError("ignoreReadonly () expects a boolean parameter");return n.ignoreReadonly=e,l},l.options=function(t){if(0===arguments.length)return e.extend(!0,{},n);if(!(t instanceof Object))throw new TypeError("options() options parameter should be an object");return e.extend(!0,n,t),e.each(n,function(e,t){if(void 0===l[e])throw new TypeError("option "+e+" is not recognized!");l[e](t)}),l},l.date=function(e){if(0===arguments.length)return f?null:p.clone();if(!(null===e||"string"==typeof e||t.isMoment(e)||e instanceof Date))throw new TypeError("date() parameter must be one of [null, string, moment or Date]");return Z(null===e?null:ie(e)),l},l.format=function(e){if(0===arguments.length)return n.format;if("string"!=typeof e&&("boolean"!=typeof e||e!==!1))throw new TypeError("format() expects a sting or boolean:false parameter "+e);return n.format=e,o&&ue(),l},l.dayViewHeaderFormat=function(e){if(0===arguments.length)return n.dayViewHeaderFormat;if("string"!=typeof e)throw new TypeError("dayViewHeaderFormat() expects a string parameter");return n.dayViewHeaderFormat=e,l},l.extraFormats=function(e){if(0===arguments.length)return n.extraFormats;if(e!==!1&&!(e instanceof Array))throw new TypeError("extraFormats() expects an array or false parameter");return n.extraFormats=e,d&&ue(),l},l.disabledDates=function(t){if(0===arguments.length)return n.disabledDates?e.extend({},n.disabledDates):n.disabledDates;if(!t)return n.disabledDates=!1,X(),l;if(!(t instanceof Array))throw new TypeError("disabledDates() expects an array parameter");return n.disabledDates=ce(t),n.enabledDates=!1,X(),l},l.enabledDates=function(t){if(0===arguments.length)return n.enabledDates?e.extend({},n.enabledDates):n.enabledDates;if(!t)return n.enabledDates=!1,X(),l;if(!(t instanceof Array))throw new TypeError("enabledDates() expects an array parameter");return n.enabledDates=ce(t),n.disabledDates=!1,X(),l},l.daysOfWeekDisabled=function(e){if(0===arguments.length)return n.daysOfWeekDisabled.splice(0);if("boolean"==typeof e&&!e)return n.daysOfWeekDisabled=!1,X(),l;if(!(e instanceof Array))throw new TypeError("daysOfWeekDisabled() expects an array parameter");if(n.daysOfWeekDisabled=e.reduce(function(e,t){return t=parseInt(t,10),t>6||0>t||isNaN(t)?e:(-1===e.indexOf(t)&&e.push(t),e)},[]).sort(),n.useCurrent&&!n.keepInvalid){for(var t=0;!z(p,"d");){if(p.add(1,"d"),7===t)throw"Tried 7 times to find a valid date";t++}Z(p)}return X(),l},l.maxDate=function(e){if(0===arguments.length)return n.maxDate?n.maxDate.clone():n.maxDate;if("boolean"==typeof e&&e===!1)return n.maxDate=!1,X(),l;"string"==typeof e&&("now"!==e&&"moment"!==e||(e=t()));var a=ie(e);if(!a.isValid())throw new TypeError("maxDate() Could not parse date parameter: "+e);if(n.minDate&&a.isBefore(n.minDate))throw new TypeError("maxDate() date parameter is before options.minDate: "+a.format(o));return n.maxDate=a,n.useCurrent&&!n.keepInvalid&&p.isAfter(e)&&Z(n.maxDate),c.isAfter(a)&&(c=a.clone().subtract(n.stepping,"m")),X(),l},l.minDate=function(e){if(0===arguments.length)return n.minDate?n.minDate.clone():n.minDate;if("boolean"==typeof e&&e===!1)return n.minDate=!1,X(),l;"string"==typeof e&&("now"!==e&&"moment"!==e||(e=t()));var a=ie(e);if(!a.isValid())throw new TypeError("minDate() Could not parse date parameter: "+e);if(n.maxDate&&a.isAfter(n.maxDate))throw new TypeError("minDate() date parameter is after options.maxDate: "+a.format(o));return n.minDate=a,n.useCurrent&&!n.keepInvalid&&p.isBefore(e)&&Z(n.minDate),c.isBefore(a)&&(c=a.clone().add(n.stepping,"m")),X(),l},l.defaultDate=function(e){if(0===arguments.length)return n.defaultDate?n.defaultDate.clone():n.defaultDate;if(!e)return n.defaultDate=!1,l;"string"==typeof e&&("now"!==e&&"moment"!==e||(e=t()));var a=ie(e);if(!a.isValid())throw new TypeError("defaultDate() Could not parse date parameter: "+e);if(!z(a))throw new TypeError("defaultDate() date passed is invalid according to component setup validations");return n.defaultDate=a,(n.defaultDate&&n.inline||""===r.val().trim()&&void 0===r.attr("placeholder"))&&Z(n.defaultDate),l},l.locale=function(e){if(0===arguments.length)return n.locale;if(!t.localeData(e))throw new TypeError("locale() locale "+e+" is not loaded from moment locales!");return n.locale=e,p.locale(n.locale),c.locale(n.locale),o&&ue(),h&&($(),ne()),l},l.stepping=function(e){return 0===arguments.length?n.stepping:(e=parseInt(e,10),(isNaN(e)||1>e)&&(e=1),n.stepping=e,l)},l.useCurrent=function(e){var t=["year","month","day","hour","minute"];if(0===arguments.length)return n.useCurrent;if("boolean"!=typeof e&&"string"!=typeof e)throw new TypeError("useCurrent() expects a boolean or string parameter");if("string"==typeof e&&-1===t.indexOf(e.toLowerCase()))throw new TypeError("useCurrent() expects a string parameter of "+t.join(", "));return n.useCurrent=e,l},l.collapse=function(e){if(0===arguments.length)return n.collapse;if("boolean"!=typeof e)throw new TypeError("collapse() expects a boolean parameter");return n.collapse===e?l:(n.collapse=e,h&&($(),ne()),l)},l.icons=function(t){if(0===arguments.length)return e.extend({},n.icons);if(!(t instanceof Object))throw new TypeError("icons() expects parameter to be an Object");return e.extend(n.icons,t),h&&($(),ne()),l},l.tooltips=function(t){if(0===arguments.length)return e.extend({},n.tooltips);if(!(t instanceof Object))throw new TypeError("tooltips() expects parameter to be an Object");return e.extend(n.tooltips,t),h&&($(),ne()),l},l.useStrict=function(e){if(0===arguments.length)return n.useStrict;if("boolean"!=typeof e)throw new TypeError("useStrict() expects a boolean parameter");return n.useStrict=e,l},l.sideBySide=function(e){if(0===arguments.length)return n.sideBySide;if("boolean"!=typeof e)throw new TypeError("sideBySide() expects a boolean parameter");return n.sideBySide=e,h&&($(),ne()),l},l.viewMode=function(e){if(0===arguments.length)return n.viewMode;if("string"!=typeof e)throw new TypeError("viewMode() expects a string parameter");if(-1===b.indexOf(e))throw new TypeError("viewMode() parameter must be one of ("+b.join(", ")+") value");return n.viewMode=e,s=Math.max(b.indexOf(e),m),B(),l},l.toolbarPlacement=function(e){if(0===arguments.length)return n.toolbarPlacement;if("string"!=typeof e)throw new TypeError("toolbarPlacement() expects a string parameter");if(-1===v.indexOf(e))throw new TypeError("toolbarPlacement() parameter must be one of ("+v.join(", ")+") value");return n.toolbarPlacement=e,h&&($(),ne()),l},l.widgetPositioning=function(t){if(0===arguments.length)return e.extend({},n.widgetPositioning);if("[object Object]"!=={}.toString.call(t))throw new TypeError("widgetPositioning() expects an object variable");if(t.horizontal){if("string"!=typeof t.horizontal)throw new TypeError("widgetPositioning() horizontal variable must be a string");if(t.horizontal=t.horizontal.toLowerCase(),-1===g.indexOf(t.horizontal))throw new TypeError("widgetPositioning() expects horizontal parameter to be one of ("+g.join(", ")+")");n.widgetPositioning.horizontal=t.horizontal}if(t.vertical){if("string"!=typeof t.vertical)throw new TypeError("widgetPositioning() vertical variable must be a string");if(t.vertical=t.vertical.toLowerCase(),-1===w.indexOf(t.vertical))throw new TypeError("widgetPositioning() expects vertical parameter to be one of ("+w.join(", ")+")");n.widgetPositioning.vertical=t.vertical}return X(),l},l.calendarWeeks=function(e){if(0===arguments.length)return n.calendarWeeks;if("boolean"!=typeof e)throw new TypeError("calendarWeeks() expects parameter to be a boolean value");return n.calendarWeeks=e,X(),l},l.showTodayButton=function(e){if(0===arguments.length)return n.showTodayButton;if("boolean"!=typeof e)throw new TypeError("showTodayButton() expects a boolean parameter");return n.showTodayButton=e,h&&($(),ne()),l},l.showClear=function(e){if(0===arguments.length)return n.showClear;if("boolean"!=typeof e)throw new TypeError("showClear() expects a boolean parameter");return n.showClear=e,h&&($(),ne()),l},l.widgetParent=function(t){if(0===arguments.length)return n.widgetParent;if("string"==typeof t&&(t=e(t)),null!==t&&"string"!=typeof t&&!(t instanceof e))throw new TypeError("widgetParent() expects a string or a jQuery object parameter");return n.widgetParent=t,h&&($(),ne()),l},l.keepOpen=function(e){if(0===arguments.length)return n.keepOpen;if("boolean"!=typeof e)throw new TypeError("keepOpen() expects a boolean parameter");return n.keepOpen=e,l},l.focusOnShow=function(e){if(0===arguments.length)return n.focusOnShow;if("boolean"!=typeof e)throw new TypeError("focusOnShow() expects a boolean parameter");return n.focusOnShow=e,l},l.inline=function(e){if(0===arguments.length)return n.inline;if("boolean"!=typeof e)throw new TypeError("inline() expects a boolean parameter");return n.inline=e,l},l.clear=function(){return ee(),l},l.keyBinds=function(e){return n.keyBinds=e,l},l.debug=function(e){if("boolean"!=typeof e)throw new TypeError("debug() expects a boolean parameter");return n.debug=e,l},l.allowInputToggle=function(e){if(0===arguments.length)return n.allowInputToggle;if("boolean"!=typeof e)throw new TypeError("allowInputToggle() expects a boolean parameter");return n.allowInputToggle=e,l},l.showClose=function(e){if(0===arguments.length)return n.showClose;if("boolean"!=typeof e)throw new TypeError("showClose() expects a boolean parameter");return n.showClose=e,l},l.keepInvalid=function(e){if(0===arguments.length)return n.keepInvalid;if("boolean"!=typeof e)throw new TypeError("keepInvalid() expects a boolean parameter");return n.keepInvalid=e,l},l.datepickerInput=function(e){if(0===arguments.length)return n.datepickerInput;if("string"!=typeof e)throw new TypeError("datepickerInput() expects a string parameter");return n.datepickerInput=e,l},l.parseInputDate=function(e){if(0===arguments.length)return n.parseInputDate;if("function"!=typeof e)throw new TypeError("parseInputDate() sholud be as function");return n.parseInputDate=e,l},l.disabledTimeIntervals=function(t){if(0===arguments.length)return n.disabledTimeIntervals?e.extend({},n.disabledTimeIntervals):n.disabledTimeIntervals;if(!t)return n.disabledTimeIntervals=!1,X(),l;if(!(t instanceof Array))throw new TypeError("disabledTimeIntervals() expects an array parameter");return n.disabledTimeIntervals=t,X(),l},l.disabledHours=function(t){if(0===arguments.length)return n.disabledHours?e.extend({},n.disabledHours):n.disabledHours;if(!t)return n.disabledHours=!1,X(),l;if(!(t instanceof Array))throw new TypeError("disabledHours() expects an array parameter");if(n.disabledHours=fe(t),n.enabledHours=!1,n.useCurrent&&!n.keepInvalid){for(var a=0;!z(p,"h");){if(p.add(1,"h"),24===a)throw"Tried 24 times to find a valid date";a++}Z(p)}return X(),l},l.enabledHours=function(t){if(0===arguments.length)return n.enabledHours?e.extend({},n.enabledHours):n.enabledHours;if(!t)return n.enabledHours=!1,X(),l;if(!(t instanceof Array))throw new TypeError("enabledHours() expects an array parameter");if(n.enabledHours=fe(t),n.disabledHours=!1,n.useCurrent&&!n.keepInvalid){for(var a=0;!z(p,"h");){if(p.add(1,"h"),24===a)throw"Tried 24 times to find a valid date";a++}Z(p)}return X(),l},l.viewDate=function(e){if(0===arguments.length)return c.clone();if(!e)return c=p.clone(),l;if(!("string"==typeof e||t.isMoment(e)||e instanceof Date))throw new TypeError("viewDate() parameter must be one of [string, moment or Date]");return c=ie(e),q(),l},a.is("input"))r=a;else if(r=a.find(n.datepickerInput),0===r.size())r=a.find("input");else if(!r.is("input"))throw new Error('CSS class "'+n.datepickerInput+'" cannot be applied to non input element');if(a.hasClass("input-group")&&(u=0===a.find(".datepickerbutton").size()?a.find(".input-group-addon"):a.find(".datepickerbutton")),!n.inline&&!r.is("input"))throw new Error("Could not initialize DateTimePicker without an input element");return e.extend(!0,n,I()),l.options(n),ue(),le(),r.prop("disabled")&&l.disable(),r.is("input")&&0!==r.val().trim().length?Z(ie(r.val().trim())):n.defaultDate&&void 0===r.attr("placeholder")&&Z(n.defaultDate),n.inline&&ne(),l};e.fn.datetimepicker=function(t){return this.each(function(){var n=e(this);n.data("DateTimePicker")||(t=e.extend(!0,{},e.fn.datetimepicker.defaults,t),n.data("DateTimePicker",a(n,t)))})},e.fn.datetimepicker.defaults={format:!1,dayViewHeaderFormat:"MMMM YYYY",extraFormats:!1,stepping:1,minDate:!1,maxDate:!1,useCurrent:!0,collapse:!0,locale:t.locale(),defaultDate:!1,disabledDates:!1,enabledDates:!1,icons:{time:"arf-glyphicon arf-glyphicon-time",date:"arf-glyphicon arf-glyphicon-calendar",up:"arf-glyphicon arf-glyphicon-chevron-up",down:"arf-glyphicon arf-glyphicon-chevron-down",previous:jQuery("body").hasClass("rtl")?"arf-glyphicon arf-glyphicon-chevron-right":"arf-glyphicon arf-glyphicon-chevron-left",next:jQuery("body").hasClass("rtl")?"arf-glyphicon arf-glyphicon-chevron-left":"arf-glyphicon arf-glyphicon-chevron-right",today:"arf-glyphicon arf-glyphicon-screenshot",clear:"arf-glyphicon arf-glyphicon-trash",close:"arf-glyphicon arf-glyphicon-remove"},tooltips:{today:"Go to today",clear:"Clear selection",close:"Close the picker",selectMonth:"Select Month",prevMonth:"Previous Month",nextMonth:"Next Month",selectYear:"Select Year",prevYear:"Previous Year",nextYear:"Next Year",selectDecade:"Select Decade",prevDecade:"Previous Decade",nextDecade:"Next Decade",prevCentury:"Previous Century",nextCentury:"Next Century"},useStrict:!1,sideBySide:!1,daysOfWeekDisabled:!1,calendarWeeks:!1,viewMode:"days",toolbarPlacement:"default",showTodayButton:!1,showClear:!1,showClose:!1,widgetPositioning:{horizontal:"auto",vertical:"auto"},widgetParent:null,ignoreReadonly:!1,keepOpen:!1,focusOnShow:!0,inline:!1,keepInvalid:!1,datepickerInput:".datepickerinput",keyBinds:{up:function(e){if(e){var a=this.date()||t();e.find(".datepicker").is(":visible")?this.date(a.clone().subtract(7,"d")):this.date(a.clone().add(this.stepping(),"m"))}},down:function(e){if(!e)return void this.show();var a=this.date()||t();e.find(".datepicker").is(":visible")?this.date(a.clone().add(7,"d")):this.date(a.clone().subtract(this.stepping(),"m"))},"control up":function(e){if(e){var a=this.date()||t();e.find(".datepicker").is(":visible")?this.date(a.clone().subtract(1,"y")):this.date(a.clone().add(1,"h"))}},"control down":function(e){if(e){var a=this.date()||t();e.find(".datepicker").is(":visible")?this.date(a.clone().add(1,"y")):this.date(a.clone().subtract(1,"h"))}},left:function(e){if(e){var a=this.date()||t();e.find(".datepicker").is(":visible")&&this.date(a.clone().subtract(1,"d"))}},right:function(e){if(e){var a=this.date()||t();e.find(".datepicker").is(":visible")&&this.date(a.clone().add(1,"d"))}},pageUp:function(e){if(e){var a=this.date()||t();e.find(".datepicker").is(":visible")&&this.date(a.clone().subtract(1,"M"))}},pageDown:function(e){if(e){var a=this.date()||t();e.find(".datepicker").is(":visible")&&this.date(a.clone().add(1,"M"))}},enter:function(){this.hide()},escape:function(){this.hide()},"control space":function(e){e.find(".timepicker").is(":visible")&&e.find('.btn[data-action="togglePeriod"]').click()},t:function(){this.date(t())},"delete":function(){this.clear()}},debug:!1,allowInputToggle:!1,disabledTimeIntervals:!1,disabledHours:!1,enabledHours:!1,viewDate:!1}});