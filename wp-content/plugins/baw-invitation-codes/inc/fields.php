<?php


function bpeic_field_link()
{
	global $bpeic_fields;
?>
	<label><input type="checkbox" name="bpeic_fields[link]" <?php checked( $bpeic_fields['link'], 'on' ); ?>/> <em><?php _e( 'Do you want an extra text/link on registration form?', 'bpeic' ); ?></em></label>
<?php
}	

function bpeic_field_text_link()
{
	global $bpeic_fields;
?>
	<label><input type="text" size="60" name="bpeic_fields[text_link]" value="<?php echo !empty( $bpeic_fields['text_link'] ) ? esc_attr( $bpeic_fields['text_link'] ) : ''; ?>"/> <em><?php _e( 'You can use HTML tags to create links for example.', 'bpeic' ); ?></em></label>
<?php
}

function bpeic_field_count()
{
?>
	<input type="number" size="3" min="1" name="bpeic_field_count" value="1" /> <em><?php _e( 'How many time this code can be used?', 'bpeic' ); ?></em>
<?php
}

function bpeic_field_length()
{
?>
	<input type="number" size="10" min="4" max="16" name="bpeic_field_length" value="8" /> <em><?php _e( 'Length of generated codes (Min. 4, Max. 16)', 'bpeic' ); ?></em>
<?php
}

function bpeic_field_howmany()
{
?>
	<input type="number" size="3" min="1" max="10" name="bpeic_field_howmany" value="5" /> <em><?php _e( 'How many codes do you need?', 'bpeic' ); ?></em>
<?php
}

function bpeic_field_code()
{
?>
	<input type="text" name="bpeic_field_code" size="40" value="" style="text-transform: uppercase;" /> <em><?php _e( 'Avoid bad chars, use A-Z and 0-9.', 'bpeic' ); ?></em>
<?php
}

function bpeic_field_prefix()
{
?>
	<input type="text" name="bpeic_field_prefix" size="10" value="" style="text-transform: uppercase;" /> <em><?php _e( 'All generated codes will start with this.', 'bpeic' ); ?></em>
<?php
}
