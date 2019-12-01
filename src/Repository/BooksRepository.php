<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Books;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use App\Service\PaginatorService;

/**
 * @method Books|null find($id, $lockMode = null, $lockVersion = null)
 * @method Books|null findOneBy(array $criteria, array $orderBy = null)
 * @method Books[]    findAll()
 * @method Books[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BooksRepository extends ServiceEntityRepository
{
    /** @var \Symfony\Bridge\Doctrine\RegistryInterface $em */
    private $em;
    /** @var LoggerInterface */
    private $logger;

    /**
     * BooksRepository constructor.
     * @param ManagerRegistry $registry
     * @param LoggerInterface $logger
     */
     public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
     {
         parent::__construct($registry, Books::class);
         $this->logger = $logger;
         $this->paginatorService = new PaginatorService($registry);
     }

    /**
     * Check if books were already improted
     * @param $title
     * @param $author
     * @param $isbns
     * @todo - fix syntax error (json encoding) on isbns
     * @return $response
     */
    public function checkDuplicates($title, $author, $isbns)
    {
      // run query for duplicates check
      $qb = $this->createQueryBuilder('b')
          ->andWhere('b.title = :title')
          ->andWhere('b.author = :author')
          // ->andWhere('b.isbns = :isbns')
          ->setParameter(':title', $title)
          ->setParameter(':author', $author)
          // ->setParameter(':isbns', $isbns)
          ->getQuery();

      $response = $qb->execute();
      return $response;
    }


    /**
     * Save books imported by Author
     * @param $data
     * @return $response
     */
    public function saveBooks($data)
    {

      foreach ($data['results'] as $item) {

        $dupsCheck = $this->checkDuplicates($item['title'], $item['author'], $item['isbns']);
        $this->logger->info('Dups checked: '.json_encode($dupsCheck));

        if (empty($dupsCheck)) {
            $reviews = [];
            foreach ($item['reviews'] as $subitem) {
              if (!empty($subitem)) {
                $reviews[] = $subitem;
              }
            }

            $entityManager = $this->getEntityManager();
            $book = new Books();
            $book->setIsbns(json_encode($item['isbns']));
            $book->setTitle($item['title']);
            $book->setAuthor($item['author']);
            $book->setDescription(json_encode($item['description']));
            $book->setReviews(count($reviews));
            $entityManager->persist($book);
            $entityManager->flush();

            $response = 'Books imported';
        } else {
            $response = 'Books already exist in DB';
        }
      }

      return $response;
    }

    /**
     * Get books
     * @param array $filters
     * @param int $page
     * @return array
     */
    public function getBooks(array $filters, $page = 1)
    {
        // Build query
        $qb = $this->createQueryBuilder('b');

        // Search and Filtering can be attached here
        if (!empty($filters['author'])) {
            $qb->andWhere('b.author LIKE :author');
            $qb->setParameter(':author', '%'.addcslashes($filters['author'], '%_').'%');
        }

        if (!empty($filters['search'])) {
            $qb->andWhere('b.title LIKE :search');
            $qb->andWhere('b.description LIKE :search');
            $qb->setParameter(':search', '%'.addcslashes($filters['search'], '%_').'%');
        }

        if (!empty($filters['reviews'])) {
            $qb->andWhere('b.reviews >= :reviews');
            $qb->setParameter(':reviews', '%'.addcslashes($filters['reviews'], '%_').'%');
        }

        // Set pagination
        $response = $this->paginatorService->paginationSetter($qb, $page);
        return $response;
    }

    /**
     * Get books
     * @param array $filters
     * @param int $page
     * @return array
     */
    public function getAuthors($page = 1)
    {
        // Build query
        $qb = $this->createQueryBuilder('b');

        // Set pagination
        $response = $this->paginatorService->paginationSetter($qb, $page);
        return $response;
    }

}
