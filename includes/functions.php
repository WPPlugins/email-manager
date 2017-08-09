<?php

/**
 * Individual attachment row.
 *
 * Used to output a table row for each attachment associated with a download.
 * Can be called directly, or attached to an action.
 *
 * @since 1.2.2
 * @param string $key Array key
 * @param array $args Array of all the arguments passed to the function
 * @return void
 */
function wpem_render_attachment_row($key = '', $args = array(), $scope) {
    $defaults = array(
        'name' => null,
        'attachment' => null,
        'attachment_id' => null
    );

    $args = wp_parse_args($args, $defaults);
    ?>

    <!--
    Disabled until we can work out a way to solve the issues raised here: https://github.com/easydigitaldownloads/Easy-Digital-Downloads/issues/1066
    <td>
            <span class="wpem_draghandle"></span>
    </td>
    -->
    <td>
        <input type="hidden" name="<?php echo $scope ?> [attachments][<?php echo absint($key); ?>][attachment_id]" class="wpem_repeatable_attachment_id_field" value="<?php echo esc_attr(absint($args['attachment_id'])); ?>"/>
        <?php
        echo wpem_text(array(
            'name' => $scope . '[attachments][' . $key . '][name]',
            'value' => $args['name'],
            'placeholder' => __('Attachment Name', 'wpem'),
            'class' => 'wpem_repeatable_name_field large-text'
        ));
        ?>
    </td>

    <div class="wpem_repeatable_upload_field_container">
        <td>

            <?php
            echo wpem_text(array(
                'name' => $scope . '[attachments][' . $key . '][attachment]',
                'value' => $args['attachment'],
                'placeholder' => __('Upload or enter the attachment URL', 'wpem'),
                'class' => 'wpem_repeatable_upload_field wpem_upload_field large-text'
            ));
            ?>
        </td>
        <td>
            <span class="wpem_upload_attachment">
                <a href="#" data-uploader_title="" data-uploader_button_text="<?php _e('Insert', 'wpem'); ?>" class="wpem_upload_attachment_button button" onclick="return false;"><?php _e('Upload', 'wpem'); ?></a>
            </span>
        </td>
    </div>


    <td>
        <a href="#" class="wpem_remove_repeatable" data-type="attachment" style="background: url(<?php echo admin_url('/images/xit.gif'); ?>) no-repeat;">&times;</a>
    </td>
    <?php
}

add_action('wpem_render_attachment_row', 'wpem_render_attachment_row', 10, 3);

function wpem_text($args = array()) {
// Backwards compatabliity
    if (func_num_args() > 1) {
        $args = func_get_args();

        $name = $args[0];
        $value = isset($args[1]) ? $args[1] : '';
        $label = isset($args[2]) ? $args[2] : '';
        $desc = isset($args[3]) ? $args[3] : '';
    }

    $defaults = array(
        'name' => isset($name) ? $name : 'text',
        'value' => isset($value) ? $value : null,
        'label' => isset($label) ? $label : null,
        'desc' => isset($desc) ? $desc : null,
        'placeholder' => '',
        'class' => 'regular-text',
        'disabled' => false,
        'autocomplete' => ''
    );

    $args = wp_parse_args($args, $defaults);

    $disabled = '';
    if ($args['disabled']) {
        $disabled = ' disabled="disabled"';
    }

    $output = '<span id="wpem-' . sanitize_key($args['name']) . '-wrap">';

    $output .= '<label class="wpem-label" for="wpem-' . sanitize_key($args['name']) . '">' . esc_html($args['label']) . '</label>';

    if (!empty($args['desc'])) {
        $output .= '<span class="wpem-description">' . esc_html($args['desc']) . '</span>';
    }

    $output .= '<input type="text" name="' . esc_attr($args['name']) . '" id="' . esc_attr($args['name']) . '" autocomplete="' . esc_attr($args['autocomplete']) . '" value="' . esc_attr($args['value']) . '" placeholder="' . esc_attr($args['placeholder']) . '" class="' . $args['class'] . '"' . $disabled . '/>';

    $output .= '</span>';

    return $output;
}

/**
 * Sanitize the file downloads
 *
 * Ensures files are correctly mapped to an array starting with an index of 0
 *
 * @since 1.0
 * @param array $files Array of all the file downloads
 * @return array $files Array of the remapped file downloads
 */
function wpem_sanitize_attachments($files) {

// Clean up filenames to ensure whitespaces are stripped
    foreach ($files as $id => $attachment) {

        if (empty($files[$id]['attachment']))
            unset($files[$id]);

        $files[$id]['attachment'] = trim($attachment['attachment']);

        if (!empty($files[$id]['name'])) {
            $files[$id]['name'] = trim($attachment['name']);
        }
    }

// Make sure all files are rekeyed starting at 0
    return array_values($files);
}

function attachments_fields($attachments, $scope) {
    ?>
    <div id="wpem_attachments">

        <input type="hidden" id="wpem_attachments" class="wpem_repeatable_upload_name_field" value=""/>

        <div id="wpem_attachment_fields" class="wpem_meta_table_wrap">
            <table class="widefat wpem_repeatable_table" width="100%" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th style="width: 20%"><?php _e('Attachment Name', 'wpem'); ?></th>
                        <th><?php _e('Attachment URL', 'wpem'); ?></th>
                        <?php do_action('wpem_attachment_table_head', $attachments, $scope); ?>
                        <th style="width: 2%"></th>
                        <th style="width: 2%"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($attachments) && is_array($attachments)) :
                        foreach ($attachments as $key => $value) :
                            $name = isset($value['name']) ? $value['name'] : '';
                            $attachment = isset($value['attachment']) ? $value['attachment'] : '';
                            $attachment_id = isset($value['attachment_id']) ? absint($value['attachment_id']) : false;

                            $args = apply_filters('wpem_attachment_row_args', compact('name', 'attachment', 'attachment_id'), $value);
                            ?>
                            <tr class="wpem_repeatable_upload_wrapper wpem_repeatable_row">
                                <?php do_action('wpem_render_attachment_row', $key, $args, $scope); ?>
                            </tr>
                            <?php
                        endforeach;
                    else :
                        ?>
                        <tr class="wpem_repeatable_upload_wrapper wpem_repeatable_row">
                            <?php do_action('wpem_render_attachment_row', 0, array(), $scope); ?>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="submit" colspan="4" style="float: none; clear:both; background: #fff;">
                            <a class="button-secondary wpem_add_repeatable" style="margin: 6px 0 10px;"><?php _e('Add New Attachment', 'wpem'); ?></a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}

add_action('add_attachment_fields', 'attachments_fields', 10, 2);

function wpem_mail_tabs() {

    $activ_tab = array('sc' => '', 'nt' => '', 'st' => '', 'sm' => '');

    if (isset($_REQUEST['mail_scope'])) {
        switch ($_REQUEST['mail_scope']) {
            case 'schedules':
                $activ_tab['sc'] = 'nav-tab-active';
                break;
            case 'notifications':
                $activ_tab['nt'] = 'nav-tab-active';
                break;
            case 'settings':
                $activ_tab['st'] = 'nav-tab-active';
                break;
            default:
                break;
        }
    } else {
        $activ_tab['sm'] = 'nav-tab-active';
    }
    ?>
    <h2 class="nav-tab-wrapper">
        <a href="<?php echo admin_url('admin.php?page=wpem_mail'); ?>" class="nav-tab  <?php echo $activ_tab['sm'] ?>"><?php _e('Send Mail', 'ninja-mail') ?></a>
        <a href="<?php echo admin_url('admin.php?page=wpem_mail&mail_scope=schedules'); ?>" class="nav-tab  <?php echo $activ_tab['sc'] ?>"><?php _e('Schedule', 'wpem') ?></a>
        <a href="<?php echo admin_url('admin.php?page=wpem_mail&mail_scope=notifications'); ?>" class="nav-tab <?php echo $activ_tab['nt'] ?>"><?php _e('Notifications', 'wpem') ?></a>
        <a href="<?php echo admin_url('admin.php?page=wpem_mail&mail_scope=settings'); ?>" class="nav-tab <?php echo $activ_tab['st'] ?>"><?php _e('Settings', 'wpem') ?></a>
    </h2>
    <?php
}

function wpem_wp_user_roles($form_context, $source) {
    $editable_roles = array_reverse(get_editable_roles());
    $roles = array();
    foreach ($editable_roles as $role => $details) {
        $roles[] = $role;
    }
    ?>
    <div class="wpem_default_source">
        <strong>Send to users with role: </strong><br/>

        <script type="text/javascript">
            var roles = <?php echo json_encode($roles); ?>;
            jQuery(document).ready(function($){
                $('#cartabs4').tabSelect({
                    tabElements: roles,
                    formElement: "#wpem-wp-roles"
                });
            });
        </script>

        <select MULTIPLE  id="wpem-wp-roles" name="<?php echo $form_context . '[source][wp_group][]'; ?>" autocomplete="off" style="display:none">
            <?php
            foreach ($roles as $role):
                $selected = '';
                if (empty($source['wp_group'])) {
                    $source['wp_group'] = array('subscriber', 'administrator');
                }

                if (in_array($role, $source['wp_group'])) {
                    $selected = 'selected';
                }
                ?>
                <option  <?php echo $selected ?> value="<?php echo $role ?>"><?php echo $role ?></option>
            <?php endforeach; ?>
        </select>

        <div class="wpem-roles">
            <span id="cartabs4"></span>
        </div>
    </div>
    <?php
}

add_action('wpem_email-source-wp', 'wpem_wp_user_roles', 10, 2);

function wpem_merge_atts($pairs, $atts) {
    $atts = (array) $atts;
    $out = array();
    foreach ($pairs as $name => $default) {

        if (array_key_exists($name, $atts)) {
            $att = array_shift($atts);

            if (is_array($att) && is_array($default)) {
                foreach ($att as $att_key => $att_value) {
                    foreach ($default as $key => $value) {
                        if ($att_key == $key)
                            $default[$key] = $att_value;
                    }
                }
            }else {
                $default = $att;
            }
        }

        $out[$name] = $default;
    }
    return $out;
}

if (!function_exists('wp_new_user_notification')) :

    /**
     * Email login credentials to a newly-registered user. (overiding "wp_new_user_notification" wordpress pluggable function)
     *
     * A new user registration notification is also sent to admin email.
     *
     * @since 2.0.0
     *
     * @param int    $user_id        User ID.
     * @param string $plaintext_pass Optional. The user's plaintext password. Default empty.
     */
    function wp_new_user_notification($user_id, $plaintext_pass = '') {
        $user = get_userdata($user_id);

// The blogname option is escaped with esc_html on the way into the database in sanitize_option
// we want to reverse this for the plain text arena of emails.
        $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

        $message = sprintf(__('New user registration on your site %s:'), $blogname) . "\r\n\r\n";
        $message .= sprintf(__('Username: %s'), $user->user_login) . "\r\n\r\n";
        $message .= sprintf(__('E-mail: %s'), $user->user_email) . "\r\n";

        @wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration'), $blogname), $message);

        if (empty($plaintext_pass))
            return;

        $message = sprintf(__('Username: %s'), $user->user_login) . "\r\n";
        $message .= sprintf(__('Password: %s'), $plaintext_pass) . "\r\n";
        $message .= wp_login_url() . "\r\n";
        $message = apply_filters('wpem_new_user_notification_body', $message);
        $subject = sprintf(__('[%s] Your username and password'), $blogname);
        wp_mail($user->user_email, $subject, $message);
    }






endif;