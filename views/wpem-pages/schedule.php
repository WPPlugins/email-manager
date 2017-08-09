<?php
$data_sources = EM_Mailer::get_data_sources();
$attachments = isset($schedule['attachments']) ? $schedule['attachments'] : array();
$errors = isset($schedule['errors']) ? $schedule['errors'] : null;
if (isset($_REQUEST['data_source']) && array_key_exists($_REQUEST['data_source'], $data_sources)) {
    $email_source = $_REQUEST['data_source'];
} elseif (isset($schedule['source']['id'])) {
    $email_source = $schedule['source']['id'];
} else {
    $email_source = 'wp';
}

if (!is_wp_error($errors)) {
    $errors = new WP_Error();
}
?>
<div class="wrap nf-mail-schedule">
    <?php wpem_mail_tabs(); ?>
    <br/>
    <h2>New Email Schedule <a class="button-secondary" href="<?php echo admin_url('admin.php?page=wpem_mail&mail_scope=schedules') ?>">Back To List</a></h2>	
    <?php if ($errors->get_error_code()) { ?>
        <div style="padding: 0 .7em;" class="error ui-state-highlight ui-corner-all">
            <p>
                <?php
                _e('There was a problem, please correct the form below and try again.', 'wpem');
                if ($errmsg = $errors->get_error_message('schedule_id'))
                    echo '<br/><b>schedule_id: </b>' . $errmsg;
                ?>
            </p>
        </div>
    <?php } ?>
    <form method="post" name="wpem_schedule" action="<?php echo esc_url($_SERVER['REQUEST_URI']) ?>">
        <?php wp_nonce_field('wpem_save_schedule_nonce', 'wpem_save_schedule'); ?>
        <input type="hidden" id="schedule_id" name="schedule[id]" value="<?php echo $id; ?>" />
        <div class="wpem-source-div">
            <h3>Emails Source</h3>
            <div class="nf-radio">
                <ul>
                    <?php
                    foreach ($data_sources as $ds_id => $data_source):
                        $disable = '';
                        $class = 'data-source';
                        if (!$data_source['active']) {
                            $disable = 'disabled="disabled"';
                            $class.=' disabled';
                        }
                        ?>
                        <li class="<?php echo $class ?>"><label><input class="wpem-data-source" <?php echo $disable ?> value="<?php echo $ds_id ?>" <?php checked($email_source, $ds_id) ?> type="radio" name="schedule[source][id]" id="wpem_source-<?php echo $ds_id ?>"> <?php echo $data_source['name'] ?></label></li>
                <?php endforeach; ?>
                </ul>
<?php if ($errmsg = $errors->get_error_message('email_source')) { ?>
                    <p class="error ui-state-highlight ui-corner-all">
                        <span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-info"></span><?php echo $errmsg ?></p>
<?php } ?>
            </div>
            <div class="clear"></div>
            <div>

                    <?php if (isset($email_source) && array_key_exists($email_source, $data_sources)): ?>
                    <div class="wpem_source-<?php echo $email_source ?>">
                        <?php $source = isset($schedule['source']) ? $schedule['source'] : array() ?>
                    <?php do_action('wpem_email-source-' . $email_source, 'schedule', $source) ?>
                    </div>
<?php endif; ?>

            </div>

        </div>

        <table class="form-table">
            <tbody>

                <tr>
                    <th valign="top" scope="row">Mail format</th>
                    <td>
                        <?php
                        $mail_format = isset($schedule['mail_format']) ? $schedule['mail_format'] : 'html';
                        ?>
                        <select style="width: 158px;" id="wpem_mail_format" name="schedule[mail_format]">
                            <option <?php selected('html', $mail_format, 1) ?> value="html">HTML</option>
                            <option value="plaintext">Plain text</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th>From Name</th>
                    <td>
                        <?php
                        $from_name = isset($schedule['from_name']) ? $schedule['from_name'] : '';
                        ?>
                        <span id="from_name"></span><input name="schedule[from_name]" placeholder="John Doe" value="<?php echo $from_name ?>" class="regular-text" type="text"/> 
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
                        $from_email = isset($schedule['from_email']) ? $schedule['from_email'] : '';
                        ?>
                        <span id="from_email"></span><input name="schedule[from_email]" value="<?php echo $from_email; ?>" type="text" class="regular-text" placeholder="sendermail@mail.com" /> 
<?php if ($errmsg = $errors->get_error_message('from_email')) { ?>
                            <p class="error ui-state-highlight ui-corner-all">
                                <span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-info"></span><?php echo $errmsg ?></p>
<?php } ?>
                    </td>
                </tr>


<?php do_action('wpem-from-email-field') ?>
                <tr  class="wpem_icons">
                    <th>Start Date</th>
                    <td>
                        <span id="start_date"></span>
                        <?php
                        $start_date = isset($schedule['start_date']) ? $schedule['start_date'] : '';
                        ?>
                        <input name="schedule[start_date]" value="<?php echo $start_date; ?>" class="wpem-date" type="text"/>
<?php if ($errmsg = $errors->get_error_message('start_date')) { ?>
                            <p class="error ui-state-highlight ui-corner-all">
                                <span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-info"></span><?php echo $errmsg ?></p>
<?php } ?>
                    </td>
                </tr>
                <tr  class="wpem_icons">
                    <th>Frequency</th>
                    <td>
                        <span id="frequency"></span>
                        <?php
                        $frequency = isset($schedule['frequency']) ? $schedule['frequency'] : 'Monthly';
                        ?>
                        <select name="schedule[frequency]">
                            <option <?php selected($frequency, 'Once', 1); ?> value="Once" >Once </option>
                            <option <?php selected($frequency, 'Daily', 1); ?> value="Daily" >Daily </option>
                            <option <?php selected($frequency, 'Weekly', 1); ?> value="Weekly" >Weekly </option>
                            <option <?php selected($frequency, 'Monthly', 1); ?> value="Monthly" >Monthly </option>
                            <option <?php selected($frequency, 'Yearly', 1); ?> value="Yearly" >Yearly </option>
                        </select>
<?php if ($errmsg = $errors->get_error_message('frequency')) { ?>
                            <p class="error ui-state-highlight ui-corner-all">
                                <span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-info"></span><?php echo $errmsg ?></p>
<?php } ?>
                    </td>
                </tr>
                <tr  class="wpem_icons">
                    <th>End Date</th>
                    <td>
                        <span id="end_date"></span>
                        <?php
                        $end_date = isset($schedule['end_date']) ? $schedule['end_date'] : '';
                        ?>
                        <input name="schedule[end_date]" value="<?php echo $end_date ?>" class="wpem-date" type="text"/>
<?php if ($errmsg = $errors->get_error_message('end_date')) { ?>
                            <p class="error ui-state-highlight ui-corner-all">
                                <span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-info"></span><?php echo $errmsg ?></p>
<?php } ?>
                    </td>
                </tr>

                <tr>
                    <th>Template</th>
                    <td>
                        <p style="display:inline-block">
                            <?php
                            $temp_id = isset($schedule['template']) ? $schedule['template'] : '-1';
                            ?>
                            <select name="schedule[template]" id="wpem_template" class = "wpem-template-selector">
                                <option <?php selected($temp_id, '-1', 1); ?> value="-1">- Select -</option>
                                <?php if (!empty($templates)): foreach ((array) $templates as $template): ?>		
                                        <option value="<?php echo $template->ID ?>" <?php selected($temp_id, $template->ID, 1) ?> > <?php echo $template->post_title ?> </option>
                                    <?php endforeach;
                                endif; ?>
                            </select>
                            <span class="spinner"></span>
                        </p>
<?php if ($errmsg = $errors->get_error_message('template')) { ?>
                            <p class="error ui-state-highlight ui-corner-all">
                                <span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-info"></span><?php echo $errmsg ?></p>
<?php } ?>
                    </td>
                </tr>

                <tr>
                    <th>Subject</th>
                    <td>
                        <?php
                        $title = isset($schedule['title']) ? $schedule['title'] : '';
                        ?>
                        <input id="title" name="schedule[title]" value="<?php echo $title ?>" class="regular-text"  placeholder="Mail Title" type="text">
<?php if ($errmsg = $errors->get_error_message('title')) { ?>
                            <p class="error ui-state-highlight ui-corner-all">
                                <span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-info"></span>
                            <?php echo $errmsg ?>
                            </p>
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
                        $body = isset($schedule['body']) ? $schedule['body'] : '';
                        wp_editor($body, 'wpem_email_body', array(
						    'wpautop' => false,
                            'editor_height' => 400,
                            'dfw' => true,
                            'drag_drop_upload' => true,
                            'textarea_name' => 'schedule[body]'
                        ));
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>Attachments</th><td>
<?php do_action('add_attachment_fields', $attachments, 'schedule'); ?>
                    </td>
                </tr>
                <tr>
                    <td></td><td>
                        <p class="submit">
                            <input type="submit" name="wpem_test_mail" id="wpem_test_mail" class="button" value="<?php esc_attr_e('Send Test'); ?>" /> 
                            <input type="submit" name="schedule[save]"  id="submit" class="button-primary" value="<?php esc_attr_e('Submit'); ?>" />

                        </p>
                    </td></tr>
            </tbody>
        </table>

    </form>


</div> <!-- .wrap -->
