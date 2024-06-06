<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$form = $displayData['form'];
$pluginName = $displayData['plugin_name'];
$fieldSets = $form->getFieldsets();

if ($fieldSets)
{
	HTMLHelper::_('bootstrap.framework');
}

$tabName = 'myTab';
?>
<div class="form-horizontal">
	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['recall' => true, 'breakpoint' => 768]); ?>
	<?php
	// Loop again over the fieldsets
	foreach ($fieldSets as $name => $fieldSet)
	{
		// Ensure any fieldsets we don't want to show are skipped (including repeating formfield fieldsets)
		if (isset($fieldSet->repeat) && $fieldSet->repeat === true)
		{
			continue;
		}

		// Determine the label
		if (!empty($fieldSet->label))
		{
			$label = Text::_($fieldSet->label);
		}
		else
		{
			$label = strtoupper('PLG_JSHOPPINGADMIN_' . mb_strtoupper($pluginName) . '_FIELDSET_' . $name . '_LABEL');
            $textLabel = Text::_($label);
            $pattern = '/^\?\?(.*?)\?\?$/';
            $textLabel = preg_replace($pattern, '$1', $textLabel);

            if ($label == $textLabel)
            {
                $label = strtoupper('FILE_JSHOPPING_' . mb_strtoupper($pluginName) . '_FIELDSET_' . $name . '_LABEL');
            }

			$label = Text::_($label);
		}

		$fieldSet = $form->getFieldset($name);

		if (empty($fieldSet))
		{
			continue;
		}

		// Start the tab
		echo HTMLHelper::_('uitab.addTab', $tabName, $name, $label);

		// Include the description when available
		if (isset($fieldSet->description) && trim($fieldSet->description))
		{
			echo '<p class="alert alert-info">' . $this->escape(Text::_($fieldSet->description)) . '</p>';
		}

		$html = [];

		foreach ($fieldSet as $field) {
			$html[] = $field->renderField();
		}

		echo implode('', $html);
		echo HTMLHelper::_('uitab.endTab');
	}
	?>
	<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
</div>
