if(!jQuery().sharrre){
/*!
     *  Sharrre.com - Make your sharing widget!
     *  Version: beta 1.3.5
     *  Author: Julien Hany
     *  License: MIT http://en.wikipedia.org/wiki/MIT_License or GPLv2 http://en.wikipedia.org/wiki/GNU_General_Public_License
     */
(function(r,p,o,w){var q="sharrre",t={className:"sharrre",share:{googlePlus:false,facebook:false,twitter:false,digg:false,delicious:false,stumbleupon:false,linkedin:false,pinterest:false},shareTotal:0,template:"",title:"",url:o.location.href,text:o.title,urlCurl:"sharrre.php",count:{},total:0,shorterTotal:true,enableHover:true,enableCounter:true,enableTracking:false,hover:function(){},hide:function(){},click:function(){},render:function(){},buttons:{googlePlus:{url:"",urlCount:false,size:"medium",lang:"en-US",annotation:""},facebook:{url:"",urlCount:false,action:"like",layout:"button_count",width:"",send:"false",faces:"false",colorscheme:"",font:"",lang:"en_US"},twitter:{url:"",urlCount:false,count:"horizontal",hashtags:"",via:"",related:"",lang:"en"},digg:{url:"",urlCount:false,type:"DiggCompact"},delicious:{url:"",urlCount:false,size:"medium"},stumbleupon:{url:"",urlCount:false,layout:"1"},linkedin:{url:"",urlCount:false,counter:""},pinterest:{url:"",media:"",description:"",layout:"horizontal"}}},v={googlePlus:"",facebook:"https://graph.facebook.com/fql?q=SELECT%20url,%20normalized_url,%20share_count,%20like_count,%20comment_count,%20total_count,commentsbox_count,%20comments_fbid,%20click_count%20FROM%20link_stat%20WHERE%20url=%27{url}%27&callback=?",twitter:"http://cdn.api.twitter.com/1/urls/count.json?url={url}&callback=?",digg:"http://services.digg.com/2.0/story.getInfo?links={url}&type=javascript&callback=?",delicious:"http://feeds.delicious.com/v2/json/urlinfo/data?url={url}&callback=?",stumbleupon:"",linkedin:"http://www.linkedin.com/countserv/count/share?format=jsonp&url={url}&callback=?",pinterest:"http://api.pinterest.com/v1/urls/count.json?url={url}&callback=?"},m={googlePlus:function(a){var c=a.options.buttons.googlePlus;r(a.element).find(".buttons").append('<div class="button googleplus"><div class="g-plusone" data-size="'+c.size+'" data-href="'+(c.url!==""?c.url:a.options.url)+'" data-annotation="'+c.annotation+'"></div></div>');p.___gcfg={lang:a.options.buttons.googlePlus.lang};var b=0;if(typeof gapi==="undefined"&&b==0){b=1;(function(){var f=o.createElement("script");f.type="text/javascript";f.async=true;f.src="//apis.google.com/js/plusone.js";var d=o.getElementsByTagName("script")[0];d.parentNode.insertBefore(f,d)})()}else{gapi.plusone.go()}},facebook:function(a){var c=a.options.buttons.facebook;r(a.element).find(".buttons").append('<div class="button facebook"><div id="fb-root"></div><div class="fb-like" data-href="'+(c.url!==""?c.url:a.options.url)+'" data-send="'+c.send+'" data-layout="'+c.layout+'" data-width="'+c.width+'" data-show-faces="'+c.faces+'" data-action="'+c.action+'" data-colorscheme="'+c.colorscheme+'" data-font="'+c.font+'" data-via="'+c.via+'"></div></div>');var b=0;if(typeof FB==="undefined"&&b==0){b=1;(function(f,i,d){var g,h=f.getElementsByTagName(i)[0];if(f.getElementById(d)){return}g=f.createElement(i);g.id=d;g.src="//connect.facebook.net/"+c.lang+"/all.js#xfbml=1";h.parentNode.insertBefore(g,h)}(o,"script","facebook-jssdk"))}else{FB.XFBML.parse()}},twitter:function(a){var c=a.options.buttons.twitter;r(a.element).find(".buttons").append('<div class="button twitter"><a href="https://twitter.com/share" class="twitter-share-button" data-url="'+(c.url!==""?c.url:a.options.url)+'" data-count="'+c.count+'" data-text="'+a.options.text+'" data-via="'+c.via+'" data-hashtags="'+c.hashtags+'" data-related="'+c.related+'" data-lang="'+c.lang+'">Tweet</a></div>');var b=0;if(typeof twttr==="undefined"&&b==0){b=1;(function(){var d=o.createElement("script");d.type="text/javascript";d.async=true;d.src="//platform.twitter.com/widgets.js";var f=o.getElementsByTagName("script")[0];f.parentNode.insertBefore(d,f)})()}else{r.ajax({url:"//platform.twitter.com/widgets.js",dataType:"script",cache:true})}},digg:function(a){var c=a.options.buttons.digg;r(a.element).find(".buttons").append('<div class="button digg"><a class="DiggThisButton '+c.type+'" rel="nofollow external" href="http://digg.com/submit?url='+encodeURIComponent((c.url!==""?c.url:a.options.url))+'"></a></div>');var b=0;if(typeof __DBW==="undefined"&&b==0){b=1;(function(){var d=o.createElement("SCRIPT"),f=o.getElementsByTagName("SCRIPT")[0];d.type="text/javascript";d.async=true;d.src="//widgets.digg.com/buttons.js";f.parentNode.insertBefore(d,f)})()}},delicious:function(d){if(d.options.buttons.delicious.size=="tall"){var c="width:50px;",f="height:35px;width:50px;font-size:15px;line-height:35px;",a="height:18px;line-height:18px;margin-top:3px;"}else{var c="width:93px;",f="float:right;padding:0 3px;height:20px;width:26px;line-height:20px;",a="float:left;height:20px;line-height:20px;"}var b=d.shorterTotal(d.options.count.delicious);if(typeof b==="undefined"){b=0}r(d.element).find(".buttons").append('<div class="button delicious"><div style="'+c+'font:12px Arial,Helvetica,sans-serif;cursor:pointer;color:#666666;display:inline-block;float:none;height:20px;line-height:normal;margin:0;padding:0;text-indent:0;vertical-align:baseline;"><div style="'+f+'background-color:#fff;margin-bottom:5px;overflow:hidden;text-align:center;border:1px solid #ccc;border-radius:3px;">'+b+'</div><div style="'+a+'display:block;padding:0;text-align:center;text-decoration:none;width:50px;background-color:#7EACEE;border:1px solid #40679C;border-radius:3px;color:#fff;"><img src="http://www.delicious.com/static/img/delicious.small.gif" height="10" width="10" alt="Delicious" /> Add</div></div></div>');r(d.element).find(".delicious").on("click",function(){d.openPopup("delicious")})},stumbleupon:function(a){var c=a.options.buttons.stumbleupon;r(a.element).find(".buttons").append('<div class="button stumbleupon"><su:badge layout="'+c.layout+'" location="'+(c.url!==""?c.url:a.options.url)+'"></su:badge></div>');var b=0;if(typeof STMBLPN==="undefined"&&b==0){b=1;(function(){var f=o.createElement("script");f.type="text/javascript";f.async=true;f.src="//platform.stumbleupon.com/1/widgets.js";var d=o.getElementsByTagName("script")[0];d.parentNode.insertBefore(f,d)})();s=p.setTimeout(function(){if(typeof STMBLPN!=="undefined"){STMBLPN.processWidgets();clearInterval(s)}},500)}else{STMBLPN.processWidgets()}},linkedin:function(a){var c=a.options.buttons.linkedin;r(a.element).find(".buttons").append('<div class="button linkedin"><script type="in/share" data-url="'+(c.url!==""?c.url:a.options.url)+'" data-counter="'+c.counter+'"><\/script></div>');var b=0;if(typeof p.IN==="undefined"&&b==0){b=1;(function(){var f=o.createElement("script");f.type="text/javascript";f.async=true;f.src="//platform.linkedin.com/in.js";var d=o.getElementsByTagName("script")[0];d.parentNode.insertBefore(f,d)})()}else{p.IN.init()}},pinterest:function(a){var b=a.options.buttons.pinterest;r(a.element).find(".buttons").append('<div class="button pinterest"><a href="http://pinterest.com/pin/create/button/?url='+(b.url!==""?b.url:a.options.url)+"&media="+b.media+"&description="+b.description+'" class="pin-it-button" count-layout="'+b.layout+'">Pin It</a></div>');(function(){var d=o.createElement("script");d.type="text/javascript";d.async=true;d.src="//assets.pinterest.com/js/pinit.js";var c=o.getElementsByTagName("script")[0];c.parentNode.insertBefore(d,c)})()}},u={googlePlus:function(){},facebook:function(){fb=p.setInterval(function(){if(typeof FB!=="undefined"){FB.Event.subscribe("edge.create",function(a){_gaq.push(["_trackSocial","facebook","like",a])});FB.Event.subscribe("edge.remove",function(a){_gaq.push(["_trackSocial","facebook","unlike",a])});FB.Event.subscribe("message.send",function(a){_gaq.push(["_trackSocial","facebook","send",a])});clearInterval(fb)}},1000)},twitter:function(){tw=p.setInterval(function(){if(typeof twttr!=="undefined"){twttr.events.bind("tweet",function(a){if(a){_gaq.push(["_trackSocial","twitter","tweet"])}});clearInterval(tw)}},1000)},digg:function(){},delicious:function(){},stumbleupon:function(){},linkedin:function(){function a(){_gaq.push(["_trackSocial","linkedin","share"])}},pinterest:function(){}},x={googlePlus:function(a){p.open("https://plus.google.com/share?hl="+a.buttons.googlePlus.lang+"&url="+encodeURIComponent((a.buttons.googlePlus.url!==""?a.buttons.googlePlus.url:a.url)),"","toolbar=0, status=0, width=900, height=500")},facebook:function(a){p.open("http://www.facebook.com/sharer/sharer.php?u="+encodeURIComponent((a.buttons.facebook.url!==""?a.buttons.facebook.url:a.url))+"&t="+a.text+"","","toolbar=0, status=0, width=900, height=500")},twitter:function(a){p.open("https://twitter.com/intent/tweet?text="+encodeURIComponent(a.text)+"&url="+encodeURIComponent((a.buttons.twitter.url!==""?a.buttons.twitter.url:a.url))+(a.buttons.twitter.via!==""?"&via="+a.buttons.twitter.via:""),"","toolbar=0, status=0, width=650, height=360")},digg:function(a){p.open("http://digg.com/tools/diggthis/submit?url="+encodeURIComponent((a.buttons.digg.url!==""?a.buttons.digg.url:a.url))+"&title="+a.text+"&related=true&style=true","","toolbar=0, status=0, width=650, height=360")},delicious:function(a){p.open("http://www.delicious.com/save?v=5&noui&jump=close&url="+encodeURIComponent((a.buttons.delicious.url!==""?a.buttons.delicious.url:a.url))+"&title="+a.text,"delicious","toolbar=no,width=550,height=550")},stumbleupon:function(a){p.open("http://www.stumbleupon.com/badge/?url="+encodeURIComponent((a.buttons.delicious.url!==""?a.buttons.delicious.url:a.url)),"stumbleupon","toolbar=no,width=550,height=550")},linkedin:function(a){p.open("https://www.linkedin.com/cws/share?url="+encodeURIComponent((a.buttons.delicious.url!==""?a.buttons.delicious.url:a.url))+"&token=&isFramed=true","linkedin","toolbar=no,width=550,height=550")},pinterest:function(a){p.open("http://pinterest.com/pin/create/button/?url="+encodeURIComponent((a.buttons.pinterest.url!==""?a.buttons.pinterest.url:a.url))+"&media="+encodeURIComponent(a.buttons.pinterest.media)+"&description="+a.buttons.pinterest.description,"pinterest","toolbar=no,width=700,height=300")}};function n(b,a){this.element=b;this.options=r.extend(true,{},t,a);this.options.share=a.share;this._defaults=t;this._name=q;this.init()}n.prototype.init=function(){var a=this;if(this.options.urlCurl!==""){v.googlePlus=this.options.urlCurl+"?url={url}&type=googlePlus";v.stumbleupon=this.options.urlCurl+"?url={url}&type=stumbleupon"}r(this.element).addClass(this.options.className);if(typeof r(this.element).data("title")!=="undefined"){this.options.title=r(this.element).attr("data-title")}if(typeof r(this.element).data("url")!=="undefined"){this.options.url=r(this.element).data("url")}if(typeof r(this.element).data("text")!=="undefined"){this.options.text=r(this.element).data("text")}r.each(this.options.share,function(c,b){if(b===true){a.options.shareTotal++}});if(a.options.enableCounter===true){r.each(this.options.share,function(d,b){if(b===true){try{a.getSocialJson(d)}catch(c){}}})}else{if(a.options.template!==""){this.options.render(this,this.options)}else{this.loadButtons()}}r(this.element).hover(function(){if(r(this).find(".buttons").length===0&&a.options.enableHover===true){a.loadButtons()}a.options.hover(a,a.options)},function(){a.options.hide(a,a.options)});r(this.element).click(function(){a.options.click(a,a.options);return false})};n.prototype.loadButtons=function(){var a=this;r(this.element).append('<div class="buttons"></div>');r.each(a.options.share,function(c,b){if(b==true){m[c](a);if(a.options.enableTracking===true){u[c]()}}})};n.prototype.getSocialJson=function(c){var a=this,b=0,d=v[c].replace("{url}",encodeURIComponent(this.options.url));if(this.options.buttons[c].urlCount===true&&this.options.buttons[c].url!==""){d=v[c].replace("{url}",this.options.buttons[c].url)}if(d!=""&&a.options.urlCurl!==""){r.getJSON(d,function(f){if(typeof f.count!=="undefined"){var g=f.count+"";g=g.replace("\u00c2\u00a0","");b+=parseInt(g,10)}else{if(f.data&&f.data.length>0&&typeof f.data[0].total_count!=="undefined"){b+=parseInt(f.data[0].total_count,10)}else{if(typeof f[0]!=="undefined"){b+=parseInt(f[0].total_posts,10)}else{if(typeof f[0]!=="undefined"){}}}}a.options.count[c]=b;a.options.total+=b;a.renderer();a.rendererPerso()}).error(function(){a.options.count[c]=0;a.rendererPerso()})}else{a.renderer();a.options.count[c]=0;a.rendererPerso()}};n.prototype.rendererPerso=function(){var a=0;for(e in this.options.count){a++}if(a===this.options.shareTotal){this.options.render(this,this.options)}};n.prototype.renderer=function(){var b=this.options.total,a=this.options.template;if(this.options.shorterTotal===true){b=this.shorterTotal(b)}if(a!==""){a=a.replace("{total}",b);r(this.element).html(a)}else{r(this.element).html('<div class="box"><a class="count" href="#">'+b+"</a>"+(this.options.title!==""?'<a class="share" href="#">'+this.options.title+"</a>":"")+"</div>")}};n.prototype.shorterTotal=function(a){if(a>=1000000){a=(a/1000000).toFixed(2)+"M"}else{if(a>=1000){a=(a/1000).toFixed(1)+"k"}}return a};n.prototype.openPopup=function(a){x[a](this.options);if(this.options.enableTracking===true){var b={googlePlus:{site:"Google",action:"+1"},facebook:{site:"facebook",action:"like"},twitter:{site:"twitter",action:"tweet"},digg:{site:"digg",action:"add"},delicious:{site:"delicious",action:"add"},stumbleupon:{site:"stumbleupon",action:"add"},linkedin:{site:"linkedin",action:"share"},pinterest:{site:"pinterest",action:"pin"}};_gaq.push(["_trackSocial",b[a].site,b[a].action])}};n.prototype.simulateClick=function(){var a=r(this.element).html();r(this.element).html(a.replace(this.options.total,this.options.total+1))};n.prototype.update=function(a,b){if(a!==""){this.options.url=a}if(b!==""){this.options.text=b}};r.fn[q]=function(b){var a=arguments;if(b===w||typeof b==="object"){return this.each(function(){if(!r.data(this,"plugin_"+q)){r.data(this,"plugin_"+q,new n(this,b))}})}else{if(typeof b==="string"&&b[0]!=="_"&&b!=="init"){return this.each(function(){var c=r.data(this,"plugin_"+q);if(c instanceof n&&typeof c[b]==="function"){c[b].apply(c,Array.prototype.slice.call(a,1))}})}}}})(jQuery,window,document)}jQuery(document).ready(function(){function a(){(function(h){var i=h(".vsu-popup-social-share"),g=i.data("url"),f="mailto:your_friend@domain.com?subject="+encodeURIComponent(VSU_Data.social_title)+"&body="+encodeURIComponent(VSU_Data.social_text)+"%0AFollow the link: "+encodeURIComponent(g);var d={facebook:[VSU_Data.texts.share],twitter:[VSU_Data.texts.tweet],googlePlus:[VSU_Data.texts.share]};if(VSU_Data.linkedin_enabled){var b="http://www.linkedin.com/shareArticle?mini=true&url="+encodeURIComponent(i.data("url"))+"&title="+encodeURIComponent(VSU_Data.social_title)+"&summary="+encodeURIComponent(VSU_Data.social_text)+"&source="+encodeURIComponent(g);d.linkedIn=[VSU_Data.texts.share,b]}d.email=[VSU_Data.texts.email,f];var c="";h.each(d,function(m,l){var j=l[0],k=l[1]||"#";c+='<a href="'+k+'" class="vsu-button vsu-social-button vsu-social-button-'+m+'">'+j+"</a>"});i.sharrre({share:{facebook:true,twitter:true,googlePlus:true,linkedin:true},title:VSU_Data.social_title,text:VSU_Data.social_text,url:i.data("url"),urlCurl:"",template:c,enableHover:false,enableTracking:false,render:function(j){h(j.element).on("click",".vsu-social-button-facebook",function(){j.openPopup("facebook")}).on("click",".vsu-social-button-twitter",function(){j.openPopup("twitter")}).on("click",".vsu-social-button-googlePlus",function(){j.openPopup("googlePlus")}).on("click",".vsu-social-button-linkedIn",function(){window.open(h(this).attr("href"),"","width=600,height=350,scrollbars=yes")}).on("click",".vsu-social-button-email",function(){window.location.href=h(this).attr("href")})}})}(jQuery))}a();(function(g){var f=false,i=false;function h(l){var m=f.find(".vsu-loader").removeClass("vsu-loading");m.find(".vsu-loader-inner").html(l);k();i.fadeIn(200);m.fadeIn(300,function(){a()})}function d(){var l=f.find(".vsu-loader").addClass("vsu-loading");k();i.fadeIn(200);l.fadeIn(300)}function j(){f.fadeOut("fast",function(){f.css({visibility:"hidden"}).find(".vsu-loader-inner").empty()});i.fadeOut(300)}function k(){f.css({visibility:"hidden",display:"block"});var m=f.find(".vsu-loader");var l=m.outerHeight(false);m.css("margin-top",Math.max((g(window).height()-l)/2+g(window).scrollTop(),50)+"px");f.css({display:"block",visibility:"visible"})}g(window).on("resize",function(){if(f!==false&&f.is(":visible")){k()}});function c(n){var m=g('<p class="vsu-antispam-field"/>'),l=g("<label>").text(VSU_Data.texts.antispam).appendTo(m),o=g("<input/>",{name:"vsu_antispam","class":"vsu-antispam-check",type:"checkbox",value:"yes"}).appendTo(l);g('<div class="vsu-tickbox"/>').insertAfter(o);n.append(m)}function b(l){if(!l.find(".vsu-antispam-check").is(":checked")){alert(VSU_Data.texts.antispam_alert);return false}return true}g(".vsu-form").each(function(){var m=g(this);if(m.is(".vsu-antispam")){c(m)}var l=g(this).find(".vsu-error-wrap");if(l.length<=0){g("<div class='vsu-error-wrap'>").prependTo(this)}if(f===false){f=g('<div class="vsu-loader-wrap"/>').appendTo("body").wrapInner('<div class="vsu-loader-inner"/>').wrapInner('<div class="vsu-loader"/>');i=g('<div class="vsu-loader-overlay"/>').insertBefore(f);g("body").on("click",function(){j()});f.find(".vsu-loader").on("click",function(n){n.stopPropagation()});g('<a class="vsu-close-button"/>').text(VSU_Data.texts.close).appendTo(f.find(".vsu-loader")).on("click",function(n){n.preventDefault();j()})}m.data("vsu_http_ref_cache",m.find('[name="vsu_http_ref"]').val()||"")}).on("submit",function(p){p.preventDefault();var m=g(this);if(m.is(".vsu-antispam")){if(!b(m)){return}}var o={action:"vsu_popup_content",vsu_email:m.find('[name="vsu_email"]').val(),vsu_antispam:m.find('[name="vsu_antispam"]:checked').val()},n=m.find('[name="vsu_ref"]'),l=m.find(".vsu-error-wrap");m.find(":input").attr("disabled","disabled");l.empty();d();if(n.length>0){o.vsu_ref=n.val()}o.http_ref=m.data("vsu_http_ref_cache");g.post(VSU_Data.ajaxurl,o,function(q){m.find(":input").removeAttr("disabled");if(q.state==="error"){j();l.html("<p>"+q.message+"</p>")}else{if("popup_html" in q.data){h(q.data.popup_html)}}},"json")})}(jQuery))});