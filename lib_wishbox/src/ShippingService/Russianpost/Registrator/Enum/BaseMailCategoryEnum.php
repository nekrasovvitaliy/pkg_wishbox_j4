<?php
/**
 * @copyright 2013-2024 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\ShippingService\Russianpost\Registrator\Enum;

/**
 * @since 1.0.0
 * @see https://otpravka.pochta.ru/specification#/enums-base-mail-category
 */
enum BaseMailCategoryEnum: string
{
	case SIMPLE = 'SIMPLE';
	case ORDERED = 'ORDERED';
	case ORDINARY = 'ORDINARY';
	case WITH_DECLARED_VALUE = 'WITH_DECLARED_VALUE';
	case WITH_DECLARED_VALUE_AND_CASH_ON_DELIVERY = 'WITH_DECLARED_VALUE_AND_CASH_ON_DELIVERY';
	case WITH_DECLARED_VALUE_AND_COMPULSORY_PAYMENT = 'WITH_DECLARED_VALUE_AND_COMPULSORY_PAYMENT';
	case WITH_COMPULSORY_PAYMENT = 'WITH_COMPULSORY_PAYMENT';
	case COMBINED_ORDINARY = 'COMBINED_ORDINARY';
	case COMBINED_WITH_DECLARED_VALUE = 'COMBINED_WITH_DECLARED_VALUE';
	case COMBINED_WITH_DECLARED_VALUE_AND_CASH_ON_DELIVERY = 'COMBINED_WITH_DECLARED_VALUE_AND_CASH_ON_DELIVERY';

	/**
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function title(): string
	{
		return match($this)
		{
			self::SIMPLE => 'Простое',
			self::ORDERED => 'Заказное',
			self::ORDINARY => 'Обыкновенное',
			self::WITH_DECLARED_VALUE => 'С объявленной ценностью',
			self::WITH_DECLARED_VALUE_AND_CASH_ON_DELIVERY => 'С объявленной ценностью и наложенным платежом',
			self::WITH_DECLARED_VALUE_AND_COMPULSORY_PAYMENT => 'С объявленной ценностью и обязательным платежом',
			self::WITH_COMPULSORY_PAYMENT => 'С обязательным платежом',
			self::COMBINED_ORDINARY => 'Комбинированное обыкновенное',
			self::COMBINED_WITH_DECLARED_VALUE => 'Комбинированное с объявленной ценностью',
			self::COMBINED_WITH_DECLARED_VALUE_AND_CASH_ON_DELIVERY => 'Комбинированное с объявленной ценностью и наложенным платежом'
		};
	}
}
