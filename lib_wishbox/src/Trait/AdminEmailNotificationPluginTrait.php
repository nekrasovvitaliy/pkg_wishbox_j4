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

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @property Registry $params
 *
 * @method getRenderer(string $string)
 *
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
trait AdminEmailNotificationPluginTrait
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
	public function sendAdminEmailNotification(): bool
	{
		$sender = $this->getAdminEmailNotificationSender();
		$recipient = $this->getAdminEmailNotificationRecipient();
		$subject = $this->getAdminEmailNotificationSubject();

		/** @var Mail $mailer */
		$mailer = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();

		$mailer->setSender($sender);
		$mailer->addRecipient($recipient);
		$mailer->setSubject($subject);
		$mailer->setBody($this->getAdminEmailNotificationBody());

		return $mailer->Send();
	}

	/**
	 * @return string
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getAdminEmailNotificationSubject(): string
	{
		$adminEmailNotification = (array) $this->params->get('admin_email_notification', []);

		if (empty($adminEmailNotification['subject']))
		{
			throw new Exception('[admin_email_notification][subject] must not be empty', 500);
		}

		return $adminEmailNotification['subject'];
	}

	/**
	 * @return string
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getAdminEmailNotificationBody(): string
	{
		return $this->getRenderer('adminemailnotification')->render($this->getAdminEmailNotificationLayoutData());
	}

	/**
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function getAdminEmailNotificationLayoutData(): array
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
	public function getAdminEmailNotificationSender(): array
	{
		$app = Factory::getApplication();

		$config = $app->getConfig();

		return [
			$config->get('mailfrom'),
			$config->get('fromname')
		];
	}

	/**
	 * @return string
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getAdminEmailNotificationRecipient(): string
	{
		$app = Factory::getApplication();

		$adminEmailNotification = (array) $this->params->get('admin_email_notification', []);

		if (empty($adminEmailNotification['email']))
		{
			$config = $app->getConfig();

			return $config->get('mailfrom');
		}

		return $adminEmailNotification['email'];
	}
}
