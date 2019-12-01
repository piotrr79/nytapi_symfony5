<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use App\Repository\BooksRepository;

/**
 * @Route("/api")
 */
class BooksController extends BaseController
{

    /**
     * BooksController constructor.
     * @param ManagerRegistry $registry
     * @param LoggerInterface $logger
     */
    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->booksRepository = new BooksRepository($registry, $logger);
    }

    /**
     * Get Books
     * @Route("/books", name="books", methods={"GET"})
     * @internal - query params: ?page=1&filters[search]=any&filters[author]=King&filter[reviews]=1
     * @todo - add more validation for filters search keyword and author name
     * @todo - improve response format for BadRequestHttpException and HttpException
     */
    public function getBooks(Request $request)
    {
        $this->authorize($request);

        $filters = $request->query->get('filters');
        $page = $request->query->get('page');

        if (strlen($filters['search']) < 3) {
            throw new BadRequestHttpException('Search keyword must be minimum 3 characters');
        }

        if (strlen($filters['author']) < 3) {
            throw new BadRequestHttpException('Autor name must be minimum 3 characters');
        }

        $this->logger->info('filters '.json_encode($filters));
        $this->logger->info('page '.json_encode($page));

        if ((null !== $filters && !is_array($filters))) {
            throw new HttpException(400, 'Filter must be an array');
        }

        $filters = (null === $filters) ? [] : $filters;

        try {
            $results = $this->booksRepository->getBooks($filters, $page);
            $code = 200;
        } catch(HttpException $e) {
            $this->logger->error('API getBooks Error: '. $e->getMessage());
            $results = $e->getMessage();
            $code = 400;
        }

        $response = new JsonResponse($results, $code);
        return $response;
    }

    /**
     * Get Authors
     * @Route("/authors", name="authors", methods={"GET"})
     * @internal - query params: ?page=1
     */
    public function getAuthors(Request $request)
    {
        $this->authorize($request);
        $page = $request->query->get('page');

        try {
            $results = $this->booksRepository->getAuthors($page);
            $code = 200;
            // Remove unwanted elelemnts from query (ugly way, instead Doctrine Query should be improved)
            foreach ($results['results'] as $key => $value) {
              unset($results['results'][$key]['id']);
              unset($results['results'][$key]['isbns']);
              unset($results['results'][$key]['title']);
              unset($results['results'][$key]['description']);
              unset($results['results'][$key]['reviews']);
            }
        } catch(HttpException $e) {
            $this->logger->error('API getBooks Error: '. $e->getMessage());
            $results = $e->getMessage();
            $code = 400;
        }

        $response = new JsonResponse($results, $code);
        return $response;
    }
}
