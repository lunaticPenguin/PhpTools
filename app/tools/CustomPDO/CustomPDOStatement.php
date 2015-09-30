<?php

namespace App\Tools\CustomPDO;

/**
 * Class CustomPDOStatement (Decorator DP).
 * Used to bound data to raw queries, in order to log or to debug them.
 *
 * @package App\Tools\CustomPDO
 */
class CustomPDOStatement
{
    /**
     * @var CustomPDO
     */
    protected $objLogPDO;

    /**
     * @var \PDOStatement
     */
    protected $objStatement;

    protected $hashBoundParams = array();

    public function __construct(\PDOStatement $objStatement)
    {
        $this->objStatement = $objStatement;
    }

    /**
     * @param CustomPDO $objLogPDO
     */
    public function setLogPDO(CustomPDO $objLogPDO)
    {
        $this->objLogPDO = $objLogPDO;
    }

    /**
     * Bound values to specific parameter for the current query
     * @param string $parameter
     * @param mixed $value
     * @param int $data_type
     */
    public function bindValue($parameter, $value, $data_type = \PDO::PARAM_STR)
    {
        $this->hashBoundParams[$parameter] = array('value' => $value, 'type' => $data_type);
        $this->objStatement->bindValue($parameter, $value, $data_type);
    }

    /**
     * Execute the prepared internal statement
     * @param array $input_parameters
     * @return bool
     */
    public function execute(array $input_parameters = null)
    {
        $strBuildQuery = CustomPDO::formatQueryString($this->objStatement->queryString);
        foreach ($this->hashBoundParams as $strField => $hashInfos) {
            if (strpos($strField, ':') === false) {
                $strField = ':' . $strField;
            }
            $strBuildQuery = str_replace($strField, $this->findSuitableReplacement($hashInfos['value'], $hashInfos['type']), $strBuildQuery);
        }
        $this->objLogPDO->notify(CustomPDO::formatQueryString($this->objStatement->queryString), $strBuildQuery);
        return $this->objStatement->execute($input_parameters);
    }

    /**
     * Determines bound values (results can be approximated)
     * Permet de déterminer la valeur des binds (peuvent être approximées, mais sert juste à avoir une idée de la requête finale)
     *
     * @param mixed $mixedValue
     * @param int $intType
     * @return int|string
     */
    protected function findSuitableReplacement($mixedValue, $intType)
    {
        switch ($intType) {
            case \PDO::PARAM_STR:
                return "'" . $mixedValue . "'";
                break;
            default:
            case \PDO::PARAM_BOOL:
            case \PDO::PARAM_INT:
                return (int) $mixedValue;
                break;
            case \PDO::PARAM_NULL:
                return 'null';
                break;
        }
    }

    /**
     * Delegates methods calls to the real (internal) PDOStatement instance
     *
     * @param string $strMethod
     * @param array $hashParams
     * @return mixed
     */
    public function __call($strMethod, $hashParams)
    {
        return call_user_func_array(array($this->objStatement, $strMethod), $hashParams);
    }

    /**
     * Fetch single result or empty array (if no results)
     *
     * @param int $intFetchType
     * @return array
     */
    public function singleResult($intFetchType = \PDO::FETCH_ASSOC)
    {
        if ($this->objStatement->rowCount() > 0) {
            return $this->objStatement->fetch($intFetchType);
        }

        // todo: change default result, depending on $intFetchType value
        return array();
    }

    /**
     * Fetch multiple results or empty array (if no results)
     * @param int $intFetchType
     * @return array
     */
    public function multipleResult($intFetchType = \PDO::FETCH_ASSOC)
    {
        if ($this->objStatement->rowCount() > 0) {
            return $this->objStatement->fetchAll($intFetchType);
        }

        // todo: change default result, depending on $intFetchType value
        return array();
    }
}
