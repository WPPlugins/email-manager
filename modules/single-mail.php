<?php


 function wpem_single_mail_fields($email_source, $errors){
 if($email_source !== 'sm')
    return;
 ?>
 <?php echo wp_nonce_field('wpem_us_nonce', 'wpem_us_nonce', true, false); ?>
 <tr>
 <th>To Name</th>
 <td>
 <?php
 $to_name = isset($mail['to_name'])?$mail['to_name']:'';
 ?>
 <span id="to_name"></span><input name="mail[to_name]" id="wpem-us-autocomplete" autocomplete="off" value="<?php echo $to_name; ?>" type="text" class="regular-text" placeholder="Type Name to Search" /> 
   <?php if ( $errmsg = $errors->get_error_message('to_name') ) { ?>
		<p class="error ui-state-highlight ui-corner-all">
		<span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-info"></span><?php echo $errmsg ?></p>
	<?php } ?>
 </td>
 </tr>
 <tr>
 <th>To Email</th>
 <td>
 <?php
 $to_email = isset($mail['to_email'])?$mail['to_email']:'';
 ?>
 <span id="to_email"></span><input name="mail[to_email]" id="wpem-us-autocomplete-email" autocomplete="off" value="<?php echo $to_email; ?>" type="text" class="regular-text" placeholder="sendermail@mail.com" /> 
   <?php if ( $errmsg = $errors->get_error_message('to_email') ) { ?>
		<p class="error ui-state-highlight ui-corner-all">
		<span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-info"></span><?php echo $errmsg ?></p>
	<?php } ?>
 </td>
 </tr>
 <?php
 }
 add_action('wpem_extra_sm_fields','wpem_single_mail_fields', 10, 2);
 
 function wpem_validate_single_mail($mail){
    $errors = $mail['errors'];
	
    if($mail['source']['id']=='sm'){

		if (empty($mail['to_name'])) {
                $errors->add('to_name', __('To name cannot be empty', 'wpem'));
            } else {
                $mail['to_name'] = sanitize_text_field($mail['to_name']);
            }
		
		if(!is_email($mail['to_email'])){
		    $errors->add('to_email', __('invalid email', 'wpem', 'wpem'));
		}
		
		$mail['errors'] = $errors;
	}
	return $mail;
 }
 add_filter('wpem_validate_mail', 'wpem_validate_single_mail',10,2);
 
 function wpem_send_to_sm($recepients, $mail){
    $recepients[] = array('email' => $mail['to_email'], 'name' => $mail['to_name']);
	return $recepients;
 }
 
 add_filter('wpem-send_to_sm', 'wpem_send_to_sm', 10, 2);