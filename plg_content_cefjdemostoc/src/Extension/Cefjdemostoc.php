<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.cefjdemostoc
 *
 * @copyright   (C) 2024 Clifford E Ford.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Cefjdemos\Plugin\Content\Cefjdemostoc\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Plugin to create a Table of Contents in articles
 *
 */
final class Cefjdemostoc extends CMSPlugin
{
    /**
     * Plugin that creates a table of contents in an article
     *
     * @param   string   $context   The context of the content being passed to the plugin.
     * @param   object   &$article  The article object.  Note $article->text is also available
     * @param   mixed    &$params   The article params
     * @param   integer  $page      The 'page' number
     *
     * @return  void
     *
     * @since   1.6
     */
    public function onContentPrepare($context, &$article, &$params, $page = 0)
    {
        // Featured article with readmore separator needs {cefjdemostoc} removed.
        if ($context === 'com_content.featured') {
            $article->text = str_ireplace('{cefjdemostoc}', '', $article->text);
            return;
        }

        // The context could be something other than com_content
        // such as a module - in which case do nothing and return.
        if ($context !== 'com_content.article') {
            return;
        }

        // Return if there is no {cefjdemostoc} in the article content.
        if (stripos($article->text, '{cefjdemostoc}') === false) {
            return;
        }

        // Load plugin language file only when needed.
        $this->loadLanguage();

        // Get the card class parameter
        $toc_class = $this->params->get('toc_class');

        // Get the card heading class
        $toc_head_class = $this->params->get('toc_head_class');

        // Get the list indent value (1, 2, or 3)
        $list_indent = $this->params->get('list_indent', 1);

        // Get a list of headings in the aticle.
        $pattern = '/<h([1-6])[^>]*>(.*?)<\/h\1>/i';
        preg_match_all($pattern, $article->text, $headings2, PREG_SET_ORDER);

        // The text of the article needs to have anchors (id values) added to headings
        foreach ($headings2 as $i => $heading) {
            $old = '<h' . $heading[1] . '>' . $heading[2] . '</h' . $heading[1] . '>';
            $new = '<h' . $heading[1] . ' id="cefjemostoc' . $i . '">' . $heading[2] . '</h' . $heading[1] . '>';
            $article->text = str_replace($old, $new, $article->text);
        }

        // Use a template for the html output.
        $path = PluginHelper::getLayoutPath('content', 'cefjdemostoc', 'default');
        ob_start();
        include $path;
        $toc2 = ob_get_clean();

        // Substitute the placeholder {cefjdemostoc} with the generated ToC.
        $article->text = str_ireplace("{cefjdemostoc}", $toc2, $article->text);

        return;
    }
}
