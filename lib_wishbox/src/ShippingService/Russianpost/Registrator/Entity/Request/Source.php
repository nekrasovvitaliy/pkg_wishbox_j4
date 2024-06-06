<?php
/**
 * @copyright 2013-2024 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\ShippingService\Russianpost\Registrator\Entity\Request;

use JsonSerializable;
use ReturnTypeWillChange;
use UnitEnum;
use function array_filter;
use function get_object_vars;
use function is_null;
use function is_object;

/**
 * @since 1.0.0
 */
class Source implements JsonSerializable, RequestInterface
{
	/**
	 * Формирует массив параметров для запроса.
	 * Удаляет пустые значения.
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function prepareRequest(): array
	{
		$entityVars = $this->pattern ?? get_object_vars($this);
		$dynamic = [];

		foreach ($entityVars as $key => $val)
		{
			if (is_null($this->{$key}))
			{
				continue;
			}

			if (!is_object($this->{$key}) && !is_array($this->{$key}))
			{
				$dynamic[self::camelToKebab($key)] = $this->{$key};
			}
			elseif ($this->{$key} instanceof UnitEnum)
			{
				$dynamic[self::camelToKebab($key)] = $this->{$key}->name;
			}
			elseif (is_array($this->{$key}))
			{
				foreach ($this->{$ke} as $v)
				{
					$arrayFromObject = get_object_vars($v);

					$arrayFromObjectNullFiltered = array_filter($arrayFromObject);

					if (!empty($arrayFromObjectNullFiltered))
					{
						$dynamic[self::camelToKebab($key)][] = $arrayFromObjectNullFiltered;
					}
				}
			}
			elseif ($this->{$key} instanceof RequestInterface)
			{
				$dynamic[self::camelToKebab($key)] = $this->{$key}->prepareRequest();
			}
			else
			{
				$a = get_object_vars($this->{$key});
				$dynamic[$key] = array_filter(
					$a,
					function ($value)
					{
						return $value !== null;
					}
				);
			}
		}

		return $dynamic;
	}

	/**
	 * @return array
	 *
	 * @since 1.0.0
	 */
	#[ReturnTypeWillChange]
	public function jsonSerialize(): array
	{
		return get_object_vars($this);
	}


	/**
	 * @param   string  $camel  Camel string
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	private static function camelToKebab(string $camel): string
	{
		// Переводим первую букву в нижний регистр
		$input = lcfirst($camel);

		// Ищем заглавные буквы и добавляем перед ними символ подчеркивания, затем переводим в нижний регистр
		$callback = function ($matches) {
			return '-' . strtolower($matches[0]);
		};

		return preg_replace_callback('/[A-Z]/', $callback, $input);
	}
}
