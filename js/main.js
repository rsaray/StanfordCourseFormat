var  appendProgressBar= (function(){
	var append = function(id){
		
		var labelID = '';
		var elementID = '';
		$('#region-sidebar .progressBar').html('');
		$('#'+id+'>.content>ul li').each(function(index) {

			if($(this).hasClass('label')){
				labelID = $(this).attr('id');
			}
			elementID = $(this).attr('id');

			if(labelID !== '' && !$(this).hasClass('label')){
				if(typeof elementID !== 'undefined'){
					if($(this).hasClass('richmedia')){
						var completionstate = $(this).find("input[name='completionstate']").attr('value');
						if(completionstate == 1) {
							if($(this).children('.mod-indent').children('.availabilityinfo').length>0) {
								$('#region-sidebar li[data-id="'+labelID+'"]').children('.progressBar').append('<li style="float:left;" class="video restricted" id="progressEleShowId-'+elementID+'"></li>');	
							}else {
								$('#region-sidebar li[data-id="'+labelID+'"]').children('.progressBar').append('<li style="float:left;" class="video" id="progressEleShowId-'+elementID+'"></li>');	
							}
									
						}else if(completionstate == 0){
							$('#region-sidebar li[data-id="'+labelID+'"]').children('.progressBar').append('<li style="float:left;" class="video done" id="progressEleShowId-'+elementID+'"></li>');		
						}else {
							$('#region-sidebar li[data-id="'+labelID+'"]').children('.progressBar').append('<li style="float:left;" class="video" id="progressEleShowId-'+elementID+'"></li>');
						}
						
					}else if($(this).hasClass('quiz')) {
						var completionstate = $(this).find("input[name='completionstate']").attr('value');
						if(completionstate == 1) {
							$('#region-sidebar li[data-id="'+labelID+'"]').children('.progressBar').append('<li style="float:left;" class="exercise" id="progressEleShowId-'+elementID+'"></li>');			
						}else{
							$('#region-sidebar li[data-id="'+labelID+'"]').children('.progressBar').append('<li style="float:left;" class="exercise done" id="progressEleShowId-'+elementID+'"></li>');		
						}
					}else {
						if($(this).children('.mod-indent').children('.availabilityinfo').length>0){
							$('#region-sidebar li[data-id="'+labelID+'"]').children('.progressBar').append('<li style="float:left;" class="file restricted" id="progressEleShowId-'+elementID+'"></li>');	
						}else {
							$('#region-sidebar li[data-id="'+labelID+'"]').children('.progressBar').append('<li style="float:left;" class="file" id="progressEleShowId-'+elementID+'"></li>');	
						}
						
					}
				}
			}
		});
		return false;
};
var update = function(){
	
};
return {
	append : append,
	update : update
};
})();

$(function() {/* clicking left Navigation to hide/show right content */
	$('#region-sidebar>ul>li').click(function(){/* click section title */
		var id4hide = $(this).attr('data-id');

		appendProgressBar.append(id4hide);
		$('#'+id4hide).siblings('li').hide();
		$('.content .section li','#'+id4hide).show();
		$('#section-0').show();		
		$('.content .section','#section-0').hide();
		$('#'+id4hide,'.stanford_course_list').css('display','block');

		var hideorshow = $(this).children('ul').css('display');

		if(hideorshow == 'none') {
			$(this).children('ul').slideDown('slow');	
			$(this).siblings('li').children('ul').slideUp('slow');
		}else {
			$(this).children('ul').slideUp ('slow');	
		}
		if($(this).hasClass('downward')){
			$(this).removeClass('downward');	
			var sVerifyFinalStep = $(this).children('a').html();
			if(sVerifyFinalStep == 'Final Steps'){
				$(this).find("#progressEleShowId-undefined").css('display','none');
			}
		}else{
			$(this).addClass('downward');
			var sVerifyFinalStep = $(this).children('a').html();
			if(sVerifyFinalStep == 'Final Steps'){
				$(this).find("#progressEleShowId-undefined").css('display','none');
			}
		}
		
		$(this).siblings('li').removeClass('downward');
		return false;

	});

	$('.module','#region-sidebar').click(function() { /* click modules title */
		$('.stanford_course_list li').show();
		var id4hide = $(this).attr('data-id');

		// console.log(id4hide);
		var sectionID = $(this).parent('ul').parents('.downward').attr('data-id');
		
		$('#'+sectionID).siblings('li').hide();	
		
		var listItem = $('#'+id4hide);
		var showIndex = $('#'+id4hide).parent('.section').children('li').index(listItem);
		var appearLable = 0;
		$('#'+id4hide).parent('.section').children('li').hide();
		$('#section-0').show();
		$('#section-0').children('.content').children('.section').hide();

		$('#'+id4hide).parent('.section').children('li').each(function(index){
			if($(this).hasClass('label') && appearLable === 1 ) {
				return false;
			}
			if(showIndex === index){
				appearLable = 1;
			}
			if(appearLable === 1){
				$(this).show();	
			}
			
		});
		return false;
	});

	$('#showallitemsaction').click(function() {
		$('.stanford_course_list li').show();
		$('#section-0').children('.content').children('.section').show();
		$('#section-0 .section.img-text li').last().addClass('lastchild');
	});


	$('.section.img-text .richmedia .activityinstance>a').click(function() {
		var popUpUrl = $(this).attr('data-url');
		var popUpVideoTitle = $(this).parents('li').attr('title');
		popUpUrl = popUpUrl+'&embedded';
		$('#dropdownvideopage iframe').attr('src',popUpUrl);
		$('#dropdownvideopage span.videotitle').html(popUpVideoTitle);
		$('#dropdownvideopage').slideDown('slow');
	});
	
	$('.section.img-text .quiz .activityinstance>a').click(function() {
		var popUpUrl = $(this).attr('data-url');
		
		var parentURL = document.URL;

		if(parentURL.indexOf("review") != -1){
			popUpUrl = popUpUrl+'&embedded&review';
		}else {
			popUpUrl = popUpUrl+'&embedded';	
		}
		$('#dropdownvideopage>a').attr('data-moduleid',$(this).parent('.activityinstance').parent('.mod-indent').parent('.quiz').attr('id'));
		$('#dropdownvideopage>a').attr('data-moduletype','quiz');		

		$('#dropdownvideopage iframe').attr('src',popUpUrl);
		$('#dropdownvideopage').slideDown('slow');
	});


	$('.section.img-text .url .activityinstance>a').click(function() {
		var moduleid = $(this).parent('.activityinstance').parent('.mod-indent').parent('.url').attr('id');
		moduleid = moduleid.replace('module-','');
		console.log(moduleid);
		var popUpUrl = "format/stanford/videoPlayer.php?id="+moduleid;
		$('#dropdownvideopage iframe').attr('src',popUpUrl);
		$('#dropdownvideopage').slideDown('slow');
	});


	
	$('#dropdownvideopage>.slideUpButton').click(function() {
		if($(this).attr('data-moduletype') === "quiz") {
			var moduleid = $(this).attr('data-moduleid');
			$('#'+moduleid).find('a').addClass('done');
			unlockModules(moduleid);
		}
		slideUPFromLiner();
	});


	$('#ta_feedback_menu>a').click(function() {
		if($('#ta_feedback_dropdown').hasClass('drop')) {
			$('#ta_feedback_dropdown').removeClass('drop');
		}else {
			$('#ta_feedback_dropdown').addClass('drop');
		}
		
	});

	
	$('#ta_feedback_dropdown li a').click(function() {

		var popUpUrl = $(this).attr('data-popup');
		
		popUpUrl = popUpUrl+'&showall=1&embedded';
		$('#dropdownvideopage iframe').attr('src',popUpUrl);
		$('#dropdownvideopage').slideDown('slow');
	});

});

function unlockModules(moduleid) {
	moduleid = moduleid.replace('module-','');
	/* Making ajax call to togglecompletion.php*/
	
	$.ajax({
		type: "GET",
	  	url: 'format/stanford/togglecompletionlib.php?op=quiz&cmid='+moduleid
	}).done(function (data) {
		$.ajax({
			type: "GET",
		  	url: 'format/stanford/unlockmodules.php?id='+moduleid
		}).done(function (data) {
			unlockNodes = jQuery.parseJSON(data);
			var unlockNodesArray = new Array();
			var domainName = document.domain;
			var subDomainName = window.location;
			var parts = subDomainName.toString().split('/'.toString());
			var protocolValue = 'http'; 
			if (window.location.protocol == "https:"){
				protocolValue = 'https'
			}
			$.each(unlockNodes, function(index, unlockNode) {
				// unlockNodesArray[index] = unlockNode.toString();
				// console.log(unlockNode);
				var targetUnlockNode = $('#module-'+unlockNode);
				
				// handle richmedia node
				if(targetUnlockNode.hasClass('richmedia') && (targetUnlockNode.find('.availabilityinfo').length > 0)) {
					// console.log('richmedia');
					if(targetUnlockNode.find('.dimmed_text>span').length > 0) {
						targetUnlockNode.find('.dimmed_text>span').addClass('instancename');
					var objectcontent = targetUnlockNode.find('.dimmed_text').html();
					targetUnlockNode.find('.activityinstance').html('<a class onclick="" href="javascript: void(0)" data-url="'+protocolValue+'://'+domainName+'/'+parts[3]+'/mod/richmedia/view.php?id='+unlockNode+'">'+objectcontent+'</a>');	
					}
				}
				// handle resource node
				if(targetUnlockNode.hasClass('resource') && (targetUnlockNode.find('.availabilityinfo').length > 0)) {
					// console.log('pdf');
					if(targetUnlockNode.find('.dimmed_text>span').length > 0) {
					targetUnlockNode.find('.dimmed_text>span').addClass('instancename');
					var resourceobjectcontent = targetUnlockNode.find('.dimmed_text').html();
					targetUnlockNode.find('.activityinstance').html('<a class="" onclick="window.open(\''+protocolValue+'://'+domainName+'/'+parts[3]+'/mod/resource/view.php?id='+unlockNode+'&amp;redirect=1\', \'\', \'width=620,height=450,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes\'); return false;" href="'+protocolValue+'://'+domainName+'/'+parts[3]+'/mod/resource/view.php?id='+unlockNode+'">'+resourceobjectcontent+'</a>');
					}
				}
				// handle final exame node
				if(targetUnlockNode.hasClass('modtype_url') && (targetUnlockNode.find('.availabilityinfo').length > 0)) {
					if(targetUnlockNode.find('.dimmed_text>span').length > 0) {
						// console.log("getting in");
						targetUnlockNode.find('.dimmed_text>span').addClass('instancename');
						var resourceobjectcontent = targetUnlockNode.find('.dimmed_text').html();
						if(targetUnlockNode.attr('title') == 'zPlayer'){
							targetUnlockNode.find('.activityinstance').html('<a class="" onclick="showvideoplayer($(this));" href="javascript:void(0);">'+resourceobjectcontent+'</a>');
						}else {

							targetUnlockNode.find('.activityinstance').html('<a class="" onclick="window.open(\''+protocolValue+'://'+domainName+'/'+parts[3]+'/mod/url/view.php?id='+unlockNode+'&amp;redirect=1\', \'\', \'width=620,height=450,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes\'); return false;" href="'+protocolValue+'://'+domainName+'/'+parts[3]+'/mod/url/view.php?id='+unlockNode+'">'+resourceobjectcontent+'</a>');
						}
						
					}
				}
			});
		});
	});


}

function showvideoplayer(elem) {
	var moduleid = elem.parent('.activityinstance').parent('.mod-indent').parent('.activity').attr('id');
	moduleid = moduleid.replace('module-','');
	var popUpUrl = "format/stanford/videoPlayer.php?id="+moduleid;
	$('#dropdownvideopage iframe').attr('src',popUpUrl);
	$('#dropdownvideopage').slideDown('slow');
}

function slideUPFromLiner(moduleid) {
	$('#dropdownvideopage').slideUp('slow');
	$('#dropdownvideopage iframe').attr('src','about:blank');
}

