<?php
global $arformcontroller,$arformsplugin;
$setvaltolic = 0;
$setvaltolic = $arformcontroller->$arformsplugin();
?>
<div class="wrap arfforms_page">
    <div class="top_bar" style="margin-bottom: 10px;">
	<span class="h2"> <?php echo addslashes(esc_html__('ARForms Add-Ons','ARForms')); ?></span>
    </div>
	<?php
    if ($setvaltolic != 1) {
        $admin_css_url = admin_url('admin.php?page=ARForms-license');
        ?>

        <div style="margin-top:20px;margin-bottom:10px;border-left: 4px solid #ffba00;box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);height:20px;width:99%;padding:10px 25px 10px 0px;background-color:#f2f2f2;color:#000000;font-size:17px;display:block;visibility:visible;text-align:right;" >ARForms License is not activated. Please activate license from <a href="<?php echo $admin_css_url; ?>">here</a></div>
    <?php } ?>
	<div id="poststuff" class="">
    	<div id="post-body" >
        	<div class="addon_content">
                <?php
                    do_action('arf_addon_page_retrieve_notice');
                ?>
                <div class="addon_page_desc"> <?php esc_html_e('Add more features to ARForms using Add-Ons','ARForms'); ?></div>
                <div class="addon_page_content">
					<?php
						global $arsettingcontroller;
						$arsettingcontroller->addons_page();
					?>
                </div>
            </div>
        </div>
    </div>
</div>
