<?php

namespace Method\Common\Interfaces;

interface WhereQueryBuilder
{
    public function GetLimit();
    public function GetOrder($table);
    public function GetWhere($table, $keywordField, $extras = TRUE);
    public function GetGroupBy();
    public function GetJoins();
}