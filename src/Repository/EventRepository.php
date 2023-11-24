<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
    * @return Event[] Returns an array of Post objects
    */
   public function findWithPagination(?Category $category, int $page, int $limit = 10): array
   {
    $request = $this->createQueryBuilder('p')
    ->orderBy('p.createdAt', 'DESC');

if ($category) {
    $request->andWhere('p.category = :category')
        ->setParameter('category', $category);
}

$request = $request->setFirstResult(($page - 1) * $limit)
    ->setMaxResults($limit)
    ->getQuery();

return $request->getResult();

   }

    /**
    * @return Event[] Returns an array of Post objects
    */
    public function maxPages(Category $category, int $limit = 10): int
    {
        $totalResults = count($this->createQueryBuilder('p')
            ->where('p.category = :category')
            ->setParameter('category', $category)
            ->getQuery()
            ->getResult());

        $pageCount = ($totalResults % $limit === 0) ? $totalResults / $limit : ceil($totalResults / $limit);
        return $pageCount;
    }

    public function checkParticipationDate(User $user, Event $event): ?Event
    {

        $startingAt = $event->getStartingAt();
        $endingAt = $event->getEndingAt();

        return $this->createQueryBuilder('e')
            ->where(':user MEMBER OF e.participants')
            ->andWhere('e.id != :id')
            ->andWhere(':startingAt BETWEEN e.startingAt AND e.endingAt')
            ->andWhere(':endingAt BETWEEN e.startingAt AND e.endingAt')
            ->setParameter('user', $user)
            ->setParameter('id', $event->getId())
            ->setParameter('startingAt', $startingAt)
            ->setParameter('endingAt', $endingAt)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

//    /**
//     * @return Event[] Returns an array of Event objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Event
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
