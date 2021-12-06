<?php

defined('_JEXEC') or die;

use Joomla\String\StringHelper;

/**
 * Responsive Widgets class
 *
 * @package     Joomla.Plugin
 * @subpackage  System.wf-responsive-widgets
 */
class PlgSystemWf_Plyr extends JPlugin
{
    public function onAfterDispatch()
    {
        $app = JFactory::getApplication();

        if ($app->getClientId() !== 0) {
            return;
        }

        $document = JFactory::getDocument();
        $docType = $document->getType();

        // only in html pages
        if ($docType != 'html') {
            return;
        }

        // get active menu
        $menus = $app->getMenu();
        $menu = $menus->getActive();

        // get menu items from parameter
        $menuitems_assign = (array) $this->params->get('menu_assign');

        // is there a menu assignment?
        if (!empty($menuitems_assign) && !empty($menuitems_assign[0])) {
            if ($menu && !in_array($menu->id, (array) $menuitems_assign)) {
                return;
            }
        }

        // get excluded menu items from parameter
        $menuitems_exclude = (array) $this->params->get('menu_exclude');

        // is there a menu exclusion?
        if (!empty($menuitems_exclude) && !empty($menuitems_exclude[0])) {
            if ($menu && in_array($menu->id, (array) $menuitems_exclude)) {
                return;
            }
        }

        $elements = $this->params->get('elements', 'video,audio,iframe');

        if (is_string($elements)) {
            $elements = explode(',', $elements);
        }

        $elements = array_replace($elements, array('iframe'), array('.plyr-iframe-embed'));

        $document->addStyleSheet('https://cdn.plyr.io/3.6.9/plyr.css');

        // support legacy browsers
        if ($this->params->get('legacy')) {
            $document->addScript('https://cdn.plyr.io/3.6.9/plyr.polyfilled.js');
        } else {
            $document->addScript('https://cdn.plyr.io/3.6.9/plyr.js');
        }

        $options = array();

        $controls = $this->params->get('controls');

        if (!is_null($controls)) {
            $options['controls'] = is_string($controls) ? implode(',', $controls) : $controls;
        }

        $settings = $this->params->get('settings');

        if (!is_null($settings)) {
            $options['settings'] = is_string($settings) ? implode(',', $settings) : $settings;
        }

        $document->addScriptDeclaration('document.addEventListener("DOMContentLoaded",function handler(){
            Plyr.setup("' . implode(',', $elements) . '",' . json_encode($options) . ');
            this.removeEventListener("DOMContentLoaded",handler);
        });');
    }

    /**
     * Wrap media elements in a container.
     *
     * @param   string   $context  The context of the content being passed to the plugin.
     * @param   mixed    &$row     An object with a "text" property.
     * @param   mixed    &$params  Additional parameters.
     * @param   integer  $page     Optional page number. Unused. Defaults to zero.
     *
     * @return  void
     */
    public function onContentPrepare($context, &$row, &$params, $page = 0)
    {
        // Don't run this plugin when the content is being indexed
        if ($context == 'com_finder.indexer') {
            return true;
        }

        // don't process if there is not text
        if (empty($row->text)) {
            return true;
        }

        $elements = $this->params->get('elements', 'iframe,video,audio');

        if (is_string($elements)) {
            $elements = explode(',', $elements);
        }

        $row->text = preg_replace_callback('#<(' . implode('|', $elements) . ')([^>]+)>([\s\S]*?)<\/\1>#i', array($this, 'wrap'), $row->text);
    }

    private function wrap($matches)
    {
        $tag    = $matches[1];
        $data   = $matches[2];

        // get attributes
        $attribs = JUtility::parseAttributes(trim($data));

        // add poster flag to container
        if ($tag == 'iframe' && preg_match('#(youtu(\.)?be|vimeo\.com)#', $attribs['src'])) {
            return '<div class="plyr-iframe-embed">' . $matches[0] . '</div>';
        }

        return $matches[0];
    }
}
