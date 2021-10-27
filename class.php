<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Context;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Web\Uri;

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 */

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('iblock')) {
    ShowError(Loc::getMessage('IBLOCK_MODULE_NOT_INSTALLED'));
    return;
}

class SimpleNews extends CBitrixComponent
{
    private $nav;

    public function executeComponent()
    {

        $this->getData();

        global $APPLICATION;
        $APPLICATION->setTitle("Список новостей ({$this->arResult['COUNT']} шт.)");

        $this->includeComponentTemplate();
    }

    private function getData()
    {
        $this->nav = new PageNavigation("p");
        $this->nav->allowAllRecords(false)
            ->setPageSize($this->arParams['PAGE_COUNT'])
            ->initFromUri();

        $request = Context::getCurrent()->getRequest();

        $year = intval($request->get('year'));
        if ($year > 0) {
            $fromDT = \Bitrix\Main\Type\DateTime::createFromTimestamp(strtotime("first day of january $year"));
            $toDT = \Bitrix\Main\Type\DateTime::createFromTimestamp(strtotime("last day of december $year"));
        } else {
            $year = date('Y');
            $fromDT = \Bitrix\Main\Type\DateTime::createFromTimestamp(strtotime('first day of january this year'));
            $toDT = \Bitrix\Main\Type\DateTime::createFromTimestamp(strtotime('last day of december this year'));
        }
        $this->arResult['YEAR'] = $year;

        $filter = [
            'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
            'ACTIVE' => 'Y',
            ">=ACTIVE_FROM" => $fromDT, "<=ACTIVE_FROM" => $toDT, '!ACTIVE_FROM' => false
        ];

        if ($this->StartResultCache($this->arParams['CACHE_TIME'], [$this->arParams['IBLOCK_ID'], $this->arParams['PAGE_COUNT']])) {

            $query = new Query(Bitrix\Iblock\ElementTable::getEntity());
            $query->registerRuntimeField("YEAR", [
                "data_type" => "Datetime", "expression" => ["YEAR(ACTIVE_FROM)", "ACTIVE_FROM"]
            ])->setSelect(['YEAR'])
                ->setFilter(["IBLOCK_ID" => $this->arParams["IBLOCK_ID"], 'ACTIVE' => 'Y', '!ACTIVE_FROM' => false])
                ->setOrder(["YEAR" => "DESC"])
                ->setGroup(["YEAR"]);
            $res = $query->exec();
            $years = $res->fetchAll();

            $uri = new Uri($request->getRequestedPageDirectory());
            $this->arResult["YEARS"] = array_map(fn($el) => [
                'VALUE' => $el['YEAR'],
                'URL' => $el['YEAR'] == date('Y') ? $uri->deleteParams(['year'])->getUri() : $uri->addParams(['year' => $el['YEAR']])->getUri(),
                'ACTIVE' => $el['YEAR'] == $year
            ], $years);

            $list = ElementTable::getList([
                "order" => ['ACTIVE_FROM' => 'DESC'],
                "select" => ['ID', 'IBLOCK_ID', 'NAME', 'ACTIVE_FROM', 'PREVIEW_TEXT', 'PREVIEW_PICTURE'],
                "filter" => $filter,
                "count_total" => true,
                "offset" => $this->nav->getOffset(),
                "limit" => $this->nav->getLimit(),
            ]);

            $this->arResult["COUNT"] = $list->getCount();
            $this->nav->setRecordCount($this->arResult["COUNT"]);

            $this->arResult['ITEMS'] = [];
            while ($item = $list->fetch()) {
                $arButtons = CIBlock::GetPanelButtons($item["IBLOCK_ID"], $item["ID"], 0, ["SECTION_BUTTONS" => false, "SESSID" => false]);
                $item["EDIT_LINK"] = $arButtons["edit"]["edit_element"]["ACTION_URL"];

                if ($item['PREVIEW_PICTURE']) $item['PREVIEW_PICTURE'] = CFile::GetPath($item['PREVIEW_PICTURE']);
                $this->arResult['ITEMS'][] = $item;
            }

            ob_start();
            global $APPLICATION;
            $APPLICATION->IncludeComponent("bitrix:main.pagenavigation", "",
                ["NAV_OBJECT" => $this->nav, "SEF_MODE" => "N"], false, ['HIDE_ICONS' => 'Y']
            );
            $navString = ob_get_contents();
            ob_end_clean();

            $this->arResult["NAV_STRING"] = $navString;
            $this->arResult["PAGE_COUNT"] = $this->nav->getPageCount();

            $this->endResultCache();
        }

    }
}
