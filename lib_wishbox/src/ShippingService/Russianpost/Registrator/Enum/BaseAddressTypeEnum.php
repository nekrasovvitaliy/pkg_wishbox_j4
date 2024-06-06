<?php
/**
 * @copyright 2013-2024 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\ShippingService\Russianpost\Registrator\Enum;

/**
 * @since 1.0.0
 */
enum BaseAddressTypeEnum: string
{
	case DEFAULT = 'DEFAULT';
	case PO_BOX = 'PO_BOX';
	case DEMAND = 'DEMAND';
	case UNIT = 'UNIT';

	/**
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function title(): string
	{
		return match($this)
		{
			self::DEFAULT => 'Стандартный (улица, дом, квартира)',
			self::PO_BOX => 'Абонентский ящик',
			self::DEMAND => 'До востребования',
			self::UNIT => 'Для военных частей'
		};
	}
}
