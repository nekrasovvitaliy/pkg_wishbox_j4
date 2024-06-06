<?php
/**
 * @copyright 2013-2024 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\ShippingService\Russianpost\Registrator\Entity\Request;

use Wishbox\ShippingService\Russianpost\Registrator\Entity\Request\Order\Dimension;
use Wishbox\ShippingService\Russianpost\Registrator\Enum\BaseAddressTypeEnum;
use Wishbox\ShippingService\Russianpost\Registrator\Enum\BaseMailCategoryEnum;
use Wishbox\ShippingService\Russianpost\Registrator\Enum\BaseMailTypeEnum;

/**
 * @since 1.0.0
 */
class Order extends Source
{
	/**
	 * @var BaseAddressTypeEnum $addressTypeTo Тип адреса
	 *
	 * @see https://otpravka.pochta.ru/specification#/enums-base-address-type
	 *
	 * @since 1.0.0
	 */
	protected BaseAddressTypeEnum $addressTypeTo;

	/**
	 * @var Dimension $dimension Размеры (Опционально)
	 *
	 * @since 1.0.0
	 */
	protected Dimension $dimension;

	/**
	 * @var string $givenName Имя получателя
	 *
	 * @since 1.0.0
	 */
	protected string $givenName;

	/**
	 * Целое число (Опционально) Почтовый индекс, для отправлений адресованных в почтомат или пункт выдачи,
	 * должен использоваться объект "ecom-data"
	 *
	 * @var integer
	 *
	 * @since 1.0.0
	 */
	protected int $indexTo;

	/**
	 * Целое число (Опционально) Сумма объявленной ценности (копейки)
	 *
	 * @var integer
	 *
	 * @since 1.0.0
	 */
	protected int $insrValue = 0;

	/**
	 * Категория РПО. См. Категория РПО
	 *
	 * @var BaseMailCategoryEnum
	 *
	 * @see https://otpravka.pochta.ru/specification#/enums-base-mail-category
	 *
	 * @since 1.0.0
	 */
	protected BaseMailCategoryEnum $mailCategory;

	/**
	 * Код страны
	 *
	 * @var integer
	 *
	 * @see https://otpravka.pochta.ru/specification#/dictionary-countries
	 *
	 * @since 1.0.0
	 */
	protected int $mailDirect = 643;

	/**
	 * @var BaseMailTypeEnum $mailType Вид РПО. См. Вид РПО
	 *
	 * @see https://otpravka.pochta.ru/specification#/enums-base-mail-type
	 *
	 * @since 1.0.0
	 */
	protected BaseMailTypeEnum $mailType;

	/**
	 * Целое число
	 * Вес РПО (в граммах)
	 *
	 * @var integer
	 *
	 * @since 1.0.0
	 */
	protected int $mass;

	/**
	 * Отчество получателя
	 *
	 * @var string
	 *
	 * @since 1.0.0
	 */
	protected string $middleName;

	/**
	 * Номер заказа. Внешний идентификатор заказа, который формируется отправителем
	 *
	 * @var string
	 *
	 * @since 1.0.0
	 */
	protected string $orderNum;

	/**
	 * Целое число (Опционально) Сумма наложенного платежа (копейки)
	 *
	 * @var integer
	 *
	 * @since 1.0.0
	 */
	protected int $payment = 0;

	/**
	 * Населенный пункт
	 *
	 * @var string
	 *
	 * @since 1.0.0
	 */
	protected string $placeTo;

	/**
	 * Строка (Опционально) Индекс места приема
	 *
	 * @var string
	 *
	 * @since 1.0.0
	 */
	protected string $postofficeCode;

	/**
	 * Строка Наименование получателя одной строкой (ФИО, наименование организации)
	 *
	 * @var string
	 *
	 * @since 1.0.0
	 */
	protected string $recipientName;

	/**
	 * Область, регион
	 *
	 * @var string
	 *
	 * @since 1.0.0
	 */
	protected string $regionTo;

	/**
	 * Целое число (Опционально) Признак услуги SMS уведомления
	 *
	 * @var integer
	 *
	 * @since 1.0.0
	 */
	protected int $smsNoticeRecipient;

	/**
	 * Часть адреса: Улица
	 *
	 * @var string
	 *
	 * @since 1.0.0
	 */
	protected string $streetTo;

	/**
	 * Фамилия получателя
	 *
	 * @var string
	 *
	 * @since 1.0.0
	 */
	protected string $surname;

	/**
	 * Целое число (Опционально) Телефон получателя (может быть обязательным для некоторых типов отправлений)
	 *
	 * @var integer
	 *
	 * @since 1.0.0
	 */
	protected int $telAddress;

	/**
	 * Логические: true или false (Опционально) Отметка 'Без разряда'
	 *
	 * @var boolean
	 *
	 * @since 1.0.0
	 */
	protected bool $woMailRank = true;


	/**
	 * @param   BaseAddressTypeEnum  $addressTypeTo  Тип адреса
	 *
	 * @see https://otpravka.pochta.ru/specification#/enums-base-address-type
	 *
	 * @return self
	 *
	 * @since 1.0.0
	 */
	public function setAddressTypeTo(BaseAddressTypeEnum $addressTypeTo): self
	{
		$this->addressTypeTo = $addressTypeTo;

		return $this;
	}

	/**
	 * @param   Dimension  $dimension  Dimension
	 *
	 * @return $this
	 *
	 * @since 1.0.0
	 */
	public function setDimension(Dimension $dimension): self
	{
		$this->dimension = $dimension;

		return $this;
	}
	/**
	 * @param   string  $givenName  Имя получателя
	 *
	 * @return self
	 *
	 * @since 1.0.0
	 */
	public function setGivenName(string $givenName): self
	{
		$this->givenName = $givenName;

		return $this;
	}

	/**
	 * Целое число (Опционально) Почтовый индекс, для отправлений адресованных в почтомат или пункт выдачи,
	 * должен использоваться объект "ecom-data"
	 *
	 * @param   integer  $indexTo  Index to
	 *
	 * @return self
	 *
	 * @since 1.0.0
	 */
	public function setIndexTo(int $indexTo): self
	{
		$this->indexTo = $indexTo;

		return $this;
	}

	/**
	 * @param   integer  $insrValue  Целое число (Опционально) Сумма объявленной ценности (копейки)
	 *
	 * @return self
	 *
	 * @since 1.0.0
	 */
	public function setInsrValue(int $insrValue): self
	{
		$this->insrValue = $insrValue;

		return $this;
	}

	/**
	 * Категория РПО. См. Категория РПО
	 *
	 * @param   BaseMailCategoryEnum  $mailCategory  Mail category
	 *
	 * @see https://otpravka.pochta.ru/specification#/enums-base-mail-category
	 *
	 * @return self
	 *
	 * @since 1.0.0
	 */
	public function setMailCategory(BaseMailCategoryEnum $mailCategory): self
	{
		$this->mailCategory = $mailCategory;

		return $this;
	}

	/**
	 * @param   integer  $mailDirect  Код страны
	 *
	 * @see https://otpravka.pochta.ru/specification#/dictionary-countries
	 *
	 * @return self
	 *
	 * @since 1.0.0
	 */
	public function setMailDirect(int $mailDirect): self
	{
		$this->mailDirect = $mailDirect;

		return $this;
	}

	/**
	 * @param   BaseMailTypeEnum  $mailType  Вид РПО
	 *
	 * @see https://otpravka.pochta.ru/specification#/enums-base-mail-type
	 *
	 * @return self
	 *
	 * @since 1.0.0
	 */
	public function setMailType(BaseMailTypeEnum $mailType): self
	{
		$this->mailType = $mailType;

		return $this;
	}

	/**
	 * @param   integer  $mass  Вес РПО (в граммах)
	 *
	 * @return self
	 *
	 * @since 1.0.0
	 */
	public function setMass(int $mass): self
	{
		$this->mass = $mass;

		return $this;
	}

	/**
	 * @param   string  $middleName  Отчество получателя
	 *
	 * @return self
	 *
	 * @since 1.0.0
	 */
	public function setMiddleName(string $middleName): self
	{
		$this->middleName = $middleName;

		return $this;
	}

	/**
	 * @param   string  $orderNum  Номер заказа. Внешний идентификатор заказа, который формируется отправителем
	 *
	 * @return self
	 *
	 * @since 1.0.0
	 */
	public function setOrderNum(string $orderNum): self
	{
		$this->orderNum = $orderNum;

		return $this;
	}

	/**
	 * @param   integer  $payment  Целое число (Опционально) Сумма наложенного платежа (копейки)
	 *
	 * @return self
	 *
	 * @since 1.0.0
	 */
	public function setPayment(int $payment): self
	{
		$this->payment = $payment;

		return $this;
	}

	/**
	 * @param   string  $placeTo  Населенный пункт
	 *
	 * @return self
	 *
	 * @since 1.0.0
	 */
	public function setPlaceTo(string $placeTo): self
	{
		$this->placeTo = $placeTo;

		return $this;
	}

	/**
	 * @param   string  $postofficeCode  Строка (Опционально) Индекс места приема
	 *
	 * @return self
	 *
	 * @since 1.0.0
	 */
	public function setPostofficeCode(string $postofficeCode): self
	{
		$this->postofficeCode = $postofficeCode;

		return $this;
	}

	/**
	 * @param   string  $recipientName  Наименование получателя одной строкой (ФИО, наименование организации)
	 *
	 * @return self
	 *
	 * @since 1.0.0
	 */
	public function setRecipientName(string $recipientName): self
	{
		$this->recipientName = $recipientName;

		return $this;
	}

	/**
	 * @param   string  $regionTo  Область, регион
	 *
	 * @return self
	 *
	 * @since 1.0.0
	 */
	public function setRegionTo(string $regionTo): self
	{
		$this->regionTo = $regionTo;

		return $this;
	}

	/**
	 * @param   integer  $smsNoticeRecipient  (Опционально) Признак услуги SMS уведомления
	 *
	 * @return self
	 *
	 * @since 1.0.0
	 */
	public function setSmsNoticeRecipient(int $smsNoticeRecipient): self
	{
		$this->smsNoticeRecipient = $smsNoticeRecipient;

		return $this;
	}

	/**
	 * @param   string  $streetTo  Часть адреса: Улица
	 *
	 * @return self
	 *
	 * @since 1.0.0
	 */
	public function setStreetTo(string $streetTo): self
	{
		$this->streetTo = $streetTo;

		return $this;
	}

	/**
	 * @param   string  $surname  Фамилия получателя
	 *
	 * @return self
	 *
	 * @since 1.0.0
	 */
	public function setSurname(string $surname): self
	{
		$this->surname = $surname;

		return $this;
	}

	/**
	 * @param   integer  $telAddress Целое число (Опционально) Телефон получателя (может быть обязательным для некоторых типов отправлений)
	 *
	 * @return self
	 *
	 * @since 1.0.0
	 */
	public function setTelAddress(int $telAddress): self
	{
		$this->telAddress = $telAddress;

		return $this;
	}

	/**
	 * @param   boolean  $woMailRank  Логические: true или false (Опционально) Отметка 'Без разряда'
	 *
	 * @return self
	 *
	 * @since 1.0.0
	 */
	public function setWoMailRank(bool $woMailRank): self
	{
		$this->woMailRank = $woMailRank;

		return $this;
	}
}
