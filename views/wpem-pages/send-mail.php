<?php
if ($_SERVER['SCRIPT_FILENAME'] == __FILE__)
    die('Access denied.');

$errors = isset($mail['errors']) ? $mail['errors'] : null;

if (!is_wp_error($errors)) {
    $errors = new WP_Error();
}

$data_sources = EM_Mailer::get_data_sources();

if (isset($_REQUEST['data_source']) && array_key_exists($_REQUEST['data_source'], $data_sources)) {
    $email_source = $_REQUEST['data_source'];
} elseif (isset($mail['source']['id'])) {
    $email_source = $mail['source']['id'];
} else {
    $email_source = 'wp';
}
?>


<div class="wrap">
    <div id="icon-options-general" class="icon32"><br /></div>

    <?php
    wpem_mail_tabs();
    if (isset($_REQUEST['message']) && isset($_REQUEST['recepients'])) {
        $recepients = $_REQUEST['recepients'];
        ?>
        <div id="message" class="updated"><p><?php echo sprintf(__('The email was sent to %s recepients', 'wpem'), $recepients) ?></p></div>
    <?php } ?>

    <form method="post" >
        <div class="wpem-source-div">
            <h3>Emails Source</h3>
            <div class="nf-radio">
                <ul>
                    <?php
                    foreach ($data_sources as $id => $data_source):
                        $disable = '';
                        $class = 'data-source';
                        if (!$data_source['active']) {
                            $disable = 'disabled="disabled"';
                            $class.=' disabled';
                        }
                        ?>
                        <li class="<?php echo $class ?>"><label><input <?php checked($email_source, $id) ?> <?php echo $disable ?> class="wpem-data-source" value="<?php echo $id ?>" type="radio" name="mail[source][id]" id="wpem_source-<?php echo $id ?>"> <?php echo $data_source['name'] ?></label></li>
<?php endforeach; ?>
                </ul>
            </div>
            <div class="clear"></div>
            <div>

                <?php if (isset($email_source) && array_key_exists($email_source, $data_sources)): ?>
                        <?php $source = isset($mail['source']) ? $mail['source'] : array() ?>
                    <div class="wpem_source-<?php echo $email_source ?>">
                    <?php do_action('wpem_email-source-' . $email_source, 'mail', $source) ?>
                    </div>
<?php endif; ?>

            </div>

        </div>

        <table class="form-table">
            <tbody>
                <tr>
                    <th valign="top" scope="row">Mail format</th>
<?php $mail_format = isset($mail['mail_format']) ? $mail['mail_format'] : 'html'; ?>
                    <td>
                        <select style="width: 158px;" id="wpem_mail_format" name="mail[mail_format]">
                            <option <?php selected($mail_format, 'html', 1); ?> value="html">HTML</option>
                            <option <?php selected($mail_format, 'plaintext', 1); ?>  value="plaintext">Plain text</option>
                        </select>
<?php if ($errmsg = $errors->get_error_message('mail_format')) { ?>
                            <p class="error ui-state-highlight ui-corner-all">
                                <span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-info"></span><?php echo $errmsg ?></p>
<?php } ?>
                    </td>
                </tr>
                <tr>
                    <th>From Name</th>
                    <td>
                        <?php
                        $from_name = isset($mail['from_name']) ? $mail['from_name'] : '';
                        ?>
                        <span id="from_name"></span><input name="mail[from_name]" placeholder="John Doe" value="<?php echo $from_name ?>" class="regular-text" type="text"/> 
<?php if ($errmsg = $errors->get_error_message('from_name')) { ?>
                            <p class="error ui-state-highlight ui-corner-all">
                                <span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-info"></span><?php echo $errmsg ?></p>
<?php } ?>
                    </td>
                </tr>

                <tr>
                    <th>From Email</th>
                    <td>
                        <?php
                        $from_email = isset($mail['from_email']) ? $mail['from_email'] : '';
                        ?>
                        <span id="from_email"></span><input name="mail[from_email]" value="<?php echo $from_email; ?>" type="text" class="regular-text" placeholder="sendermail@mail.com" /> 
<?php if ($errmsg = $errors->get_error_message('from_email')) { ?>
                            <p class="error ui-state-highlight ui-corner-all">
                                <span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-info"></span><?php echo $errmsg ?></p>
<?php } ?>
                    </td>
                </tr>
<?php do_action('wpem_extra_sm_fields', $email_source, $errors) ?>
                <tr>
                    <th>Template</th>
                    <td>
                        <p style="display:inline-block">
                            <?php
                            $temp_id = isset($mail['template']) ? $mail['template'] : '-1';
                            ?>
                            <select autocomplete="off" id="wpem_template" name="mail[template]" class = "wpem-template-selector">
                                <option <?php selected($temp_id, '-1', 1); ?> value="-1">- Select -</option>
                                <?php if (!empty($templates)): foreach ((array) $templates as $template): ?>		
                                        <option value="<?php echo $template->ID ?>" <?php selected($temp_id, $template->ID, 1) ?> > <?php echo $template->post_title ?> </option>
                                    <?php endforeach;
                                endif; ?>
                            </select>
                            <span class="spinner"></span>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th>Subject</th>
                    <td>
                        <?php $title = isset($mail['title']) ? $mail['title'] : ''; ?>
                        <input id="title" autocomplete="off" name="mail[title]" type="text" value="<?php echo $title ?>" class="regular-text" />
<?php if ($errmsg = $errors->get_error_message('title')) { ?>
                            <p class="error ui-state-highlight ui-corner-all">
                                <span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-info"></span><?php echo $errmsg ?></p>
<?php } ?>
                    </td>
                </tr>
                <tr>
                    <th>Body</th>
                    <td>
<?php if ($errmsg = $errors->get_error_message('body')) { ?>
                            <p class="error ui-state-highlight ui-corner-all">
                                <span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-info"></span><?php echo $errmsg ?></p>
                        <?php } ?>
                        <?php
                        $body = isset($mail['body']) ? $mail['body'] : '';

                        wp_editor($body, 'wpem_email_body', array(
						    'wpautop' => false,
                            'editor_height' => 400,
                            'dfw' => true,
                            'drag_drop_upload' => true,
                            'textarea_name' => 'mail[body]',
                        ));
                        ?>
                    </td>
                </tr>
                <tr>
                        <?php $attachments = isset($mail['attachments']) ? $mail['attachments'] : array(); ?>
                    <th>Attachments</th><td>
                        <?php do_action('add_attachment_fields', $attachments, 'mail'); ?>
                    </td>
                </tr>
                <tr>
                    <td></td><td>
                        <p class="submit" style="display:inline-block">
                            <input type="submit" name="wpem_test_mail" id="wpem_test_mail" class="button" value="<?php esc_attr_e('Send Test'); ?>" /> 
                            <input type="submit" name="wpem_send_mail" id="submit" class="button-primary" value="<?php esc_attr_e('Send'); ?>" />
							<span class="spinner" style="display: none;"></span>
                        </p>
                    </td></tr>
            </tbody>
        </table>
        <?php wp_nonce_field('wpem_send_mail', 'wpem_send_mail_nonce'); ?>
    </form>
</div> <!-- .wrap -->
