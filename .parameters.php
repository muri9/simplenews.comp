<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var array $arCurrentValues */

if (!\Bitrix\Main\Loader::includeModule('iblock')) return;

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$arIBlock = [];
$iblockFilter = !empty($arCurrentValues['IBLOCK_TYPE']) ? ['TYPE' => $arCurrentValues['IBLOCK_TYPE'], 'ACTIVE' => 'Y'] : ['ACTIVE' => 'Y'];
$rsIBlock = CIBlock::GetList(['SORT' => 'ASC'], $iblockFilter);
while ($arr = $rsIBlock->Fetch()) {
    $id = (int)$arr['ID'];
    $arIBlock[$id] = '[' . $id . '] ' . $arr['NAME'];
}
unset($id, $arr, $rsIBlock, $iblockFilter);

$arComponentParameters = [
    "GROUPS" => [
        "TEST" => [
            "NAME" => 'Мой компонент новостей',
        ],
    ],
    "PARAMETERS" => [
        "IBLOCK_TYPE" => [
            "PARENT" => "BASE",
            "NAME" => "IBLOCK_TYPE",
            "TYPE" => "LIST",
            "VALUES" => $arIBlockType,
            "REFRESH" => "Y",
        ],
        "IBLOCK_ID" => [
            "PARENT" => "BASE",
            "NAME" => "IBLOCK_ID",
            "TYPE" => "LIST",
            "ADDITIONAL_VALUES" => "N",
            "VALUES" => $arIBlock,
            "REFRESH" => "Y",
        ],
        "PAGE_COUNT" => [
            "PARENT" => "BASE",
            "NAME" => 'PAGE_COUNT',
            "TYPE" => "STRING",
            "DEFAULT" => "10",
        ],
        "CACHE_TYPE" => [
            "DEFAULT" => "A",
        ],
        "CACHE_TIME" => [
            "DEFAULT" => "3600",
        ]
    ],
];
