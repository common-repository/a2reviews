(function( $ ) {
	'use strict';
	
	$(document).ready(function($){
		$('#wp-admin-bar-a2reviews-app').find('a').attr('target', '_blank');
	});
	
	var a2_settings = (typeof a2reviews_settings !== 'undefined')? a2reviews_settings: {};
	
	var app = new Vue({
		el: '#a2review-settings',
		data: {
			a2reviewsSettings: Object.assign({}, {
				tab_active: 'Default',
				authentication: false,
				widget_position: 'Replace default tab',
				widget_custom_position: 2,
				tab_label_mask: 'Reviews ({% total_reviews %})',
				remove_reviews_tab: false,
				widget_total_position: 2,
				cat_widget_total_position: 2,
				qa_position: 'In tab',
				qa_custom_position: 2,
				tab_qa_label_mask: 'Questions & Answers ({% total_questions %})',
	        }, a2_settings),
	        
	        rules: {
				
	        },
	        
	        widget_position_options: [
		        {value: 1, label: 'Before single product'},
		        {value: 2, label: 'After single product'},
		        {value: 3, label: 'Before single product summary'},
		        {value: 4, label: 'After single product summary'},
		        {value: 9, label: 'Customize display position'},
	        ],
	        
	        widget_total_position_options: [
		        {value: 1, label: 'Before single product title'},
		        {value: 2, label: 'After single product title'},
		        {value: 9, label: 'Customize display position'},
	        ],
	        
	        cat_widget_total_position_options: [
		        {value: 1, label: 'Before loop product title'},
		        {value: 2, label: 'After loop product title'},
		        {value: 9, label: 'Customize display position'},
	        ],
	        
	        saving: false,
	        doing_auth: false,
	        opening_app: false,
		},
		
		methods: {
			doAuthentication(){
				this.doing_auth = true;
				
				axios({
			        method: 'post',
					url: `${ajaxurl}?action=a2reviews_do_authentication`,
					data: {
						security: $('#a2_security').val()
					},
					headers: { 'content-type': 'application/x-www-form-urlencoded' }
		        }).then(response => {
			        if(response.data.status === 'success'){
				        window.location.href = response.data.url;
			        }else{
				        this.$message.error('An unexpected error occurred..');
			        }
			        
			        this.doing_auth = false;
		        }).catch( error => {
			        this.$message.error('An unexpected error occurred..');
			        this.doing_auth = false;
		        });
			},
			
			openA2APP(){
				this.opening_app = true;
				
				axios({
			        method: 'post',
					url: `${ajaxurl}?action=a2reviews_do_open_app`,
					data: {
						security: $('#a2_security').val()
					},
					headers: { 'content-type': 'application/x-www-form-urlencoded' }
		        }).then(response => {
			        if(response.data.status === 'success'){
				        window.open(response.data.url, '_blank');
			        }else{
				        this.$message({
				          message: 'Could not create link to application.',
				          type: 'warning'
				        });
			        }
			        
			        this.opening_app = false;
		        }).catch( error => {
			        this.opening_app = false;
		        });
			},
			
	    	saveSettings(formName) {
		    	var save_change = () => {
			    	this.saving = true;
			    	
			        axios({
				        method: 'post',
						url: `${ajaxurl}?action=a2reviews_save_settings`,
						data: {
							settings: this.a2reviewsSettings,
							security: $('#a2_security').val()
						},
						headers: { 'content-type': 'application/x-www-form-urlencoded' }
			        }).then(response => {
				        if(response.data.status === 'success'){
					        this.$notify({
					        	title: 'Success',
								message: 'Settings saved!',
								type: 'success'
					        });
				        }else{
					        this.$notify.error({
					        	title: 'Error',
								message: 'Save error!'
					        });
				        }
			            
				        this.saving = false;
			        }).catch( error => {
			            this.saving = false;
			        });
		    	}
		    	
		        this.$refs[formName].validate((valid) => {
					if (valid) {
						save_change();
					} else {
						return false;
					}
		        });
	    	}
	    }
	});
	
	var a2_the_list = new Vue({
		el: '#the-list',
		data: {}
	});
	
	$('#a2review-settings').show();
	$('#a2-loading').hide();
})( jQuery );
