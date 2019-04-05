<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogElementComponent $component
 */
$s = new Stock();
$onlineProduct = 0;
$offlineProduct = 0;
$size = 'N';
$sizeItemsStr = '';

$arSectId = array();
$navChain = CIBlockSection::GetNavChain(2, $arResult['IBLOCK_SECTION_ID']);
while ($arNav = $navChain->GetNext()) {
    $arSectId[] = $arNav['ID'];
}
foreach ($arResult['OFFERS'] as &$offer) {
    $arOffer['STOCK'] = $s->getStock($offer['ID'], $_COOKIE['CityID']);

    $onlineProduct = $onlineProduct + $arOffer['STOCK']['result']['PRODUCT_AMOUNT']['ONLINE'];
    $offlineProduct = $offlineProduct + $arOffer['STOCK']['result']['PRODUCT_AMOUNT']['OFFLINE'];

    $db_props = CIBlockElement::GetByID($offer['PROPERTIES']['SIZE']['VALUE']);
    if ($obRes = $db_props->GetNextElement()) {
        $ar_props = $obRes->GetFields();

        if (in_array(MALE_SECT, $arSectId)) {
            $originSize = $obRes->GetProperty("ORIGIN_SIZE");
        } elseif (in_array(FEMALE_SECT, $arSectId)) {
            $originSize = $obRes->GetProperty("FEMALE_SIZE");
        } elseif (in_array(CHILD_SECT, $arSectId)) {
            $originSize = $obRes->GetProperty("CHILD_SIZE");
        } else {
            $originSize['VALUE'] = '';
        }
        $size = 'Y';
        $offer['PROPERTIES']['SIZES_SHOES']['VALUE_SIZE'] = $ar_props['NAME'];
        $offer['PROPERTIES']['SIZES_SHOES']['RU_VALUE_SIZE'] = $originSize['VALUE'];
        $offer['PROPERTIES']['SIZES_SHOES']['ONLINE'] = $arOffer['STOCK']['result']['PRODUCT_AMOUNT']['ONLINE'];
        $offer['PROPERTIES']['SIZES_SHOES']['OFFLINE'] = $arOffer['STOCK']['result']['PRODUCT_AMOUNT']['OFFLINE'];
    } else {
        $offer['PROPERTIES']['SIZES_SHOES']['VALUE_SIZE'] = '';
        $offer['PROPERTIES']['SIZES_SHOES']['RU_VALUE_SIZE'] = '';
        $offer['PROPERTIES']['SIZES_SHOES']['ONLINE'] = 0;
        $offer['PROPERTIES']['SIZES_SHOES']['OFFLINE'] = 0;
    }
    if (($arOffer['STOCK']['result']['PRODUCT_AMOUNT']['ONLINE'] > 0)
        || ($arOffer['STOCK']['result']['PRODUCT_AMOUNT']['OFFLINE'] > 0)) {
        $sizeItemsStr .= $ar_props['NAME'] . ',';
    }

    if (is_array($arOffer['STOCK']['result']['STOCK_KEY_ID'])) {
        foreach ($arOffer['STOCK']['result']['STOCK_KEY_ID'] as $oneStcok) {
            if (array_key_exists($oneStcok['ID'], $arResult['arSTOCK'])) {
                if (!in_array(
                    $offer['PROPERTIES']['SIZES_SHOES']['VALUE_SIZE'],
                    $arResult['arSTOCK'][$oneStcok['ID']]['PRODUCT_SIZE']
                )
                ) {
                    $arResult['arSTOCK'][$oneStcok['ID']]['PRODUCT_SIZE'][] =
                        $offer['PROPERTIES']['SIZES_SHOES']['VALUE_SIZE'];
                }
            } else {
                $oneStcok['PRODUCT_SIZE'][] = $offer['PROPERTIES']['SIZES_SHOES']['VALUE_SIZE'];
                $arResult['arSTOCK'][$oneStcok['ID']] = $oneStcok;
            }
        }
    };
}

$arResult['SIZE_CHECK'] = $size;
$arResult['ONLINE'] = $onlineProduct;
$arResult['OFFLINE'] = $offlineProduct;

$component = $this->getComponent();

if (is_object($component)) {
    $colorStr = '';
    $hellStr = '';
    $sizeStr = '';
    $podoshavaStr = '';
    $materialStr = '';
    if (!empty($sizeItemsStr)) {
        $sizeItemsStr = mb_substr($sizeItemsStr, 0, -1);
        $sizeStr = ' размеры ' . $sizeItemsStr;
    }
    if (count($arResult['PROPERTIES']['COLOR']['VALUE']) > 0) {
        $colorStr = ' цвет ';
        foreach ($arResult['PROPERTIES']['COLOR']['VALUE'] as $keyColor => $itemColor) {
            $color = CIBlockElement::GetByID($itemColor);
            if ($ar_color = $color->GetNext()) {
                $colorStr .= $ar_color['NAME'];
                if (count($arResult['PROPERTIES']['COLOR']['VALUE']) > 1 &&
                    count($arResult['PROPERTIES']['COLOR']['VALUE']) - 1 != $keyColor) {
                    $colorStr .= ', ';
                }
            }
        }
    }
    if (count($arResult['PROPERTIES']['HEEL']['VALUE']) > 0) {
        $hellStr = ' ';
        foreach ($arResult['PROPERTIES']['HEEL']['VALUE'] as $keyHeel => $itemHeel) {
            $heel = CIBlockElement::GetByID($itemHeel);
            if ($ar_heel = $heel->GetNext()) {
                $hellStr .= $ar_heel['NAME'];
                $hellStr .= ', ';
            }
        }
    }
    if (count($arResult['PROPERTIES']['SKIN_TYPE']['VALUE']) > 0) {
        foreach ($arResult['PROPERTIES']['SKIN_TYPE']['VALUE'] as $keySkin_type => $itemSkin_type) {
            $skin_type = CIBlockElement::GetByID($itemSkin_type);
            if ($ar_skin_type = $skin_type->GetNext()) {
                $podoshavaStr .= $ar_skin_type['NAME'];
                $podoshavaStr .= ', ';
            }
        }
    }

    if (count($arResult['PROPERTIES']['MATERIAL']['VALUE']) > 0) {
        foreach ($arResult['PROPERTIES']['MATERIAL']['VALUE'] as $keyMaterial => $itemMaterial) {
            $material = CIBlockElement::GetByID($itemMaterial);
            if ($ar_material = $material->GetNext()) {
                $materialStr .= $ar_material['NAME'];
                if (count($arResult['PROPERTIES']['MATERIAL']['VALUE']) > 1 &&
                    count($arResult['PROPERTIES']['MATERIAL']['VALUE']) - 1 != $keyMaterial) {
                    $materialStr .= ', ';
                }
            }
        }
    }

    $component->arResult['title_custom'] = mb_ucfirst($arResult['NAME']) . $colorStr . $sizeStr .
        " по цене " . round($arResult['PROPERTIES']['OPTIMAL_PRICE']['VALUE']) .
        " руб, артикул " . $arResult['PROPERTIES']['ARTNUMBER']['VALUE'];
    $component->arResult['description_custom'] = mb_ucfirst($arResult['NAME']) . " артикул " .
        $arResult['PROPERTIES']['ARTNUMBER']['VALUE'] . ',' . $colorStr . ',' . $hellStr . $podoshavaStr . $materialStr .
        ' продаются в официальном интернет-магазине Salamander в России';
    $component->SetResultCacheKeys(array("title_custom", "description_custom"));
}
$arParams = $component->applyTemplateModifications();