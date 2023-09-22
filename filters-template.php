<?php
$term = get_queried_object();
if (isset($term->term_id) && $term->term_id > 0) {
    global $wpdb;
    $filter_out = false;
    $filter_check = false;
    $row_class = '';
    $term_id = $term->term_id;
    $prefix = 'ik_f-';
    $filtering_data = $wpdb->get_results('SELECT * FROM wp_ik_filters WHERE category_id = ' . $term_id . ' GROUP BY attr_value, attr_name;', ARRAY_A);
    $settings_filter_list = get_field('ik-filters-list', 'option');
    $settings_post_type = get_field('ik-filters-post-type', 'option');
    $settings_taxonomy = get_field('ik-filters-taxonomy', 'option');

    ?>
    
    <div class="ik-filter-block" id="ik-filter-block">
        <div class="hide-filters-button"> Скрыть фильтры </div>
        
        <form method="get" action="<?php echo get_term_link($term_id); ?>" id="ik-filter-form">

            <?php
            if (is_array($filtering_data) && !empty($filtering_data)) {
                ob_start();
                ?>
                <div class="ik-filter-body">
                    <div class="ik-filter-rows">					
                        <!-- СОРТИРОВКА НАЧАЛО --> 
                        <?php
                        $id = 'ik-filter-order';
                        $options = [
                            ['value' => 'top', 'text' => __('Сначала популярные'), 'order' => 'desc'],
                            ['value' => 'date', 'text' => __('Сначала новые'), 'order' => 'desc'],
                            ['value' => 'price', 'text' => __('Сначала дороже'), 'order' => 'desc'], 
                            ['value' => 'price', 'text' => __('Сначала дешевле'), 'order' => 'asc'],                                                                        
                        ];
                        ?>
                        <div class="ik-filter-row active">
                            <div class="ik-filter-row-head ik-filter-row-head-inactive">
                                <label for="<?php echo $id; ?>" class="ik-filter-row-label">
                                    <span class="ik-filter-name">Сортировать по:</span>
                                    <span class="ik-filter-value"></span>
                                </label>
                            </div>
                            <div class="ik-filter-row-body">
                                <div class="ik-filter-checkbox-list-block" id="<?php echo $id; ?>">
                                    <div class="ik-filter-checkbox-list" id="<?php echo $id; ?>">
                                        <?php
                                        $n = 1;
                                        foreach ($options as $d) {
                                            $check = ($d['value'] === 'top') ? 'checked="checked"' : '';
                                            $class = '';
                                            if (isset($_REQUEST['ik_f-order']) && isset($_REQUEST['ik_f-order-type'])) {
                                                if ($_REQUEST['ik_f-order'] == $d['value'] && $_REQUEST['ik_f-order-type'] == $d['order']) {
                                                    $class = 'checked"';
                                                    $check = 'checked="checked"';
                                                    $filter_check = true;
                                                }
                                            }
                                            ?>
                                            <label for="<?php echo $id . '-' . $n; ?>"
                                                   class="ik-filter-checkbox-item <?php echo $class; ?>"
                                                   data-id="<?php echo $id; ?>"
                                                   data-n="<?php echo $n; ?>">
                                                
                                                <input type="radio"
                                                       id="<?php echo $id . '-' . $n; ?>"
                                                       name="<?php echo $id; ?>"
                                                       value="<?php echo $d['value'] . ':' . $d['order']; ?>" <?php echo $check; ?>>
                                                <span class="checkmark"></span>
                                                <span class="label"><?php echo $d['text']; ?></span>
                                            </label>
                                            <?php
                                            $n++;

                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- СОРТИРОВКА  КОНЕЦ --> 
                        
                        
						<!-- ФИЛЬТРАЦИЯ НАЧАЛО --> 
                        <?php
                        foreach ($settings_filter_list as $d) {
                            $type = $d['type'];
                            $data = $d[$type];
							
							/* СОРТИРОВКА ПО ЦЕНЕ (СЛАЙДЕР) - НАЧАЛО*/
                            if ($type == 'slider' && 1 == 1) {
                                $attr_name = ik_filters_handle_string($data['name']);
                                $isset_filter = false;
                                foreach ($filtering_data as $bbb) {
                                    if (isset($bbb['attr_name']) && $bbb['attr_name'] == $attr_name && !empty($bbb['attr_value'])) {
                                        $isset_filter = true;
                                        break;
                                    }
                                }

                                if ($isset_filter) {
                                    $filter_out = true;
                                    $id = $prefix . $attr_name;
                                    $min = 1000000;
                                    $max = 1;
                                    foreach ($filtering_data as $dd) {
                                        if ($dd['attr_name'] == $attr_name) {
                                            $attr_value = intval($dd['attr_value']);
                                            if ($min > $attr_value) {
                                                $min = $attr_value;
                                            }
                                            if ($max < $attr_value) {
                                                $max = $attr_value;
                                            }
                                        }
                                    }
                                    $current_min = $min;
                                    $current_max = $max;
                                    if (isset($_REQUEST[$id . '-min']) && !empty($_REQUEST[$id . '-min'])) {
                                        $current_min = intval($_REQUEST[$id . '-min']);
                                    }
                                    if (isset($_REQUEST[$id . '-max']) && !empty($_REQUEST[$id . '-max'])) {
                                        $current_max = intval($_REQUEST[$id . '-max']);
                                    }
                                    ?>
						
						
									<div class="ik-filter-row ik-filter-row-reset-btn">
										<?php
										if ($filter_out) {
											?>
											<a href="<?php echo get_term_link($term_id); ?>" class="reset-btn"> Очистить </a>
											<?php
										}
										?>
									</div>
						
                                    <div class="ik-filter-row <?php echo $row_class; ?>">
                                        <div class="ik-filter-row-head">
                                            <label for="<?php echo $id; ?>" class="ik-filter-row-label">
                                                <?php
                                                echo $data['name'];
                                                ?>
                                                <span class="arr ik-filter-value"></span>
                                            </label>
                                        </div>
                                        <div class="ik-filter-row-body">
                                            <div class="ik-filter-slider-block" id="<?php echo $id; ?>">
                                                <div class="ik-filter-slider-inputs" id="<?php echo $id; ?>">
                                                    <div class="ik-filter-slider-input input-min">
                                                        <div class="ik-filter-slider-input-desc">от</div>
                                                        <input type="number" name="<?php echo $id; ?>-min"
                                                               id="<?php echo $id; ?>-min"
                                                               value="<?php echo $current_min; ?>">
                                                    </div>
                                                    <div class="ik-filter-slider-input input-max">
                                                        <div class="ik-filter-slider-input-desc">до</div>
                                                        <input type="number" name="<?php echo $id; ?>-max"
                                                               id="<?php echo $id; ?>-max"
                                                               value="<?php echo $current_max; ?>">
                                                    </div>
                                                </div>
                                                <div class="ik-filter-slider-wrap">
                                                    <div class="ik-filter-slider"
                                                         id="<?php echo $id; ?>-slider"
                                                         data-id="<?php echo $id; ?>"
                                                         data-min="<?php echo $min; ?>"
                                                         data-max="<?php echo $max; ?>"
                                                         data-current-min="<?php echo $current_min; ?>"
                                                         data-current-max="<?php echo $current_max; ?>">

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
							
							/* СОРТИРОВКА ПО ЦЕНЕ (СЛАЙДЕР) - КОНЕЦ*/
							
							/* СОРТИРОВКА ПО ЧЕКБОКСАМ - НАЧАЛО*/
                            if ($type == 'checkbox') {
                                $attr_name = ik_filters_handle_string($data['name']);
                                //pout($filtering_data);
                                $isset_filter = false;
                                foreach ($filtering_data as $bbb) {
                                    if (isset($bbb['attr_name']) && $bbb['attr_name'] == $attr_name && !empty($bbb['attr_value'])) {
                                        $isset_filter = true;
                                        break;
                                    }
                                }

                                if ($isset_filter) {
                                    $filter_out = true;
                                    $id = $prefix . $attr_name;
                                    $image_mode = $data['image-mode'];
                                    ?>
                                    <div class="ik-filter-row <?php echo $row_class; ?>">
                                        <div class="ik-filter-row-head">
                                            <label for="<?php echo $id; ?>" class="ik-filter-row-label">
                                                <span><?php echo $data['name']; ?></span>
                                                <span class="ik-filter-value"></span>
                                            </label>
                                        </div>
                                        <div class="ik-filter-row-body">
                                            <div class="ik-filter-checkbox-list-block" id="<?php echo $id; ?>">
                                                <div class="ik-filter-checkbox-list <?php echo $data['name_en']; ?>" id="<?php echo $id; ?>">
                                                    <?php
                                                    $n = 1;
                                                    foreach ($data['list'] as $dd) {
                                                        $temp_attr_value = ik_filters_handle_string($dd['value']);
                                                        foreach ($filtering_data as $ddd) {
                                                            if ($ddd['attr_name'] == $attr_name && $ddd['attr_value'] == $temp_attr_value) {
                                                                $check = '';
                                                                $class = '';
                                                                if (isset($_REQUEST[$id])) {
                                                                    foreach ($_REQUEST[$id] as $rv) {
                                                                        if ($temp_attr_value == $rv) {
                                                                            $class = 'checked"';
                                                                            $check = 'checked="checked"';
                                                                            $filter_check = true;
                                                                        }
                                                                    }
                                                                }
                                                                ?>
                                                                <label for="<?php echo $id . '-' . $n; ?>"
                                                                       class="ik-filter-checkbox-item <?php echo $class; ?>"
                                                                       data-id="<?php echo $id; ?>"
                                                                       data-n="<?php echo $n; ?>">

                                                                    <?php
                                                                    if ($image_mode == 'image' && !empty($dd['image'])) {
                                                                        ?>
                                                                        <div class="image">
                                                                            <img src="<?php echo $dd['image']; ?>"
                                                                                 alt=""
                                                                                 title="">
                                                                        </div>
                                                                        <?php
                                                                    }
                                                                    if ($image_mode == 'color' && !empty($dd['color'])) {
                                                                        ?>
                                                                        <div class="color"
                                                                             style="background-color: <?php echo $dd['color']; ?>;">
                                                                        </div>
                                                                        <?php
                                                                    }
                                                                    ?>
																	<input type="checkbox"
                                                                           id="<?php echo $id . '-' . $n; ?>"
                                                                           name="<?php echo $id; ?>[]"
                                                                           value="<?php echo $temp_attr_value; ?>" <?php echo $check; ?>>
                                                                    <span class="checkmark <?php echo $data['name_en']; ?>-checkmark"></span>
                                                                    <span class="label"><?php echo $dd['value']; ?></span>

                                                                </label>
                                                                <?php
                                                                $n++;
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
							
							/* СОРТИРОВКА ПО ЧЕКБОКСАМ - КОНЕЦ*/
							
							/* СВОБОДНЫЙ ФИЛЬТР - ТЕСТ - НАЧАЛО*/
                            if ($type == 'free-text') {
                                $attr_name = ik_filters_handle_string($data['name']);
                                $isset_filter = false;
                                foreach ($filtering_data as $bbb) {
                                    if (isset($bbb['attr_name']) && $bbb['attr_name'] == $attr_name) {
                                        $isset_filter = true;
                                        break;
                                    }
                                }

                                if ($isset_filter) {
                                    $filter_out = true;

                                    $id = $prefix . $attr_name;
                                    $values = array();
                                    foreach ($filtering_data as $dd) {
                                        if ($dd['attr_name'] == $attr_name) {
                                            $attr_value = $dd['attr_value'];
                                            $attr_source_value = $dd['attr_source_value'];
                                            $values[$attr_value] = $attr_source_value;
                                        }
                                    }
                                    ?>
                                    <div class="ik-filter-row <?php echo $row_class; ?>">
                                        <div class="ik-filter-row-head">
                                            <label for="<?php echo $id; ?>" class="ik-filter-row-label">
                                                <span><?php echo $data['name']; ?></span>
                                                <span class="ik-filter-value"></span>
                                            </label>
                                        </div>
                                        <div class="ik-filter-row-body">
                                            <div class="ik-filter-checkbox-list-block" id="<?php echo $id; ?>">
                                                <div class="ik-filter-checkbox-list" id="<?php echo $id; ?>">
                                                    <?php
                                                    $n = 1;
                                                    foreach ($values as $key => $value) {
                                                        $check = '';
                                                        $class = '';
                                                        if (isset($_REQUEST[$id])) {
                                                            foreach ($_REQUEST[$id] as $rv) {
                                                                if ($key == $rv) {
                                                                    $class = 'checked"';
                                                                    $check = 'checked="checked"';
                                                                    $filter_check = true;
                                                                    break;
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                        <label for="<?php echo $id . '-' . $n; ?>"
                                                               class="ik-filter-checkbox-item <?php echo $class; ?>"
                                                               data-id="<?php echo $id; ?>"
                                                               data-n="<?php echo $n; ?>">
                                                            <span class="label"><?php echo $value; ?></span>
                                                            <input type="checkbox"
                                                                   id="<?php echo $id . '-' . $n; ?>"
                                                                   name="<?php echo $id; ?>[]"
                                                                   value="<?php echo $key; ?>" <?php echo $check; ?>>
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <?php
                                                        $n++;

                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
							/* СВОБОДНЫЙ ФИЛЬТР - ТЕСТ - КОНЕЦ*/
                        }
                        ?>

						<!-- ФИЛЬТРАЦИЯ КОНЕЦ -->                     
                    </div>

                </div>
                <?php
                $ik_filter_body = ob_get_clean();
                if ($filter_out == true) {
                    echo $ik_filter_body;
                }
            }
            ?>
            <input type="hidden" id="<?php echo $prefix . 'order'; ?>" name="<?php echo $prefix . 'order'; ?>"
                   value="">
            <input type="hidden" id="<?php echo $prefix . 'order-type'; ?>"
                   name="<?php echo $prefix . 'order-type'; ?>"
                   value="">
        
            <div class="k-filter-row-phone">
                <div class="ik-filter-row-save ik-filter-row-save_mobil"> Применить </div>
                <?php
                    if ($filter_out) {
                ?>
                <a href="<?php echo get_term_link($term_id); ?>" class="reset-btn reset-btn_mobil"> Очистить </a>
                <?php
                    }
                ?>
            </div>
            
            <div class="filter-button-send-row">    
                <div class="ik-filter-row-save ik-filter-row-save-desktop"> Применить </div>
            </div>
            
            </form>
    </div>
    <?php
}
?>
