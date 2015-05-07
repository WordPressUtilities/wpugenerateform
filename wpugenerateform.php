<?php

/*
Plugin Name: WPU Generate HTML Form
Plugin URI: https://github.com/WordPressUtilities/wpuvalidateform
Description: Generate HTML Form from a model
Version: 0.5.1
Author: Darklg
Author URI: http://darklg.me/
License: MIT License
License URI: http://opensource.org/licenses/MIT
*/

class WPUGenerateHTMLForm {
    private $form_settings = array(
        'submit-button-class' => 'cssc-button--default',
        'submit-button-text' => 'Submit',
        'submit-box-class' => 'box submit-box',
        'submit-display' => true,
        'form-class' => 'cssc-form cssc-form--default',
        'form-action' => '',
        'form-id' => '',
    );

    function __construct($fields = array() , $settings = array()) {
        $this->add_fields($fields);
        $this->set_settings($settings);
    }

    public function add_fields($fields = array()) {
        if (is_array($fields)) {
            $this->fields = $fields;
        }
    }

    public function set_settings($settings = array()) {
        if (!is_array($settings)) {
            $settings = array();
        }
        $this->form_settings = array_merge($this->form_settings, $settings);
    }

    public function display_form() {
        $is_multipart = false;
        foreach ($this->fields as $id => $field) {
            if (isset($field['type']) && $field['type'] == 'file') {
                $is_multipart = true;
            }
        }

        $html_return = '<form id="' . $this->form_settings['form-id'] . '" action="' . $this->form_settings['form-action'] . '" method="post" ' . ($is_multipart ? ' enctype="multipart/form-data"' : '') . '>';
        $html_return.= '<ul class="' . $this->form_settings['form-class'] . '">';
        foreach ($this->fields as $id => $field) {
            $html_return.= $this->generate_field($id, $field);
        }
        if ($this->form_settings['submit-display']) {
            $html_return.= '<li class="' . $this->form_settings['submit-box-class'] . '">';
            $html_return.= '<button class="cssc-button ' . $this->form_settings['submit-button-class'] . '" type="submit">' . $this->form_settings['submit-button-text'] . '</button>';
            $html_return.= '</li>';
        }
        $html_return.= '</ul>';
        $html_return.= '</form>';
        return $html_return;
    }

    public function generate_field($id, $field) {
        $html = '';

        // Set label
        $label = $id;
        if (isset($field['label'])) {
            $label = $field['label'];
        }
        $item_id = 'item-' . $id;
        $html_label = '<label for="' . $item_id . '">' . $label . '</label>';
        if (isset($field['hide_label']) && $field['hide_label']) {
            $html_label = '';
        }
        $html_placeholder = $label;
        if (isset($field['placeholder']) && !empty($field['placeholder'])) {
            $html_placeholder = $field['placeholder'];
        }
        $html_attr = 'name="' . $id . '" ';

        // Id / name
        $id_name = 'id="' . $item_id . '" ';
        if (isset($field['required']) && $field['required']) {
            $html_attr.= 'required="required" ';
        }

        // Set value
        $value = '';
        if (isset($field['value'])) {
            $value = stripslashes($field['value']);
        }

        // Field type
        $box_type = '';
        if (!isset($field['type'])) {
            $field['type'] = 'text';
        }

        // Field datas
        if (!isset($field['datas']) || !is_array($field['datas'])) {
            $field['datas'] = array(
                'No',
                'Yes'
            );
        }

        // Additional class
        $additional_class = isset($field['additional_class']) ? $field['additional_class'] : '';

        // Open | Close
        $display_open = !(isset($field['display_open']) && $field['display_open'] == false);
        $display_close = !(isset($field['display_close']) && $field['display_close'] == false);

        // Set Field HTML
        $html_field = '';
        switch ($field['type']) {
            case 'checkbox':
                $box_type = 'checked-box';
                $current = in_array($value, array(
                    '1',
                    'checked'
                )) ? 'checked="checked" ' : '';
                $html_field.= '<input title="' . esc_attr($label) . '" ' . $html_attr . $id_name . $current . ' type="checkbox" value="" /> ' . $html_label;
            break;
            case 'radio':
                $box_type = 'checked-box';
                $html_field.= '<span class="fake-label">' . $label . '</span>';
                foreach ($field['datas'] as $key => $var) {
                    $current = $key == $value ? 'checked="checked" ' : '';
                    $html_field.= '<input title="' . esc_attr($label . ' ' . $var) . '" type="radio" id="' . $item_id . '__' . $key . '" ' . $html_attr . ' value="' . $key . '" /> ';
                    $html_field.= '<label for="' . $item_id . '__' . $key . '">' . $var . '</label> ';
                }
            break;
            case 'select':
                $html_field.= $html_label . '<select title="' . esc_attr($label) . '" ' . $html_attr . $id_name . '>';
                $html_field.= '<option value="" disabled selected style="display:none;">' . $html_placeholder . '</option>';
                foreach ($field['datas'] as $key => $var) {
                    $current = $key == $value ? 'selected="selected" ' : '';
                    $html_field.= '<option value="' . $key . '" ' . $current . '>' . $var . '</option>';
                }
                $html_field.= '</select>';
            break;
            case 'textarea':
                $html_field.= $html_label . '<textarea title="' . esc_attr($label) . '" placeholder="' . esc_attr($html_placeholder) . '" ' . $html_attr . $id_name . ' rows="3" cols="40">' . esc_attr($value) . '</textarea>';
            break;
            case 'html':
                $html_field.= '<input ' . $html_attr . $id_name . ' type="hidden" value="' . esc_attr($value) . '" />';
                if (isset($field['content'])) {
                    $html_field.= $field['content'];
                }
            break;
            case 'file':
                $html_field.= $html_label . '<input title="' . esc_attr($label) . '" ' . $html_attr . $id_name . ' type="file"/>';
            break;
            case 'text':
            case 'password':
            case 'email':
            case 'url':
                $html_field.= $html_label . '<input title="' . esc_attr($label) . '" placeholder="' . esc_attr($html_placeholder) . '" ' . $html_attr . $id_name . ' type="' . $field['type'] . '" value="' . esc_attr($value) . '" />';
            break;
        }

        if ($display_open) {
            $html.= '<li class="' . $this->form_settings['box-class'] . ' ' . $additional_class . ' box--' . $id . ' ' . $box_type . '">';
        }
        $html.= $html_field;
        if ($display_close) {
            $html.= '</li>';
        }
        return $html;
    }
}
