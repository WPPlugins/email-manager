<?php
$request_source = isset($_REQUEST['data_source']) ? $_REQUEST['data_source'] : null;
?>
<div class="wrap nf-mail-notifications">
    <?php wpem_mail_tabs(); ?>
    <br/>
    <h2><?php echo $notification['name'] ?> <a class="button-secondary" href="<?php echo admin_url('admin.php?page=wpem_mail&mail_scope=notifications') ?>">Back To Notifications List</a></h2>	

    <form method="post" name="wpem_edit_notification" action="<?php echo esc_url($_SERVER['REQUEST_URI']) ?>">

        <input type="hidden" id="notification_id" name="notification[id]" value="<?php echo $id; ?>" />
        <?php wp_nonce_field('wpem_edit_notification_nonce', 'wpem_edit_notification'); ?>
        <table class="form-table">
            <tbody>
                <tr>
                    <th valign="top" scope="row">Notification Setting</th>
                    <?php
                    $use_custom = isset($notification['use_custom']) ? true : false;
                    $display_class = ($use_custom) ? 'class="wpem-show"' : 'class="wpem-hide"';
                    ?>
                    <td><label><input name="notification[use_custom]" id="custom-notification" autocomplete="off" type="checkbox" <?php checked(true, $use_custom, 1) ?>> Use Custom Notification</label></td>
                </tr>
                <tr <?php echo $display_class ?>>
                    <th valign="top" scope="row">Mail format</th>
                    <td>
                        <?php
                        $mail_format = isset($notification['mail_format']) ? $notification['mail_format'] : 'html';
                        ?>
                        <select style="width: 158px;" autocomplete="off" name="notification[mail_format]" >
                            <option <?php selected($mail_format, 'html', 1) ?> value="html">HTML</option>
                            <option <?php selected($mail_format, 'plaintext', 1) ?> value="plaintext">Plain text</option>
                        </select>
                    </td>
                </tr>

                <tr <?php echo $display_class ?> >
                    <th>Template</th>
                    <td>
                        <p style="display:inline-block">
                            <select name="notification[template]" autocomplete="off" id = "wpem-notification-template">
                                <option value=0>- Select -</option>
                                <?php
                                $temp_id = isset($notification['template']) ? $notification['template'] : '';
                                if (!empty($templates)): foreach ((array) $templates as $template):
                                        ?>		
                                        <option value="<?php echo $template->ID ?>" <?php selected($temp_id, $template->ID, 1) ?> > <?php echo $template->post_title ?> </option>
    <?php endforeach;
endif; ?>
                            </select>
                            <span class="spinner"></span>
                        </p>
                    </td>
                </tr>
                <tr <?php echo $display_class ?>>
                    <th>Body</th>
                    <td>
                        <?php
                        $content = isset($notification['body']) ? htmlspecialchars_decode(wp_richedit_pre($notification['body'])) : '[' . __('system-message', 'wpem') . ']';

                        if (!empty($temp_id)) {
                            $_REQUEST['temp_id'] = $temp_id;
                        }

                        wp_editor($content, 'notification_body', array(
						    'wpautop' => false,
                            'editor_height' => 400,
                            'dfw' => true,
                            'drag_drop_upload' => true,
                            'textarea_name' => 'notification[body]',
                        ));
                        ?>
                    </td>
                </tr>

                <tr>
                    <td></td><td>
                        <p class="submit">
                            <input type="submit" name="notification[save]" id="submit" class="button-primary" value="<?php esc_attr_e('Submit'); ?>" />
                        </p>
                    </td></tr>
            </tbody>
        </table>

    </form>


</div> <!-- .wrap -->
