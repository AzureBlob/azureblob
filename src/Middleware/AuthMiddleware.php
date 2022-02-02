<?php
declare(strict_types=1);

namespace AzureBlob\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Response as NewResponse;

final class AuthMiddleware
{
    private LoggerInterface $logger;
    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $excludePaths = ['/', '/login', '/logout'];
        $response = new NewResponse();
        $uri = $request->getUri();
        if (in_array($uri->getPath(), $excludePaths)) {
            return $handler->handle($request);
        }
        if (! $request->hasHeader('Cookie')) {
            $this->logger->info(sprintf(
                'Request at %s had no cookie header',
                $uri->getPath()
            ));
            return $response
                ->withHeader('Location', '/')
                ->withStatus(302);
        }
        if ([] === $_SESSION) {
            $this->logger->info(sprintf(
                'Request at %s had no active session',
                $uri->getPath()
            ));
            return $response
                ->withHeader('Location', '/')
                ->withStatus(302);
        }
        $accountName = $_SESSION['az_account_name'] ?? '';
        $accountKey = $_SESSION['az_account_key'] ?? '';
        if (in_array('', [$accountName, $accountKey])) {
            $this->logger->info(sprintf(
                'Request at %s session was empty',
                $uri->getPath()
            ));
            return $response
                ->withHeader('Location', '/')
                ->withStatus(302);
        }
        return $handler->handle($request);
    }
}
