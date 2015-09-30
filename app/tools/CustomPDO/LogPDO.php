<?php

namespace App\Tools\CustomPDO;

/**
 * Class LogPDO.
 *
 * Extends PDO class in order to collect data from queries execution.
 * Provides duration and counts queries.
 * Results from "SHOW PROFILES" query are crossed with collected data to get more precision.
 *
 * @package App\Tools\CustomPDO
 */
class LogPDO extends CustomPDO
{
    protected $arrayQueries = array();

    /**
     * Makes link between logged queries and the queries fetch by the "SHOW PROFILES" query
     *
     * @var array
     */
    protected $arrayQueryIds = array();

    protected $intQueryStringIndex = 0;
    protected $hashQueryIndexes = array();

    protected $intQueryIndex = 0;

    /**
     * @inheritdoc
     */
    public function __construct($dsn, $username = '', $passwd = '', $options = array())
    {
        parent::__construct($dsn, $username, $passwd, $options);
        $this->exec('SET PROFILING = 0, PROFILING_HISTORY_SIZE = 0, PROFILING_HISTORY_SIZE = 100, PROFILING = 1');
    }

    /**
     * @inheritdoc
     */
    public function prepare($statement, $options = array())
    {
        $statement = self::formatQueryString($statement);
        $strKey = md5($statement);
        if (!isset($this->hashQueryIndexes[$strKey])) {
            $this->hashQueryIndexes[$strKey] = $this->intQueryStringIndex;
            $this->arrayQueries[] = array(
                'raw' => $statement,
                'average_duration' => 0.0,
                'total_duration' => 0.0,
                'count_prepare' => 1,
                'count_execute' => 0,
                'queries'   => array()
            );
            ++$this->intQueryStringIndex;
        } else {
            ++$this->arrayQueries[$this->hashQueryIndexes[$strKey]]['count_prepare'];
        }

        $objLogPDOStatement= parent::prepare($statement, $options);
        $objLogPDOStatement->setLogPDO($this);
        return $objLogPDOStatement;
    }

    /**
     * Notify the custom PDO instance about the statement execution
     *
     * @param string $strRawQuery
     * @param string $strBuildQuery
     */
    public function notify($strRawQuery, $strBuildQuery)
    {
        parent::notify($strRawQuery, $strBuildQuery);

        $strKey = md5($strRawQuery);
        $this->arrayQueryIds[] = $this->hashQueryIndexes[$strKey];
        ++$this->arrayQueries[$this->hashQueryIndexes[$strKey]]['count_execute'];
        $this->arrayQueries[$this->hashQueryIndexes[$strKey]]['queries'][$this->intQueryIndex] = array(
            'query' => $strBuildQuery,
            'duration' => 0.0
        );
        ++$this->intQueryIndex;
    }

    /**
     * Returns collected data with duration (milliseconds) and details for each logged query
     * @return array
     */
    public function getDetailedLogs()
    {
        $intCountQueries = 0;
        $this->exec('SET PROFILING = 0');
        $objStatement = $this->query('SHOW PROFILES');
        $arrayResult = $objStatement->fetchAll(\PDO::FETCH_ASSOC);
        $floatTotalDuration = 0.0;
        foreach ($arrayResult as $hashQueryInfos) {
            $intQueryId = (int) $hashQueryInfos['Query_ID'] - 1;
            $hashQueryInfos['Duration'] = (float) $hashQueryInfos['Duration'] * 1000;
            $this->arrayQueries[$this->arrayQueryIds[$intQueryId]]['total_duration'] += (float) $hashQueryInfos['Duration'];
            $this->arrayQueries[$this->arrayQueryIds[$intQueryId]]['queries'][$intQueryId]['duration'] = $hashQueryInfos['Duration'];
            $floatTotalDuration += (float) $hashQueryInfos['Duration'];
        }

        // average execution time for a query
        foreach ($this->arrayQueries as $intIndex => $hashQueryInfos) {
            $intNbQueries = count($hashQueryInfos['queries']);
            $intCountQueries += $intNbQueries;
            $this->arrayQueries[$intIndex]['average_duration'] = $hashQueryInfos['total_duration'] / ($intNbQueries > 0 ? $intNbQueries : 1);
        }

        return array(
            'QUERIES_DURATION'  => $floatTotalDuration . 'ms', // milliseconds
            'QUERIES_QUOTA'     => $intCountQueries,
            'QUERIES_DETAILS'   => $this->arrayQueries
        );
    }

    /**
     * Begins a transaction
     * @return boolean
     */
    public function beginTransaction()
    {
        $this->addCustomQuery('BEGIN TRANSACTION');
        return parent::beginTransaction();
    }

    /**
     * Closes a transaction
     * @return boolean
     */
    public function commit()
    {
        $this->addCustomQuery('COMMIT');
        return parent::commit();
    }

    /**
     * Rollbacks a transaction
     * @return boolean
     */
    public function rollBack()
    {
        $this->addCustomQuery('ROLLBACK');
        return parent::rollBack();
    }

    /**
     * Log a custom query (simulates a "prepare" call and then simulates "execute" call)
     * Used only for transactions
     * @param string $strQuery
     */
    protected function addCustomQuery($strQuery)
    {
        $statement = self::formatQueryString($strQuery);
        $strKey = md5($statement);
        if (!isset($this->hashQueryIndexes[$strKey])) {
            $this->hashQueryIndexes[$strKey] = $this->intQueryStringIndex;
            $this->arrayQueries[] = array(
                'raw' => $statement,
                'average_duration' => 0.0,
                'total_duration' => 0.0,
                'count_prepare' => 1,
                'count_execute' => 1,
                'queries'   => array()
            );
            ++$this->intQueryStringIndex;
        } else {
            ++$this->arrayQueries[$this->hashQueryIndexes[$strKey]]['count_prepare'];
        }

        $this->arrayQueryIds[] = $this->hashQueryIndexes[$strKey];
        $this->arrayQueries[$this->hashQueryIndexes[$strKey]]['queries'][$this->intQueryIndex] = array(
            'query' => $strQuery,
            'duration' => 0.0
        );
        ++$this->intQueryIndex;
    }
}
