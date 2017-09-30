<?php
/**
 * This file could be used to catch submitted form data. When using a non-configuration
 * view to save form data, remember to use some kind of identifying field in your form.
 */
    $raised = ( isset( $_POST['raised'] ) ) ? stripslashes( $_POST['raised'] ) : self::get_thermometer_widget_option('raised_string');
    $target = ( isset( $_POST['target'] ) ) ? stripslashes( $_POST['target'] ) : self::get_thermometer_widget_option('target_string');
    $trailing = ( isset( $_POST['trailing'] ) ) ? stripslashes( $_POST['target'] ) : self::get_thermometer_widget_option('target_string');
    self::update_thermometer_widget_options(
            array(                                      
                'raised_string' => $raised,
                'target_string' => $target,
            )
    );

?>
<div style="padding: 10px 0px 20px 0;">
Target: <input type="text" name="target" value="<?php echo self::get_thermometer_widget_option('target_string'); ?>" style="width: 90px;"/>
Raised: <input type="text" name="raised" value="<?php echo self::get_thermometer_widget_option('raised_string'); ?>" style="width: 90px;"/>
</div>
