<?php
/**
 * @copyright 2013-2024 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\ShippingService\Russianpost\Registrator\Entity\Response;

use Wishbox\ShippingService\Russianpost\Registrator\Entity\Response\Source;

/**
 * Class EntityResponse Информация о сущности.
 *
 * @since 1.0.0
 */
class EntityResponse extends Source
{
	/**
	 * Идентификатор запроса в ИС СДЭК.
	 *
	 * @var RequestsResponse[]
	 *
	 * @since 1.0.0
	 */
	protected array $requests;

	/**
	 * Сущность и ее идентификатор.
	 *
	 * @var array
	 *
	 * @since 1.0.0
	 */
	protected array $entity;

	/**
	 * Получить идентификатор запроса в ИС СДЭК.
	 *
	 * @return RequestsResponse[]
	 *
	 * @since 1.0.0
	 */
	public function getRequests(): array
	{
		return $this->requests;
	}

	/**
	 * Получить сущность и ее идентификатор.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function getEntityUuid(): string
	{
		return $this->entity['uuid'];
	}

	/**
	 * Получить массив сущности.
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function getEntity(): array
	{
		return $this->entity;
	}
}
