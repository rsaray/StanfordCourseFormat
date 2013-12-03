/*Zhao PDF*/

var PDFCarousel = {};

PDFCarousel.Animate = {
	pdfSubElemIndex : 0,
	liSize : 0,
	init: function() {
		PDFCA.liSize = $('#sidebarTab-pdf-sub-nav>li').size();
	},
	Prev: function() {
		var currentObject = $('#sidebarTab-pdf-sub-nav').find('.currentchild');
		currentObject.css('display','none');
		currentObject.next('li').css('display','block');
		currentObject.next('li').addClass('currentchild');
		currentObject.removeClass('currentchild');
	},
	Next: function() {
		var currentObject = $('#sidebarTab-pdf-sub-nav').find('.currentchild');
		currentObject.css('display','none');
		currentObject.prev('li').css('display','block');
		currentObject.prev('li').addClass('currentchild');
		currentObject.removeClass('currentchild');
	}
};
var PDFCA = PDFCarousel.Animate;


$(function() {
	PDFCA.init();	
	$('.pdf-prev').click(function() {
		if(!$('.currentchild').hasClass('lastchild')){
			if($('.firstchild').hasClass('currentchild')){
				$('.pdf-next').addClass('active');	
				$('.pdf-next').removeClass('inactive');	
			}
			PDFCA.Prev();	
			
		}else {
			$('.pdf-prev').removeClass('active');
			$('.pdf-prev').addClass('inactive');
		}
	});
	$('.pdf-next').click(function() {
		if(!$('.currentchild').hasClass('firstchild')){
			if($('.lastchild').hasClass('currentchild')){
				$('.pdf-prev').addClass('active');	
				$('.pdf-prev').removeClass('inactive');
			}
			PDFCA.Next();	
			
		}else{
			$('.pdf-next').removeClass('active');
			$('.pdf-next').addClass('inactive');
		}
		
	});
});



/*pop-out pdf button*/
function popupPDF() {
	var url = $('#sidebarTab-pdf-sub-nav .pdf-sub-elem.actived').attr('data-url');

	var p1 = 'scrollbars=yes,resizable=yes,status=yes,location=no,toolbar=no,menubar=no,';
	var p2 = 'width=400,height=400,left=100,top=100';
	window.open(url, 'supplementObject', p1+p2);
	return false;	
}

$(document).ready(function(){
    $('a[rel="external"]').click(function() {
        window.open($(this).attr('href'));
        return false;
    });
});

/*pdfs switcher */ 

function switchpdf(elem) {
	$('.pdf-sub-elem').removeClass('actived');
	elem.parent('.pdf-sub-elem').addClass('actived');
	
	var sub_class_name = elem.parent('.pdf-sub-elem').children('a').text();
	$('.pdfsidebarTab').removeClass('actived');
	$("."+sub_class_name).addClass('actived');

	var url = elem.parent('.pdf-sub-elem').attr('data-url');
	var urlid = elem.parent('.pdf-sub-elem').attr('data-urlid');
	myPDF = new PDFObject({url: url});


	if(myPDF.get("pluginTypeFound") != null) {
		$('.pdfsidebarTab').html('<iframe frameBorder=0 seamless style="display:block;" class="supplementView" src="'+url+'"></iframe>');
	}else if(BrowserDetect.browser == "Firefox"){
		$('.pdfsidebarTab').html('<iframe  frameBorder=0 seamless style="display:block;" class="supplementView" src="'+url+'"></iframe>');
	}else if(BrowserDetect.browser == "Chrome") {
		$('.pdfsidebarTab').html('<iframe  frameBorder=0 seamless style="display:block;" class="supplementView" src="'+url+'"></iframe>');
	} 
	else {
		$('#sidebarTab-pdf-sub-nav').css('display','none');
		$('#viewmodes').css('display','none');
		var supplemntalfordownload = '';
		$('#sidebarTab-pdf-sub-nav .pdf-sub-elem').each(function(index){
			var contentName = $(this).children('a').html();
			var contentURL = $(this).attr('data-url');
			supplemntalfordownload += '<li class="noneReaderSupplement-item"><a href="'+ contentURL +'" target="_blank">'+contentName+'</a></li>';
		});
		$('.pdfsidebarTab').html('<div style="padding: 10px;border:1px solid #8C1515"><p>It appears you do not have Adobe Reader or PDF support in this web browser. <a href="http://get.adobe.com/reader/" target="_blank">Click here to download the PDF Reader</a></p></div><ul class="noneReaderSupplement">'+supplemntalfordownload+'</ul>');
		
	}
}


function loadFirstPDF() {
	
	var url = $('#sidebarTab-pdf-sub-nav .firstchild .pdf-sub-elem.actived').attr('data-url');
	var urlid = $('#sidebarTab-pdf-sub-nav .firstchild .pdf-sub-elem.actived').attr('data-urlid');

	var myPDF = new PDFObject({url: url});
	if(url != undefined) {
		if(myPDF.get("pluginTypeFound") != null) {
			$('.pdfsidebarTab').html('<iframe frameBorder=0 seamless style="display:block;" style="height:100%" class="supplementView" src="'+url+'"></iframe>');
		}else if(BrowserDetect.browser == "Firefox"){
			$('.pdfsidebarTab').html('<iframe frameBorder=0 seamless style="display:block;" style="height:100%" class="supplementView" src="'+url+'"></iframe>');
			// $('.pdfsidebarTab').html('<iframe  frameBorder=0 seamless style="display:block;" class="supplementView" src="pdfweb/viewer.php?url='+urlid+'"></iframe>');
		}else if(BrowserDetect.browser == "Chrome") {
			$('.pdfsidebarTab').html('<iframe  frameBorder=0 seamless style="display:block;" class="supplementView" src="pdfweb/viewer.php?url='+urlid+'"></iframe>');
		} 
		else {
			// $('.pdfsidebarTab').html('<p>It appears you do not have Adobe Reader or PDF support in this web browser. <a href="http://get.adobe.com/reader/" target="_blank">Click here to download the PDF Reader</a></p>');
			$('#sidebarTab-pdf-sub-nav').css('display','none');
			$('#viewmodes').css('display','none');
			var supplemntalfordownload = '';
			$('#sidebarTab-pdf-sub-nav .pdf-sub-elem').each(function(index){
				var contentName = $(this).children('a').html();
				var contentURL = $(this).attr('data-url');
				supplemntalfordownload += '<li class="noneReaderSupplement-item"><a href="'+ contentURL +'" target="_blank">'+contentName+'</a></li>';
			});
			$('.pdfsidebarTab').html('<div style="padding: 10px; border:1px solid #8C1515"><p>It appears you do not have Adobe Reader or PDF support in this web browser. <a href="http://get.adobe.com/reader/" target="_blank">Click here to download the PDF Reader</a></p></div><ul class="noneReaderSupplement">'+supplemntalfordownload+'</ul>');
		}	
	}
	

}


/* Detect Browsers */
var BrowserDetect = {
	init: function () {
		this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
		this.version = this.searchVersion(navigator.userAgent)
			|| this.searchVersion(navigator.appVersion)
			|| "an unknown version";
		this.OS = this.searchString(this.dataOS) || "an unknown OS";
	},
	searchString: function (data) {
		for (var i=0;i<data.length;i++)	{
			var dataString = data[i].string;
			var dataProp = data[i].prop;
			this.versionSearchString = data[i].versionSearch || data[i].identity;
			if (dataString) {
				if (dataString.indexOf(data[i].subString) != -1)
					return data[i].identity;
			}
			else if (dataProp)
				return data[i].identity;
		}
	},
	searchVersion: function (dataString) {
		var index = dataString.indexOf(this.versionSearchString);
		if (index == -1) return;
		return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
	},
	dataBrowser: [
		{
			string: navigator.userAgent,
			subString: "Chrome",
			identity: "Chrome"
		},
		{ 	string: navigator.userAgent,
			subString: "OmniWeb",
			versionSearch: "OmniWeb/",
			identity: "OmniWeb"
		},
		{
			string: navigator.vendor,
			subString: "Apple",
			identity: "Safari",
			versionSearch: "Version"
		},
		{
			prop: window.opera,
			identity: "Opera",
			versionSearch: "Version"
		},
		{
			string: navigator.vendor,
			subString: "iCab",
			identity: "iCab"
		},
		{
			string: navigator.vendor,
			subString: "KDE",
			identity: "Konqueror"
		},
		{
			string: navigator.userAgent,
			subString: "Firefox",
			identity: "Firefox"
		},
		{
			string: navigator.vendor,
			subString: "Camino",
			identity: "Camino"
		},
		{		// for newer Netscapes (6+)
			string: navigator.userAgent,
			subString: "Netscape",
			identity: "Netscape"
		},
		{
			string: navigator.userAgent,
			subString: "MSIE",
			identity: "Explorer",
			versionSearch: "MSIE"
		},
		{
			string: navigator.userAgent,
			subString: "Gecko",
			identity: "Mozilla",
			versionSearch: "rv"
		},
		{ 		// for older Netscapes (4-)
			string: navigator.userAgent,
			subString: "Mozilla",
			identity: "Netscape",
			versionSearch: "Mozilla"
		}
	],
	dataOS : [
		{
			string: navigator.platform,
			subString: "Win",
			identity: "Windows"
		},
		{
			string: navigator.platform,
			subString: "Mac",
			identity: "Mac"
		},
		{
			   string: navigator.userAgent,
			   subString: "iPhone",
			   identity: "iPhone/iPod"
	    },
		{
			string: navigator.platform,
			subString: "Linux",
			identity: "Linux"
		}
	]

};
BrowserDetect.init();

