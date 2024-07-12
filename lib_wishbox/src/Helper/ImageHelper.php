<?php
/**
 * @copyright   (с) 2013-2024 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later
 */
namespace Wishbox\Helper;

use GdImage;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
class ImageHelper
{
	/**
	 * @param   GdImage  $image  Image
	 * @param   GdImage  $mask   Mask image
	 *
	 * @return GdImage
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public static function addMaskToImage(GdImage $image, GdImage $mask): GdImage
	{
		$width = imagesx($image);
		$height = imagesy($image);
		$img = imagecreatetruecolor($width, $height);

		// Определяем прозрачный цвет для картинки. Черный
		$transColor = imagecolorallocate($img, 255, 255, 255);
		imagecolortransparent($img, $transColor);

		imagefill($img, 0, 0, $transColor);

		// задаем прозрачность для картинки
		// перебираем картинку по пикселю
		for ($posX = 0; $posX < $width; $posX++)
		{
			for ($posY = 0; $posY < $height; $posY++)
			{
				// получаем индекс цвета пикселя в координате $posX, $posY для картинки
				$colorIndex = imagecolorat($image, $posX, $posY);

				// получаем цвет по его индексу в формате RGB
				$colorImage = imagecolorsforindex($image, $colorIndex);

				// получаем индекс цвета пикселя в координате $posX, $posY для маски
				$colorIndex = imagecolorat($mask, $posX, $posY);

				$maskColor = imagecolorsforindex($mask, $colorIndex);

				// получаем цвет по его индексу в формате RGB
				// если в точке $posX, $posY цвет маски не белый, то наносим на холст пиксель с нужным цветом
				if (!($maskColor['red'] == 255 && $maskColor['green'] == 255 && $maskColor['blue'] == 255))
				{
					// получаем цвет для пикселя
					$colorIndex = imagecolorallocate($img, $colorImage['red'], $colorImage['green'], $colorImage['blue']);

					// рисуем пиксель
					imagesetpixel($img, $posX, $posY, $colorIndex);
				}
			}
		}

		return $img;
	}

	/**
	 * @param   GdImage  $image  Image
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function getBase64FromGDImage(GdImage $image): string
	{
		ob_start();
		imagejpeg($image);
		$imageData = ob_get_clean();
		$base64Image = 'data:image/png;base64,' . base64_encode($imageData);

		// Освобождаем память, выделенную для результирующего изображения
		imagedestroy($image);

		return $base64Image;
	}
}
