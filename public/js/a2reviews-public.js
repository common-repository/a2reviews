(function( $ ) {
	'use strict';

	$(document).ready(function($){
		var a2rwt = $('.a2-reviews-widget-total');
		var widgetTotal = null;
		var tab_active = 'Default';
		
		$.each(a2rwt.parent('a2-reviews-total'), function(){
			if($(this).attr('collection') === undefined){
				widgetTotal = $(this);
			}
		});
		
		$('#tab-title-a2_reviews_tab').find('a').trigger('click');
		
		if(widgetTotal !== null && widgetTotal.length > 0){
			$(widgetTotal).on('click', function(){
				var a2tab = $('#tab-title-a2_reviews_tab');
				var scroll_to = () => {
					$('html, body').animate({
				        scrollTop: $("#tab-a2_reviews_tab").offset().top
				    }, 1000);
				}

				if(a2tab.length > 0 && $("#tab-a2_reviews_tab").is(':hidden')){
					setTimeout(function(){
						a2tab.find('a').trigger('click');
						scroll_to();
					}, 300);
				}else{
					scroll_to();
				}
				
				$(window).trigger('resize'); 
			});
		}
		
		try{
			tab_active = a2reviews_settings.options.tab_active;
			
			if(tab_active === 'Reviews'){				
				setTimeout(function(){
					$('#tab-title-a2_reviews_tab > a').click();
				}, 300);
			}else if(tab_active === 'Question and Answers'){	
				setTimeout(function(){			
					$('#tab-title-a2_qa_tab > a').click();
				}, 300);
			}
		}catch( e ){
			//No action set the first tab for default
		}
	});

})( jQuery );
