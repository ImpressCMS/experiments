<?php

namespace ImpressCMS\Core\Controllers;

use GuzzleHttp\Psr7\Response;
use icms;
use ImpressCMS\Core\DataFilter;
use ImpressCMS\Core\Response\ViewResponse;
use ImpressCMS\Core\View\Form\Elements\Captcha\ImageRenderer;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Router\Annotation\Route;

/**
 * Does few usefull actions
 *
 * @package ImpressCMS\Core\Controllers
 */
class MiscController
{

	/**
	 * Gets default misc page
	 *
	 * @Route(
	 *     name="default_misc_page",
	 *     path="/misc.php",
	 *     methods={"GET", "POST"}
	 * )
	 *
	 * @param ServerRequestInterface $request Request
	 *
	 * @return ResponseInterface
	 */
	public function getDefaultPage(ServerRequestInterface $request): ResponseInterface
	{
		$params = $request->getQueryParams();

		switch ($params['action'] ?? null) {
			case 'update-captcha':
				return $this->updateCaptcha($request);
			case 'showpopups':
				switch ($params['type'] ?? null) {
					case 'smilies':
					case 'smiles':
						return $this->showSmilesPopup($request);
					case 'friends':
						return $this->showFriendsPopup($request);
					case 'avatars':
						return $this->showAvatarsPopup($request);
					case 'online':
						return $this->showUsersOnlinePopup($request);
				}
		}

		/**
		 * @var ResponseFactoryInterface $responseFactory
		 */
		$responseFactory = icms::getInstance()->get('response_factory');

		return $responseFactory->createResponse(404);
	}

	/**
	 * Updates captcha to be entered
	 *
	 * @Route(
	 *     name="update_captcha",
	 *     path="/update-captcha",
	 *     methods={"GET"}
	 * )
	 *
	 * @param ServerRequestInterface $request Request
	 *
	 * @return ResponseInterface
	 */
	public function updateCaptcha(ServerRequestInterface $request)
	{
		$image_handler = new ImageRenderer();
		$image_handler->clearAttempts();

		ob_start();
		$image_handler->loadImage();
		$contents = ob_get_clean();
		ob_end_clean();

		return new Response(200, headers_list(), $contents);
	}

	/**
	 * Shows smiles list for popup
	 *
	 * @Route(
	 *     name="show_smiles_popup",
	 *     path="/smiles/popup",
	 *     methods={"GET"}
	 * )
	 *
	 * @param ServerRequestInterface $request Request
	 *
	 * @return ResponseInterface
	 */
	public function showSmilesPopup(ServerRequestInterface $request): ResponseInterface
	{
		$params = $request->getQueryParams();

		$target = $params['target'] ?? '';
		if (!$target || !preg_match('/^[0-9a-z_]*$/i', $target)) {
			/**
			 * @var ResponseFactoryInterface $responseFactory
			 */
			$responseFactory = icms::getInstance()->get('response_factory');
			return $responseFactory->createResponse(400, "Target not correct or specified");
		}

		$response = new ViewResponse([
			'template_canvas' => 'db:system_blank.html',
			'template_main' => 'db:system_smiles.html',
		]);
		$response->assign(
			'smiles',
			DataFilter::getSmileys(true)
		);
		$response->assign('target', $target);
		return $response;
	}

	/**
	 * Shows friends list for popup
	 *
	 * @Route(
	 *     name="show_friends_popup",
	 *     path="/avatars/popup",
	 *     methods={"GET"}
	 * )
	 *
	 * @param ServerRequestInterface $request Request
	 *
	 * @return ResponseInterface
	 */
	public function showFriendsPopup(ServerRequestInterface $request): ResponseInterface
	{

	}

	/**
	 * Shows avatars list for popup
	 *
	 * @Route(
	 *     name="show_avatars_popup",
	 *     path="/avatars/popup",
	 *     methods={"GET"}
	 * )
	 *
	 * @param ServerRequestInterface $request Request
	 *
	 * @return ResponseInterface
	 */
	public function showAvatarsPopup(ServerRequestInterface $request): ResponseInterface
	{

	}

	/**
	 * Shows online list for popup
	 *
	 * @Route(
	 *     name="show_online_popup",
	 *     path="/online/popup",
	 *     methods={"GET"}
	 * )
	 *
	 * @param ServerRequestInterface $request Request
	 *
	 * @return ResponseInterface
	 */
	public function showUsersOnlinePopup(ServerRequestInterface $request): ResponseInterface
	{

	}

}