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

    public function logout(Request $request, Response $response, array $args = []): Response
    {
        session_destroy();
        return $response
            ->withHeader('Location', '/')
            ->withStatus(302);
    }
}