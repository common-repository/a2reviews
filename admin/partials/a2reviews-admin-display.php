<div class="wrap">
    <h1>A2 Reviews</h1>
    
    <div id="a2-loading">
	    <p>Loading settings...</p>
    </div>
    <div id="a2review-settings">
        <el-form 
        	:model="a2reviewsSettings" 
        	:rules="rules" ref="a2reviewsSettings" 
        	label-width="180px" 
        	class="demo-a2reviewsSettings" 
        	label-position="left"
        >
        	<?php wp_nonce_field( 'a2_settings_auth', 'a2_security' ); ?>
        	
		 	<el-form-item label="<?php _e('Authentication', 'a2reviews'); ?>" prop="connect">
			    <el-button 
			    	type="primary" round 
			    	size="small" 
			    	icon="el-icon-unlock" 
			    	@click="doAuthentication" 
			    	v-if="!a2reviewsSettings.authentication"
			    	:loading="doing_auth"><?php _e('Authentication', 'a2reviews'); ?>
			    </el-button> 
			    <el-button 
			    	v-else
			    	type="success" round 
			    	size="small" 
			    	icon="el-icon-unlock" 
			    	:loading="doing_auth"
			    	@click="doAuthentication"><?php _e('Connected', 'a2reviews'); ?>
			    </el-button> 
			    
			    <el-button 
			    	type="primary" round 
			    	size="small" 
			    	icon="el-icon-star-on" 
			    	:disabled="!a2reviewsSettings.authentication"
			    	:loading="opening_app"
			    	@click="openA2APP"><?php _e('A2Reviews APP', 'a2reviews') ?>
			    </el-button>
			</el-form-item>
			
			<el-form-item label="<?php _e('Tab active', 'a2reviews'); ?>" prop="tab_active">
        		<el-radio-group v-model="a2reviewsSettings.tab_active" size="small">
			      <el-radio-button label="Default"></el-radio-button>
			      <el-radio-button label="Reviews"></el-radio-button>
			      <el-radio-button label="Question and Answers"></el-radio-button>
			    </el-radio-group>
        	</el-form-item>
			
			<el-form-item label="<?php _e('Reviews Widget', 'a2reviews'); ?>" prop="widget">
				<el-radio-group 
					v-model="a2reviewsSettings.widget_position" 
					size="small">
				    <el-radio-button label="Replace default tab"></el-radio-button>
				    <el-radio-button label="Custom"></el-radio-button>
			    </el-radio-group>
			    
			    <el-row v-if="a2reviewsSettings.widget_position != 'Custom'">
			    	<label><?php _e('Label mask', 'a2reviews'); ?></label>
			    	<el-input v-model="a2reviewsSettings.tab_label_mask"></el-input>
			    	<p>Use <span style="color: #4caf50;">{% total_reviews %}</span> to show total reviews in the tab label.</p>
			    </el-row>
			    
			    <el-row v-else style="margin-top: 10px;">
			    	<el-select v-model="a2reviewsSettings.widget_custom_position" placeholder="Select" size="small">
					    <el-option
					      v-for="item in widget_position_options"
					      :key="item.value"
					      :label="item.label"
					      :value="item.value">
					    </el-option>
					</el-select>
					
					<p v-if="a2reviewsSettings.widget_custom_position == 9">
						<span>Add the following code to the single product page file, where you want to display the reviews widget.</span><br />
						<?php highlight_string('<?php do_action(\'a2reviews_widget\'); ?>'); ?>
					</p>
					
					<el-checkbox v-model="a2reviewsSettings.remove_reviews_tab">Remove the default reviews tab</el-checkbox>
			    </el-row>
			</el-form-item>
			
			<el-form-item label="Total Widget" prop="widget">
				<el-select v-model="a2reviewsSettings.widget_total_position" placeholder="Select" size="small">
				    <el-option
				      v-for="item in widget_total_position_options"
				      :key="item.value"
				      :label="item.label"
				      :value="item.value">
				    </el-option>
				</el-select>
				
				<p v-if="a2reviewsSettings.widget_total_position == 9">
					<span>Add the following code to the single product page file, where you want to display the reviews total widget.</span><br />
					<?php highlight_string('<?php do_action(\'a2reviews_widget_total\'); ?>'); ?>
				</p>
			</el-form-item>
			
			<el-form-item label="Category Total Widget" prop="widget">
				<el-switch v-model="a2reviewsSettings.replace_cwt_default"></el-switch> <span>Replace stars rating default</span> <br />
				
				<el-select v-model="a2reviewsSettings.cat_widget_total_position" placeholder="Select" size="small" :disabled="a2reviewsSettings.replace_cwt_default">
				    <el-option
				      v-for="item in cat_widget_total_position_options"
				      :key="item.value"
				      :label="item.label"
				      :value="item.value">
				    </el-option>
				</el-select>
				
				<p v-if="a2reviewsSettings.cat_widget_total_position == 9">
					<span>Add the following code into the loop product page file, where you want to display the reviews total widget.</span><br />
					<?php highlight_string('<?php do_action(\'a2reviews_loop_widget_total\'); ?>'); ?>
				</p>
			</el-form-item>
			
			<el-form-item label="<?php _e('Q&A Widget', 'a2reviews'); ?>" prop="qa_widget">
				<p>Question and Answers widget</p>
				<el-radio-group 
					v-model="a2reviewsSettings.qa_position" 
					size="small">
				    <el-radio-button label="In tab"></el-radio-button>
				    <el-radio-button label="Custom"></el-radio-button>
			    </el-radio-group>
			    
			    <el-row v-if="a2reviewsSettings.qa_position != 'Custom'">
			    	<label><?php _e('Label mask', 'a2reviews'); ?></label>
			    	<el-input v-model="a2reviewsSettings.tab_qa_label_mask"></el-input>
			    	<p>Use <span style="color: #4caf50;">{% total_questions %}</span> to show total questions in the tab label.</p>
			    </el-row>
			    
			    <el-row v-else style="margin-top: 10px;">
			    	<el-select v-model="a2reviewsSettings.qa_custom_position" placeholder="Select" size="small">
					    <el-option
					      v-for="item in widget_position_options"
					      :key="item.value"
					      :label="item.label"
					      :value="item.value">
					    </el-option>
					</el-select>
					
					<p v-if="a2reviewsSettings.qa_custom_position == 9">
						<span>Add the following code to the single product page file, where you want to display the questions and answers widget.</span><br />
						<?php highlight_string('<?php do_action(\'a2_questions_answers_widget\'); ?>'); ?>
					</p>
			    </el-row>
				
			</el-form-item>
		 
			<el-form-item>
		    	<el-button 
		    		type="primary" 
		    		@click="saveSettings('a2reviewsSettings')" 
		    		size="small" 
		    		:loading="saving"><?php _e('Save Settings', 'a2reviews'); ?>
		    	</el-button>
			</el-form-item>
		</el-form>
    </div>
</div>