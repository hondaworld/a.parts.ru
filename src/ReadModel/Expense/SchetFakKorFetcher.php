<?php


namespace App\ReadModel\Expense;


use App\Model\Document\Entity\Type\DocumentType;
use App\Model\Expense\Entity\SchetFak\SchetFak;
use App\Model\Expense\Entity\SchetFakKor\SchetFakKor;
use App\Model\Firm\Entity\Firm\Firm;
use App\ReadModel\Expense\Filter\SchetFakKor\Filter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class SchetFakKorFetcher
{
    private $connection;
    private $repository;
    private PaginatorInterface $paginator;

    public const DEFAULT_SORT_FIELD_NAME = 'dateofadded';
    public const DEFAULT_SORT_DIRECTION = 'desc';
    public const PER_PAGE = 20;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(SchetFakKor::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): SchetFakKor
    {
        return $this->repository->get($id);
    }

    public function getNext(Firm $firm): int
    {
        return $this->getNextDocumentNumber($firm);
    }

    private function getNextDocumentNumber(Firm $firm): int
    {
        $documentNumber = $this->connection->createQueryBuilder()
            ->select('IfNull(Max(document_num), 0)')
            ->from('schet_fak_kor', 'a')
            ->andWhere('a.firmID = :firmID')
            ->setParameter('firmID', $firm->getId())
            ->andWhere('YEAR(dateofadded) = ' . (new \DateTime())->format('Y') . '')
            ->executeQuery()
            ->fetchOne();
        return $documentNumber + 1;
    }

    /**
     * @param Filter $filter
     * @param int $page
     * @param array $settings
     * @return PaginationInterface
     * @throws \Exception
     */
    public function all(Filter $filter, int $page, array $settings): PaginationInterface
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'sfk.schet_fak_korID',
                'sfk.firmID',
                'f.name_short AS firm_name',
                'sfk.dateofadded',
                'sfk.document_num',
                'u.name AS user_name',
                'u.userID',
                'u.edo',
                '(SELECT SUM(price * quantity) FROM income WHERE incomeDocumentID IN (SELECT incomeDocumentID FROM link_SFK_VZ WHERE schet_fak_korID = sfk.schet_fak_korID)) AS sum'
            )
            ->from('schet_fak_kor', 'sfk')
            ->innerJoin('sfk', 'schet_fak', 'sf', 'sfk.schet_fakID = sf.schet_fakID')
            ->innerJoin('sfk', 'firms', 'f', 'sfk.firmID = f.firmID')
            ->innerJoin('sf', 'expenseDocuments', 'ed', 'sf.expenseDocumentID = ed.expenseDocumentID')
            ->innerJoin('ed', 'users', 'u', 'ed.userID = u.userID')
        ;

        if ($filter->firmID) {
            $qb->andWhere('sfk.firmID = :firmID');
            $qb->setParameter('firmID', $filter->firmID);
        }

        if ($filter->user_name) {
            $qb->andWhere($qb->expr()->like('u.name', ':user_name'));
            $qb->setParameter('user_name', '%' . mb_strtolower($filter->user_name) . '%');
        }

        if ($filter->document_num) {
            $qb->andWhere('sfk.document_num = :document_num');
            $qb->setParameter('document_num', $filter->document_num);
        }

        if ($filter->dateofadded) {
            if ($filter->dateofadded['date_from']) {
                $qb->andWhere($qb->expr()->gte('sfk.dateofadded', ':date_from'));
                $qb->setParameter('date_from', (new \DateTime($filter->dateofadded['date_from']))->format('Y-m-d 00:00:00'));
            }
            if ($filter->dateofadded['date_till']) {
                $qb->andWhere($qb->expr()->lte('sfk.dateofadded', ':date_till'));
                $qb->setParameter('date_till', (new \DateTime($filter->dateofadded['date_till']))->format('Y-m-d 23:59:59'));
            }
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['dateofadded', 'user_name', 'firm_name', 'document_num'], true)) {
//            throw new \UnexpectedValueException('Невозможно отсортировать ' . $sort);
            $sort = 'dateofadded';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

    /**
     * @param int $document_num
     * @param int $year
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function findByDocumentNumAndYear(int $document_num, int $year): array
    {
        return $this->connection->createQueryBuilder()
            ->select(
                DocumentType::SFK . ' AS doc_typeID',
                'sfk.schet_fak_korID AS id',
                'sfk.firmID',
                'f.name_short AS from_name',
                'sfk.dateofadded',
                'sfk.document_num',
                'u.name AS to_name',
                'u.userID',
                'u.edo',
                '(SELECT SUM(price * quantity) FROM income WHERE incomeDocumentID IN (SELECT incomeDocumentID FROM link_SFK_VZ WHERE schet_fak_korID = sfk.schet_fak_korID)) AS sum'
            )
            ->from('schet_fak_kor', 'sfk')
            ->innerJoin('sfk', 'schet_fak', 'sf', 'sfk.schet_fakID = sf.schet_fakID')
            ->innerJoin('sfk', 'firms', 'f', 'sfk.firmID = f.firmID')
            ->innerJoin('sf', 'expenseDocuments', 'ed', 'sf.expenseDocumentID = ed.expenseDocumentID')
            ->innerJoin('ed', 'users', 'u', 'ed.userID = u.userID')
            ->andWhere('sfk.document_num = :document_num')
            ->setParameter('document_num', $document_num)
            ->andWhere("YEAR(sfk.dateofadded) = :year")
            ->setParameter('year', $year)
            ->orderBy('sfk.dateofadded', 'desc')
            ->executeQuery()
            ->fetchAllAssociative();
    }
}