<?php
if ( !current_user_can( "manage_options" ) )  {
	   wp_die( __( "You do not have sufficient permissions to access this page." ) );
	}	
	settings_errors();
?>
	<form method="post" action="options.php">
		<?php
			settings_fields( 'ipractice_fields' );
			do_settings_sections( 'ipractice_fields' );
			submit_button();
		?>
	</form>
<?php
	

	
		
		


