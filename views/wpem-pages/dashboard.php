<?php
if ($_SERVER['SCRIPT_FILENAME'] == __FILE__)
    die('Access denied.');

$tab = array();
$support = isset($_GET['support']) ? true : false;
$tab['d'] = ($support) ? '' : 'nav-tab-active';
$tab['s'] = ($support) ? 'nav-tab-active' : '';
?>
<h2 class="nav-tab-wrapper">
    <a class="nav-tab  <?php echo $tab['d'] ?>" href="<?php echo admin_url('admin.php?page=wpem_dashboard') ?>"><?php _e('Dashboard','wpem')?></a>
    <a class="nav-tab <?php echo $tab['s'] ?> " href="<?php echo admin_url('admin.php?page=wpem_dashboard&support=true') ?>"><?php _e('How To','php')?></a>
</h2>


<div class="wrap about-wrap">
    <?php if (!$support): ?>
        <h1><?php _e('Welcome to eMail Manager','wpem'); ?></h1>

        <div class="about-text wpem-welcome">
            <?php echo sprintf(__('Thank you for using eMail Manager, I made this plugin as easy to use as possible and provided plenty of hooks to make it easily scalable. You can show support for this plugin by rating it well at %s', 'wpem'), '<a href="https://wordpress.org/support/view/plugin-reviews/email-manager">WordPress.Org</a>'); ?>
        </div>

        <div class="changelog point-releases"></div>

        <div class="changelog">

            <div id="extend" class="changelog">
                <h3 style="text-align:left"><?php _e("HTML Email Templates", 'wpem') ?></h3>

                <div class="feature-section images-stagger-right">
                    <img alt="HTM email templates" src="<?php echo WPEM_PLUGIN_URL . 'images/responsive_design.png' ?>" class=""/>
                    <h4 style="text-align: left"><?php _e('Plenty of Free Responsive Email Template Themes', 'wpem') ?></h4></br>
                    <p style="text-align: left"><?php _e("With millions of people pulling out their phones to check their email, the need for responsive HTML emails can't be stressed enough. The Good news is that a handfull of companies and individuals have met this challenge and provide you with free templates!", 'wpem') ?>
                    </p>
                    <p style="text-align: left"><?php echo sprintf(__('We have listed two of the many sites where you can download HTML Email templates that will work seemlessly with Email Manager. Please read <a href="%s">this tutorial </a> to learn how to port the templates directly to Email Manager.', 'wpem'), "https://wordpress.org/plugins/email-manager/faq/") ?></p>
                    <p style="text-align:left">    
                        <a class="button" title="Visit the extension's page" href="http://zurb.com/playground/responsive-email-templates" target="_blank">Zurb Templates &raquo;</a>
                        <a class="button" title="Visit the extension's page" href="https://github.com/mailchimp/Email-Blueprints" target="_blank">Email Blue Prints &raquo;</a>
                    </p>

                </div>
            </div>

            <div class="changelog">
                <h3 style="text-align:right"><?php _e('Professional Support', 'wpem') ?></h3>

                <div class="feature-section images-stagger-left wpem_icons">
                    <span id="tools"></span>
                    <h4 style="text-align: right"><?php _e('Do you need a hand rolling out your email designs?', 'wpem') ?></h4>
                    <p style="text-align: right; padding-left: 2em;"><?php _e('You can get Free support for plugin bugs at the Forum, but for custom work Like CSS modifications,  conversion of a PSD email template to a usable HTML CSS email template and plugin modification, you need to hire a professional','wpem')?></p>
                    <p style="text-align:right">    
                        <a class="button-primary dashboard-buttons" title="<?php _e('Employ our expertise', 'wpem') ?>" href="mailto:ayebare11@gmail.com?cc=<?php echo get_option('admin_email')?>" ><?php _e('Get A Free Quote', 'wpem') ?> &raquo;</a>
                    </p>
                </div>
            </div>

            <div id="wpem-changelog" class="changelog">

                <h3><?php printf(__('Changelog in version %1$s', 'wpem'), WPEM_VERSION); ?></h3>

                <p><?php do_action('wpem_changelog'); ?></p>

            </div>


            <div class="return-to-dashboard">
                <a href="<?php echo esc_url(self_admin_url()); ?>"><?php is_blog_admin() ? _e('Go to Dashboard &rarr; Home', 'wpem') : _e('Go to Dashboard', 'wpem'); ?></a>
            </div>

        </div>
    <?php else: ?>

        <h1><?php _e('Short Tutorials', 'wpem'); ?></h1>

        <div class="changelog">
            <div class="about-text wpem-welcome">
                <?php _e('Email Manager can be used to schedule regular emails, modifiy WordPress notifications, send targeted mass mails, create newsletters e.t.c. In case you are stuck, you can read through the <a href="https://wordpress.org/plugins/email-manager/faq/" target="_blank">Frequently Asked Questions</a>. Need more help with usage, you can post a question in our forum. Below are a few links to kickstart you.','wpem')?>
            </div>
            <div class="feature-section col two-col">
                <div>
                    <br/>
                    <a class="button-secondary wpem-help" title="documentation" href="https://wordpress.org/plugins/email-manager/faq/"><?php _e('Creating an HTML Template', 'wpem'); ?></a>
                </div>
                <div class="last-feature">
                    <br/>
                    <a class="button-secondary wpem-help" title="faq" href="https://wordpress.org/plugins/email-manager/other_notes/"><?php _e('Short Codes', 'wpem'); ?></a>
                </div>
            </div><!-- .two-col -->
            <div class="feature-section col two-col">
                <div>
                    <a class="button-secondary wpem-help" title="code snippets" href="https://wordpress.org/plugins/email-manager/faq/"><?php _e('Creating Newsletter Template', 'wpem'); ?></a>
                </div>
                <div class="last-feature">
                    <a class="button-secondary wpem-help" title="forum" href="https://wordpress.org/support/plugin/email-manager" target="_blank"><?php _e('Discuss in the user forum', 'wpem'); ?></a>
                </div>
            </div><!-- .two-col -->
        </div><!-- .changelog -->

        <div class="changelog point-releases"></div>

        <div class="changelog">
            <div class="feature-section col three-col">

                <div>
                    <h3><?php _e('Need Expert Help!', 'wpem'); ?></h3>
                    <p><?php _e('<strong>We handle all plugin bugs free!</strong><br/> For custom plugin or Email Template Modification or devevlopement.', 'wpem') ?></br>

                        <a class="button dashboard-buttons" href="mailto:ayebare11@gmail.com?cc=<?php echo get_option('admin_email')?>"><?php _e('Contact Me','wpem')?></a>
                    </p>
                </div>

                <div>
                    <h3><?php _e('Are you a happy user?', 'wpem'); ?></h3>
                    <p><?php _e('If you are happy with the plugin, say it on wordpress.org and give us a nice review! <br />(We are fueled by your feedbacks...)', 'wpem') ?></br>
                        <a class="button-primary dashboard-buttons"  href="https://wordpress.org/support/view/plugin-reviews/email-manager" target="_blank"><?php _e('Review Email Manager','wpem')?> &raquo;</a></p>
                </div>

                <div class="last-feature">
                    <h3><?php _e('Translation', 'wpem'); ?></h3>
					    <p><?php _e('Would you like to help Translate Email Manager to your Native language? Let me know so I can send you the .po files', 'wpem') ?></br>
                        <a class="button dashboard-buttons" href="mailto:ayebare11@gmail.com?cc=<?php echo get_option('admin_email')?>"><?php _e('Send Me .po file','wpem')?> &raquo;</a></p>

                </div><!-- .feature-section -->
            </div><!-- .changelog -->
        </div>
    <?php endif ?>
</div>