<?php
/**
 * @copyright 2023 Nekrasov Vitaliy
 * @license GNU General Public License version 2 or later
 */
namespace Wishbox\Trait;

use Exception;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Mail\Mail;
use Joomla\CMS\Mail\MailerFactoryInterface;

/**
 * @property Registry $params
 *
 * @method getRenderer(string $string)
 * @method getUserEmailNotificationRecipient(): string
 *
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
trait UserEmailNotificationPluginTrait
{
	/**
	 * @return boolean
	 *
	 * @throws Exception
	 *
	 * @throws \PHPMailer\PHPMailer\Exception
	 *
	 * @since 1.0.0
	 */
	public function sendUserEmailNotification(): bool
	{
		$sender = $this->getUserEmailNotificationSender();
		$recipient = $this->getUserEmailNotificationRecipient();
		$subject = $this->getUserEmailNotificationSubject();

		/** @var Mail $mailer */
		$mailer = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();

		$mailer->setSender($sender);
		$mailer->addRecipient($recipient);
		$mailer->setSubject($subject);

		$body = $this->getUserEmailNotificationBody();

		$mailer->setBody($body);
		$mailer->isHTML();

		return $mailer->Send();
	}

	/**
	 * @return string
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getUserEmailNotificationSubject(): string
	{
		$userEmailNotification = (array) $this->params->get('user_email_notification', []);

		if (empty($userEmailNotification['subject']))
		{
			throw new Exception('[user_email_notification][subject] must not be empty', 500);
		}

		return $userEmailNotification['subject'];
	}

	/**
	 * @return string
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getUserEmailNotificationBody(): string
	{
		return $this->getRenderer('useremailnotification')->render($this->getUserEmailNotificationLayoutData());
	}

	/**
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function getUserEmailNotificationLayoutData(): array
	{
		return [];
	}

	/**
	 * @return string[] An array with two elements.
	 *                  - Email
	 *                  - Name
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getUserEmailNotificationSender(): array
	{
		$app = Factory::getApplication();

		$config = $app->getConfig();

		return [
			$config->get('mailfrom'),
			$config->get('fromname')
		];
	}
}
