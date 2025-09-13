<div class="lws_cl_tab">
    <?php foreach($plugin_lists as $key => $list) : ?>
        <?php if ($key == "plugins" || $key == "themes") : ?>
            <div class="lws_cl_table_row <?php echo $list[4] == 0 ? esc_attr('lws_cl_green_side') : esc_attr('lws_cl_red_side');?>">
                <div <?php $image = $list[4] == 0 ? 'pouce' : (in_array($key, $bottom_thumb_key) ? 'pouce_bas' : 'warning');?> class="lws_cl_table_left" onclick="lws_cl_open_submenu(this)">
                    <img class="lws_cl_images_left_table"
                        id="lws_cl_left_<?php echo esc_html($key)?>"
                        src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'images/' . $image . '.svg')?>">
                    <span>
                        <?php printf($list[4] == 0 ? wp_kses($list[1], $arr): wp_kses($list[0], $arr), $list[4]); ?>
                    </span>
                    <span class="lws_cl_tooltip_content">
                        <img class="lws_cl_images_left_table"
                            id="lws_cl_tooltip_<?php echo esc_attr($key);?>"
                            width="15px" height="15px" style="vertical-align:middle"
                            src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'images/infobulle.svg')?>">
                        <span>
                            <?php echo esc_attr($list[5]);?>
                        </span>
                    </span>   
                    <?php if ($list[4] != 0) : ?>
                    <img class="lws_cl_chevron" width="15px" height="8px" src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'images/chevron.svg')?>">        
                    <?php endif ?>         
                </div>            
                <div class="lws_cl_table_right">
                    <button class="<?php echo $list[4] == "0" ? esc_attr("lws_cl_button_green lws_cl_bouton_no_pointer") : esc_attr("lws_cl_button_red")?>"
                        id="<?php echo esc_attr('lws_cl_' . $key); ?>"
                        onclick="" <?php echo $list[4] == "0" ? esc_attr('disabled') : '' ?>>
                        <span class="" name="update">
                            <img class="lws_cl_images_button"
                                src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'images/' . ($list[4] == 0 ? esc_attr('securiser') : 'supprimer') . '.svg')?>">
                            <?php printf($list[4] == "0" ? wp_kses($list[3], $arr) : wp_kses($list[2], $arr), $list[4]) ?>
                        </span>
                        <span class="hidden" name="loading">
                            <img class="" width="15px" height="15px"
                                src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'images/loading.svg')?>">

                            <span id="loading_1"><?php esc_html_e("Deletion...", "lws-cleaner");?></span>
                        </span>
                        <span class="hidden" name="validated">
                            <img class="" width="18px" height="18px"
                                src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'images/check_blanc.svg')?>">
                            <?php esc_html_e('Deleted', 'lws-cleaner'); ?>
                            &nbsp;
                        </span>
                    </button>
                </div>
            </div>
            <?php if ($list[4] !== 0 && $key == "plugins") : ?>
            <div class="lws_cl_tab_line_submenu">
                <?php foreach ($unused_plugins as $key => $p_delete) : ?>
                <div class="lws_cl_tab_submenu lws_cl_warning">
                    <?php echo(esc_html($p_delete['name'])); ?>
                    <button class="lws_cl_update_element_button"
                        id="<?php echo "lws_cl_delete_plugin_specific_" . $p_delete['slug'] ?>"
                        name="lws_cl_delete_plugin_specific"
                        value="<?php echo(esc_attr($p_delete['package'])); ?>"
                        onclick="lws_cleaner_deletePlugin(this)">
                        <span class="" name="update">
                            <img class="lws_cl_image" width="19px" height="20px"
                                src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'images/supprimer_red.svg')?>">
                            <?php esc_html_e('Delete this plugin', 'lws-cleaner')?>
                        </span>
                        <span class="hidden" name="loading">
                            <img class="lws_cl_image" width="15px" height="15px"
                                src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'images/loading_red.svg')?>">
                            <span><?php esc_html_e("Deletion in progress...", "lws-cleaner");?></span>
                        </span>
                        <span class="hidden" name="validated">
                            <img class="lws_cl_image" width="18px" height="18px"
                                src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'images/check_red.svg')?>">
                            <?php esc_html_e('Deleted', 'lws-cleaner'); ?>
                        </span>
                    </button>
                </div>
                <?php endforeach ?>
            </div> 

            <script>
                function lws_cl_open_submenu(element) {
                    element.parentNode.nextElementSibling.classList.toggle('lws_cl_submenu_shown');
                    element.children[3].classList.toggle('lws_cl_chevron_flip');
                }

                function lws_cleaner_deletePlugin(button) {
                    var button_id = button.id;
                    button.children[0].classList.add('hidden');
                    button.children[2].classList.add('hidden');
                    button.children[1].classList.remove('hidden');
                    button.setAttribute('disabled', true);
                    var data = {
                        action: "lwscleaner_deletePlugin",
                        lws_cleaner_delete_plugin_specific: button.value,
                        _ajax_nonce: '<?php echo esc_attr(wp_create_nonce('cleaner_delete_one_plugin')); ?>',
                    };
                    jQuery.post(ajaxurl, data, function(response) {
                        var button = jQuery('#' + button_id);
                        button.children()[0].classList.add('hidden');
                        button.children()[2].classList.remove('hidden');
                        button.children()[1].classList.add('hidden');
                    });
                }
            </script>
            <?php elseif ($list[4] !== 0 && $key == "themes") : ?>
                <div class="lws_cl_tab_line_submenu">
                    <?php foreach ($unused_themes as $key => $t_delete) : ?>
                    <div class="lws_cl_tab_submenu lws_cl_warning">
                        <?php echo(esc_html($t_delete['name'])); ?>
                        <button class="lws_cl_update_element_button"
                            id="<?php echo "lws_cl_delete_theme_specific_" . $t_delete['slug'] ?>"
                            name="lws_cl_delete_theme_specific"
                            value="<?php echo(esc_attr($t_delete['slug'])); ?>"
                            onclick="lws_cl_deleteTheme(this)">
                            <span class="" name="update">
                                <img class="lws_cl_image" width="19px" height="20px"
                                    src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'images/supprimer_red.svg')?>">
                                <?php esc_html_e('Delete this theme', 'lws-cleaner')?>
                            </span>
                            <span class="hidden" name="loading">
                                <img class="lws_cl_image" width="15px" height="15px"
                                    src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'images/loading_red.svg')?>">
                                <span><?php esc_html_e("Deletion in progress...", "lws-cleaner");?></span>
                            </span>
                            <span class="hidden" name="validated">
                                <img class="lws_cl_image" width="18px" height="18px"
                                    src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'images/check_red.svg')?>">
                                <?php esc_html_e('Deleted', 'lws-cleaner'); ?>
                            </span>
                        </button>
                    </div>
                    <?php endforeach ?>
                </div>

                <script>
                    function lws_cl_open_submenu(element) {
                        element.parentNode.nextElementSibling.classList.toggle('lws_cl_submenu_shown');
                        element.children[3].classList.toggle('lws_cl_chevron_flip');
                    }

                    function lws_cl_deleteTheme(button) {
                        var button_id = button.id;
                        button.children[0].classList.add('hidden');
                        button.children[2].classList.add('hidden');
                        button.children[1].classList.remove('hidden');
                        button.setAttribute('disabled', true);
                        var data = {
                            action: "lwscleaner_deleteTheme",
                            lws_cl_delete_theme_specific: button.value,
                            _ajax_nonce: '<?php echo esc_attr(wp_create_nonce('lwscleaner_delete_one_theme')); ?>',
                        };
                        jQuery.post(ajaxurl, data, function(response) {
                            var button = jQuery('#' + button_id);
                            button.children()[0].classList.add('hidden');
                            button.children()[2].classList.remove('hidden');
                            button.children()[1].classList.add('hidden');
                        });
                    }
                </script>
            <?php endif ?>
        <?php else : ?>
            <div <?php if (in_array($key, $button_is_blue)) : ?>
                <?php $image = 'parametres'; ?>
                class="lws_cl_table_row lws_cl_blue_side"
                <?php else : ?>
                class="lws_cl_table_row <?php echo $list[4] == 0 ? esc_attr('lws_cl_green_side') :
                        (in_array($key, $bottom_thumb_key) ? esc_attr('lws_cl_red_side') : esc_attr('lws_cl_orange_side'));?>"
                <?php endif ?>>
                <div <?php if (in_array($key, $button_is_blue)) : ?>
                    <?php $image = 'parametres'; ?>
                    class="lws_cl_table_left"
                    <?php else : ?>
                    <?php $image = $list[4] == 0 ? 'pouce' : (in_array($key, $bottom_thumb_key) ? 'pouce_bas' : 'warning');?>
                    class="lws_cl_table_left"
                    <?php endif ?>
                    >

                    <img class="lws_cl_images_left_table"
                        id="lws_cl_left_<?php echo esc_html($key)?>"
                        src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'images/' . $image . '.svg')?>">
                    <span>
                        <?php printf($list[4] == 0 ? wp_kses($list[1], $arr): wp_kses($list[0], $arr), $list[4]); ?>
                    </span>
                    <span class="lws_cl_tooltip_content">
                        <img class="lws_cl_images_left_table"
                            id="lws_cl_tooltip_<?php echo esc_attr($key);?>"
                            width="15px" height="15px" style="vertical-align:middle"
                            src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'images/infobulle.svg')?>">
                        <span>
                            <?php echo esc_attr($list[5]);?>
                        </span>
                    </span>

                </div>
                <div class="lws_cl_table_right">
                    <?php if ($key == 'deactivate_comments' || $key == 'hide_comments') : ?>
                        <label class="switch">
                            <input type="checkbox"
                            id="<?php echo esc_attr('lws_cl_' . $key); ?>"
                            <?php echo get_option('lws_cl_' . $key) ? esc_attr('checked') : '' ?>>
                            <span class="slider round"></span>
                        </label>
                    <?php else : ?>
                    <button <?php if (in_array($key, $button_is_blue)) : ?>
                        <?php if ($list[4] == 0) : ?>
                        class="lws_cl_button_blue noclick"
                        <?php else : ?>
                        class="lws_cl_button_blue"
                        <?php endif ?>

                        <?php else : ?>
                        class="<?php echo $list[4] == "0" ? esc_attr("lws_cl_button_green lws_cl_bouton_no_pointer") : esc_attr("lws_cl_button_red")?>"
                        <?php endif ?>
                        id="<?php echo esc_attr('lws_cl_' . $key); ?>"
                        onclick="" <?php echo $list[4] == "0" ? esc_attr('disabled') : '' ?>>
                        <span class="" name="update">
                            <img class="lws_cl_images_button"
                                src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'images/' . ($list[4] == 0 ? esc_attr('securiser') : 'supprimer') . '.svg')?>">
                            <?php printf($list[4] == "0" ? wp_kses($list[3], $arr) : wp_kses($list[2], $arr), $list[4]) ?>
                        </span>
                        <span class="hidden" name="loading">
                            <img class="" width="15px" height="15px"
                                src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'images/loading.svg')?>">

                            <span id="loading_1"><?php esc_html_e("Deletion...", "lws-cleaner");?></span>
                        </span>
                        <span class="hidden" name="validated">
                            <img class="" width="18px" height="18px"
                                src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'images/check_blanc.svg')?>">
                            <?php esc_html_e('Deleted', 'lws-cleaner'); ?>
                            &nbsp;
                        </span>
                    </button>
                    <?php endif ?>
                </div>
            </div>
        <?php endif ?>
    <?php endforeach ?>
</div>

<script>
    <?php foreach ($plugin_lists as $key => $list) : ?>
    jQuery('#lws_cl_<?php echo esc_attr($key)?>').on('click',
        function() {
            <?php if ($key == "hide_comments" || $key == "deactivate_comments") : ?>
            var data = {
                action: "lws_cleaner_comments_ajax",
                data: "<?php echo esc_attr($key);?>",
                checked: this.checked,
                _ajax_nonce: '<?php echo esc_attr(wp_create_nonce('lws_cleaner_comments')); ?>',        
            };
            jQuery.post(ajaxurl, data);
            <?php else : ?>
            let button = this;
            let button_id = this.id;
            button.children[0].classList.add('hidden');
            button.children[2].classList.add('hidden');
            button.children[1].classList.remove('hidden');
            button.classList.remove('lws_cl_validated_button_tools');
            button.setAttribute('disabled', true);
            var data = {
                action: "lws_cleaner_<?php echo esc_attr($lws_cl_page_type);?>_ajax",
                _ajax_nonce: '<?php echo esc_attr(wp_create_nonce('lws_cleaner_' . esc_attr($lws_cl_page_type))); ?>',        
                data: "<?php echo esc_attr($key);?>",
            };
            jQuery.post(ajaxurl, data, function(response) {
                console.log(response);
                var button = jQuery('#' + button_id);
                button.children()[0].classList.add('hidden');
                button.children()[2].classList.remove('hidden');
                button.children()[1].classList.add('hidden');
                button.addClass('lws_cl_validated_button_tools');
            });
            <?php endif?>
        });
    <?php endforeach ?>
</script>
