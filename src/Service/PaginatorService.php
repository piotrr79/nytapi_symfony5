<?php
declare(strict_types=1);

namespace App\Service;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Orm\Query;

 /**
  * PaginatorService - set pagination info for json response
  */
class PaginatorService
{
    /** @var RegistryInterface */
    private $em;

    /**
     * PaginatorService constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->em = $registry;
    }
    /**
     * Set pgination for QueryBuilder by page and return results with pagination array attached
     * @param $qb
     * @param $page
     * @return array
     */
    public function paginationSetter($qb, $page)
    {

        if ($page === null) {
            $page = 1;
        }
        $limit = 20;
        $offset = ($page - 1) * $limit;

        /** @var \Doctrine\ORM\QueryBuilder $qb */
        $qb->setMaxResults($limit)->setFirstResult($offset);
        // Load Paginator
        $paginator = new Paginator($qb);
        $response = $paginator->getQuery()->getResult(Query::HYDRATE_ARRAY);

        // Set pagination results
        $countResults = $paginator->count();
        $totalPages = ceil($countResults/$limit);

        if (!empty($response)) {
            $pagination = ['count_results' => $countResults, 'total_pages' => $totalPages, 'current_page' => $page];
        } else {
            $pagination = [];
        }

        return ['results'=> $response, 'pagination' => $pagination];
    }
}
