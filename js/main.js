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
});

