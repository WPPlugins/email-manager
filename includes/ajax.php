<?php
/**
 * the ajax SWTICHBOARD that fires specific functions
 * according to the value of Query Var 'wpem_ajx' 
 * @package eMail Manager
 * @author Mucunguzi Ayebare
 */
if ($_SERVER['SCRIPT_FILENAME'] == __FILE__)
    die('Access denied.');

if (is_admin() && isset($_REQUEST['wpem_ajx'])) {


    switch ($_REQUEST['wpem_ajx']) {
        case 'load_notification_temp':
            $t_id = intval($_REQUEST['t_id']); //get template ID from ajax request
            $n_id = $_REQUEST['n_id']; // get notification ID from ajax request
            $post = get_post($t_id);
            $error = false;
            $json = array();
            if (!empty($post)) {
                $notification_body = '[' . __('system-message', 'wpem') . ']';
                $content = $post->post_content;
                $content = str_replace("[mail_body]", $notification_body, $content);
                $json['title'] = $post->post_title;
                $json['css'] = get_post_meta($post->ID, 'wpem_css-box-field', true);
                if ($_REQUEST['editor_type'] == 'rich') {
                    $json['body'] = htmlspecialchars_decode(wp_richedit_pre($content));
                } else {
                    $json['body'] = htmlspecialchars_decode(wp_htmledit_pre($content));
                }
            } else {
                $json['error'] = __('Post not found', 'Zanto');
            }
            do_action('wpem_load_notification_temp_ajx', $n_id);

            echo json_encode($json);
            die();
            break;

        case 'load_temp':
            $t_id = intval($_REQUEST['t_id']); //get template ID from ajax request
            $post = get_post($t_id);
            $error = false;
            $json = array();
            if (!empty($post)) {
                $content = $post->post_content;
                $json['title'] = $post->post_title;
                $json['css'] = get_post_meta($post->ID, 'wpem_css-box-field', true);
                if ($_REQUEST['editor_type'] == 'rich') {
                    $json['body'] = htmlspecialchars_decode(wp_richedit_pre($content));
                } else {
                    $json['body'] = htmlspecialchars_decode(wp_htmledit_pre($content));
                }
            } else {
                $json['error'] = __('Post not found', 'Zanto');
            }
            do_action('wpem_load_temp_ajx', $t_id);

            echo json_encode($json);
            die();
            break;

        case 'user_search':
            check_ajax_referer('wpem_us_nonce', '_wpnonce');
            $search_term = $_REQUEST['term'];
            $users = get_users();
            $matches = array();

            foreach ($users as $user) {

                if (false !== stripos($user->user_login, $search_term) || false !== stripos($user->display_name, $search_term)) {
                    $full_names = (!empty($user->first_name) || !empty($user->last_name)) ? ucfirst($user->first_name) . ' ' . ucfirst($user->last_name) : $user->display_name;
                    $value = $full_names;
                    $matches[] = array('label' => $full_names . ' (' . $user->user_login . ')' . '<' . $user->user_email . '>', 'value' => $value, 'email' => $user->user_email);
                }
                if (count($matches) == 100)
                    break;
            }


            if (!empty($matches)) {
                $json = $matches;
            } else {
                $json = array('label' => __('No Matches', 'Zanto'), 'value' => '');
            }
            echo json_encode($json);
            die();
            break;

        case 'test_mail':
            $ms = WPEM()->modules['settings']->settings['mail'];
            $mail['source'] = array('id' => 'sm');
            $mail['mail_format'] = $_REQUEST['m_format'];
            $mail['from_email'] = (!empty($ms['from_sender_email'])) ? $ms['from_sender_email'] : get_option('admin_email');
            $mail['from_name'] = (!empty($ms['from_sender_name'])) ? $ms['from_sender_name'] : get_bloginfo('name');
            $mail['title'] = __('Test Mail', 'wpem');
            $mail['body'] = $_REQUEST['m_body'];
            $mail['template'] = $_REQUEST['temp_id'];
            $current_user = (get_user_by('id', get_current_user_id()));
            $full_names = (!empty($current_user->first_name) || !empty($current_user->last_name)) ? ucfirst($current_user->first_name) . ' ' . ucfirst($current_user->last_name) : $current_user->display_name;
            $mail['to_email'] = $current_user->user_email;
            $mail['to_name'] = $full_names;
            $json = array();
            try {
                $mail = EM_Mailer::send_mail($mail);
                if (is_integer($mail)) {
                    global $user_email;
                    $json['sent'] = sprintf(__('Test email sent to %s', 'wpem'), $user_email);
                } else {
                    if (isset($mail['errors'])) {
                        if ($errmsg = $mail['errors']->get_error_message('body')) {
                            $json['error'] = $errmsg;
                        } else {
                            print_r($mail['errors']);
                        }
                    }
                }
                echo json_encode($json);
            } catch (Exception $e) {
                ?><span style="color:#f00" ><?php echo $e->getMessage() ?></span> <?php
            }
            die();
            break;

        default:
            $output = 'No function specified, check your jQuery.ajax() call';
            break;
    }

    die();
}