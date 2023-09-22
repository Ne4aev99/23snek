<?php
add_action('wp_enqueue_scripts', 'ik_filter_scripts');
function ik_filter_scripts()
{
    wp_enqueue_script('jquery-ui-widget');
    wp_enqueue_script('jquery-ui-autocomplete');
    wp_enqueue_script('jquery-ui-slider');
    $path = get_template_directory_uri() . '/';
    //wp_enqueue_style('ik-filters-css', $path . 'css/ik-filter-block.css', array());
    wp_enqueue_script('touch-punch-js', $path . "js/jquery.ui.touch-punch.min.js", array('jquery'), false, true);
    wp_enqueue_script('ik-filters-js', $path . "js/filters.js", array('jquery'), false, true);
}

if (function_exists('acf_add_options_page')) {


    acf_add_options_page(array(
        'page_title' => 'Фильтрация',
        'menu_title' => 'Фильтрация',
        'menu_slug' => 'ik-filters-settings',
        'capability' => 'edit_posts',
        'redirect' => false
    ));
    acf_add_options_sub_page(array(
        'page_title' => 'Обновить фильтры',
        'menu_title' => 'Обновить фильтры',
        'menu_slug' => 'ik-filters-update',
        'capability' => 'edit_posts',
        'redirect' => false,
        'parent_slug' => 'ik-filters-settings',
    ));

    add_action('admin_menu', 'ik_filters_add_update_page');
    function ik_filters_add_update_page()
    {
        add_submenu_page(
            null,
            'Обновить фильтры',
            'Обновить фильтры',
            'manage_options',
            'ik-filters-update',
            'ik_filters_update_callback'
        );
    }

}

if (function_exists('acf_add_local_field_group')) {
    $settings_post_type = get_field('ik-filters-post-type', 'option');
    $settings_taxonomy = get_field('ik-filters-taxonomy', 'option');
    $settings_filter_list = get_field('ik-filters-list', 'option');
    $fields = array();
    if (is_array($settings_filter_list)) {
        foreach ($settings_filter_list as $d) {
            $choices = [];
            $type = $d['type'];
            $data = $d[$type];
            $name = $data['name'];
            $key = ik_filters_handle_string($name);
            if ($type == 'slider') {
                $fields[] = array(
                    'key' => $key,
                    'label' => $name,
                    'name' => $key,
                    'type' => 'number',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '50',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => $data['min'],
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'min' => $data['min'],
                    'max' => $data['max'],
                    'step' => '',
                );
            }
            if ($type == 'free-text') {
                $fields[] = array(
                    'key' => $key,
                    'label' => $name,
                    'name' => $key,
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '50',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                );
            }
            if ($type == 'checkbox') {
                //$choices = ['' => 'Пустой'] ;
                $choices['empty_item'] = 'Пустой элемент';
                foreach ($data['list'] as $dd) {
                    $choice_name = $dd['value'];
                    $choice_key = ik_filters_handle_string($choice_name);
                    $choices[$choice_key] = $choice_name;
                }

                if ($data['multi'] != 2) {
                    $fields[] = array(
                        'key' => $key,
                        'label' => $name,
                        'name' => $key,
                        'type' => 'select',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '50',
                            'class' => '',
                            'id' => '',
                        ),
                        'choices' => $choices,
                        'default_value' => array(),
                        'allow_null' => 0,
                        'multiple' => 1,
                        'ui' => 1,
                        'ajax' => 0,
                        'return_format' => 'value',
                        'placeholder' => '',
                    );
                } else {
                    $fields[] = array(
                        'key' => $key,
                        'label' => $name,
                        'name' => $key,
                        'type' => 'select',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '50',
                            'class' => '',
                            'id' => '',
                        ),
                        'choices' => $choices,
                        'default_value' => 1,
                        'allow_null' => 0,
                        'multiple' => 0,
                        'ui' => 0,
                        'return_format' => 'value',
                        'ajax' => 0,
                        'placeholder' => '',
                    );
                }
            }
        }
    }
    acf_add_local_field_group(array(
        'key' => 'ik-filters-base',
        'title' => 'Фильтрация',
        'fields' => array(
            array(
                'key' => 'ik-filter',
                'label' => 'Фильтрация',
                'name' => 'ik-filter',
                'type' => 'group',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'layout' => 'block',
                'sub_fields' => $fields
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => $settings_post_type,
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
    ));
}

function ik_filters_update_post_filters($post_id, $log = false)
{
    global $wpdb;
    //$settings_post_type = get_field('ik-filters-post-type', 'option');
    $settings_taxonomy = get_field('ik-filters-taxonomy', 'option');
    //$settings_filter_list = get_field('ik-filters-list', 'option');
    $ik_filter = get_field('ik-filter', $post_id);
    //$saved_post_id = $post_id;
    $terms = get_the_terms($post_id, $settings_taxonomy);
    $wpdb->delete('wp_ik_filters', array('post_id' => $post_id));
    foreach ($ik_filter as $attr_name => $value) {
        //$key - ключ фильтра в обработтаном состоянии
        if (is_array($value)) {//Если значений несколько
            foreach ($value as $subvalue) {
                $attr_value = ik_filters_handle_string($subvalue);
                foreach ($terms as $term) {
                    $category_id = $term->term_id;
                    if ($attr_value != 'empty_item') {
                        $insert_data = [
                            'category_id' => $category_id,
                            'post_id' => $post_id,
                            'attr_name' => $attr_name,
                            'attr_value' => $attr_value,
                            'attr_source_value' => $subvalue
                        ];
                        $wpdb->insert(
                            'wp_ik_filters',
                            $insert_data,
                            array('%d', '%d', '%s', '%s', '%s')
                        );
                        if ($log == true) {
                            echo '<pre>' . print_r($insert_data, true) . '</pre>';
                        }
                    }
                }
            }
        } else {
            $attr_value = ik_filters_handle_string($value);//Значение фильтра в обработанном состоянии
            if (strlen($attr_value) > 0) {
                foreach ($terms as $term) {
                    $category_id = $term->term_id;
                    if ($attr_value != 'empty_item') {
                        $insert_data = [
                            'category_id' => $category_id,
                            'post_id' => $post_id,
                            'attr_name' => $attr_name,
                            'attr_value' => $attr_value,
                            'attr_source_value' => $value
                        ];

                        $wpdb->insert(
                            'wp_ik_filters',
                            $insert_data,
                            array('%d', '%d', '%s', '%s', '%s')
                        );
                        if ($log == true) {
                            echo '<pre>' . print_r($insert_data, true) . '</pre>';
                        }
                    }
                }
            }
        }
    }
}

add_action('save_post', 'ik_filters_update_post_fields');
function ik_filters_update_post_fields($post_id)
{

    $settings_post_type = get_field('ik-filters-post-type', 'option');
    if (!isset($_POST['post_type']) || $settings_post_type != $_POST['post_type']) {
        return;
    }
    ik_filters_update_post_filters($post_id);

}

add_shortcode('ik-filter', 'ik_filter_output');
function ik_filter_output($atts)
{
    $html = '';
    ob_start();
    require 'filters-template.php';
    $html = ob_get_clean();
    return $html;
}

add_shortcode('ik-filter-order', 'ik_filter_order_output');
function ik_filter_order_output($atts)
{
    $html = '';
    ob_start();
    $term = get_queried_object();
    if (isset($term->term_id) && $term->term_id > 0) {
        $options = [
            ['value' => 'top', 'text' => 'По популярности', 'order' => 'desc'],
            ['value' => 'date', 'text' => 'По новизне', 'order' => 'desc'],
            ['value' => 'price', 'text' => 'По возрастанию цены', 'order' => 'asc'],
            ['value' => 'price', 'text' => 'По убыванию цены', 'order' => 'desc'],
            ['value' => 'stock_desc', 'text' => 'По наличию: убывание', 'order' => 'desc'], // Новый пункт по наличию
            ['value' => 'stock_asc', 'text' => 'По наличию: возрастание', 'order' => 'asc'], // Новый пункт по наличию
        ];
        ?>
        <div class="ik-filter-order-block">
            <div class="ik-filter-label">
                Сортировать по
            </div>
            <select class="ik-filter-order-select" id="ik-filter-order-select">
                <option value="" data-order="">По умолчанию</option>
                <?php
                foreach ($options as $d) {
                    $selected = '';
                    if (isset($_REQUEST['ik_f-order']) && isset($_REQUEST['ik_f-order-type'])) {
                        if ($_REQUEST['ik_f-order'] == $d['value'] && $_REQUEST['ik_f-order-type'] == $d['order']) {
                            $selected = 'selected';
                        }
                    }
                    echo '<option value="' . $d['value'] . '" data-order="' . $d['order'] . '" ' . $selected . '>' . $d['text'] . '</option>';
                }
                ?>
            </select>
        </div>
        <?php
    }
    $html = ob_get_clean();
    return $html;
}


function ik_filter_term_children_out($terms = array(), $parent = 0, $level = 0)
{
    foreach ($terms as $term) {
        if ($term->parent == $parent) {
            echo '<div class="checkboxItem">';
            for ($i = 0; $i <= ($level * 6); $i++) {
                echo '&nbsp;';
            }
            echo '<input type="checkbox" id="term_' . $term->term_id . '" name="post_term[]" value="' . $term->term_id . '">';
            echo '<label for="term_' . $term->term_id . '">' . $term->name . '</label>';
            echo '</div>';
            ik_filter_term_children_out($terms, $term->term_id, $level + 1);
        }
    }
}

function ik_filters_update_callback()
{
    global $wpdb;
    $settings_post_type = get_field('ik-filters-post-type', 'option');
    $settings_taxonomy = get_field('ik-filters-taxonomy', 'option');
    ?>
    <div class="wrap">
        <form method="post" action="">
            <?php
            $terms = get_terms(array(
                'taxonomy' => $settings_taxonomy,
                'hide_empty' => false,
            ));
            ik_filter_term_children_out($terms, 0, 0);
            ?>
            <br>
            <button type="submit" class="button button-primary button-large">Обновить фильтра для выбранных категорий
            </button>
        </form>
        <?php
        if (isset($_REQUEST['post_term'])) {
            foreach ($_REQUEST['post_term'] as $term_id) {
                $wpdb->delete('wp_ik_filters', array('category_id' => $term_id));
                $args = array(
                    'post_type' => $settings_post_type,
                    'posts_per_page' => -1,
                    'tax_query' => array(
                        array(
                            'taxonomy' => $settings_taxonomy,
                            'field' => 'term_id',
                            'terms' => $term_id,
                        ),
                    ),
                );
                $recent = new WP_Query($args);
                while ($recent->have_posts()) {
                    $recent->the_post();
                    echo '<br><h3>' . get_the_id() . '. ' . get_the_title() . '</h3>';
                    ik_filters_update_post_filters(get_the_id(), true);
                }
                wp_reset_postdata();
                wp_reset_query();
            }
        }
        ?>
    </div>
    <?php
}

function ik_filters_strip_whitespaces($s = '')
{
    $old_s = $s;
    $s = strip_tags($s);
    $s = preg_replace('/([^\pL\pN\pP\pS\pZ])|([\xC2\xA0])/u', ' ', $s);
    $s = str_replace('  ', ' ', $s);
    $s = trim($s);

    if ($s === $old_s) {
        return $s;
    } else {
        return ik_filters_strip_whitespaces($s);
    }
}

function ik_filters_handle_string($s = '')
{
    $converter = array(
        'а' => 'a',
        'б' => 'b',
        'в' => 'v',
        'г' => 'g',
        'д' => 'd',
        'е' => 'e',
        'ё' => 'e',
        'ж' => 'zh',
        'з' => 'z',
        'и' => 'i',
        'й' => 'y',
        'к' => 'k',
        'л' => 'l',
        'м' => 'm',
        'н' => 'n',
        'о' => 'o',
        'п' => 'p',
        'р' => 'r',
        'с' => 's',
        'т' => 't',
        'у' => 'u',
        'ф' => 'f',
        'х' => 'h',
        'ц' => 'c',
        'ч' => 'ch',
        'ш' => 'sh',
        'щ' => 'sch',
        'ь' => '\'',
        'ы' => 'y',
        'ъ' => '\'',
        'э' => 'e',
        'ю' => 'yu',
        'я' => 'ya',

        'А' => 'A',
        'Б' => 'B',
        'В' => 'V',
        'Г' => 'G',
        'Д' => 'D',
        'Е' => 'E',
        'Ё' => 'E',
        'Ж' => 'Zh',
        'З' => 'Z',
        'И' => 'I',
        'Й' => 'Y',
        'К' => 'K',
        'Л' => 'L',
        'М' => 'M',
        'Н' => 'N',
        'О' => 'O',
        'П' => 'P',
        'Р' => 'R',
        'С' => 'S',
        'Т' => 'T',
        'У' => 'U',
        'Ф' => 'F',
        'Х' => 'H',
        'Ц' => 'C',
        'Ч' => 'Ch',
        'Ш' => 'Sh',
        'Щ' => 'Sch',
        'Ь' => '\'',
        'Ы' => 'Y',
        'Ъ' => '\'',
        'Э' => 'E',
        'Ю' => 'Yu',
        'Я' => 'Ya',
    );
    $s = strtr($s, $converter);
    $s = strtolower($s);
    $s = ik_filters_strip_whitespaces($s);
    $s = preg_replace('/[^ a-z_0-9\d]/ui', '', $s);
    $s = str_replace('-', '', $s);
    $s = str_replace(' ', '', $s);

    return $s;
}

add_action('pre_get_posts', 'ik_filter_change_query');

function ik_filter_change_query($query)
{
    $settings_taxonomy = get_field('ik-filters-taxonomy', 'option');

    if ($query->is_main_query() && $query->is_archive() && isset($query->query[$settings_taxonomy])) {
        $term = get_queried_object();
        if (isset($term->term_id) && $term->term_id > 0) {
            global $wpdb;
            $term_id = $term->term_id;
            $settings_filter_list = get_field('ik-filters-list', 'option');
            $prefix = 'ik_f-';
            $count = 0;
            $out = array();
            $sql = '';
            foreach ($settings_filter_list as $d) {
                $type = $d['type'];
                $data = $d[$type];
                $attr_name = ik_filters_handle_string($data['name']);
                $id = $prefix . $attr_name;


                if ($type == 'slider') {
                    //if (isset($_REQUEST[$id . '-min']) && !empty($_REQUEST[$id . '-min']) && isset($_REQUEST[$id . '-max']) && !empty($_REQUEST[$id . '-min'])) {
                    if (isset($_REQUEST[$id . '-min']) && isset($_REQUEST[$id . '-max']) && !empty($_REQUEST[$id . '-min'])) {
                        if ($count != 0) {
                            $sql .= ' UNION ALL';
                        }
                        $sql .= ' SELECT * FROM wp_ik_filters WHERE attr_name = \'' . $attr_name . '\' AND attr_value BETWEEN ' . $_REQUEST[$id . '-min'] . ' AND ' . $_REQUEST[$id . '-max'];
                        $count++;
                    }
                }
                if ($type == 'checkbox') {

                    if (isset($_REQUEST[$id]) && !empty($_REQUEST[$id])) {
                        if ($count != 0) {
                            $sql .= ' UNION ALL';
                        }
                        $in = '';
                        $k = 1;
                        foreach ($_REQUEST[$id] as $attr_value) {
                            if ($k > 1) {
                                $in .= ',';
                            }
                            $in .= '\'' . $attr_value . '\'';
                            $k++;
                        }
                        $sql .= ' SELECT * FROM wp_ik_filters WHERE attr_name = \'' . $attr_name . '\' AND attr_value IN (' . $in . ')';
                        $count++;
                    }
                }
                if ($type == 'free-text') {
                    if (isset($_REQUEST[$id]) && !empty($_REQUEST[$id])) {
                        if ($count != 0) {
                            $sql .= ' UNION ALL';
                        }
                        $in = '';
                        $k = 1;
                        foreach ($_REQUEST[$id] as $attr_value) {
                            if ($k > 1) {
                                $in .= ',';
                            }
                            $in .= '\'' . $attr_value . '\'';
                            $k++;
                        }
                        $sql .= ' SELECT * FROM wp_ik_filters WHERE attr_name = \'' . $attr_name . '\' AND attr_value IN (' . $in . ')';
                        $count++;
                    }
                }

            }
            if ($sql != '') {
                $sql = 'SELECT * FROM (' . $sql . ') as F WHERE category_id = \'' . $term_id . '\';';
            }

            //echo '<br>' . $sql . '';
            if (strlen($sql)) {
                $data = $wpdb->get_results($sql, ARRAY_A);
                foreach ($data as $d) {
                    if (isset($out[$d['post_id']][$d['attr_name']])) {
                        $out[$d['post_id']][$d['attr_name']] += 1;
                    } else {
                        $out[$d['post_id']][$d['attr_name']] = 1;
                    }

                }

                $product_ids = array();
                foreach ($out as $product_id => $d) {
                    if (count($d) >= $count) {

                        $product_ids[$product_id] = $product_id;
                    }
                }
                if ($count > 0) {
                    if (is_array($product_ids) && !empty($product_ids)) {
                        $query->set('post__in', $product_ids);
                    } else {
                        $query->set('post__in', array(-1));
                    }

                }
            }

            if (isset($_REQUEST[$prefix . 'order'])) {
                $ordering = intval($_REQUEST[$prefix . 'order']);
            } else {
                $ordering = 1;
            }

            $meta_key = '';
            $orderby = '';
            $order = '';


            if (isset($_REQUEST[$prefix . 'order']) && !empty($_REQUEST[$prefix . 'order'])) {
                //$query->set_query_var('ignore_custom_sort', TRUE);
                if ($_REQUEST[$prefix . 'order'] == 'price') {
                    $orderby = 'meta_value_num';
                    $meta_key = '_price';
                    if (isset($_REQUEST[$prefix . 'order-type']) && $_REQUEST[$prefix . 'order-type'] == 'asc') {
                        $order = 'asc';
                    } else {
                        $order = 'desc';
                    }
                }
                if ($_REQUEST[$prefix . 'order'] == 'top') {
                    $orderby = 'popularity';
                    if (isset($_REQUEST[$prefix . 'order-type']) && $_REQUEST[$prefix . 'order-type'] == 'asc') {
                        $order = 'asc';
                    } else {
                        $order = 'desc';
                    }
                }
                if ($_REQUEST[$prefix . 'order'] == 'date') {
                    $orderby = 'date';
                    if (isset($_REQUEST[$prefix . 'order-type']) && $_REQUEST[$prefix . 'order-type'] == 'asc') {
                        $order = 'asc';
                    } else {
                        $order = 'desc';
                    }
                }
            }

            if (strlen($orderby) > 0) {
                $query->set('orderby', $orderby);
            } else{
                $query->set('orderby', 'menu_order');
            }
            if (strlen($order) > 0) {
                $query->set('order', $order);
            } else{
                $query->set('order', 'asc');
            }
            if (strlen($meta_key) > 0) {
                $query->set('meta_key', $meta_key);
            }

        }
    }
}
