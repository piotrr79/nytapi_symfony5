<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;
use App\Constants\ConfigurationConstants;

/**
* BaseController
*/
class BaseController extends AbstractController
{
    /** @var LoggerInterface */
    private $logger;

    /**
     * ImportController constructor.
     * @param LoggerInterface $logger
     * @param ImportManager $importManager
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Authorize by auth token
     * @param $request
     * @return void
     */
    protected function authorize($request)
    {
        // Get data from request
        $requestToken = $request->headers->get('Auth-Key');
        // Compare token
        if ($requestToken != ConfigurationConstants::API_KEY) {
            $message = 'Unauthorized. Invalid token';
            $this->logger->info(json_encode($message));
            throw new AccessDeniedHttpException($message);
        }
    }
}
