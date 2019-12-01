<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;
use App\Manager\ImportManager;

/**
 * @Route("/api")
 */
class ImportController extends BaseController
{
    /** @var LoggerInterface */
    private $logger;
    /** @var ImportManager */
    private $importManager;
    private $nytApiToken;

    /**
     * ImportController constructor.
     * @param LoggerInterface $logger
     * @param ImportManager $importManager
     */
    public function __construct(LoggerInterface $logger, ImportManager $importManager)
    {
        $this->logger = $logger;
        $this->importManager = $importManager;
    }

    /**
     * Import data from NYT Api call
     * @param Request $request
     * @return $response
     *
     * @Route("/import", name="import", methods={"GET"})
     */
    public function import(Request $request)
    {
        $this->authorize($request);
        $clientData = json_decode($request->getContent(), true);

        if (!array_key_exists('author',$clientData)) {
          $message = 'Author name cannot be empty';
          $this->logger->error(json_encode($message));
          throw new HttpException(400, $message);
        }

        if (array_key_exists('author',$clientData)) {
          $author = str_replace(' ', '+', trim($clientData['author']));
          $author = trim($author);
        }

        // Pass author to Manager, get data, validate against dupes, save to DB
        $resutls = $this->importManager->importBooks($author);

        $response = new JsonResponse($resutls, 200);
        return $response;
    }
}
