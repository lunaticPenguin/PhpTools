<?php

namespace App\Models;

abstract class AbstractNoSqlModel extends AbstractModel
{
    const PARAM_NULL = 0;
    const PARAM_INT = 1;
    const PARAM_STR = 2;
    const PARAM_BOOL = 5;

    protected static $hashInfos = array();
}