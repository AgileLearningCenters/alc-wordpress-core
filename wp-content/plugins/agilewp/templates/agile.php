<div class='wrap' style='background: white; padding: 20px'>
	<?php echo '<img src="' . plugins_url( '../images/agile500.png' , __FILE__) . '" > ';?>
	<h3>
		Do not have an account with Agile CRM? <span style="font-size: 75%;">It's
			fast and free for two users</span>
	</h3>
	<div
		style="width: 500px; height: auto; color: #8a6d3b; background-color: #fcf8e3; border: 1px solid #faebcc; border-radius: 5px">
		<div>
			<a
				href="https://www.agilecrm.com/?utm_source=plugins&amp;utm_medium=wordpress&amp;utm_campaign=wordpress"
				target="_blank"> <?php submit_button('Create a new account','secondary','create_account', false);?>
			</a>
		</div>
		<p style="margin-left: 50px; margin-top: 15px;"
			id="create_account_text">Once you have created, please come back and
			fill in the details below</p>
	</div>
	<br>
	<h3>
		Already have an account? <span style="font-size: 75%;">Enter your
			details</span>
	</h3>
	<div
		style="width: 600px; height: auto; border: 1px solid #e3e3e3; background-color: #f5f5f5; border-radius: 5px">
		<form action="options.php" method="POST"
			style="margin-left: 25px; margin-top: 20px;" id="settings_form">
			<?php settings_fields( 'agile-settings-group' ); ?>
			<?php do_settings_sections( 'agile-plugin' ); ?>
			<br> <span><?php submit_button('Save Changes','primary','submit_button',false); ?>&nbsp;<span
				style="vertical-align: sub;" id="error_text"></span>
		
		</form>
	</div>
</div>
<div>
	<p>Like Agile CRM? Share the love.</p>
	<span style="display: inline-block; width: 225px;">&nbsp;&nbsp;<a
		href="https://twitter.com/share" class="twitter-share-button"
		data-url="https://www.agilecrm.com"
		data-text="Sell like Fortune 500 with #AgileCRM">Tweet</a>
		<div id="fb-root" style='display: inline-block;'></div>
		<div class="fb-like" style='display: inline-block; float: left;'
			data-href="https://www.facebook.com/CRM.Agile" data-layout="button"
			data-action="like" data-show-faces="false" data-share="true"></div>
	</span>
</div>
<?php echo '<script type="text/javascript" src="'.plugins_url('../scripts/agile_js.js', __FILE__).'"></script>';?>