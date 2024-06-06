<?php
/**
 * @copyright 2013-2024 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\ShippingService\Russianpost\Registrator\Entity\Response;

use AntistressStore\CdekSDK2\Constants;

/**
 * @since 1.0.0
 */
class Source
{
	/**
	 * Формирует объект класса из ответа.
	 * @param   array|null  $properties  Properties
	 * @since 1.0.0
	 */
	public function __construct(?array $properties = null)
	{
		if ($properties != null)
		{
			if (isset($properties['entity']))
			{
				if (count($properties['entity']) > 1)
				{
					$properties = $properties['entity'];
				}
			}

			foreach ($properties as $key => $value)
			{
				if (! property_exists($this, $key))
				{
					continue;
				}

				if (isset(Constants::SDK_CLASSES[$key]))
				{
					$className = '\\AntistressStore\\CdekSDK2\\Entity\\Responses\\'
						. Constants::SDK_CLASSES[$key] . 'Response';
					$this->{$key} = $className::create($value);
				}
				elseif (isset(Constants::SDK_ARRAY_RESPONSE_CLASSES[$key]))
				{
					foreach ($value as $v)
					{
						$className = '\\AntistressStore\\CdekSDK2\\Entity\\Responses\\' .
							Constants::SDK_ARRAY_RESPONSE_CLASSES[$key] . 'Response';
						$this->{$key}[] = $className::create($v);
					}
				}
				else
				{
							$this->{$key} = $value;
				}
			}
		}
	}

	/**
	 * @param   array  $properties  Properties
	 *
	 * @return static
	 *
	 * @since 1.0.0
	 */
	public static function create(array $properties): static
	{
		return new static($properties);
	}
}
