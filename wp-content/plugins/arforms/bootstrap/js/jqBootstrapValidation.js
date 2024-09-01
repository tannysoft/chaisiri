!function(m){var o=[],e={options:{prependExistingHelpBlock:!1,sniffHtml:!0,preventSubmit:!0,submitError:!1,submitSuccess:!1,semanticallyStrict:!1,autoAdd:{helpBlocks:!0},filter:function(){return!(m(this).parents(".control-group").length<1)&&(m(this).hasClass("arf_color_picker_input")?m(this).parents(".arf_field_type_colorpicker").is(":visible"):m(this).hasClass("arf_star_rating_input")?m(this).parents(".arf_field_type_scale").is(":visible"):"arf_google_recaptcha_response"==m(this).attr("id")?m(this).parent().is(":visible"):(isMaterialForm=0<m(this).parents(".arf_materialize_form").length,isMaterialOutlineForm=0<m(this).parents(".arf_material_outline_form").length,((isMaterialForm||isMaterialOutlineForm)&&m(this).hasClass("arf-selectpicker-input-control")&&!m(this).is(":visible")&&m(this).parents(".control-group").is(":visible")?m(this).parents(".control-group"):isMaterialOutlineForm&&m(this).hasClass("arf_signature_output_input")?m(this).parent():m(this)).is(":visible")))}},methods:{init:function(a){var c=m.extend(!0,{},e);c.options=m.extend(!0,c.options,a);a=m.uniqueSort(this.map(function(){return m(this).parents("form")[0]}).toArray());return m(a).on("submit",function(a){var e=m(this);window.arf_is_submitting_form=!0;var t=0,i=e.find("input,textarea,select").not("[type=submit],[type=image],[data-jqvalidate='false'],[disabled='disabled']").filter(c.options.filter);i.each(function(a,e){e=m(e),e.parents(".control-group").first().find(".help-block").first();e.trigger("change")}),i.trigger("submit.validation").trigger("validationLostFocus.validation"),i.each(function(a,e){e=m(e).parents(".control-group").first();e.hasClass("arf_warning")&&(e.removeClass("arf_warning").addClass("arf_error"),t++)}),i.trigger("validationLostFocus.validation"),t?(c.options.preventSubmit&&a.preventDefault(),e.addClass("arf_error"),"function"==typeof c.options.submitError&&c.options.submitError(e,a,i.jqBootstrapValidation("collectErrors",!0))):(e.removeClass("arf_error"),"function"==typeof c.options.submitSuccess&&c.options.submitSuccess(e,a))}),this.each(function(){var s=m(this);if(void 0===s.attr("data-jqvalidate")||"false"!=s.attr("data-jqvalidate")){var a,e,d=s.parents(".control-group").first(),t=d.find(".help-block").first(),i=(s.parents("form").first(),[]);!t.length&&c.options.autoAdd&&c.options.autoAdd.helpBlocks&&(t=m('<div class="help-block" />'),d.find(".controls").append(t),o.push(t[0])),c.options.sniffHtml&&(a="",void 0!==s.attr("pattern")&&(a="Not in the expected format\x3c!-- data-validation-pattern-message to override --\x3e",s.data("validationPatternMessage")&&(a=s.data("validationPatternMessage")),s.data("validationPatternMessage",a),s.data("validationPatternRegex",s.attr("pattern"))),void 0===s.attr("max")&&void 0===s.attr("aria-valuemax")||(a="Too high: Maximum of '"+(e=void 0!==s.attr("max")?s.attr("max"):s.attr("aria-valuemax"))+"'\x3c!-- data-validation-max-message to override --\x3e",s.data("validationMaxMessage")&&(a=s.data("validationMaxMessage")),s.data("validationMaxMessage",a),s.data("validationMaxMax",e)),void 0===s.attr("min")&&void 0===s.attr("aria-valuemin")||(a="Too low: Minimum of '"+(e=void 0!==s.attr("min")?s.attr("min"):s.attr("aria-valuemin"))+"'\x3c!-- data-validation-min-message to override --\x3e",s.data("validationMinMessage")&&(a=s.data("validationMinMessage")),s.data("validationMinMessage",a),s.data("validationMinMin",e)),void 0!==s.attr("maxlength")&&(a="Too long: Maximum of '"+s.attr("maxlength")+"' characters\x3c!-- data-validation-maxlength-message to override --\x3e",s.data("validationMaxlengthMessage")&&(a=s.data("validationMaxlengthMessage")),s.data("validationMaxlengthMessage",a),s.data("validationMaxlengthMaxlength",s.attr("maxlength"))),void 0!==s.attr("minlength")&&(a="Too short: Minimum of '"+s.attr("minlength")+"' characters\x3c!-- data-validation-minlength-message to override --\x3e",s.data("validationMinlengthMessage")&&(a=s.data("validationMinlengthMessage")),s.data("validationMinlengthMessage",a),s.data("validationMinlengthMinlength",s.attr("minlength"))),void 0===s.attr("required")&&void 0===s.attr("aria-required")||(a=c.builtInValidators.required.message,s.data("validationRequiredMessage")&&(a=s.data("validationRequiredMessage")),s.data("validationRequiredMessage",a)),void 0!==s.attr("type")&&"number"===s.attr("type").toLowerCase()&&(a=c.builtInValidators.number.message,s.data("validationNumberMessage")&&(a=s.data("validationNumberMessage")),s.data("validationNumberMessage",a)),void 0!==s.attr("type")&&"email"===s.attr("type").toLowerCase()&&(a="Not a valid email address\x3c!-- data-validator-validemail-message to override --\x3e",s.data("validationValidemailMessage")?a=s.data("validationValidemailMessage"):s.data("validationEmailMessage")&&(a=s.data("validationEmailMessage")),s.data("validationValidemailMessage",a)),void 0!==s.attr("minchecked")&&(a="Not enough options checked; Minimum of '"+s.attr("minchecked")+"' required\x3c!-- data-validation-minchecked-message to override --\x3e",s.data("validationMincheckedMessage")&&(a=s.data("validationMincheckedMessage")),s.data("validationMincheckedMessage",a),s.data("validationMincheckedMinchecked",s.attr("minchecked"))),void 0!==s.attr("maxchecked")&&(a="Too many options checked; Maximum of '"+s.attr("maxchecked")+"' required\x3c!-- data-validation-maxchecked-message to override --\x3e",s.data("validationMaxcheckedMessage")&&(a=s.data("validationMaxcheckedMessage")),s.data("validationMaxcheckedMessage",a),s.data("validationMaxcheckedMaxchecked",s.attr("maxchecked"))),void 0!==s.attr("maxselected")&&(a="Too many options selected; Maximum of '"+s.attr("maxselected")+"' required\x3c!-- data-validation-maxselected-message to override --\x3e",s.data("validationMaxselectedMessage")&&(a=s.data("validationMaxselectedMessage")),s.data("validationMaxselectedMessage",a),s.data("validationMaxselectedMaxselected",s.attr("maxselected"))),void 0!==s.attr("minselected")&&(a="Not enough options selected; Minimum of '"+s.attr("minselected")+"' required\x3c!-- data-validation-minselected-message to override --\x3e",s.data("validationMinselectedMessage")&&(a=s.data("validationMinselectedMessage")),s.data("validationMinselectedMessage",a),s.data("validationMinselectedMinselected",s.attr("minselected")))),void 0!==s.data("validation")&&(i=s.data("validation").split(",")),m.each(s.data(),function(a,e){a=a.replace(/([A-Z])/g,",$1").split(",");"validation"===a[0]&&a[1]&&i.push(a[1])});for(var n=i,r=[];m.each(i,function(a,e){i[a]=u(e)}),i=m.uniqueSort(i),r=[],m.each(n,function(a,e){void 0!==s.data("validation"+e+"Shortcut")?m.each(s.data("validation"+e+"Shortcut").split(","),function(a,e){r.push(e)}):!c.builtInValidators[e.toLowerCase()]||"shortcut"===(e=c.builtInValidators[e.toLowerCase()]).type.toLowerCase()&&m.each(e.shortcut.split(","),function(a,e){e=u(e),r.push(e),i.push(e)})}),0<(n=r).length;);var l={};m.each(i,function(a,t){var i,n,e=void 0!==(o=s.data("validation"+t+"Message")),r=!1,o=o||"This field cannot be blank. \x3c!-- Add attribute 'data-validation-"+t.toLowerCase()+"-message' to input to change this message --\x3e";m.each(c.validatorTypes,function(a,e){void 0===l[a]&&(l[a]=[]),r||void 0===s.data("validation"+t+u(e.name))||(l[a].push(m.extend(!0,{name:u(e.name),message:o},e.init(s,t))),r=!0)}),!r&&c.builtInValidators[t.toLowerCase()]&&(i=m.extend(!0,{},c.builtInValidators[t.toLowerCase()]),e&&(i.message=o),"shortcut"===(n=i.type.toLowerCase())?r=!0:m.each(c.validatorTypes,function(a,e){void 0===l[a]&&(l[a]=[]),r||n!==a.toLowerCase()||(s.data("validation"+t+u(e.name),i[e.name.toLowerCase()]),l[n].push(m.extend(i,e.init(s,t))),r=!0)})),r||m.error("Cannot find validation info for '"+t+"'")}),t.data("original-contents",t.data("original-contents")?t.data("original-contents"):t.html()),t.data("original-role",t.data("original-role")?t.data("original-role"):t.attr("role")),d.data("original-classes",d.data("original-clases")?d.data("original-classes"):d.attr("class")),s.data("original-aria-invalid",s.data("original-aria-invalid")?s.data("original-aria-invalid"):s.attr("aria-invalid")),s.on("validation.validation",function(a,e){var i=v(s),n=[];return null==i&&(i=""),m.each(l,function(t,a){void 0!==i&&(i||i.length||e&&e.includeEmpty||c.validatorTypes[t].blockSubmit&&e&&e.submitting)&&m.each(a,function(a,e){c.validatorTypes[t].validate(s,i,e)&&n.push(e.message)})}),n}),s.on("getValidators.validation",function(){return l}),s.on("submit.validation",function(){return s.triggerHandler("change.validation",{submitting:!0})}),s.on(["click","change","blur"].join(".validation ")+".validation",function(n,r){var a,e=v(s),o=[],l=jQuery(s.closest("form")[0]).hasClass("arfliteshowmainform")||!1;d.find("input,textarea,select").each(function(a,e){var t,i=o.length;"focusout"==n.type&&"tel"==s.attr("type")||void 0!==s.attr("data-mask")?void 0===s.attr("data-mask")||"blur"!=n.type&&!window.arf_is_submitting_form?setTimeout(function(){m.each(m(e).triggerHandler("validation.validation",r),function(a,e){o.push(e)})}):m.each(m(e).triggerHandler("validation.validation",r),function(a,e){o.push(e)}):(m.each(m(e).triggerHandler("validation.validation",r),function(a,e){o.push(e)}),m(e).hasClass("arf_phone_utils")&&(t="field_"+e.id,void 0===window.phone_fields[t]||""==e.value||window.phone_fields[t].isValidNumber()||void 0!==e.getAttribute("data-do-validation")&&null!=e.getAttribute("data-do-validation")&&"true"==e.getAttribute("data-do-validation")&&(t=m(e).attr("data-invalid-format-message")?m(e).attr("data-invalid-format-message"):"Invalid Phone Number",o.push(t)))),o.length>i?m(e).attr("aria-invalid","true"):(i=s.data("original-aria-invalid"),m(e).attr("aria-invalid",void 0!==i&&i),i=s.closest("form").find('[data-id="form_id"]').val(),"advance"==(l?"advance"==jQuery('[data-id="arflite_form_tooltip_error_'+i+'"]').val()?"advance":"normal":"advance"==jQuery('[data-id="form_tooltip_error_'+i+'"]').val()?"advance":"normal")&&("true"==d.find("input,select,textarea").attr("aria-invalid")?d.find(".popover").hide():d.find(".popover").remove()))}),(o=m.uniqueSort(o.sort())).length?(d.removeClass("arf_success arf_error").addClass("arf_warning"),c.options.semanticallyStrict&&1===o.length?t.html(o[0]+(c.options.prependExistingHelpBlock?t.data("original-contents"):"")):(a=s.closest("form").find('[data-id="form_id"]').val(),"advance"==(l?"advance"==jQuery('[data-id="arflite_form_tooltip_error_'+a+'"]').val()?"advance":"normal":"advance"==jQuery('[data-id="form_tooltip_error_'+a+'"]').val()?"advance":"normal")?(l?arflite_show_tooltip:arf_show_tooltip)(d,t,""+o):(t.removeClass("arfanimated bounceInDownNor"),(l?arflite_show_tooltip_destroy:arf_show_tooltip_destroy)(d,t,""+o),t.addClass("arfanimated bounceInDownNor"),t.html('<ul role="alert"><li>'+o.join("</li><li>")+"</li></ul>"+(c.options.prependExistingHelpBlock?t.data("original-contents"):"")).show()))):(a=s.closest("form").find('[data-id="form_id"]').val(),"advance"==(l?"advance"==jQuery('[data-id="arflite_form_tooltip_error_'+a+'"]').val()?"advance":"normal":"advance"==jQuery('[data-id="form_tooltip_error_'+a+'"]').val()?"advance":"normal")&&(d.hasClass("error")||d.hasClass("arf_warning")||("true"==d.find("input,select,textarea").attr("aria-invalid")?d.find(".popover").hide():d.find(".popover").remove())),d.removeClass("arf_warning arf_error arf_success"),null!=e&&0<e.length&&d.addClass("arf_success"),t.html(t.data("original-contents")).removeClass("arfanimated bounceInDownNor")),"blur"===n.type&&d.removeClass("arf_success")}),s.on("validationLostFocus.validation",function(){d.removeClass("arf_success")})}})},destroy:function(){return this.each(function(){var a=m(this),e=a.parents(".control-group").first(),t=e.find(".help-block").first();m(this).off("submit"),a.off(".validation"),t.html(t.data("original-contents")),e.attr("class",e.data("original-classes")),a.attr("aria-invalid",a.data("original-aria-invalid")),t.attr("role",a.data("original-role")),-1<o.indexOf(t[0])&&t.remove()})},collectErrors:function(a){var i={};return this.each(function(a,e){var t=m(e),e=t.attr("name"),t=t.triggerHandler("validation.validation",{includeEmpty:!0});i[e]=m.extend(!0,t,i[e])}),m.each(i,function(a,e){0===e.length&&delete i[a]}),i},hasErrors:function(){var t=[];return this.each(function(a,e){t=t.concat(m(e).triggerHandler("getValidators.validation")?m(e).triggerHandler("validation.validation",{submitting:!0}):[])}),0<t.length},override:function(a){e=m.extend(!0,e,a)}},validatorTypes:{callback:{name:"callback",init:function(a,e){return{validatorName:e,callback:a.data("validation"+e+"Callback"),lastValue:a.val(),lastValid:!0,lastFinished:!0}},validate:function(a,e,t){return t.lastValue===e&&t.lastFinished?!t.lastValid:(!0===t.lastFinished&&(t.lastValue=e,t.lastValid=!0,t.lastFinished=!1,n=a,function(a,e){for(var t=Array.prototype.slice.call(arguments).splice(2),i=a.split("."),n=i.pop(),r=0;r<i.length;r++)e=e[i[r]];e[n].apply(this,t)}((i=t).callback,window,a,e,function(a){i.lastValue===a.value&&(i.lastValid=a.valid,a.message&&(i.message=a.message),i.lastFinished=!0,n.data("validation"+i.validatorName+"Message",i.message),setTimeout(function(){n.trigger("change.validation")},1))})),!1);var i,n}},ajax:{name:"ajax",init:function(a,e){return{validatorName:e,url:a.data("validation"+e+"Ajax"),lastValue:a.val(),lastValid:!0,lastFinished:!0}},validate:function(e,a,t){return""+t.lastValue==""+a&&!0===t.lastFinished?!1===t.lastValid:(!0===t.lastFinished&&(t.lastValue=a,t.lastValid=!0,t.lastFinished=!1,m.ajax({url:t.url,data:"value="+a+"&field="+e.attr("name"),dataType:"json",success:function(a){""+t.lastValue==""+a.value&&(t.lastValid=!!a.valid,a.message&&(t.message=a.message),t.lastFinished=!0,e.data("validation"+t.validatorName+"Message",t.message),setTimeout(function(){e.trigger("change.validation")},1))},failure:function(){t.lastValid=!0,t.message="ajax call failed",t.lastFinished=!0,e.data("validation"+t.validatorName+"Message",t.message),setTimeout(function(){e.trigger("change.validation")},1)}})),!1)}},regex:{name:"regex",init:function(a,e){return{regex:(e=a.data("validation"+e+"Regex"),new RegExp("^"+e+"$"))}},validate:function(a,e,t){return!t.regex.test(e)&&!t.negative||t.regex.test(e)&&t.negative}},required:{name:"required",init:function(a,e){return{}},validate:function(a,e,t){var i,n;return!a.hasClass("arf_autocomplete_check_option")||""===e&&null===e||(i=e,n=null==a.attr("data-source")?(n=a.attr("data-id"),jQuery('input[name="item_meta['+n+']"]').attr("data-sep-labels")):a.attr("data-source"),n=JSON.parse(n),-1==(n=Object.values(n)).indexOf(i)&&(a.val(""),e="")),!(0!==e.length||t.negative)||!!(0<e.length&&t.negative)},blockSubmit:!0},match:{name:"match",init:function(a,e){e=a.parents("form").first().find('[name="'+a.data("validation"+e+"Match")+'"]').first();return e.on("validation.validation",function(){""!=a.val()&&a.trigger("change.validation",{submitting:!0})}),{element:e}},validate:function(a,e,t){return e!==t.element.val()&&!t.negative||e===t.element.val()&&t.negative},blockSubmit:!0},max:{name:"max",init:function(a,e){return{max:a.data("validation"+e+"Max")}},validate:function(a,e,t){return parseFloat(e,10)>parseFloat(t.max,10)&&!t.negative||parseFloat(e,10)<=parseFloat(t.max,10)&&t.negative}},min:{name:"min",init:function(a,e){return{min:a.data("validation"+e+"Min")}},validate:function(a,e,t){return parseFloat(e)<parseFloat(t.min)&&!t.negative||parseFloat(e)>=parseFloat(t.min)&&t.negative}},maxlength:{name:"maxlength",init:function(a,e){return{maxlength:a.data("validation"+e+"Maxlength")}},validate:function(a,e,t){return e.length>t.maxlength&&!t.negative||e.length<=t.maxlength&&t.negative}},minlength:{name:"minlength",init:function(a,e){return{minlength:a.data("validation"+e+"Minlength")}},validate:function(a,e,t){return e.length<t.minlength&&!t.negative||e.length>=t.minlength&&t.negative}},maxchecked:{name:"maxchecked",init:function(a,e){var t=a.parents("form").first().find('[name="'+a.attr("name")+'"]');return t.on("click.validation",function(){a.trigger("change.validation",{includeEmpty:!0})}),{maxchecked:a.data("validation"+e+"Maxchecked"),elements:t}},validate:function(a,e,t){return t.elements.filter(":checked").length>t.maxchecked&&!t.negative||t.elements.filter(":checked").length<=t.maxchecked&&t.negative},blockSubmit:!0},strongpass:{name:"strongpass",init:function(a,e){var t=a.parents("form").first().find('[name="'+a.attr("name")+'"]');return t.on("click.validation",function(){a.trigger("change.validation",{includeEmpty:!0})}),{strongpass:a.data("validation"+e),elements:t}},validate:function(a,e,t){var i=new RegExp("^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[~!@#$%^&*\\(\\)\\-_+=|\\\\\\{\\}\\[\\]:;\\\"'<>,.?\\/]).{8,}$","g");return!(i=new RegExp("[^\\x00-\\x7F]+","u").test(e)?new RegExp("(?=.*\\p{L})(?=.*\\p{N})(?=.*[~!@#$%^&*\\(\\)_+=|\\\\\\{\\}\\[\\]:;'<>,.?/]).{8,}","gmu"):i).test(e)},blockSubmit:!0},maxselected:{name:"maxselected",init:function(a,e){var t=a.parents("form").first().find('[name="'+a.attr("name")+'"]');return t.on("click.validation",function(){a.trigger("change.validation",{includeEmpty:!0})}),{maxselected:a.data("validation"+e+"Maxselected"),elements:t}},validate:function(a,e,t){return null!=e&&e.length>t.maxselected&&!t.negative},blockSubmit:!0},minselected:{name:"minselected",init:function(a,e){var t=a.parents("form").first().find('[name="'+a.attr("name")+'"]');return t.on("click.validation",function(){a.trigger("change.validation",{includeEmpty:!0})}),{minselected:a.data("validation"+e+"Minselected"),elements:t}},validate:function(a,e,t){return null!=e&&e.length<t.minselected&&!t.negative},blockSubmit:!0},minchecked:{name:"minchecked",init:function(a,e){var t=a.parents("form").first().find('[name="'+a.attr("name")+'"]');return t.on("click.validation",function(){a.trigger("change.validation",{includeEmpty:!0})}),{minchecked:a.data("validation"+e+"Minchecked"),elements:t}},validate:function(a,e,t){return t.elements.filter(":checked").length<t.minchecked&&!t.negative||t.elements.filter(":checked").length>=t.minchecked&&t.negative},blockSubmit:!0},rating:{name:"rating",init:function(a,e){var t=a.parents("form").first().find('[name="'+a.attr("name")+'"]');return t.on("click.validation",function(){a.trigger("change.validation",{includeEmpty:!0})}),{rating:1,elements:t}},validate:function(a,e,t){for(var i,n=0,r=0;i=t.elements[r];r++)if(jQuery(i).is(":checked")){n=i.value;break}return n<t.rating},blockSubmit:!0},maxupload:{name:"maxupload",init:function(a,e){var t=a.parents("form").first().find('[name="'+a.attr("name")+'"]');return t.on("click.validation",function(){a.trigger("change.validation",{includeEmpty:!0})}),{maxupload:a.data("validation"+e+"Maxupload"),elements:t}},validate:function(a,e,t){return null!=(e=e.split(","))&&e.length>t.maxupload&&!t.negative},blockSubmit:!0}},builtInValidators:{email:{name:"Email",type:"shortcut",shortcut:"validemail"},validemail:{name:"Validemail",type:"regex",regex:"[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,4}",message:"Not a valid email address\x3c!-- data-validator-validemail-message to override --\x3e"},passwordagain:{name:"Passwordagain",type:"match",match:"password",message:"Does not match the given password\x3c!-- data-validator-paswordagain-message to override --\x3e"},positive:{name:"Positive",type:"shortcut",shortcut:"number,positivenumber"},negative:{name:"Negative",type:"shortcut",shortcut:"number,negativenumber"},number:{name:"Number",type:"regex",regex:"([+-]?\\d+(\\.\\d*)?([eE][+-]?[0-9]+)?)?",message:"Must be a number\x3c!-- data-validator-number-message to override --\x3e"},integer:{name:"Integer",type:"regex",regex:"[+-]?\\d+",message:"No decimal places allowed\x3c!-- data-validator-integer-message to override --\x3e"},positivenumber:{name:"Positivenumber",type:"min",min:0,message:"Must be a positive number\x3c!-- data-validator-positivenumber-message to override --\x3e"},negativenumber:{name:"Negativenumber",type:"max",max:0,message:"Must be a negative number\x3c!-- data-validator-negativenumber-message to override --\x3e"},required:{name:"Required",type:"required",message:"This is required\x3c!-- data-validator-required-message to override --\x3e"},checkone:{name:"Checkone",type:"minchecked",minchecked:1,message:"Check at least one option\x3c!-- data-validation-checkone-message to override --\x3e"},selectone:{name:"Selectone",type:"minselected",minselected:1,message:"Check at least one option\x3c!-- data-validation-selectone-message to override --\x3e"},rating:{name:"Rating",type:"rating",minchecked:1,message:"Please give at-least one star"},strongpass:{name:"Strongpass",type:"strongpass",message:"Please enter strong password"}}},u=function(a){return a.toLowerCase().replace(/(^|\s)([a-z])/g,function(a,e,t){return e+t.toUpperCase()})},v=function(a){var e,t,i,n=a.val(),r=a.attr("type"),o=a.attr("id");return void 0!==o&&"arf_google_recaptcha_response"==o&&(n=a.parents(".control-group").find("textarea[id^=g-recaptcha-response]").val()),"file"==r&&""==n&&void 0!==a.attr("multiple")&&"multiple"==a.attr("multiple")&&(e=a.attr("data-form-id"),o=a.attr("data-form-data-id"),i=a.attr("id").replace("field_",""),t=a.attr("data-field-id"),""==(n=null!=jQuery("#uploaded_file_name_"+e+"_"+o+"_"+t)?jQuery("#uploaded_file_name_"+e+"_"+o+"_"+t).val():"")&&null!=document.getElementById("arf_multi_file_uploader_"+i)&&null!=document.getElementById("arf_multi_file_uploader_"+i).querySelector(".arf_info")&&(n=document.getElementById("arf_multi_file_uploader_"+i).querySelector(".arf_info").innerHTML)),!a.hasClass("frm_date")||""==n||void 0!==(t=a.attr("data-off-days"))&&null!=t&&(i=new Date(n).getDay(),t=JSON.parse(t),i=String(i),-1<t.indexOf(i)&&(a.val(""),n="")),"checkbox"==r&&(n=a.is(":checked")?n:""),"radio"==r&&(n=0<m('input[name="'+a.attr("name")+'"]:checked').length?n:""),n=a.hasClass("arf-selectpicker-input-control")&&"text"==r&&m('dl[data-name="'+a.attr("name")).hasClass("multi-select")&&/,/.test(n)?n.split(","):n};m.fn.jqBootstrapValidation=function(a){return e.methods[a]?e.methods[a].apply(this,Array.prototype.slice.call(arguments,1)):"object"!=typeof a&&a?(m.error("Method "+a+" does not exist on jQuery.jqBootstrapValidation"),null):e.methods.init.apply(this,arguments)},m.jqBootstrapValidation=function(a){m(":input").not("[type=image],[type=submit]").jqBootstrapValidation.apply(this,arguments)}}(jQuery);