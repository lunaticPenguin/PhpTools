<?php

namespace App\Tools\CustomPDO;

/**
 * Class CustomPDO.
 * Extends PDO class to allow logging and/or debugging
 *
 * @package App\Tools\CustomPDO
 */
class CustomPDO extends \PDO
{
    /**
     * Contains raw query
     * @var string
     */
    protected $strRawQuery = '';

    /**
     * Contains mapped query
     * @var string
     */
    protected $strBuiltQuery = '';

    /**
     * @inheritdoc
     */
    public function __construct($dsn, $username = '', $passwd = '', $options = array())
    {
        parent::__construct($dsn, $username, $passwd, $options);
    }

    /**
     * @inheritdoc
     */
    public function prepare($strStatement, $options = array())
    {
        $strStatement = self::formatQueryString($strStatement);
        $objStatement = parent::prepare($strStatement, $options);
        $objLogPDOStatement = new CustomPDOStatement($objStatement);
        $objLogPDOStatement->setLogPDO($this);
        return $objLogPDOStatement;
    }

    /**
     * Called when CustomPDOStatement::execute() is executed. It stores raw and built queries.
     *
     * @param string $strRawQuery
     * @param string $strBuiltQuery
     */
    public function notify($strRawQuery, $strBuiltQuery)
    {
        $this->strRawQuery= $strRawQuery;
        $this->strBuiltQuery = $strBuiltQuery;
    }

    /**
     * Returns the last built query
     * @return string
     */
    public function getLastQuery()
    {
        return $this->strBuiltQuery;
    }

    /**
     * Formats and returns the querystring (<=> trim)
     * @param string $strQueryString
     * @return string
     */
    public static function formatQueryString($strQueryString)
    {
        $strQueryString = preg_replace('/\n|\t|\r|[ ]+/', ' ', $strQueryString);
        return preg_replace('/\n|\t|\r|[ ]+/', ' ', $strQueryString);
    }
}
