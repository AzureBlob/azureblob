<?php

namespace AzureBlob\Controllers;

use AzureBlob\Services\AzureBlobService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;

final class HomeController
{
    private LoggerInterface $logger;
    private AzureBlobService $blobService;

    /**
     * @param LoggerInterface $logger
     * @param AzureBlobService $blobService
     */
    public function __construct(LoggerInterface $logger, AzureBlobService $blobService)
    {
        $this->logger = $logger;
        $this->blobService = $blobService;
    }

    /**
     * Returns the home page for AzureBlob website
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getHome(Request $request, Response $response, array $args = []): Response
    {
        $this->logger->info(sprintf(
            'User with IP %s accessed the homepage',
            $request->getAttribute('ip_address')
        ));
        if ([] !== $_COOKIE && isset($_COOKIE[AzureBlobService::REMEMBER_COOKIE_NAME])) {
            $accountHash = base64_decode($_COOKIE[AzureBlobService::REMEMBER_COOKIE_NAME]);
            list ($accountName, $accountKey) = explode(':', $accountHash);
            $this->blobService->startSession($accountName, $accountKey);
            return $response
                ->withHeader('Location', '/storage')
                ->withStatus(302);
        }
        $view = Twig::fromRequest($request);
        return $view->render($response, 'home.twig');
    }

    public function postSettings(Request $request, Response $response, array $args = []): Response
    {
        $postData = (array) $request->getParsedBody();
        $accountName = $postData['account_name'] ?? '';
        $accountKey = $postData['account_key'] ?? '';
        $rememberMe = $postData['remember_me'] ?? '0';
        $this->blobService->startSession($accountName, $accountKey);
        $result = false;
        if (1 === intval($rememberMe)) {
            $result = $this->blobService->setRememberMeCookie($accountName, $accountKey);
        }
        return $response
            ->withHeader('Location', '/storage')
            ->withStatus(302);
    }

    public function logout(Request $request, Response $response, array $args = []): Response
    {
        unset($_SESSION['az_account_name']);
        unset($_SESSION['az_account_key']);
        session_destroy();
        return $response
            ->withHeader('Location', '/')
            ->withStatus(302);
    }
}