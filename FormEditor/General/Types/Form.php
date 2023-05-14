<?php

/**
 * Form Post Type
 * @package Form Editor
 * @author Makanaokeakua Edwards | Makri Software Development
 * @copyright 2023 @ Makri Software Development
 * @license Proprietary
 */

declare(strict_types=1);

namespace FormEditor\General\Types;

use FormEditor\Classes\Base;

class Form extends Base
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
                'add_new' => sprintf(__('New %s'), $singular),
                'add_new_item' => sprintf(__('Add New %s'), $singular),
                'new_item' => sprintf(__('New %s'), $singular),
                'edit_item' => sprintf(__('Edit %s'), $singular),
                'view_item' => sprintf(__('View %s'), $singular),
                'all_items' => sprintf(__('%s'), $plural),
                'search_items' => sprintf(__('Search %s'), $plural),
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
        add_meta_box('form_entry_meta', __('Form'), [$this, 'doMetaBoxes'], 'form', 'advanced', 'high');
    }

    function buildOptions($arr, $key, $options)
    {
?>
            <?php if (isset($arr['children'])) { 
                    $keys = $options ? array_column($options, 'slug') : false;
                ?>
                <optgroup label="<?php echo $arr['name'] ?>">
                    <?php foreach ($arr['children'] as $child) { 
                            $class = "";

                            if(isset($keys) && is_array($keys)) {
                                foreach($keys as $v) {
                                    if($child['slug'] == $v) {
                                        $class = "selected";
                                    }
                                }
                            }
                        ?>
                        <option value="<?php echo $child['slug'] ?>" onclick='updateTags("<?php echo $child["slug"] ?>", "<?php echo htmlentities($child["name"], ENT_QUOTES, "UTF-8") ?>", "#<?php echo $arr["slug"] ?>-tags-<?php echo $key ?>", this, <?php echo $key ?>, "<?php echo $arr["slug"] ?>")' class="<?php echo $class ?>"><?php echo $child['name'] ?></option>
                    <?php } ?>
                </optgroup>
            <?php } else {
                        $class = "";

                        if(isset($keys) && is_array($keys)) {
                            foreach($keys as $v) {
                                if($child['slug'] == $v) {
                                    $class = "selected";
                                }
                            }
                        }
            ?>
                <option value="<?php echo $arr['slug'] ?>" onclick='updateTags("<?php echo $arr["slug"] ?>", "<?php echo htmlentities($arr["name"], ENT_QUOTES, "UTF-8") ?>", "#<?php echo $arr["slug"] ?>-tags-<?php echo $key ?>", this, <?php echo $key ?>, "<?php echo $arr["slug"] ?>")' class="<?php echo $class ?>"><?php echo $arr['name'] ?></option>
            <?php }
        ?>
    <?php
    }

    private function array_some($arr, $key, $val = null)
	{

		if (!is_null($val)) {
			foreach ($arr as $a) {
				if (isset($a[$key]) && $a[$key] = $val) {
					return true;
				}
			}
		} else {
			foreach ($arr as $a) {
				if ($a == $key) {
					return true;
				}
			}
		}

		return false;
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
                <tbody class="sortable">
                    <?php if (isset($form_inputs[0])) { ?>
                        <?php foreach ($form_inputs[0] as $key => $input) { ?>
                            <tr id="input_<?php echo $key ?>" class="handle">
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
                                    <select id="input_type_<?php echo $key ?>" name="form_inputs[<?php echo $key ?>][input_type]" onchange="updateSelectedTerm(this.value, '#options-selector-<?php echo $key ?>', <?php echo $key ?>)">
                                        <?php foreach ($options as $value => $label) { ?>
                                            <option value="<?php echo $value ?>" <?php echo ($input['input_type'] == $value ? 'selected' : '') ?>><?php echo $label ?></option>
                                        <?php } ?>
                                    </select>
                                </td>

                                <td id="options-selector-<?php echo $key ?>" <?php echo(isset($input['input_type']) && isset($requiresSelections[$input['input_type']]) ? '' : 'style="display: none"') ?>>
                                    <label for="options-selector">Select Option Group</label>
                                    
                                    <select id="options-selector" name="form_inputs[<?php echo $key ?>][option_group]" onchange="updateOptions('#' + this.value + '-<?php echo $key ?>', '#input_<?php echo $key ?>', `#${this.value}-tags-<?php echo $key ?>`)">
                                        <option value="uncategorized" <?php echo(isset($input['option_group']) && $input['option_group'] == 'uncategorized' ? 'selected' : '') ?>>Uncategorized</option>
                                        <?php foreach ($mapped as $map) {
                                                if(isset($map['children'])) {
                                             ?>
                                            <option value="<?php echo $map['slug'] ?>" <?php echo (isset($input['option_group']) && $input['option_group'] == $map['slug'] ? 'selected' :  '') ?>><?php echo $map['name'] ?></option>
                                        <?php 
                                                }
                                            } ?>
                                    </select>
                                </td>

                                    <?php foreach ($mapped as $map) { 
                                        $selected = isset($requiresSelections[$input['input_type']]) ? $map['slug'] == $input['option_group'] : false;
                                        
                                        if(isset($map['children'])) {
                                        ?>
                                        <td class="options-dropper" id="<?php echo $map['slug'] . '-' . $key ?>" <?php echo ($selected ? '' : 'style="display: none"') ?>>
                                            <input placeholder="Search Options" type="search" id="search-<?php echo $key ?>" oninput='updateSelections(this.value, "#<?php echo $map["slug"] . "-" . $key ?> #<?php echo $map["slug"] ?>-dropper-select-<?php echo $key ?>")'>

                                            <div class="tags" id="<?php echo $map['slug'] ?>-tags-<?php echo $key ?>">
                                                <?php if(isset($input['options']) && is_array($input['options']) && $map['slug'] === $input['option_group'] ) {
                                                    foreach($input['options'] as $k => $tag) {
                                                ?>
                                                    <span id="<?php echo $tag['slug'] ?>" onclick="removeTag('<?php echo $tag['slug'] ?>', '#input_<?php echo $key ?> #<?php echo $map['slug'] ?>-dropper-select-<?php echo $key ?> option', '#<?php echo $map['slug'] ?>-tags-<?php echo $key ?>')">
                                                        <?php echo $tag['name'] ?>
                                                        <input type="text" value="<?php echo $tag['slug'] ?>" style="display: none" id="<?php echo $tag['slug'] ?>" name="form_inputs[<?php echo $key ?>][options][<?php echo $k ?>][slug]">
                                                        <input type="text" value="<?php echo $tag['name'] ?>" style="display: none" id="<?php echo $tag['slug'] ?>" name="form_inputs[<?php echo $key ?>][options][<?php echo $k ?>][name]">
                                                    </span>
                                                <?php }
                                                } ?>
                                            </div>

                                            <select id="<?php echo $map['slug'] ?>-dropper-select-<?php echo $key ?>" multiple>
                                                <?php echo $this->buildOptions($map, $key, isset($input['options']) ? $input['options'] : false); ?>                                            
                                            </select>

                                            <span>click on the option(s) you want to add</span>
                                        </td>
                                    <?php }
                                    } ?>
                                    
                                    <?php $selected = isset($requiresSelections[$input['input_type']]) ? "uncategorized" == $input['option_group'] : false; ?>

                                    <td class="options-dropper" id="<?php echo 'uncategorized-' . $key ?>" <?php echo ($selected ? '' : 'style="display: none"') ?>>
                                        <input placeholder="Search Options" type="search" id="search-<?php echo $key ?>" oninput='updateSelections(this.value, "#<?php echo "uncategorized-" . $key ?> #uncategorized-dropper-select-<?php echo $key ?>")'>
                                        
                                        <div class="tags" id="uncategorized-tags-<?php echo $key ?>">
                                            <?php if(isset($input['options']) && is_array($input['options']) && 'uncategorized' == $input['option_group'] ) {
                                                foreach($input['options'] as $k => $tag) {
                                            ?>
                                                <span id="<?php echo $tag['slug'] ?>" onclick="removeTag('<?php echo $tag['slug'] ?>', '#input_<?php echo $key ?> #uncategorized-dropper-select-<?php echo $key ?> option', '#uncategorized-tags-<?php echo $key ?>')">
                                                    <?php echo $tag['name'] ?>
                                                    <input type="text" value="<?php echo $tag['slug'] ?>" style="display: none" id="<?php echo $tag['slug'] ?>" name="form_inputs[<?php echo $key ?>][options][<?php echo $k ?>][slug]">
                                                    <input type="text" value="<?php echo $tag['name'] ?>" style="display: none" id="<?php echo $tag['slug'] ?>" name="form_inputs[<?php echo $key ?>][options][<?php echo $k ?>][name]">
                                                </span>
                                            <?php }
                                            } ?>
                                        </div>

                                        <select id="uncategorized-dropper-select-<?php echo $key ?>" multiple>
                                            <optgroup label="Uncategorized">
                                                <?php 
                                                $keys = array_column($tax_terms, 'parent');
                                                
                                                $options_keys = isset($input['options']) && is_array($input['options']) ? array_column($input['options'], 'slug') : false;

                                                foreach($tax_terms as $tax) {
                                                    if($keys) {
                                                        $hasKids = $this->array_some($keys, $tax['term_id']);
                                                        $class = $options_keys ? $this->array_some($options_keys, $tax['slug']) ? 'selected' : false : false;
                                                        if($tax['parent'] == 0 && $hasKids == false) {  
                                                    ?>
                                                        <option value="<?php echo $tax['slug'] ?>" onclick='updateTags("<?php echo $tax["slug"] ?>", "<?php echo htmlentities($tax["name"], ENT_QUOTES, "UTF-8") ?>", "#uncategorized-tags-<?php echo $key ?>", this, <?php echo $key ?>, "<?php echo $tax["slug"] ?>", true)' class="<?php echo $class ?? '' ?>"><?php echo $tax['name'] ?></option>                                           
                                                    <?php 
                                                        }
                                                    }
                                                }?>
                                            </optgroup>
                                        </select>

                                        <span>click on the option(s) you want to add</span>
                                    </td>
                              
                                <td>
                                    <label for="per_attendee-<?php echo $key ?>">Per Attendee?</label>
                                    <input name="form_inputs[<?php echo $key ?>][per_attendee]" id="per_attendee-<?php echo $key ?>" type="checkbox" <?php echo (isset($input['per_attendee']) ? 'checked' : '') ?>>
                                </td>

                                <td>
                                    <label for="required-<?php echo $key ?>">Required</label>
                                    <input name="form_inputs[<?php echo $key ?>][required]" id="required-<?php echo $key ?>" type="checkbox" <?php echo (isset($input['required']) ? 'checked' : '') ?>>
                                </td>

                                <td class="delete">
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
            <?php echo "var options = " . json_encode($requiresSelections) . ";" ?>

            var inputCount = <?php echo (isset($form_inputs[0]) ? count($form_inputs[0]) + 1 : 0) ?>;

            function toggleSelectAll(id, target) {
                var parent = jQuery(id);

                // check if parent exist and get all child inputs and toggle them selected or not(disable or not)
                if(parent) {
                    var inputs = jQuery('input', parent);

                    if(inputs) {
                        for(const child of inputs) {
                            if(child.type === 'text') {
                                if(!target.checked) {
                                    child.setAttribute('disabled', true)
                                } else {
                                    child.removeAttribute('disabled')
                                }
                            } else {
                                if(!target.checked) {
                                    child.classList.remove('selected');
                                } else {
                                    child.classList.add('selected');
                                }
                            }
                        }
                    }
                }
            }
             
            function toggleDisabledAttribute(id, target) {
                var parent = jQuery('#parent_' + id);
                var slug = jQuery('#slug_' + id);

                var parentDisabled = parent.attr('disabled');
                var slugDisabled = slug.attr('disabled');
                
                if (parentDisabled) {
                    parent.removeAttr('disabled');
                    slug.removeAttr('disabled');
                } else {
                    parent.attr('disabled', true);
                    slug.attr('disabled', true);
                }

                target.classList.toggle('selected');
            }

            function removeInput(id) {
                jQuery(id).remove();
                inputCount--;
            }

            function updateSelectedTerm(value, id, key) {
                var selector = jQuery(id);

                var selectEl = jQuery('select', selector)

                var t = jQuery(`#input_${key} .options-dropper .tags`)

                if(typeof options[value] !== 'undefined') {
                    selector.attr('style', 'display: auto')
                    selectEl.removeAttr('disabled')
                } else {
                    selector.attr('style', 'display: none')
                    selectEl.attr('disabled', true)
                    for(const tag of t) {
                        t.html('');
                    }
                
                }

                var show = jQuery(`#${selectEl.val()}-${key}`)

                const selectOptions = jQuery(`#${selectEl.val()}-${key} select option`)

                if(selector.attr('style') == 'display: auto') {
                    show.attr('style', 'display: auto')
                } else {
                    show.attr('style', 'display: none')
                    for(const option of selectOptions) {
                        option.classList.remove('selected')
                    }
                }
            }

            let tags = [];

            function updateOptions(id, baseId, tagsId){
                // remove existing tags before updating if tags exist
                tags = [];

                if(tagsId) {
                    const t = jQuery(tagsId)
                    for(const tag of t.children()) {
                        tag.remove();
                    }
                }

                var selected = jQuery(id);

                selected.attr('style', 'display: auto');

                var base = jQuery(baseId)

                var notSelectedItems = jQuery('.options-dropper', base).not(selected);

                notSelectedItems.each((item) => {
                    notSelectedItems[item].style.display = 'none';

                    for(const child of notSelectedItems[item].children) {
                        const inputs = jQuery('input', child)
                        const selections = jQuery('option', child)

                        for(const input of inputs) {
                            if(input.type === 'text') {
                                input.setAttribute('disabled', true)
                            }
                        }

                        for(const option of selections) {
                            option.classList.remove('selected')
                        }
                    }
                })

                const children = selected.children();

                for(const child of children) {
                    const inputs = jQuery('input', child)

                    for(const input of inputs) {
                        if(input.type === 'text') {
                            input.removeAttribute('disabled')
                        }
                    }
                }
            }

            function updateSelections(value, id) {
                const parent = jQuery(id)

                if(parent) {
                    const children = jQuery('option', parent)

                    for(const child of children) {
                        if(child.value?.toLowerCase()?.startsWith(value.toLowerCase()) && value !== '' && value !== null) {
                            child.setAttribute('style', 'display: auto');
                        } else if(!child.value?.toLowerCase()?.startsWith(value.toLowerCase()) && value !== '') {
                            child.setAttribute('style', 'display: none');
                        } else if(value === "") {
                            child.setAttribute('style', 'display: auto');
                        } else {
                            child.setAttribute('style', 'display: none');
                        }
                    }
                }
            }

            function updateTags(slug, name, id, target, key, selectId, uncategorized = false) {
                const tagWrapper = jQuery(id);

                const temp = [];
                    
                for(const tag of tagWrapper.children()) {
                    const inputs = jQuery('input', tag)
                        
                    temp.push({
                        slug: inputs[0]?.value,
                        name: inputs[1]?.value
                    })
                }

                tags = temp;

                target.classList.toggle('selected');

                if(tags.some(tag => tag.slug === slug)) {
                    tags = tags.filter(tag => tag.slug !== slug);
                } else {
                    tags.push({slug, name})
                }
                
                tagWrapper.html(tags.map((tag, i) => `
                    <span id="${tag.slug}" onclick="removeTag('${tag.slug}', '#input_${key} #${uncategorized ? 'uncategorized' : selectId}-dropper-select-${key} option', '${id}')">
                        ${tag.name}
                        <input type="text" value="${tag.slug}" style="display: none" id="${tag.slug}" name="form_inputs[${key}][options][${i}][slug]">
                        <input type="text" value="${tag.name}" style="display: none" id="${tag.slug}" name="form_inputs[${key}][options][${i}][name]">
                    </span>
                `).join(''));
            }

            function removeTag(slug, base, tagsId) {
                if(tagsId) {
                    const t = jQuery(tagsId)
                    for(const tag of t.children()) {
                        tags.push({
                            slug: t.id,
                            name: t.innerHTML
                        })
                    }
                }

                tags = tags.filter(tag => tag.slug !== slug);
                tag = jQuery(`${tagsId} #${slug}`);
                tag.remove();

                if(base) {
                    const options = jQuery(base)

                    for(const o of options) {
                        if(o.value === slug) {
                            o.classList.remove('selected')
                        }
                    }
                }
            }

            var selected;

            function buildOptions(arr, key, name) {
                if(!arr || arr === null) return
                
                let output = '';

                if (arr.children) {
                    let children = arr.children;

                    output += `<optgroup label="${arr.name}">`;

                    for (const child of children) {
                        output += `<option value="${child.slug}" onclick='updateTags("${child.slug}", "${encodeURIComponent(child.name)}", "#${arr.slug}-tags-${key}", this, ${key}, "${arr.slug}")'>${child.name}</option>`;
                    }

                    output += '</optgroup>';
                } else {
                    output += `<option value="${arr.slug}" onclick='updateTags("${arr.slug}", "${encodeURIComponent(arr.name)}", "#${arr.slug}-tags-${key}", this, ${key}, "${arr.slug}")'>${arr.name}</option>`;
                }

                return output;
            }


            jQuery(document).ready(function($) {
                $(".multidatalist").focusin ( function() { $(this).attr("type","email"); });    
                $(".multidatalist").focusout( function() { $(this).attr("type","textbox"); });

                $('#add-button').click(() => {

                    $('.sortable').append(`
                        <tr id="input_${inputCount}" class="handle">
                            <td id="label_${inputCount}">
                                <label for="label">Label</label>
                                <input name="form_inputs[${inputCount}][label]" id="label" type="text">
                            </td>

                            <td>
                                <label for="helpText">Help Text</label>
                                <input type="text" id="helpText" name="form_inputs[${inputCount}][help_text]">
                            </td>

                            <td>
                                <label for="type">Type</label>
                                <select id="input_type_${inputCount}" name="form_inputs[${inputCount}][input_type]" onchange="updateSelectedTerm(this.value, '#options-selector-${inputCount}', ${inputCount})">
                                    <?php foreach ($options as $value => $label) { ?>
                                        <option value="<?php echo $value ?>"><?php echo $label ?></option>
                                    <?php } ?>
                                </select>
                            </td>

                            <td id="options-selector-${inputCount}" style="display: none">
                                <label for="options-selector">Select Option Group</label>
                                    
                                <select id="options-selector" value="uncategorized" name="form_inputs[${inputCount}][option_group]" onchange="updateOptions('#' + this.value + '-${inputCount}', '#input_${inputCount}', '#' + this.value + '-tags-${inputCount}')">
                                    <option value="uncategorized">Uncategorized</option>
                                    <?php foreach ($mapped as $map) { 
                                        if(isset($map['children'])) {
                                    ?>
                                         <option value="<?php echo $map['slug'] ?>"><?php echo $map['name'] ?></option>
                                    <?php 
                                        }
                                    } ?>
                                </select>
                            </td>

                            <?php foreach ($mapped as $map) {
                                if(isset($map['children'])) { ?>
                                <td class="options-dropper" id="<?php echo $map['slug'] ?>-${inputCount}" style="display: none">
                                    <input placeholder="Search Options" type="search" id="search-${inputCount}" oninput='updateSelections(this.value, "#<?php echo $map["slug"] ?>-${inputCount} #<?php echo $map['slug'] ?>-dropper-select-${inputCount}", <?php echo json_encode($map) ?>)'>

                                    <div class="tags" id="<?php echo $map['slug'] ?>-tags-${inputCount}">
                                        <?php if(isset($input['options']) && is_array($input['options'])) {
                                            foreach($input['options'] as $k => $tag) {
                                            ?>
                                                <span id="<?php echo $tag['slug'] ?>" onclick="removeTag('<?php echo $tag['slug'] ?>', '#input_${inputCount} #<?php echo $map['slug'] ?>-dropper-select-${inputCount} option', '#<?php echo $map['slug'] ?>-tags-${inputCount}')">
                                                    <?php echo $tag['name'] ?>
                                                    <input type="text" value="<?php echo $tag['slug'] ?>" style="display: none" id="<?php echo $tag['slug'] ?>" name="form_inputs[<?php echo $key ?>][options][<?php echo $k ?>][slug]">
                                                    <input type="text" value="<?php echo $tag['name'] ?>" style="display: none" id="<?php echo $tag['slug'] ?>" name="form_inputs[<?php echo $key ?>][options][<?php echo $k ?>][name]">
                                                </span>
                                            <?php }
                                        } ?>
                                     </div>

                                    <select id="<?php echo $map['slug'] ?>-dropper-select-${inputCount}" multiple>
                                        ${buildOptions(<?php echo json_encode($map) ?>, inputCount, `<?php echo(isset($input['options']) && is_array($input['options']) ? $input['options'] : false) ?>`)}
                                    </select>

                                    <span>click on the option(s) you want to add</span>
                                </td>
                            <?php } 
                        } ?>    
                            
                            <td class="options-dropper" id="uncategorized-${inputCount}" style="display: none">
                                <input placeholder="Search Options" type="search" id="search-${inputCount}" oninput='updateSelections(this.value, "#uncategorized-${inputCount} #uncategorized-dropper-select-${inputCount}")'>

                                <div class="tags" id="uncategorized-tags-${inputCount}"></div>

                                <select id="uncategorized-dropper-select-${inputCount}" multiple>
                                    <optgroup label="Uncategorized">
                                        <?php 
                                        $keys = array_column($tax_terms, 'parent');

                                        foreach($tax_terms as $tax) {
                                            if($keys) {
                                                $hasKids = $this->array_some($keys, $tax['term_id']);
                                                if($tax['parent'] == 0 && $hasKids == false) {  
                                            ?>
                                                <option value="<?php echo $tax['slug'] ?>" onclick='updateTags("<?php echo $tax["slug"] ?>", "<?php echo htmlspecialchars($tax['name'], ENT_QUOTES, 'UTF-8') ?>", "#uncategorized-tags-${inputCount}", this, ${inputCount}, "<?php echo $tax['slug'] ?>", true)'><?php echo $tax['name'] ?></option>                                           
                                            <?php 
                                                }
                                            }
                                        }?>
                                    </optgroup>
                                </select>

                                <span>click on the option(s) you want to add</span>
                            </td>

                            <td>
                                <label for="per_attendee-${inputCount}">Per Attendee?</label>
                                <input name="form_inputs[${inputCount}][per_attendee]" id="per_attendee-${inputCount}" type="checkbox">
                            </td>

                            <td id="required_${inputCount}">
                                <label for="required-${inputCount}">Required</label>
                                <input name="form_inputs[${inputCount}][required]" id="required-${inputCount}" type="checkbox">
                            </td>

                            <td class="delete">
                                <input class="button" type="button" value="Delete" onclick="removeInput('#input_${inputCount}')">
                            </td>
                        </tr>
                    `)

                    inputCount++;
                })

                $('.sortable').sortable({
                    start: function(event, ui) {
                        // make sure each td width is auto
                        $('.sortable tr td').each(function() {
                            $(this).css('width', 'auto');
                        })
                    },
                    update: function(event, ui) {
                        // make sure each td width is auto
                        $('.sortable tr td').each(function() {
                            $(this).css('width', 'auto');
                        })
                    }
                });

                $('.sortable').removeClass('ui-sortable');


                $('.tags').sortable();
            })
        </script>

        <style>
            .form-table {
                display: block;
                width: 100%;
                padding: 2em 5%;
            }

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

            tr {
                display: grid;
                grid-template-columns: repeat(8, 1fr);
                align-items: space-between;
                padding: 1em;
                background-color: rgba(0,0,0,0.01);
                border-radius: 3px;
                margin-bottom: 1em;
                box-shadow: 0px 0px 4px 1px rgba(0,0,0,0.035);
            }

            tr td.delete {
                grid-column: 8 / span 1;
            }

            @media screen and (max-width: 900px) {
                .form-table tbody {
                    width: 100%;
                    display: block;
                }

                tr {
                    display: flex;
                    flex-direction: column;
                    width: 100%;
                }

                tr td.delete {
                    grid-column: unset;
                }
            }

            td.options-dropper {
                min-width: max-content;
                overflow: hidden;
                border-radius: 5px;
                display: flex;
                flex-direction: column;
                margin: 0;
            }

            td.options-dropper > input {
                margin-bottom: 0.5em;
            }

            td.options-dropper select[multiple] {
                min-height: 100px;
            }

            td.options-dropper option {
                border-radius: 3px;
                margin-bottom: 0.2rem;
            }

            td.options-dropper select:hover {
                color: #2c3338;
            }

            option:active, option:focus {
                background-color: gray;
                color: white;
                cursor: grabbing;
            }

            option:not(.selected) {
                font-weight: 500;
            }

            option:not(.selected):hover {
                background-color: gray;
                color: white;
            }

            option.selected {
                background-color: lightgray;
                color: white;
            }

            option.selected:hover {
                background-color: red;
                opacity: 1;
            }

            option.selected:focus {
                background: red;
            }

            .tags {
                padding: 0.1em;
                display: flex;
                flex-direction: row;
                flex-wrap: wrap;
            }

            .tags span {
                background-color: #007cba;
                color: white;
                padding: 0 0.5em;
                margin: 0.1em;
                border-radius: 3px;
                cursor: pointer;
                transition: background-color 0.05s ease-in-out;
            }

            .tags span:hover {
                background-color: red;
            }

            .tags span:active {
                cursor: grabbing;
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
        foreach ($form_inputs_meta as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    if(is_array($v)) {
                        foreach($v as $i => $j) {
                            if(is_array($j)) {
                                foreach($j as $l => $m) {
                                    $filtered[$key][$k][$i][$l] = sanitize_text_field($m);
                                }
                            } else {
                                $filtered[$key][$k][$i] = sanitize_text_field($j);
                            }
                        }
                    } else {
                        $filtered[$key][$k] = sanitize_text_field($v);
                    }
                }
            } else {
                $filtered[$key] = sanitize_text_field($value);
            }
        }

        // wp_send_json($filtered);

        update_post_meta($post_id, 'form_inputs', $filtered);
    }
}