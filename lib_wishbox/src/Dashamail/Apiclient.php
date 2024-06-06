<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license   GNU General Public License version 2 or later
 */

namespace Wishbox\Dashamail;

use Exception;

/**
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
class Apiclient
{
	/**
	 * @var string $apiKey API key
	 *
	 * @since 1.0.0
	 */
	private string $apiKey;

	/**
	 * @var string $apiUrl Dashamail api url
	 *
	 * @since 1.0.0
	 */
	private string $apiUrl = 'https://api.dashamail.com/';

	/**
	 * @var string $checkUrl Check email
	 *
	 * @since 1.0.0
	 */
	private string $checkUrl = 'https://labs.dashamail.com/email.fix/check.php';

	/**
	 * Constructor
	 *
	 * @param   string  $apiKey  API key
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function __construct(string $apiKey)
	{
		if (empty($apiKey))
		{
			throw new Exception($this->getError('100'), 500);
		}

		$this->apiKey = $apiKey;
	}

	/**
	 * @param   string $key Key
	 *
	 * @return string
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	private function getError(string $key): string
	{
		$errors = [
			'1' => 'Неверный логин и(или) пароль',
			'2' => 'Ошибка при добавлении в базу',
			'3' => 'Заданы не все необходимые параметры',
			'4' => 'Нет данных при выводе',
			'5' => 'У пользователя нет адресной базы с таким id',
			'6' => 'Некорректный email-адрес',
			'7' => 'Такой пользователь уже есть в этой адресной базе',
			'8' => 'Лимит по количеству активных подписчиков на тарифном плане клиента',
			'9' => 'Нет такого подписчика у клиента',
			'10' => 'Пользователь уже отписан',
			'11' => 'Нет данных для обновления подписчика',
			'12' => 'Не заданы элементы списка',
			'13' => 'Не задано время рассылки',
			'14' => 'Не задан заголовок письма',
			'15' => 'Не задано поле От Кого?',
			'16' => 'Не задан обратный адрес',
			'17' => 'Не задана ни html ни plain_text версия письма',
			'18' => 'Нет ссылки отписаться в тексте рассылки. Пример ссылки: отписаться',
			'19' => 'Нет ссылки отписаться в тексте рассылки',
			'20' => 'Задан недопустимый статус рассылки',
			'21' => 'Рассылка уже отправляется',
			'22' => 'У вас нет кампании с таким campaign_id',
			'23' => 'Нет такого поля для сортировки',
			'24' => 'Заданы недопустимые события для авторассылки',
			'25' => 'Загружаемый файл уже существует',
			'26' => 'Загружаемый файл больше 5 Мб',
			'27' => 'Файл не найден',
			'28' => 'Указанный шаблон не существует',
			'100' => 'Неверные данные для подключения API',
			'101' => 'Несуществующий метод API или указан некорректный метод API',
		];

		if (!isset($errors[$key]))
		{
			throw new Exception('', 500);
		}

		return $errors[$key];
	}

	/**
	 * Get data by method
	 *
	 * @param   string $method Method
	 * @param   array  $params Params
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getData(string $method, array $params = []): mixed
	{
		$user = ['api_key' => $this->apiKey];
		$params = array_merge($user, $params);
		$params = http_build_query($params);

		$url = $this->apiUrl . '?method=' . $method . '&' . $params;

		$options = [
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => 0,
		];

		return $this->extracted($options);
	}

	/**
	 * @param   string $method Method
	 * @param   array  $params Params
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	private function sendData(string $method, array $params = []): mixed
	{
		$user = ['api_key' => $this->apiKey];
		$params = array_merge($user, $params);

		$options = [
			CURLOPT_URL => $this->apiUrl . '?method=' . $method,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $params,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => 0,
		];

		return $this->extracted($options);
	}

	/**
	 * Check the email address
	 *
	 * @param   string $email Email
	 *
	 * @return string|false
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function checkEmail(string $email): string|false
	{
		$checking = $this->checkUrl . '?email=' . $email . '&format=json';
		$final = file_get_contents($checking, true);
		$final = json_decode($final);

		if (!$final)
		{
			throw new Exception('При проверке email получены неверные данные');
		}

		$err = $final->response->err_code;

		if ($err == 0 || $err == 1)
		{
			return $final->response->text;
		}
		else
		{
			return false;
		}
	}


	/*****************************************************************
	 * *************** Работа с Адресными Базами ***********************
	 ******************************************************************/
	/**
	 * Lists.get - Получаем список баз пользователя
	 *
	 * @param   string  $listId  List id
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function listsGet(string $listId = ''): mixed
	{
		if (!empty($listId))
		{
			$params = ['list_id' => $listId];
		}
		else
		{
			$params = [];
		}

		return $this->getData('lists.get', $params);
	}

	/**
	 * @param   string $name   Name
	 * @param   array  $params Params
	 *                         - abuse_email
	 *                         - abuse_name
	 *                         - company...
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @see          http://dashamail.ru/api_details.php?method=lists.add
	 *
	 * @noinspection PhpUnused
	 *
	 * @since        1.0.0
	 */
	public function listsAdd(string $name, array $params = []): mixed
	{
		if (empty($name))
		{
			return $this->getError('3');
		}

		$required = ['name' => $name];

		if (isset($params['abuse_email']))
		{
			$email = $this->checkEmail($params['abuse_email']);

			if ($email !== false)
			{
				$params['abuse_email'] = $email;
			}
			else
			{
				return $this->getError('6');
			}
		}

		$params = array_merge($required, $params);

		return $this->sendData('lists.add', $params);
	}

	/**
	 * Lists.update - Обновляем контактную информацию адресной базы
	 *
	 * @param   int   $listId List id
	 *
	 * @param   array $params Params
	 *                        optional: name, abuse_email, abuse_name, company...
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @see          http://dashamail.ru/api_details.php?method=lists.update
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function listsUpdate(int $listId, array $params = []): mixed
	{
		if ($listId == 0)
		{
			return $this->getError('3');
		}

		$listId = ['list_id' => $listId];

		if (isset($params['abuse_email']))
		{
			$email = $this->checkEmail($params['abuse_email']);

			if ($email !== false)
			{
				$params['abuse_email'] = $email;
			}
			else
			{
				return $this->getError('6');
			}
		}

		$params = array_merge($listId, $params);

		return $this->sendData('lists.update', $params);
	}

	/**
	 * Lists.delete - Удаляем адресную базу и всех активных подписчиков в ней.
	 *
	 * @param   integer $listId List id
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function listsDelete(int $listId): mixed
	{
		if ($listId == 0)
		{
			return $this->getError('3');
		}

		$params = ['list_id' => $listId];

		return $this->sendData('lists.delete', $params);
	}

	/**
	 * Lists.get_members - Получаем подписчиков в адресной базе с возможностью фильтра и регулировки выдачи.
	 *
	 * @param   int   $listId List id
	 * @param   array $params Params
	 *                        optional: state, start, limit...
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @since
	 *
	 * @see          : http://dashamail.ru/api_details.php?method=lists.get_members
	 *
	 * @noinspection PhpUnused
	 */
	public function listsGetMembers(int $listId, array $params = []): mixed
	{
		if ($listId == 0)
		{
			return $this->getError('3');
		}

		$required = ['list_id' => $listId];

		$params = array_merge($required, $params);

		return $this->getData('lists.get_members', $params);
	}

	/**
	 * Lists.upload - Импорт подписчиков из файла
	 *
	 * @param   integer $listId List id
	 * @param   string  $file   File
	 * @param   string  $email  Email
	 * @param   array   $params Array
	 *                          optional: merge_1, merge_2, type, update...
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @see          http://dashamail.ru/api_details.php?method=lists.upload
	 *
	 * @noinspection PhpUnused
	 */
	public function listsUpload(int $listId, string $file, string $email, array $params = []): mixed
	{
		if ($listId == 0 || empty($email) || empty($file))
		{
			return $this->getError('3');
		}

		$email = $this->checkEmail($email);

		if (!$email)
		{
			return $this->getError('6');
		}

		$required = ['list_id' => $listId, 'file' => $file, 'email' => $email];
		$params = array_merge($required, $params);

		return $this->sendData('lists.upload', $params);
	}

	/**
	 * Lists.add_member - Добавляем подписчика в базу
	 *
	 * @param   integer $listId  List id
	 * @param   string  $email   Email
	 * @param   array   $params  Array
	 *                           optional: merge_1, merge_2..., update...
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @see          : http://dashamail.ru/api_details.php?method=lists.add_member
	 *
	 * @noinspection PhpUnused
	 */
	public function listsAddMember(int $listId, string $email, array $params = []): mixed
	{
		if ($listId == 0 || empty($email))
		{
			return $this->getError('3');
		}

		$email = $this->checkEmail($email);

		if ($email !== false)
		{
			$required = ['list_id' => $listId, 'email' => $email];
		}
		else
		{
			return $this->getError('6');
		}

		$params = array_merge($required, $params);

		return $this->sendData('lists.add_member', $params);
	}

	/**
	 * @param   int    $listId List id
	 * @param   string $batch  Batch
	 *
	 * @return mixed|string
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function listsAddMemberBatch(int $listId, string $batch): mixed
	{
		if ($listId == 0)
		{
			return $this->getError('3');
		}

		if (empty($batch))
		{
			return $this->getError('3');
		}

		$params = ['list_id' => $listId, 'batch' => $batch];

		return $this->sendData('lists.add_member_batch', $params);
	}


	/**
	 * Lists.update_member - Редактируем подписчика в базе
	 *
	 * @param   int   $memberId Member id
	 * @param   array $params   Params
	 *                          optional: merge_1, merge_2...
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @see          http://dashamail.ru/api_details.php?method=lists.update_member
	 *
	 * @noinspection PhpUnused
	 */
	public function listsUpdateMember(int $memberId, array $params = []): mixed
	{
		if ($memberId == 0)
		{
			return $this->getError('3');
		}

		$required = ['member_id' => $memberId];
		$params = array_merge($required, $params);

		return $this->sendData('lists.update_member', $params);
	}

	/**
	 * Lists.delete_member - Удаляем подписчика из базы
	 *
	 * @param   integer $memberId Member id
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function listsDeleteMember(int $memberId): mixed
	{
		if ($memberId == 0)
		{
			return $this->getError('3');
		}

		$params = ['member_id' => $memberId];

		return $this->sendData('lists.delete_member', $params);
	}

	/**
	 * Lists.unsubscribe_member - Редактируем подписчика в базе
	 *
	 * @param   array $params Params
	 *                        optional: member_id, email, list_id
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @see          http://dashamail.ru/api_details.php?method=lists.unsubscribe_member
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function listsUnsubscribeMember(array $params = []): mixed
	{
		if (isset($params['email']))
		{
			$email = $this->checkEmail($params['email']);

			if ($email !== false)
			{
				$params['email'] = $email;
			}
			else
			{
				return $this->getError('6');
			}
		}

		return $this->sendData('lists.unsubscribe_member', $params);
	}

	/**
	 * Lists.move_member - Перемещаем подписчика в другую адресную базу.
	 *
	 * @param   integer $memberId Member id
	 * @param   integer $listId   List id
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function listsMoveMember(int $memberId, int $listId): mixed
	{
		if ($memberId == 0 || $listId == 0)
		{
			return $this->getError('3');
		}

		$params = ['member_id' => $memberId, 'list_id' => $listId];

		return $this->sendData('lists.move_member', $params);
	}

	/**
	 * Lists.copy_member - Копируем подписчика в другую адресную базу
	 *
	 * @param   integer $memberId Member id
	 * @param   integer $listId   List id
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function listsCopyMember(int $memberId, int $listId): mixed
	{
		if ($memberId == 0 || $listId == 0)
		{
			return $this->getError('3');
		}

		$params = ['member_id' => $memberId, 'list_id' => $listId];

		return $this->sendData('lists.copy_member', $params);
	}

	/**
	 * Lists.add_merge - Добавить дополнительное поле в адресную базу
	 *
	 * @param   integer $listId List id
	 * @param   string  $type   Type
	 * @param   array   $params Params
	 *                          optional: choises, title, ...
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @see          http://dashamail.ru/api_details.php?method=lists.add_merge
	 *
	 * @noinspection PhpUnused
	 */
	public function listsAddMerge(int $listId, string $type, array $params = []): mixed
	{
		if ($listId == 0 || empty($type))
		{
			return $this->getError('3');
		}

		$required = ['list_id' => $listId, 'type' => $type];
		$params = array_merge($required, $params);

		return $this->sendData('lists.add_merge', $params);
	}

	/**
	 * Lists.update_merge - Обновить настройки дополнительного поля в адресной базе
	 *
	 * @param   integer $listId  List id
	 * @param   integer $mergeId Merge id
	 * @param   array   $params  Params
	 *                           optional: choisesm title, ...
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @see          http://dashamail.ru/api_details.php?method=lists.update_merge
	 *
	 * @noinspection PhpUnused
	 */
	public function listsUpdateMerge(int $listId, int $mergeId, array $params = []): mixed
	{
		if ($mergeId == 0 || $listId == 0)
		{
			return $this->getError('3');
		}

		$required = ['list_id' => $listId, 'merge_id' => $mergeId];
		$params = array_merge($required, $params);

		return $this->sendData('lists.update_merge', $params);
	}

	/**
	 * Lists.delete_merge - Удалить дополнительное поле из адресной базы
	 *
	 * @param   integer $listId  List id
	 * @param   integer $mergeId Merge id
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @see          http://dashamail.ru/api_details.php?method=lists.delete_merge
	 *
	 * @noinspection PhpUnused
	 */
	public function listsDeleteMerge(int $listId, int $mergeId): mixed
	{
		if ($mergeId == 0 || $listId == 0)
		{
			return $this->getError('3');
		}

		$params = ['list_id' => $listId, 'merge_id' => $mergeId];

		return $this->sendData('lists.delete_merge', $params);
	}


	/*******************************************************************
	 * ************************ Работа с рассылками **********************
	 ********************************************************************/

	/**
	 * Campaigns.get - Получаем список рассылок пользователя
	 *
	 * @param   array $params Params
	 *                        - int    $campaignId ID рассылки
	 *                        - string $status      Статус рассылки
	 *                        - int    $listId     ID списка для рассылки
	 *                        - string $type        Тип рассылки
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @see          http://dashamail.ru/api_details.php?method=campaigns.get
	 *
	 * @noinspection PhpUnused
	 */
	public function campaignsGet(array $params = []): mixed
	{
		return $this->getData('campaigns.get', $params);
	}

	/**
	 * Campaigns.create - Создаем рассылку
	 *
	 * @param   array $listId List id
	 * @param   array $params Params
	 *                        optional: name, subject, ...
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @see          http://dashamail.ru/api_details.php?method=campaigns.create
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function campaignsCreate(array $listId, array $params = []): mixed
	{
		if (count($listId) == 0)
		{
			return $this->getError('3');
		}

		$listId = serialize($listId);
		$required = ['list_id' => $listId];
		$params = array_merge($required, $params);

		return $this->sendData('campaigns.create', $params);
	}

	/**
	 * Campaigns.create_auto - Создаем авторассылку
	 *
	 * @param   array $params Params
	 *                        optional: list_id, name, subject
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @see          http://dashamail.ru/api_details.php?method=campaigns.create_auto
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function campaignsCreateAuto(array $params = []): mixed
	{
		$params['list_id'] = serialize($params['list_id']);

		return $this->sendData('campaigns.create_auto', $params);
	}

	/**
	 * Campaigns.update - Обновляем параметры рассылки
	 *
	 * @param   integer $campaignId Campaign id
	 * @param   array   $params     Params
	 *                              optional: list_id, name, subject, ...
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @see          http://dashamail.ru/api_details.php?method=campaigns.update
	 *
	 * @noinspection PhpUnused
	 */
	public function campaignsUpdate(int $campaignId, array $params = []): mixed
	{
		if ($campaignId == 0)
		{
			return $this->getError('3');
		}

		$required = ['campaign_id' => $campaignId];

		if (isset($params['list_id']))
		{
			$params['list_id'] = serialize($params['list_id']);
		}

		$params = array_merge($required, $params);

		return $this->sendData('campaigns.update', $params);
	}

	/**
	 * Campaigns.update_auto - Обновляем параметры авторассылки
	 *
	 * @param   integer $campaignId Campaign id
	 * @param   array   $params     Params
	 *                              optional: list_id, name, subject, ...
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @see          http://dashamail.ru/api_details.php?method=campaigns.update_auto
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function campaignsUpdateAuto(int $campaignId, array $params = []): mixed
	{
		if ($campaignId == 0)
		{
			return $this->getError('3');
		}

		$required = array('campaign_id' => $campaignId);
		$params['list_id'] = serialize($params['list_id']);
		$params = array_merge($required, $params);

		return $this->sendData('campaigns.update_auto', $params);
	}

	/**
	 * Campaigns.delete - Удаляем рассылку
	 *
	 * @param   integer $campaignId Campaign id
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @see          http://dashamail.ru/api_details.php?method=campaigns.delete
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function campaignsDelete(int $campaignId): mixed
	{
		if ($campaignId == 0)
		{
			return $this->getError('3');
		}

		$params = ['campaign_id' => $campaignId];

		return $this->sendData('campaigns.delete', $params);
	}

	/**
	 * Campaigns.attach - Прикрепляем файл
	 *
	 * @param   integer     $campaignId Campaign id
	 * @param   string|null $url        URL
	 * @param   array       $params     Params
	 *                                  optional: name
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @see          http://dashamail.ru/api_details.php?method=campaigns.attach
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function campaignsAttach(int $campaignId, string $url = null, array $params = []): mixed
	{
		if ($campaignId == 0 || is_null($url))
		{
			return $this->getError('3');
		}

		$required = ['campaign_id' => $campaignId, 'url' => $url];
		$params = array_merge($required, $params);

		return $this->sendData('campaigns.attach', $params);
	}

	/**
	 * Campaigns.get_attachments - Получаем приложенные файлы
	 *
	 * @param   integer $campaignId Campaign id
	 * @param   array   $params     Params
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @see          http://dashamail.ru/api_details.php?method=campaigns.get_attachments
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function campaignsGetAttachments(int $campaignId, array $params = []): mixed
	{
		if ($campaignId == 0)
		{
			return $this->getError('3');
		}

		$required = ['campaign_id' => $campaignId];
		$params = array_merge($required, $params);

		return $this->getData('campaigns.get_attachments', $params);
	}

	/**
	 * Campaigns.delete_attachments - Удаляем приложенный файл
	 *
	 * @param   integer $campaignId Campaign id
	 *
	 * @param   integer $id         Id
	 *
	 * @param   array   $params     Params
	 *                              required: campaign_id, id
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @see          http://dashamail.ru/api_details.php?method=campaigns.delete_attachments
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function campaignsDeleteAttachments(int $campaignId, int $id, array $params = []): mixed
	{
		if ($campaignId == 0 || $id == 0)
		{
			return $this->getError('3');
		}

		$required = ['campaign_id' => $campaignId, 'id' => $id];
		$params = array_merge($required, $params);

		return $this->sendData('campaigns.delete_attachments', $params);
	}

	/**
	 * Campaigns.get_templates - Получаем html шаблоны
	 *
	 * @param   array $params Params
	 *                        optional: name, id
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @see          http://dashamail.ru/api_details.php?method=campaigns.get_templates
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function campaignsGetTemplates(array $params = []): mixed
	{
		return $this->getData('campaigns.get_templates', $params);
	}

	/**
	 * Campaigns.add_template - Добавляем html шаблон
	 *
	 * @param   string $name     Name
	 *
	 * @param   string $template Template
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @see          http://dashamail.ru/api_details.php?method=campaigns.add_template
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function campaignsAddTemplate(string $name, string $template): mixed
	{
		if (empty($email) || empty($template))
		{
			return $this->getError('3');
		}

		$params = array('name' => $name, 'template' => $template);

		return $this->sendData('campaigns.add_template', $params);
	}

	/**
	 * Campaigns.delete_template - Удаляем html шаблон
	 *
	 * @param   integer  $id  Id
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function campaignsDeleteTemplate(int $id): mixed
	{
		if ($id == 0)
		{
			return $this->getError('3');
		}

		$params = ['id' => $id];

		return $this->sendData('campaigns.delete_templates', $params);
	}

	/**
	 * Campaigns.force_auto - Принудительно вызываем срабатывание авторассылки (при этом она должна быть активна)
	 *
	 * @param   integer $campaignId Campaign id
	 * @param   string  $email      Email
	 * @param   array   $params     Params
	 *                              optional: delay
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @see          http://dashamail.ru/api_details.php?method=campaigns.force_auto
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function campaignsForceAuto(int $campaignId, string $email, array $params = []): mixed
	{
		if ($campaignId == 0 || empty($email))
		{
			return $this->getError('3');
		}

		$email = $this->checkEmail($email);

		if ($email !== false)
		{
			$required = ['campaign_id' => $campaignId, 'email' => $email];
		}
		else
		{
			return $this->getError('6');
		}

		$params = array_merge($required, $params);

		return $this->sendData('campaigns.force_auto', $params);
	}

	/***************************************************************
	 * ******************** Работа с отчетами ************************
	 ****************************************************************/
	/**
	 * Reports.send - Список отправленных писем в рассылке
	 *
	 * @param   integer $campaignId Campaign id
	 * @param   array   $params     Params
	 *                              optional: start, limit, order
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @see          http://dashamail.ru/api_details.php?method=reports.send
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function reportsSent(int $campaignId, array $params = []): mixed
	{
		if ($campaignId == 0)
		{
			return $this->getError('3');
		}

		$required = array('campaign_id' => $campaignId);
		$params = array_merge($required, $params);

		return $this->sendData('reports.sent', $params);
	}

	/**
	 * Reports.delivered - Список доставленных писем в рассылке
	 *
	 * @param   integer $campaignId Campaign id
	 * @param   array   $params     Params
	 *                              optional: start, limit, order
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @see          http://dashamail.ru/api_details.php?method=reports.delivered
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function reportsDelivered(int $campaignId, array $params = []): mixed
	{
		if ($campaignId == 0)
		{
			return $this->getError('3');
		}

		$required = array('campaign_id' => $campaignId);
		$params = array_merge($required, $params);

		return $this->getData('reports.delivered', $params);
	}

	/**
	 * Reports.opened - Список открытых писем в рассылке
	 *
	 * @param   integer $campaignId Campaign id
	 * @param   array   $params     Params
	 *                              optional: start, limit, order
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @see          http://dashamail.ru/api_details.php?method=reports.opened
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function reportsOpened(int $campaignId, array $params = []): mixed
	{
		if ($campaignId == 0)
		{
			return $this->getError('3');
		}

		$required = array('campaign_id' => $campaignId);
		$params = array_merge($required, $params);

		return $this->getData('reports.opened', $params);
	}

	/**
	 * Reports.unsubscribed - Список писем отписавшихся подписчиков в рассылке
	 *
	 * @param   integer $campaignId Campaign id
	 * @param   array   $params     Params
	 *                              optional: start, limit, order
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @see          http://dashamail.ru/api_details.php?method=reports.unsubscribed
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function reportsUnsubscribed(int $campaignId, array $params = []): mixed
	{
		if ($campaignId == 0)
		{
			return $this->getError('3');
		}

		$required = array('campaign_id' => $campaignId);
		$params = array_merge($required, $params);

		return $this->getData('reports.unsubscribed', $params);
	}

	/**
	 * Reports.bounced - Список возвратившихся писем в рассылке
	 *
	 * @param   integer $campaignId Campaign id
	 * @param   array   $params     Params
	 *                              start
	 *                              limit
	 *                              order
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @see          http://dashamail.ru/api_details.php?method=reports.bounced
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function reportsBounced(int $campaignId, array $params = []): mixed
	{
		if ($campaignId == 0)
		{
			return $this->getError('3');
		}

		$required = ['campaign_id' => $campaignId];
		$params = array_merge($required, $params);

		return $this->getData('reports.unsubscribed', $params);
	}

	/**
	 * Reports.clickstat - Cтатистика по кликам по различным url в письме
	 *
	 * @param   integer $campaignId Campaign id
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @see          http://dashamail.ru/api_details.php?method=reports.clickstat
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function reportsClickstat(int $campaignId): mixed
	{
		if ($campaignId == 0)
		{
			return $this->getError('3');
		}

		$params = ['campaign_id' => $campaignId];

		return $this->getData('report]s.clickstat', $params);
	}

	/**
	 * Reports.bouncestat - Cтатистика по всевозможным причинам возврата письма
	 *
	 * @param   integer $campaignId Campaign id
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @see          : http://dashamail.ru/api_details.php?method=reports.bouncestat
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function reportsBouncestat(int $campaignId): mixed
	{
		if ($campaignId == 0)
		{
			return $this->getError('3');
		}

		$params = ['campaign_id' => $campaignId];

		return $this->getData('reports.bouncestat', $params);
	}

	/**
	 * Reports.summary - Краткая статистика по рассылке
	 *
	 * @param   integer $campaignId Campaign id
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @see          http://dashamail.ru/api_details.php?method=reports.summary
	 *
	 * @noinspection PhpUnused
	 */
	public function reportsSummary(int $campaignId): mixed
	{
		if ($campaignId == 0)
		{
			return $this->getError('3');
		}

		$params = ['campaign_id' => $campaignId];

		return $this->getData('reports.summary', $params);
	}

	/**
	 * Reports.clients - Cтатистика по браузерам, ОС и почтовым клиентам
	 *
	 * @param   integer $campaignId Campaign id
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @see          http://dashamail.ru/api_details.php?method=reports.clients
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function reportsClients(int $campaignId): mixed
	{
		if ($campaignId == 0)
		{
			return $this->getError('3');
		}

		$params = ['campaign_id' => $campaignId];

		return $this->getData('reports.clients', $params);
	}

	/**
	 * Reports.geo - Cтатистика по регионам открытия
	 *
	 * @param   int $campaignId Campaing id
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @see          : http://dashamail.ru/api_details.php?method=reports.geo
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function reportsGeo(int $campaignId): mixed
	{
		if ($campaignId == 0)
		{
			return $this->getError('3');
		}

		$params = ['campaign_id' => $campaignId];

		return $this->getData('reports.geo', $params);
	}

	/**
	 * @param   array $options Options
	 *
	 * @return mixed|string
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	private function extracted(array $options): mixed
	{
		$ch = curl_init();
		curl_setopt_array($ch, $options);
		$final = curl_exec($ch);

		if (!$final)
		{
			throw new Exception(curl_error($ch));
		}

		curl_close($ch);
		$final = json_decode($final);


		if (!$final)
		{
			throw new Exception('Получены неверные данные, пожалуйста, убедитесь, что запрашиваемый метод API существует');
		}

		if ($final->response->msg->err_code == 0)
		{
			return $final->response->data;
		}
		else
		{
			return $this->getError($final->response->msg->err_code);
		}
	}
}
