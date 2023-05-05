<?php

/**
 * Form Post Type
 * @package Form Edtior
 * @author Makanaokeakua Edwards | Makri Software Development
 * @copyright 2023 @ Makri Software Development
 * @license Proprietary
 */

declare(strict_types=1);

namespace FormEdtior\Types;

use FormEdtior\Classes\Base;

class Forms extends Base
{
    public function init()
    {
        add_action('init', [$this, 'registerTypes']);
        add_action('add_meta_boxes', [$this, 'loadMetaBoxes']);
        add_action('save_post_form', [$this, 'saveMetaBoxes']);
    }

    public function registerTypes()
    {
        $id = "form";
        $singular = "Form";
        $plural = "Forms";
        $supports = ['title', 'editor', 'author'];

        $options = [
            'labels' => [
                'name' => $plural,
                'singular_name' => $singular,
                'menu_name' => $plural,
                'name_admin_bar' => $singular,
                'add_new' => sprintf(__('New %s', 'archipelago'), $singular),
                'add_new_item' => sprintf(__('Add New %s', 'archipelago'), $singular),
                'new_item' => sprintf(__('New %s', 'archipelago'), $singular),
                'edit_item' => sprintf(__('Edit %s', 'archipelago'), $singular),
                'view_item' => sprintf(__('View %s', 'archipelago'), $singular),
                'all_items' => sprintf(__('%s', 'archipelago'), $plural),
                'search_items' => sprintf(__('Search %s', 'archipelago'), $plural),
            ],
            'public' => false,
            'show_in_rest' => true,
            'publicly_queryable' => false,
            'has_archive' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'menu_position' => 1,
            'query_var' => true,
            'map_meta_cap' => true,
            'capabilities'          => [
                'edit_posts' => 'edit_users',
                'edit_others_posts' => 'edit_users',
                'edit_published_posts' => 'edit_users',
                'publish_posts' => 'edit_users',
                'create_posts' => 'edit_users',
            ],
            'supports'           => $supports,
            'taxonomies'            => []
        ];
        register_post_type($id, $options);
    }

    public function loadMetaBoxes()
    {
        add_meta_box('form_entry_meta', __('Form', 'archipelago'), [$this, 'doMetaBoxes'], 'form', 'advanced', 'high');
    }

    function buildOptions($arr, $key, $name, $options = null)
    {
        $inputName = $name . '[' . $arr['slug'] . ']';
?>
        <li>
            <input type="text" name="<?php echo $inputName ?>[label]" style="display: none" value="<?php echo $arr['name'] ?>">
            <input type="text" name="<?php echo $inputName ?>[slug]" style="display: none" value="<?php echo $arr['slug'] ?>">

            <?php if (isset($arr['children'])) {
                if(isset($options['children'])) {
                    $children = $options['children'];
                }
            ?>
                <label class="title"><strong><?php echo $arr['name'] ?></strong></label>
                <?php
                foreach ($arr['children'] as $child) {
                ?>
                    <ul>
                        <?php echo $this->buildOptions($child, $key, $inputName . '[children]', $children[$child['slug']] ?? null); ?>
                    </ul>
                <?php }
            } else {
                ?>
                    <label for="checkbox_<?php echo $arr['slug'] ?>"><?php echo $arr['name'] ?></label>
                    <input type="checkbox" id="checkbox_<?php echo $arr['slug'] ?>" name="<?php echo $inputName ?>[value]" <?php echo (isset($options) && isset($options['value']) && boolval($options['value']) ? 'checked' : '') ?>>
            <?php
            }
            ?>
        </li>
    <?php
    }

    // Map the terms by parents to make building html easier
    function buildTree(array $flatData, $rootId = 0)
    {
        if (count($flatData) <= 0) {
            return $flatData;
        }

        $groupedData = [];
        foreach ($flatData as $node) {
            $groupedData[$node['parent']][] = $node;
        }

        $fnBuilder = function ($siblings) use (&$fnBuilder, $groupedData) {
            $tree = [];
            foreach ($siblings as $sibling) {
                $id = isset($sibling['term_id']) ? $sibling['term_id'] : null;

                if (isset($groupedData[$id])) {
                    $sibling['children'] = $fnBuilder($groupedData[$id]);
                }
                $tree[] = $sibling;
            }
            return $tree;
        };

        return $fnBuilder($groupedData[$rootId]);
    }

    public function doMetaBoxes($post)
    {
        wp_nonce_field('form_metaboxes', 'form_metaboxes_nonce');

        $meta = get_post_meta($post->ID, 'form_inputs');

        $form_inputs = isset($meta) ? $meta : [];

        $taxonomy = 'form_option';

        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'hierarchical' => 1
        ]);

        $options = [
            "input_text" => "Short Text",
            "textarea" => "Long Text",
            "input_radio" => "Radios",
            "input_checkbox" => "Checkboxes",
            "select_choice" => "Multiple Choice",
            "select_dropdown" => "Drop Down",
            "input_tel" => "Phone Number",
            "input_email" => "Email"
        ];

        $requiresSelections = [
            "input_radio" => "Radios",
            "input_checkbox" => "Checkboxes",
            "select_choice" => "Multiple Choice",
            "select_dropdown" => "Drop Down",
        ];

        $tax_terms = json_decode(json_encode($terms), true);

        $mapped = $this->buildTree($tax_terms);

    ?>
        <section class="form-wrap">
            <table class="form-table">
                <tbody id="inputs">

                    <?php if (isset($form_inputs[0])) { ?>
                        <?php foreach ($form_inputs[0] as $key => $input) { ?>
                            <tr id="input_<?php echo $key ?>">
                                <td>
                                    <label for="label">Label</label>
                                    <input name="form_inputs[<?php echo $key ?>][label]" id="label" type="text" value="<?php echo ($input['label'] ?? '') ?>">
                                </td>

                                <td>
                                    <label for="helpText">Help Text</label>
                                    <input type="text" id="helpText" name="form_inputs[<?php echo $key ?>][help_text]" value="<?php echo ($input['help_text'] ?? '') ?>">
                                </td>

                                <td>
                                    <label for="type">Type</label>
                                    <select id="input_type_<?php echo $key ?>" name="form_inputs[<?php echo $key ?>][input_type]" onchange="setOptions(this, <?php echo $key ?>)">
                                        <?php foreach ($options as $value => $label) { ?>
                                            <option value="<?php echo $value ?>" <?php echo ($input['input_type'] == $value ? 'selected' : '') ?>><?php echo $label ?></option>
                                        <?php } ?>
                                    </select>
                                </td>

                                <td>
                                    <label for="required">Required</label>
                                    <input name="form_inputs[<?php echo $key ?>][required]" id="required" type="checkbox" <?php echo (isset($input['required']) ? 'checked' : '') ?>>
                                </td>


                                <td id="taxonomy-meta-wrapper-<?php echo $key ?>" style="<?php echo (key_exists($input['input_type'], $requiresSelections) ? '' : 'display: none') ?>">
                                    <div class="options" id="<?php echo ("options-" . $key) ?>" name="form_inputs[<?php echo $key ?>][options]">
                                        <select id="options-selecter-<?php echo $key ?>" onchange="switchOption(this.value, <?php echo $key ?>)">
                                            <option value="">Select an Option</option>
                                            <?php foreach ($mapped as $map) { ?>
                                                <option value="<?php echo $map['slug'] . '-' . $key ?>" <?php echo (isset($input['options'][$map['slug']]) ? 'selected' : '') ?>><?php echo $map['name'] ?></option>
                                            <?php } ?>
                                        </select>

                                        <?php
                                        foreach ($mapped as $map) {
                                            $mapOptions = isset($input['options'][$map['slug']]) ? $input['options'][$map['slug']] : null;
                                        ?>
                                            <ul id="<?php echo $map['slug'] ?>-<?php echo $key ?>" <?php echo (isset($input['options'][$map['slug']]) ? '' : 'style="display: none"') ?>>
                                                <?php echo $this->buildOptions($map, $key, "form_inputs[" . $key . "][options]", $mapOptions); ?>
                                            </ul>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                </td>

                                <td>
                                    <input class="button" type="button" value="Delete" onclick="removeInput('#input_<?php echo $key ?>')">
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>

            <div class="add-form-field">
                <a id="add-button" class="button">
                    Add Form Field
                </a>
            </div>
        </section>

        <script>
            <?php
            $inputCount = (isset($form_inputs[0]) ? (count($form_inputs[0]) > 0 ? count($form_inputs[0]) : 1) : 1);
            ?>
            var inputCount = <?php echo (isset($form_inputs[0]) ? count($form_inputs[0]) : 0) ?>;

            function removeInput(id) {
                jQuery(id).remove();
                inputCount--;
            }

            <?php echo "var options = " . json_encode($requiresSelections) . ";" ?>

            var selected;

            function setOptions(target, id) {
                var keys = Object.keys(options);

                var option = jQuery(`#input_${id} .options`);

                var allOptionList = jQuery(`#options-${id} > ul`);

                var tax = jQuery(`#taxonomy-meta-wrapper-${id}`);

                var allInputs = jQuery(`#options-${id} #${target.value} input`);

                var allOptionsInputs = jQuery(`#options-${id} input`);

                var selector = jQuery(`#options-selecter-${id}`);

                if (keys.some((k) => k === target.value)) {
                    option.attr('style', 'display: auto')
                    tax.attr('style', 'display: auto');
                } else {
                    option.attr('style', 'display: none')
                    tax.attr('style', 'display: none');

                    selector.val(null);

                    for (var input of allOptionsInputs) {
                         input.setAttribute('disabled', true)
                    }

                    for (var element of allOptionList) {
                        element.setAttribute('style', 'display: none')
                    }
                }
            }

            function switchOption(slug, id) {
                var all = jQuery(`#options-${id} > ul`);

                for (var element of all) {
                    var allInputs = jQuery(`#${element.id} input`);

                    if (element.id === slug) {
                        element.setAttribute('style', 'display: auto')

                        for (var input of allInputs) {
                            input.removeAttribute('disabled')
                        }
                    } else {
                        element.setAttribute('style', 'display: none')

                        for (var input of allInputs) {
                            input.setAttribute('disabled', true)
                        }
                    }
                }
            }

            function buildOptions(arr, key, name) {
                const inputName = `${name}[${arr.slug}]`;
                let children = null;
                let output = '';

                output += '<li>';
                output += `<input type="text" name="${inputName}[label]" style="display: none" value="${arr.name}" disabled>`;
                output += `<input type="text" name="${inputName}[slug]" style="display: none" value="${arr.slug}" disabled>`;

                if (arr.children) {
                    output += `<label class="title"><strong>${arr.name}</strong></label>`;
                    arr.children.forEach((child) => {
                    output += '<ul>';
                    output += buildOptions(child, key, `${inputName}[children]`, children ? children[child.slug] : null);
                    output += '</ul>';
                    });
                } else {
                    output += `<label for="checkbox_${arr.slug}">${arr.name}</label>`;
                    output += `<input type="checkbox" id="checkbox_${arr.slug}" name="${inputName}[value]" disabled>`;
                }

                output += '</li>';

                return output;
            }


            jQuery(document).ready(function($) {
                $('#add-button').click(() => {

                    $('#inputs').append(`
                        <tr id="input_${inputCount}">
                            <td id="label_${inputCount}">
                                <label for="label">Label</label>
                                <input name="form_inputs[${inputCount}][label]" id="label" type="text">
                            </td>

                            <td>
                                <label for="helpText">Help Text</label>
                                <input type="text" id="helpText" name="form_inputs[${inputCount}][help_text]">
                            </td>

                            <td id="input_type_wrap_${inputCount}">
                                <label for="type">Type</label>
                                <select id="input_type_${inputCount}" name="form_inputs[${inputCount}][input_type]" onchange="setOptions(this, ${inputCount})">
                                <?php foreach ($options as $value => $label) {  ?>
                                    <option value="<?php echo $value ?>"><?php echo $label ?></option>
                                <?php } ?>
                                </select>
                            </td>

                            <td id="required_${inputCount}">
                                <label for="required">Required</label>
                                <input name="form_inputs[${inputCount}][required]" id="required" type="checkbox">
                            </td>

                            <td id="taxonomy-meta-wrapper-${inputCount}" style="display: none">
                                <div class="options" id="<?php echo "options-" ?>${inputCount}" style="display: none" name="form_inputs[${inputCount}][options]">
                                        <select id="options-selecter-${inputCount}"  onchange="switchOption(this.value, ${inputCount})">
                                            <option value="">Select an Option</option>
                                            <?php foreach ($mapped as $map) { ?>
                                                <option value="<?php echo $map['slug'] . "-" ?>${inputCount}"><?php echo $map['name'] ?></option>
                                            <?php } ?>
                                        </select>
                                        <?php

                                        foreach ($mapped as $map) {
                                        ?>
                                            <ul id="<?php echo $map['slug'] ?>-${inputCount}" style="display: none">
                                                ${buildOptions(<?php echo json_encode($map) ?>, inputCount, `form_inputs[${inputCount}][options]`)}
                                            </ul>
                                            <?php
                                        }
                                            ?>
                                    
                                </div>
                            </td>

                            <td>
                                <input class="button" type="button" value="Delete" onclick="removeInput('#input_${inputCount}')">
                            </td>
                        </tr>
                    `)

                    inputCount++;
                })

                $('#inputs').sortable();
            })
        </script>

        <style>
            .add-form-field {
                width: 100%;
                height: max-content;
                display: flex;
                flex-direction: column;
                margin-top: 2em;
            }

            .add-form-field a.button {
                display: block;
                text-align: center;
                width: max-content;
                align-self: center;
            }

            .options {
                display: flex;
                flex-direction: column;
                justify-content: flex-start;
                align-items: center;
            }

            .options .option-select {
                background-color: whitesmoke;
                border-radius: 3px;
                padding: 0.5em;
                width: calc(100% - 1em);
                margin: auto;
                margin-bottom: 0.5em;
            }

            .options>ul {
                width: 100%;
                margin: 0;
                margin-top: 0.5em;
                padding: 1em;
                background-color: whitesmoke;
                border-radius: 3px;
            }

            .options>ul ul {
                margin-left: 1em;
            }

            .options label {
                display: flex;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                width: 100%;
            }

            .options label input {
                margin-left: 1em;
            }
        </style>
<?php
    }

    public function saveMetaBoxes($post_id)
    {
        if (!isset($_POST['form_metaboxes_nonce'])) {
            return $post_id;
        }

        $nonce = $_POST['form_metaboxes_nonce'];

        if (!wp_verify_nonce($nonce, 'form_metaboxes')) {
            return $post_id;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        $form_inputs_meta = $_POST['form_inputs'] ?? [];

        $filtered = [];

        // Sanitize inputs before saving meta
        foreach ($form_inputs_meta as $value) {
            if (is_array($value)) {
                $filtered[] = $value;
            } else {
                foreach ($value as $v) {
                    $filtered[][] = sanitize_text_field($v);
                }
            }
        }

        update_post_meta($post_id, 'form_inputs', $filtered);
    }
}
