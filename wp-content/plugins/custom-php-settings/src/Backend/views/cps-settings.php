<?php
$ini_settings = $this->getIniSettings();
?>
<div class="wrap custom-php-settings">
    <?php require_once('cps-tabs.php'); ?>
    <input type="text" id="search" name="search" placeholder="<?php _e('Search for settings', self::TEXT_DOMAIN); ?>" />
    <input type="checkbox" id="cbkModified" /> Show customized
    <table class="custom-php-settings-table widefat">
        <thead>
            <th><?php echo __('Name', self::TEXT_DOMAIN); ?></th>
            <th><?php echo __('Value', self::TEXT_DOMAIN); ?></th>
            <th><?php echo __('Default', self::TEXT_DOMAIN); ?></th>
            <th></th>
        </thead>
        <?php $i = 0; ?>
        <?php foreach ($ini_settings as $key => $value) : ?>
            <?php $class = ($value['global_value'] !== $value['local_value'] ? 'modified' : ''); ?>
            <?php $class .= (++$i & 1) ? ' striped' : ''; ?>
            <tr class="<?php echo $class; ?>">
                <td><?php echo $key; ?></td>
                <td><?php echo $value['local_value']; ?></td>
                <td><?php echo $value['global_value']; ?></td>
                <td><span title="<?php _e('Copy', self::TEXT_DOMAIN); ?>" class="dashicons dashicons-insert"></span></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
