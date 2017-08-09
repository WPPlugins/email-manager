<?php
if ($_SERVER['SCRIPT_FILENAME'] == __FILE__)
    die('Access denied.');

$updated = false;

if (isset($_REQUEST['settings-updated']) && $_REQUEST['settings-updated']) {
    $updated = $_REQUEST['settings-updated'];
}
?>
<div class="wrap">
    <div id="icon-options-general" class="icon32"><br /></div>
    <?php wpem_mail_tabs(); ?>
    <?php if ($updated) : ?>
        <div id="message" class="updated"><p><?php _e('Settings Updated', 'wpem') ?></p></div>
    <?php endif; ?>
    <form method="post" action="options.php">
        <?php settings_fields('wpem_settings'); ?>
        <?php do_settings_sections('wpem_settings'); ?>

        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
        </p>
    </form>
</div> <!-- .wrap -->
