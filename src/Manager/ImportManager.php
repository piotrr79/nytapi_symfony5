<?php
declare(strict_types=1);

namespace App\Manager;

use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use App\Repository\BooksRepository;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpClient\HttpClient;
use App\Constants\ConfigurationConstants;

/**
 * ImportManager
 */
class ImportManager
{
    /** @var ManagerRegistry */
    private $registry;
    /** @var LoggerInterface */
    private $logger;
    /** @var BooksRepository */
    private $booksRepository;

    /**
     * ImportManager constructor.
     * @param ManagerRegistry $registry
     * @param LoggerInterface $logger
     */
    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->booksRepository = new BooksRepository($registry, $logger);
    }

    /**
     * Import manager
     * @param $author
     * @return $response
     */
    public function importBooks($author)
    {
        $apiUrl = 'https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json?';
        $url = $apiUrl.'author='.$author.'&api-key='.ConfigurationConstants::NYT_CREDENTIALS;

        // Log url
        $this->logger->info('URL: '.($url));

        // Create http call
        $client = HttpClient::create();
        $apiCall = $client->request('GET', $url);

        $statusCode = $apiCall->getStatusCode();
        $contentType = $apiCall->getHeaders()['content-type'][0];
        $content = $apiCall->getContent();
        $data = $apiCall->toArray();

        // Send result to Repository
        $result = $this->booksRepository->saveBooks($data);

        $response = ['status_code' => $statusCode, 'content' => $result];
        $this->logger->info(json_encode($response));
        return $response;
    }
}
