<?php

/*
 * Mail Section
 */
?>


<?php if ( 'wpem_default-mail-format' == $field['label_for'] ) : ?>
<select id="<?php esc_attr_e( 'wpem_settings[mail][default_mail_format]' ); ?>" name="<?php esc_attr_e( 'wpem_settings[mail][default_mail_format]' ); ?>" >
<option value="html" <?php selected('html',$settings['mail']['default_mail_format']) ?>> <?php _e('HTML','ninja_mail') ?> </option>
<option value="plain-text" <?php selected('plain-text',$settings['mail']['default_mail_format']) ?> > <?php _e('Plain Text','ninja_mail') ?> </option>
</select>

<?php endif; ?>

<?php if ( 'wpem_max-bcc-recipients' == $field['label_for'] ) : ?>

<select id="<?php esc_attr_e( 'wpem_settings[mail][max_bcc_recipients]' ); ?>" name="<?php esc_attr_e( 'wpem_settings[mail][max_bcc_recipients]' ); ?>">
    <option <?php selected($settings['mail']['max_bcc_recipients'] , 0, 1)?>    value="0">Unlimited</option>
    <option <?php selected($settings['mail']['max_bcc_recipients'] , -1, 1)?>   value="-1">No BCC Recepients</option>
    <option <?php selected($settings['mail']['max_bcc_recipients'] , 20, 1)?>   value="20">20</option>
    <option <?php selected($settings['mail']['max_bcc_recipients'] , 40, 1)?>   value="40">40</option>
    <option <?php selected($settings['mail']['max_bcc_recipients'] , 80, 1)?>   value="80">80</option>
    <option <?php selected($settings['mail']['max_bcc_recipients'] , 100, 1)?>  value="100">100</option>
    <option <?php selected($settings['mail']['max_bcc_recipients'] , 200, 1)?>  value="200">200</option>
    <option <?php selected($settings['mail']['max_bcc_recipients'] , 400, 1)?>  value="400">400</option>
    <option <?php selected($settings['mail']['max_bcc_recipients'] , 600, 1)?>  value="600">600</option>
    <option <?php selected($settings['mail']['max_bcc_recipients'] , 800, 1)?>  value="800">800</option>
    <option <?php selected($settings['mail']['max_bcc_recipients'] , 1000, 1)?> value="1000">1000</option>
	<option <?php selected($settings['mail']['max_bcc_recipients'] , 5000, 1)?> value="5000">5000</option>
</select>

<p class="description">Try 40 if you have problems sending emails to many users (some providers forbid too many recipients in BCC field).</p>
<?php endif; ?>

<?php if ( 'wpem_inline-style' == $field['label_for'] ) : ?>
<label>
<input type="checkbox" id="<?php esc_attr_e( 'wpem_settings[mail][inline_style]' ); ?>" value="1" name="<?php esc_attr_e( 'wpem_settings[mail][inline_style]' ); ?>" <?php checked( $settings['mail']['inline_style'], 1 ); ?>/> 
Add CSS inline(CSS will be added to the header tag otherwise)
</label>

<?php endif; ?>

<?php if ( 'wpem_omit-display-names' == $field['label_for'] ) : ?>
<label>
<input type="checkbox" id="<?php esc_attr_e( 'wpem_settings[mail][omit_display_names]' ); ?>" value="1" name="<?php esc_attr_e( 'wpem_settings[mail][omit_display_names]' ); ?>" <?php checked( $settings['mail']['omit_display_names'], 1 ); ?>/> 
Omit Display Names when sending email.
</label>
<p class="description">Use "john.doe@example.com" instead of "John Doe <john.doe@example.com>"</p>
<?php endif; ?>

<?php if ( 'wpem_copy-sender' == $field['label_for'] ) : ?>
<label>
<input type="checkbox" id="<?php esc_attr_e( 'wpem_settings[mail][copy_sender]' ); ?>" value="1" name="<?php esc_attr_e( 'wpem_settings[mail][copy_sender]' ); ?>" <?php checked( $settings['mail']['copy_sender'], 1 ); ?>/> 
Copy sender (add sender email to Cc: header) when sending email.
</label>
<?php endif; ?>

<?php if ( 'wpem_from-sender-exclude' == $field['label_for'] ) : ?>
<label>
<input type="checkbox" id="<?php esc_attr_e( 'wpem_settings[mail][from_sender_exclude]' ); ?>" value="1" name="<?php esc_attr_e( 'wpem_settings[mail][from_sender_exclude]' ); ?>" <?php checked( $settings['mail']['from_sender_exclude'], 1 ); ?>/> 
Exclude sender from email recipient list.
</label>

<?php endif; ?>

<?php if ( 'wpem_from-sender-name' == $field['label_for'] ) : ?>
<label>
<input class="regular-text" type="text" id="<?php esc_attr_e( 'wpem_settings[mail][from_sender_name]' ); ?>" name="<?php esc_attr_e( 'wpem_settings[mail][from_sender_name]' ); ?>" value="<?php esc_attr_e( $settings['mail']['from_sender_name']) ?>"/> 
<p class="description" >A name that can be used in place of the logged in user's name when sending email or notifications.</p>
</label>

<?php endif; ?>

<?php if ( 'wpem_bounces-address' == $field['label_for'] ) : ?>
<label>
<input class="regular-text" type="text" id="<?php esc_attr_e( 'wpem_settings[mail][bounces_address]' ); ?>" name="<?php esc_attr_e( 'wpem_settings[mail][bounces_address]' ); ?>" value="<?php esc_attr_e( $settings['mail']['bounces_address']) ?>"/> 
<p class="description">An email address that can be used in place of the logged in user's email address to receive bounced email notifications.</p>
</label>

<?php endif; ?>

<?php if ( 'wpem_from-sender-email' == $field['label_for'] ) : ?>
<input class="regular-text" type="text" type="text" id="<?php esc_attr_e( 'wpem_settings[mail][from_sender_email]' ); ?>" name="<?php esc_attr_e( 'wpem_settings[mail][from_sender_email]' ); ?>" value="<?php esc_attr_e( $settings['mail']['from_sender_email']); ?>">
<p class="description">An email address that can be used in place of the logged in user's email address when sending email or notifications.</p>
<?php endif; ?>

<?php if ( 'wpem_show-in-browser-text' == $field['label_for'] ) : ?>
<input class="regular-text" type="text" type="text" id="<?php esc_attr_e( 'wpem_settings[mail][show_in_browser_text]' ); ?>" name="<?php esc_attr_e( 'wpem_settings[mail][show_in_browser_text]' ); ?>" value="<?php esc_attr_e( $settings['mail']['show_in_browser_text']); ?>">
<p class="description">Text to show in the link generated by the [wpem link=browser_mail] shortcode.</p>
<?php endif; ?>


<?php
/*
 * Scheduler Section
 */
?>

<?php if ( 'wpem_send-time' == $field['label_for'] ) : ?>
 <select id="<?php esc_attr_e( 'wpem_settings[schedule][send_time][hh]' ); ?>"  name="<?php esc_attr_e( 'wpem_settings[schedule][send_time][hh]' ); ?>">
 
 <?php  for($i=0; $i<=23; $i++){?>
       <option  <?php echo selected($i, $settings['schedule']['send_time']['hh']) ?> value="<?php echo $i ?>"><?php echo sprintf("%02s", $i) ?> </option> 
	   <?php } ?>
 </select>
 <span>:</span>
 
  <select id="<?php esc_attr_e( 'wpem_settings[schedule][send_time][mn]' ); ?>"  name="<?php esc_attr_e( 'wpem_settings[schedule][send_time][mn]' ); ?>">
 
 <?php  for($i=0; $i<=59; $i++){?>
       <option  <?php echo selected($i, $settings['schedule']['send_time']['mn']) ?> value="<?php echo $i ?>"><?php echo sprintf("%02s", $i) ?> </option> 
	   <?php } ?>
 </select>
 <span>HRS</span>
 <span id="local-time"><?php printf(__('Local time is <code>%1$s</code>'), date_i18n(DATE_TIME_FORMAT)); ?></span>
<?php
$current_offset = get_option('gmt_offset');
$tzstring = get_option('timezone_string');

$check_zone_info = true;

// Remove old Etc mappings. Fallback to gmt_offset.
if ( false !== strpos($tzstring,'Etc/GMT') )
	$tzstring = '';

if ( empty($tzstring) ) { // Create a UTC+- zone if no timezone string exists
	$check_zone_info = false;
	if ( 0 == $current_offset )
		$tzstring = 'UTC+0';
	elseif ($current_offset < 0)
		$tzstring = 'UTC' . $current_offset;
	else
		$tzstring = 'UTC+' . $current_offset;
}
 echo $tzstring; 
?>


 <p class="description">Time to send Scheduled emails in 24hr format</p>

<?php endif; ?>


<?php
/*
 * Notices Section
 */
?>

<?php if ( 'wpem_default-template' == $field['label_for'] ) : ?>
 <select id="<?php esc_attr_e( 'wpem_settings[notices][default_template]' ); ?>"  name="<?php esc_attr_e( 'wpem_settings[notices][default_template]' ); ?>">
 <option value="-1" <?php echo selected(-1, $settings['notices']['default_template']) ?>  > - Select - </option> 
 
 <?php  foreach((array)$templates as $template):?>
       <option  <?php echo selected($template->ID, $settings['notices']['default_template']) ?> value="<?php echo $template->ID ?>"> <?php echo $template->post_title ?> </option> 
	   <?php endforeach; ?>
 </select>
 <p class="description">Default template to use for all user notifications.</p>

<?php endif; ?>

<?php if ( 'wpem_default-admin-template' == $field['label_for'] ) : ?>

 <select id="<?php esc_attr_e( 'wpem_settings[notices][default_admin_template]' ); ?>" name="<?php esc_attr_e( 'wpem_settings[notices][default_admin_template]' ); ?>">
<option <?php echo selected(-1, $settings['notices']['default_admin_template']) ?> value="-1"> - Select - </option> 
 
 <?php  foreach((array)$templates as $template):?>
       <option <?php echo selected($template->ID, $settings['notices']['default_admin_template']) ?> value="<?php echo $template->ID ?>"> <?php echo $template->post_title ?> </option> 
	   <?php endforeach; ?>
 </select> 
  <p class="description">Default template to use for all admin notifications.</p>
<?php endif; ?>

<?php
/*
 * User Section
 */
?>

<?php if ( 'wpem_user-stgs' == $field['label_for'] ) : ?>
<label> 
<input type="checkbox" id="<?php esc_attr_e( 'wpem_settings[user][unsubscribe_enable]' ); ?>" value="1" name="<?php esc_attr_e( 'wpem_settings[user][unsubscribe_enable]' ); ?>" <?php checked( $settings['user']['unsubscribe_enable'], 1 ); ?>/> 
	Allow users to control their email settings
</label><br/>

<p> 
<label for="<?php esc_attr_e( 'wpem_settings[user][unsubscribe_text]' ); ?>">Unsubscribe link text</label>
<br>
<input type="text" class="regular-text" id="<?php esc_attr_e( 'wpem_settings[user][unsubscribe_text]' ); ?>" value="<?php echo $settings['user']['unsubscribe_text']; ?>" name="<?php esc_attr_e( 'wpem_settings[user][unsubscribe_text]' ); ?>" /> 
</p>
<p class="description">Text to show in the link generated by the [wpem link=unsubscribe] shortcode.</p>

<p> 
<label for="<?php esc_attr_e( 'wpem_settings[user][unsubscribe_page]' ); ?>">Unsubscribe Page</label>
<br/>
<?php
    $selected = isset($settings['user']['unsubscribe_page'])?$settings['user']['unsubscribe_page']: -1;
    wp_dropdown_pages( array('selected' => $selected,'name' =>  'wpem_settings[user][unsubscribe_page]', 'id' => 'wpem_settings[user][unsubscribe_page]','show_option_none' => '-select-','option_none_value' => ''))
?>
</p>

<?php endif; ?>

<?php
/*
 * Headers Section
 */
?>

<?php if ( 'wpem_header-stgs' == $field['label_for'] ) : ?>

<label> 
<input type="checkbox" id="<?php esc_attr_e( 'wpem_settings[headers][x_mailer]' ); ?>" value="1" name="<?php esc_attr_e( 'wpem_settings[headers][x_mailer]' ); ?>" <?php checked( $settings['headers']['x_mailer'], 1) ?>/> 
	 Add X-Mailer mail header record..
</label><br/>

<label> 
<input type="checkbox" id="<?php esc_attr_e( 'wpem_settings[headers][mime]' ); ?>" value="1" name="<?php esc_attr_e( 'wpem_settings[headers][mime]' ); ?>"   <?php checked( $settings['headers']['mime'], 1) ?>/> 
	 	Add MIME-Version mail header record.
</label><br/>
<?php endif; ?>