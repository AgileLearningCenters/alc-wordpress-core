<?php

namespace TeamBooking\Admin\Framework;

class PanelSettingEmailEditor extends PanelSetting implements Element
{
    protected $placeholders = array();
    protected $subject = '';
    protected $body = '';
    protected $show_send = FALSE;
    protected $send_state = FALSE;
    protected $send_fieldname = '';

    public function setSendState($bool)
    {
        if ($bool) {
            $this->send_state = TRUE;
        } else {
            $this->send_state = FALSE;
        }
    }

    public function setShowSend($bool)
    {
        if ($bool) {
            $this->show_send = TRUE;
        } else {
            $this->show_send = FALSE;
        }
    }

    public function setSendFieldname($fieldname)
    {
        $this->send_fieldname = $fieldname;
    }

    public function setSubject($string)
    {
        $this->subject = esc_html($string);
    }

    public function setBody($string)
    {
        $this->body = $string;
    }

    public function addPlaceholder($text, $is_enclosure = FALSE)
    {
        if ($is_enclosure) {
            $this->placeholders[] = array(
                'label' => '[' . $text . ']',
                'open'  => '[' . $text . ']',
                'close' => '[/' . $text . ']',
                'value' => esc_html__('Insert link text here', 'team-booking')
            );
        } else {
            $this->placeholders[] = array(
                'label' => '[' . $text . ']',
                'value' => '[' . $text . ']'
            );
        }
    }

    public function render()
    {
        echo '<a class="ui mini circular label tb-toggle-email-editor" style="float: right" tabindex="0">';
        echo '<span class="dashicons dashicons-welcome-write-blog"></span></a>';
        echo '<h4>' . $this->title . '</h4>';
        if ($this->show_send) {
            echo '<p style="color:initial"><label for="' . $this->send_fieldname . '">';
            echo '<input name="' . $this->send_fieldname . '" type="checkbox" value="1"' . (($this->send_state) ? ' checked="checked">' : '>');
            esc_html_e('Send', 'team-booking');
            echo '</label></p>';
        }
        if (!empty($this->description)) {
            echo '<p>' . $this->description . '</p>';
        }
        echo '<div class="tb-email-editor">';
        echo '<p style="color:initial">' . esc_html__('Subject', 'team-booking');
        echo ' <input type="text" value="' . $this->subject . '" class="regular-text" name="' . $this->fieldname . '[subject]">';
        echo '</p>';
        if (!empty($this->placeholders)) echo '<p>' . esc_html__('Available form hooks (click to insert at cursor point):', 'team-booking') . '</p>';
        echo '<p class="description">';
        foreach ($this->placeholders as $placeholder) {
            echo '<a class="ui mini tb-hook-placeholder label" tabindex="0"'
                . ' data-value="' . $placeholder['value'] . '"'
                . (isset($placeholder['open']) ? ' data-open="' . $placeholder['open'] . '"' : '')
                . (isset($placeholder['close']) ? ' data-close="' . $placeholder['close'] . '"' : '')
                . '>' . $placeholder['label'] . '</a>';
        }
        echo '</p>';
        wp_editor(
            $this->body,
            str_replace(array('[', ']'), '_', $this->fieldname . '_body'),
            array(
                'media_buttons' => FALSE,
                'textarea_name' => $this->fieldname . '[body]',
                'tinymce'       => FALSE,
            )
        );
        echo '</div>';
    }
}