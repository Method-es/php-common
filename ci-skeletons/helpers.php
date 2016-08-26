<?php

function GetResults($query, $singletonPool = FALSE)
{

    if($query === FALSE){
        throw new Exception("DB ERROR");
    }

    $rows = [];
    if(!$singletonPool)
    {
        foreach($query->result() as $row){
            $rows[] = $row;
        }
    }else
    {
        foreach($query->result() as $row){
            $rows[] = $singletonPool::GetByData($row);
        }
    }
    return $rows;
}

function GetResult($query, $singletonPool = FALSE) {
    $result = GetResults($query,$singletonPool);

    if(!empty($result)) {
        return $result[0];
    }
    return FALSE;
}

function GetFieldList($fields, $table, $exclusion = [], $aliasPrefix = "")
{
    $list = [];
    foreach($fields as $field)
    {
        if(in_array($field, $exclusion))
            continue;
        $word = "`{$table}`.`{$field}`";
        if(!empty($aliasPrefix)){
            $word .= " as `{$aliasPrefix}{$field}`";
        }
        $list[] = $word;
    }
    return implode(", ", $list);
}

function GetInsertPhrase($fields, $object, $exclusion = [])
{
    $CI = GetController();
    $list = [];
    foreach($fields as $field)
    {
        if(in_array($field, $exclusion))
            continue;
        $list[] = $CI->db->escape($object->$field);
    }
    return implode(", ", $list);
}

function GetUpdatePhrase($fields, $object, $table, $exclusion = [])
{
    $CI = GetController();
    $list = [];
    foreach($fields as $field)
    {
        if(in_array($field, $exclusion))
            continue;
        $list[] = "`{$table}`.`{$field}` = ".$CI->db->escape($object->$field);
    }
    return implode(", ", $list);
}

function GetOnDuplicatePhrase($fields, $table, $exclusion = [])
{
    $list = [];
    foreach($fields as $field)
    {
        if(in_array($field, $exclusion))
            continue;
        $list[] = "`{$table}`.`{$field}`=VALUES(`{$table}`.`{$field}`)";
    }
    return implode(", ", $list);
}

function UintToRGB($value, $excludeAlpha = TRUE)
{
    // $red = ($value >> 16) & 0xFF;
    // $green = ($value >> 8) & 0xFF;
    // $blue = ($value >> 0) & 0xFF;
    // return "#".$red
    if(!is_numeric($value))
        return "";
    if($excludeAlpha)
        return "#".dechex(($value & 0xFFFFFF)+0);
    else
        return "#".dechex($value+0);
}

function RGBToUint($value, $excludeAlpha = TRUE)
{
    $hex = hexdec($value);
    return "#".(0xFF & ($hex >> 0x10)).(0xFF & ($hex >> 0x8)).(0xFF & $hex);
}

function HandleUploadErrors($error)
{
    $msg = "";
    if(empty($error))
        return $msg;

    switch ($error)
    {
        case UPLOAD_ERR_OK:
            $msg = ""; // should not hit as success is handled above
            break;
        case UPLOAD_ERR_INI_SIZE:
            $msg = "The file is bigger than allowed";
            break;
        case UPLOAD_ERR_FORM_SIZE:
            $msg = "The file is bigger than this form allows";
            break;
        case UPLOAD_ERR_PARTIAL:
            $msg = "Only part of the file was uploaded";
            break;
        case UPLOAD_ERR_NO_FILE:
            $msg = "No file was uploaded";
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            $msg = "Missing a temporary folder";
            break;
        case UPLOAD_ERR_CANT_WRITE:
            $msg = "Failed to write file to disk";
            break;
        case UPLOAD_ERR_EXTENSION:
            $msg = "File upload stopped by extension";
            break;
        default:
            $msg = "unknown error ".$_FILES["Filedata"]['error'];
            break;
    }

    return $msg;
}


function endsWith($haystack, $needle)
{
    return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}
function startsWith($haystack, $needle)
{
    return $needle === "" || strpos($haystack, $needle) === 0;
}
