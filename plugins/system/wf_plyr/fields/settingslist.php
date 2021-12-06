<?php

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldSettingsList extends JFormFieldList
{
    /**
     * The form field type.
     *
     * @var string
     *
     * @since  11.1
     */
    protected $type = 'SettingsList';

    protected function getOptions()
    {
        $options = parent::getOptions();

        $this->value = is_array($this->value) ? $this->value : explode(',', $this->value);

        $custom = array();

        foreach ($this->value as $value) {
            $tmp = array(
                'value' => $value,
                'text'  => $value,
                'selected' => true,
            );

            $found = false;

            foreach($options as $option) {
                if ($option->value === $value) {
                    $found = true;
                }
            }

            if (!$found) {
                $custom[] = (object) $tmp;
            }
        }

        // Merge any additional options in the XML definition.
		$options = array_merge($options, $custom);

        return $options;
    }
}
