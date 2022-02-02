<?php

namespace AzureBlob\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;

final class HomeController
{
    private LoggerInterface $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
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
        $view = Twig::fromRequest($request);
        return $view->render($response, 'home.twig');
    }

    public function postSettings(Request $request, Response $response, array $args = []): Response
    {
        $postData = (array) $request->getParsedBody();
        $accountName = $postData['account_name'] ?? '';
        $accountKey = $postData['account_key'] ?? '';
        $_SESSION['az_account_name'] = base64_encode($accountName);
        $_SESSION['az_account_key'] = base64_encode($accountKey);
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