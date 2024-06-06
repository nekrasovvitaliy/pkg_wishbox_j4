<?php
/**
 * @copyright 2013-2024 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\ShippingService\Russianpost\Registrator\Enum;

/**
 * @since 1.0.0
 */
enum BaseMailTypeEnum: string
{
	case POSTAL_PARCEL = 'POSTAL_PARCEL';
	case ONLINE_PARCEL = 'ONLINE_PARCEL';
	case ONLINE_COURIER = 'ONLINE_COURIER';
	case EMS = 'EMS';
	case EMS_OPTIMAL = 'EMS_OPTIMAL';
	case EMS_RT = 'EMS_RT';
	case EMS_TENDER = 'EMS_TENDER';
	case LETTER = 'LETTER';
	case LETTER_CLASS_1 = 'LETTER_CLASS_1';
	case BANDEROL = 'BANDEROL';
	case BUSINESS_COURIER = 'BUSINESS_COURIER';
	case BUSINESS_COURIER_ES = 'BUSINESS_COURIER_ES';
	case PARCEL_CLASS_1 = 'PARCEL_CLASS_1';
	case BANDEROL_CLASS_1 = 'BANDEROL_CLASS_1';
	case VGPO_CLASS_1 = 'VGPO_CLASS_1';
	case SMALL_PACKET = 'SMALL_PACKET';
	case EASY_RETURN = 'EASY_RETURN';
	case VSD = 'VSD';
	case ECOM = 'ECOM';
	case ECOM_MARKETPLACE = 'ECOM_MARKETPLACE';
	case HYPER_CARGO = 'HYPER_CARGO';
	case COMBINED = 'COMBINED';

	/**
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function title(): string
	{
		return match($this)
		{
			self::POSTAL_PARCEL => 'Посылка "нестандартная"',
			self::ONLINE_PARCEL => 'Посылка "онлайн"',
			self::ONLINE_COURIER => 'Курьер "онлайн"',
			self::EMS => 'Отправление EMS',
			self::EMS_OPTIMAL => 'EMS оптимальное',
			self::EMS_RT => 'EMS РТ',
			self::EMS_TENDER => 'EMS тендер',
			self::LETTER => 'Письмо',
			self::LETTER_CLASS_1 => 'Письмо 1-го класса',
			self::BANDEROL => 'Бандероль',
			self::BUSINESS_COURIER => 'Бизнес курьер',
			self::BUSINESS_COURIER_ES => 'Бизнес курьер экпресс',
			self::PARCEL_CLASS_1 => 'Посылка 1-го класса',
			self::BANDEROL_CLASS_1 => 'Бандероль 1-го класса',
			self::VGPO_CLASS_1 => 'ВГПО 1-го класса',
			self::SMALL_PACKET => 'Мелкий пакет',
			self::EASY_RETURN => 'Легкий возврат',
			self::VSD => 'Отправление ВСД',
			self::ECOM => 'ЕКОМ',
			self::ECOM_MARKETPLACE => 'ЕКОМ Маркетплейс',
			self::HYPER_CARGO => 'Доставка день в день',
			self::COMBINED => 'Комбинированное'
		};
	}
}
