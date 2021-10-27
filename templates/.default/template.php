<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 */

?>
<section>
    <div style="margin: 10px; padding: 10px; border: 1px solid gray">
        <?php foreach ($arResult['YEARS'] as $year) { ?>
            <a <?= $year['ACTIVE'] ? 'style="font-weight: bold;"' : '' ?>
                    href="<?= $year['URL'] ?>"><?= $year['VALUE'] ?></a>
        <?php } ?>
    </div>
    <div style="margin: 10px; padding: 10px; border: 1px solid gray">
        <?php foreach ($arResult['ITEMS'] as $item) {
            $this->AddEditAction($item['ID'], $item['EDIT_LINK'], CIBlock::GetArrayByID($item["IBLOCK_ID"], "ELEMENT_EDIT"));
            ?>
            <div style="margin: 10px; padding: 10px; background-color: ghostwhite"
                 id="<?= $this->GetEditAreaId($item['ID']); ?>">
                <p><?= $item['ACTIVE_FROM'] ?></p>
                <p><img width="50px" height="50px" src="<?= $item['PREVIEW_PICTURE'] ?>"></p>
                <p><?= $item['NAME'] ?></p>
                <p><?= $item['PREVIEW_TEXT'] ?></p>
            </div>
        <?php } ?>
    </div>
    <?php if ($arResult['PAGE_COUNT'] > 1) { ?>
        <nav style="margin: 10px; padding: 10px; border: 1px solid gray">
            <?= $arResult["NAV_STRING"] ?>
        </nav>
    <? } ?>
</section>
