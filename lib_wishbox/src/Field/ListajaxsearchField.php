<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseDriver;
use SimpleXMLElement;
use function defined;
use function in_array;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
class ListajaxsearchField extends ListField
{
	/**
	 * A flexible tag list that respects access controls
	 *
	 * @var    string
	 *
	 * @since  1.0.0
	 */
	public $type = 'Listajaxsearch';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 *
	 * @since  1.0.0
	 */
	protected $layout = 'joomla.form.field.listajaxsearch';

	/**
	 * Min length of terms
	 *
	 * @var    integer
	 *
	 * @since  1.0.0
	 */
	protected int $minTermLength;

	/**
	 * Url
	 *
	 * @var    string
	 *
	 * @since  1.0.0
	 */
	protected string $url;

	/**
	 * Query
	 *
	 * @var    string
	 *
	 * @since  1.0.0
	 */
	protected string $query;

	/**
	 * Method to attach a Form object to the field.
	 *
	 * @param   SimpleXMLElement   $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example, if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     FormField::setup()
	 *
	 * @var   SimpleXMLElement $element Element
	 *
	 * @since 1.0.0
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null): bool
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->minTermLength = (int) $this->element['minTermLength'];
			$this->url = (string) $this->element['url'];
			$this->query = (string) $this->element['query'];
		}

		return $return;
	}


	/**
	 * Method to get the list of options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.0.0
	 */
	protected function getOptions(): array
	{
		$options = parent::getOptions();

		if ($this->value && !empty($this->query))
		{
			$db = Factory::getContainer()->get(DatabaseDriver::class);
			$db->setQuery($this->query . $this->value);
			$text = $db->loadResult();

			if ($text)
			{
				$options[] = HTMLHelper::_(
						'select.option',
						$this->value,
						$text
					);
			}
		}

		// Merge any additional options in the XML definition.
		return $options;
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	protected function getLayoutData()
	{
		$data = parent::getLayoutData();

		$data['value'] = $this->value;

		$data['remoteSearch']  	= $this->isRemoteSearch();
		$data['options']      	= $this->getOptions();
		$data['isNested']      	= false;
		$data['allowCustom']  	= $this->allowCustom();
		$data['minTermLength']	= $this->minTermLength;
		$data['url']			= Uri::root(true) . '/' . $this->url;

		return $data;
	}

	/**
	 * Determines if the field allows or denies custom values
	 *
	 * @return  boolean
	 *
	 * @since 1.0.0
	 */
	public function allowCustom(): bool
	{
		if ($this->element['custom'] && in_array((string) $this->element['custom'], ['0', 'false', 'deny']))
		{
			return false;
		}

		return true;
	}

	/**
	 * Check whether you need to enable AJAX search
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public function isRemoteSearch(): bool
	{
		if ($this->element['remote-search'])
		{
			return !in_array((string) $this->element['remote-search'], ['0', 'false', '']);
		}

		return false;
	}
}
