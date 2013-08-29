<?php
namespace Hostnet\HnEntitiesPlugin;
use Doctrine\ORM\QueryBuilder;

/**
 * Paginator for Doctrine 2.3+
 *
 * @author Nico Schoenmaker <nico@hostnet.nl>
 */
class DoctrinePager extends \sfPager
{

    private $query_builder;

    private $result_query;

    /**
     * @param QueryBuilder $query_builder The query to paginate
     */
    public function setQueryBuilder(QueryBuilder $query_builder)
    {
        $this->query_builder = $query_builder;
    }

    /**
     * @throws DomainException
     * @return QueryBuilder
     */
    private function getNewQueryBuilder()
    {
        if (! ($this->query_builder instanceof QueryBuilder)) {
            throw new \DomainException('Call setQueryBuilder first!');
        }
        return clone $this->query_builder;
    }

    private function getAlias(QueryBuilder $builder)
    {
        $dql_part = $builder->getDQLPart('from');

        if (! is_array($dql_part) || count($dql_part) !== 1 ||
                 ! ($dql_part[0] instanceof \Doctrine\ORM\Query\Expr\From)) {
            throw new \DomainException('The from should be an instance of from');
        }
        return $dql_part[0]->getAlias();
    }

    /**
     * function to be called after parameters have been set
     *
     * @see sfPager::init()
     */
    public function init()
    {
        $has_max_record_limit = ($this->getMaxRecordLimit() !== false);
        $max_record_limit = $this->getMaxRecordLimit();

        $builder = $this->getNewQueryBuilder();
        $builder->setFirstResult(0);
        $builder->setMaxResults(1);
        $builder->select('COUNT(' . $this->getAlias($builder) . ')');

        $result = $builder->getQuery()->execute();
        if (! isset($result[0][1])) {
            throw new \RuntimeException(
                    sprintf(
                            'The indice [0][1] is supposed to have the count in it! %s',
                            json_encode($result)));
        }
        $count = $result[0][1];
        $this->setNbResults(
                $has_max_record_limit ? min($count, $max_record_limit) : $count);

        $builder = $this->getNewQueryBuilder();
        $builder->setFirstResult(0);
        $builder->setMaxResults(0);

        if (($this->getPage() == 0 || $this->getMaxPerPage() == 0)) {
            $this->setLastPage(0);
        } else {
            $this->setLastPage(
                    ceil($this->getNbResults() / $this->getMaxPerPage()));

            $offset = ($this->getPage() - 1) * $this->getMaxPerPage();
            $builder->setFirstResult($offset);

            if ($has_max_record_limit) {
                $max_record_limit = $max_record_limit - $offset;
                if ($max_record_limit > $this->getMaxPerPage()) {
                    $builder->setMaxResults($this->getMaxPerPage());
                } else {
                    $builder->setMaxResults($max_record_limit);
                }
            } else {
                $builder->setMaxResults($this->getMaxPerPage());
            }
        }
        $this->result_query = $builder->getQuery();
    }

    /**
     * main method: returns an array of result on the given page
     *
     * @see sfPager::getResults()
     */
    public function getResults()
    {
        return $this->result_query->execute();
    }

    /**
     * used internally by getCurrent()
     *
     * @see sfPager::retrieveObject()
     */
    protected function retrieveObject($offset)
    {
        throw new \RuntimeException('RetrieveObject is not yet supported here');
    }
}
