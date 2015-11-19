<?php
namespace Method\Common\DB;

interface Config
{
    public function GetHost();
    public function GetUsername();
    public function GetPassword();
    public function GetName();
}