jQuery.noConflict();
var wc = document.createElement("DIV");
function widgetTrigger(type, lang) {
	var uniqID = bcc.uniqID;

    var langg = (lang != '-1' && typeof lang != 'undefined') ? '&lang='+lang : "";
    
    var width = (type == 'custom') ? jQuery('#width').val() : ((type == 'fix') ? 200 : 0);
    var height = (type == 'custom') ? jQuery('#height').val() : ((type == 'fix') ? 350 : 0);

    var bg_color = jQuery('#style').val() ? jQuery('#style').val().replace('#', '') : 'D51D29'
    var font_color = jQuery('#font_color').val() ? jQuery('#font_color').val().replace('#', '') : 'FFFFFF'

    var amount = jQuery('#amount').val() ? jQuery('#amount').val() : '1';

    var from = jQuery('#from').val() ? jQuery('#from').val() : 'EUR';
    var to = jQuery('#to').val() ? jQuery('#to').val() : 'USD';
    
    var gradient = jQuery('#gradient').val() ? jQuery('#gradient').val() : 'on'
    var shadow = jQuery('#shadow').val() ? jQuery('#shadow').val() : 'on'
    var border = jQuery('#border').val() ? jQuery('#border').val() : 'on'
    var flag = jQuery('#flag').val() ? jQuery('#flag').val() : 'on'
    var display = jQuery('#display').val() ? jQuery('#display').val() : 'c'
    var currencies = jQuery('#currencies').val() ? jQuery('#currencies').val() : ''
    if (currencies && currencies.length > 0) {
        currencies = currencies.join(",")
    }
    var symbol = jQuery('#symbol').val() ? jQuery('#symbol').val() : 'off'
    var monetary_format = jQuery('#monetary_format').val() ? jQuery('#monetary_format').val() : '1'
    var decimal_format = jQuery('#decimal_format').val() ? jQuery('#decimal_format').val() : '2'
    var date_format = jQuery('#date_format').val() ? jQuery('#date_format').val() : '1'
    var support = jQuery('#support').val() ? jQuery('#support').val() : 'on'

    wc.id = "wc"+uniqID;
    wc.name = "wc"+uniqID;
    var wc_style = "position:relative;display:inline-block;border:none;min-width:200px;min-height:350px;";
    if (shadow && shadow == 'on') {
        wc_style += "box-shadow:0 0 5px #000;";
    }

    var fr = document.createElement("IFRAME");
    if (width && height) {
        if (width < 200 ) width = 200;
        if (height < 350 ) height = 350;

        fr.width = width+"px";
        fr.height = height+"px";
        wc_style += "width:"+width+"px;";
        wc_style += "height:"+height+"px;";
    }
    else {
        fr.width= "100%";
        if (!height || height < 350 ) height = 350;
        fr.height = height + "px";
        wc_style += "width:100%;";
        wc_style += "height:"+height+"px;";
    }
    wc.style = wc_style;
    wc.innerHTML = ''
    wc.appendChild(fr)

    if(support && support == 'on') {
        var lb=document.createElement("DIV");
        lb.style="position:absolute;display:inline-block;box-sizing:border-box;width:100%;left:0;bottom:0;padding:0 15px;text-align:right;line-height:25px;color:#{{$fc}};font-size:13px;font-family:Arial,Helvetica,sans-serif;"
        var lb_1=document.createElement("A");
        lb_1.innerHTML=from+"/"+to;
        lb_1.href="https://www.currency.wiki/"+from.toLowerCase()+"_"+to.toLowerCase();
        lb_1.rel="nofollow";
        lb_1.target="_blank";
        lb_1.style="float:left;margin:0;padding:0;text-transform:uppercase;line-height:25px;color:#"+font_color+";"
        var lb_2=document.createElement("A");
        lb_2.innerHTML="Currency.Wiki";
        lb_2.href="https://www.currency.wiki";
        lb_2.rel="nofollow";
        lb_2.target="_blank";
        lb_2.style="line-height:25px;margin:0;padding:0;color:#"+font_color+";"
        lb.appendChild(lb_1);
        lb.appendChild(lb_2);
        wc.appendChild(lb);
    }

	fr.id = uniqID;
	fr.name = uniqID;
	fr.style = "border:none;min-width:200px;min-height:350px";
	fr.width = (type == 'custom') ? width+"px" : ((type == 'fix') ? "200px" : "100%");
	fr.height = (type == 'custom') ? height+"px" : ((type == 'fix') ? "350px" : "100%");

	document.getElementById("currency-bcc-"+uniqID).appendChild(wc);
	
    var yp = JSON.stringify({
		cd:uniqID,
        a: amount ? amount : 1, // amount
		w: width, // width
        h: height, // height
        f: from.toUpperCase(), // from currency
        t: to.toUpperCase(), // to currency
        fc: font_color, // font color
        c: bg_color, // bg color
        g: gradient, // gradient bg
        sh: shadow, // shadow
        b: border, // border
        fl: flag, // flag
        p: display, // page - converter or exchange rates
        cs: currencies, // currencies 
        s: symbol, // symbol right, left, off
        mf: monetary_format, // monetary format
        df: decimal_format, // decimal format
        d: date_format, // date
        su: support, // support us
	});
    
	var url = "https://www.currency.wiki/widget/embed?wd=1&f="+from+"&t="+to+"&cs="+currencies+"&d="+date_format+"&tm="+bcc.time+langg;
	url = url.replace(/\"/g, "");
    fr.setAttribute("src", url);
	var w = window.frames[uniqID];
	fr.onload = function() {
		w.postMessage({"t": yp}, "*");
	}
}

function preview() {
    var type = jQuery('input[name="size"]:checked').val();  
    var lang = (jQuery('#lang').val() != "-1") ? ' lang="'+jQuery('#lang').val()+'"' : "";

    var short_code = '[currency_bcc type="'+type+'"'

    var width = (type == 'custom') ? jQuery('#width').val() : ((type == 'fix') ? 200 : 0);
    var height = (type == 'custom') ? jQuery('#height').val() : ((type == 'fix') ? 350 : 0);
    short_code += ' w="'+width+'"'
    short_code += ' h="'+height+'"'

    var bg_color = jQuery('#style').val() ? jQuery('#style').val().replace('#', '') : 'D51D29'
    short_code += ' c="'+bg_color+'"'
    var font_color = jQuery('#font_color').val() ? jQuery('#font_color').val().replace('#', '') : 'FFFFFF'
    short_code += ' fc="'+font_color+'"'

    var amount = jQuery('#amount').val() ? jQuery('#amount').val() : '1';
    short_code += ' a="'+amount+'"'

    var from = jQuery('#from').val() ? jQuery('#from').val().toUpperCase() : 'EUR';
    var to = jQuery('#to').val() ? jQuery('#to').val().toUpperCase() : 'USD';
    short_code += ' f="'+from+'"'
    short_code += ' t="'+to+'"'

    var gradient = jQuery('#gradient').val() ? jQuery('#gradient').val() : 'on'
    short_code += ' g="'+gradient+'"'

    var shadow = jQuery('#shadow').val() ? jQuery('#shadow').val() : 'on'
    short_code += ' sh="'+shadow+'"'

    var border = jQuery('#border').val() ? jQuery('#border').val() : 'on'
    short_code += ' b="'+border+'"'

    var flag = jQuery('#flag').val() ? jQuery('#flag').val() : 'on'
    short_code += ' fl="'+flag+'"'

    var display = jQuery('#display').val() ? jQuery('#display').val() : 'c'
    short_code += ' p="'+display+'"'

    var currencies = jQuery('#currencies').val() ? jQuery('#currencies').val() : ''
    if (currencies && currencies.length > 0) {
        currencies = currencies.join(",")
    }
    short_code += ' cs="'+currencies+'"'

    var symbol = jQuery('#symbol').val() ? jQuery('#symbol').val() : 'off'
    short_code += ' s="'+symbol+'"'

    var monetary_format = jQuery('#monetary_format').val() ? jQuery('#monetary_format').val() : '1'
    short_code += ' mf="'+monetary_format+'"'

    var decimal_format = jQuery('#decimal_format').val() ? jQuery('#decimal_format').val() : '2'
    short_code += ' df="'+decimal_format+'"'

    var date_format = jQuery('#date_format').val() ? jQuery('#date_format').val() : '1'
    short_code += ' d="'+date_format+'"'

    var support = jQuery('#support').val() ? jQuery('#support').val() : 'on'
    short_code += ' su="'+support+'"'

    short_code += lang+']'

    jQuery('#shortcode-input').val(short_code);
    widgetTrigger(type, jQuery('#lang').val());
}

// widgetTrigger(fr, 'fix');
preview()
jQuery(document).ready(function($) {
    $('#style, #font_color').wpColorPicker({
        change: function(event, ui){
            var color = ui.color.toString();
            $(event.target).val(color)
            preview()
        },
    });
    new SlimSelect({
        select: '#currencies'
    })
});
jQuery('input[name="size"]').change(function() {
	if (jQuery('input[name="size"]:checked').val() == 'custom') {
		jQuery('#width-section').show();
		jQuery('#height-section').show();
	} else {
		jQuery('#width-section').hide();
		jQuery('#height-section').hide();
	}
});
jQuery('.currency-bcc-configs select').bind('change', function(){
    preview()
});
jQuery('.currency-bcc-configs input').bind('change', function(){
    preview()
});
jQuery('.currency-bcc-widget-input-preview').bind('click', function() {
    preview();	
});