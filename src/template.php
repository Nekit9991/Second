<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

//if(!empty($arResult['IPROPERTY_VALUES']['ELEMENT_META_TITLE'])){
//    $APPLICATION->SetTitle($arResult['IPROPERTY_VALUES']['ELEMENT_META_TITLE']);
//}
use \Bitrix\Main\Localization\Loc;

$s = new Stock;
$f = new Favorite;
$arImage = array();
$arPagination = array();


function cmp($a, $b){if ($a['PROPERTIES']['SIZES_SHOES']['VALUE_SIZE'] == $b['PROPERTIES']['SIZES_SHOES']['VALUE_SIZE']) {
        return 0;
    }
    return ($a['PROPERTIES']['SIZES_SHOES']['VALUE_SIZE'] < $b['PROPERTIES']['SIZES_SHOES']['VALUE_SIZE']) ? -1 : 1;
}
usort($arResult['OFFERS'], "cmp");

if (!empty($arResult['~PREVIEW_PICTURE'])) {
    $arImage['RESIZE'][] = CFile::ResizeImageGet(
        $arResult['~PREVIEW_PICTURE'],
        array("width" => 400, "height" => 535),
        BX_RESIZE_IMAGE_PROPORTIONAL
    );
    $arPagination[] = CFile::ResizeImageGet(
        $arResult['~PREVIEW_PICTURE'],
        array("width" => 64, "height" => 90),
        BX_RESIZE_IMAGE_PROPORTIONAL
    );

    //для модалки купить в один клик
    $oneImage = CFile::ResizeImageGet(
        $arResult['~PREVIEW_PICTURE'],
        array("width" => 100, "height" => 130),
        BX_RESIZE_IMAGE_PROPORTIONAL
    );

    $arImage['ORIGIN'][] = CFile::GetPath($arResult["~PREVIEW_PICTURE"]);
    if (!empty($arResult['~DETAIL_PICTURE'])) {
        $arImage['RESIZE'][] = CFile::ResizeImageGet(
            $arResult['~DETAIL_PICTURE'],
            array("width" => 400, "height" => 535),
            BX_RESIZE_IMAGE_PROPORTIONAL
        );
        $arImage['ORIGIN'][] = CFile::GetPath($arResult["~DETAIL_PICTURE"]);

        $arPagination[] = CFile::ResizeImageGet(
            $arResult['~DETAIL_PICTURE'],
            array("width" => 64, "height" => 90),
            BX_RESIZE_IMAGE_PROPORTIONAL
        );
        if(empty($oneImage)){
            //для модалки купить в один клик
            $oneImage = CFile::ResizeImageGet(
                $arResult['~DETAIL_PICTURE'],
                array("width" => 100, "height" => 130),
                BX_RESIZE_IMAGE_PROPORTIONAL
            );
        }

    }
} else {
    $arImage['RESIZE'][] = array('src' => ELEMPL);
    $oneImage = array('src' => PAGONE);
    $arImage['ORIGIN'][] = ELEMPL;
    $arPagination[] = array('src' => PAGPL);
}

if (!empty($arResult['PROPERTIES']['MORE_PHOTO']['VALUE'])) {
    foreach ($arResult['PROPERTIES']['MORE_PHOTO']['VALUE'] as $imageItems) {
        $arImage['RESIZE'][] = CFile::ResizeImageGet(
            $imageItems,
            array("width" => 400, "height" => 535),
            BX_RESIZE_IMAGE_PROPORTIONAL
        );

        $arPagination[] = CFile::ResizeImageGet(
            $imageItems,
            array("width" => 64, "height" => 90),
            BX_RESIZE_IMAGE_PROPORTIONAL
        );
        $arImage['ORIGIN'][] = CFile::GetPath($imageItems);

    }
}

if (!isset($_COOKIE['PRODUCT_VIEW']) || $_COOKIE['PRODUCT_VIEW'] == 'null') {
    $value = json_encode((array)$arResult['ID']);
    setcookie("PRODUCT_VIEW", $value, time() + 86400, "/");
    // todo: update  $_SERVER['HTTP_HOST'] change $_SERVER["HTTPS"]
} else {
    if (isset($_COOKIE['PRODUCT_VIEW']) || $_COOKIE['PRODUCT_VIEW'] != 'null') {
        $arr_cook = json_decode($_COOKIE['PRODUCT_VIEW'], true);
        $count = count($arr_cook);
        if ($count < 12) {
            $value = $_COOKIE['PRODUCT_VIEW'];
            $value = json_decode($value, true);
            if (in_array($arResult['ID'], $value)) {
            } else {
                array_unshift($value, $arResult['ID']);
                $value = json_encode($value);
                setcookie("PRODUCT_VIEW", $value, time() + 86400, "/");
            }
        } else if ($count >= 12) {
            $value = $_COOKIE['PRODUCT_VIEW'];
            $value = json_decode($value, true);
            if (in_array($arResult['ID'], $value)) {
            } else {
                array_pop($value);
                array_unshift($value, $arResult['ID']);
                $value = json_encode($value);
                setcookie("PRODUCT_VIEW", $value, time() + 86400, "/");
            }
        }
    }
}
?>
<?//='<pre>'.print_r($arResult, true).'</pre>'?>
<?

$res = CIBlockElement::GetList(
    array("sort" => 'asc'),
    Array(
        "IBLOCK_ID" => $arResult["IBLOCK_ID"],
        "ACTIVE_DATE" => "Y", "ACTIVE" => "Y",
        "IBLOCK_SECTION_ID" =>
            $arResult["IBLOCK_SECTION_ID"]
    ),
    false,
    array(),
    Array("ID", "DETAIL_PAGE_URL")
);
$navElement = array();
$countTwo = false;

if (intval($res->SelectedRowsCount()) > 3) {
    $i = 0;
    $countTwo = true;
    while ($ob = $res->GetNext()) {

        if ($ob['ID'] == $arResult['ID']) {
            $itemKey = $i;
        }
        $navElement[] = $ob;
        $i++;

    }
} else {
    $countTwo = false;
}
if ($countTwo) {
    if ($itemKey === 0) {
        $arNextPrev['NEXT'] = $navElement[$itemKey + 1];
        $arNextPrev['PREV'] = $navElement[count($navElement) - 1];
    } else if ($itemKey === count($navElement) - 1) {
        $arNextPrev['NEXT'] = $navElement[0];
        $arNextPrev['PREV'] = $navElement[$itemKey - 1];
    } else {
        $arNextPrev['NEXT'] = $navElement[$itemKey + 1];
        $arNextPrev['PREV'] = $navElement[$itemKey - 1];
    }
}
?>
<section class="product_page-posotion">
    <? if (!empty($arNextPrev)): ?>
        <div class="product_page--buttons">
            <div class="product_page--buttons--message product_page-message-js">Следующий товар</div>
            <div class="product_page--buttons--hover">

                <a href="<?= $arNextPrev['NEXT']['DETAIL_PAGE_URL'] ?>"
                   class="product_page--but product_page--but_transition product_page--but1 product_page-but1-js"></a>
            </div>
            <a href="<?= $arNextPrev['PREV']['DETAIL_PAGE_URL'] ?>"
               class="product_page--but product_page--but_transition product_page--but2"></a>
        </div>
    <? endif; ?>
    <div class="container">
        <div class='row'>
            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                <div class="product_page">
                    <div class="product_page--blocks">


                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-8 col-lg-8 col-xl-8">

                                <div class="row">
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                        <!-- Swiper -->
                                        <div class="swiper-container--product">
                                            <div class="label-parent">
                                                <?# лэйбы
                                                $differ = 0;
                                                $precent = 0;
                                                if (($arResult['PROPERTIES']['OPTIMAL_PRICE']['VALUE'] > 0) || ($arResult['PROPERTIES']['MINIMUM_PRICE']['VALUE'] > 0)){
                                                        $differ = $arResult['PROPERTIES']['MINIMUM_PRICE']['VALUE'] - $arResult['PROPERTIES']['OPTIMAL_PRICE']['VALUE'];
                                                        if ($differ>0){
                                                            $b = $arResult['PROPERTIES']['MINIMUM_PRICE']['VALUE'];
                                                            $a = $arResult['PROPERTIES']['OPTIMAL_PRICE']['VALUE'];
                                                            $precent = round(abs((($a-$b)/$b) * 100));
                                                        }
                                                    } ?>
                                                    <?if ($precent>0): ?>
                                                        <span class="label_percent">-<?= $precent ?> %</span>
                                                    <? elseif ($arResult['PROPERTIES']['NEWPRODUCT']['VALUE'] == 'да'): ?>
                                                        <span class="label_new">new</span>
                                                    <? endif; ?>

                                            </div>

                                            <div class="swiper-wrapper left_card_main product--slider--width">
                                                <? foreach ($arImage['RESIZE'] as $key => $imageSlide): ?>
                                                    <div class="swiper-slide swiper-container">
                                                        <img class="visit--slider--img resize-image-js"
                                                             src="<?= $imageSlide['src'] ?>">
                                                        <img class="visit--slider--img origin-image-js"
                                                             style="display: none"
                                                             src="<?= $arImage['ORIGIN'][$key] ?>">
                                                    </div>
                                                <? endforeach; ?>
                                            </div>

                                            <!-- Add Pagination -->
                                            <div class="swiper-pagination_product"></div>

                                            <!-- Add Arrows -->

                                            <div class="swiper-button-next_product d-none d-sm-none d-md-block d-lg-block d-xl-block"></div>
                                            <div class="swiper-button-prev_product d-none d-sm-none d-md-block d-lg-block d-xl-block"></div>

                                        </div>
                                        <div id="main-navigation">
                                            <ul class="links-container">
                                                <? foreach ($arPagination as $keyPag => $imagePagin): ?>
                                                    <li class="nav-link <?= $keyPag == 0 ? 'active' : '' ?>">
                                                        <img class=visit--slider--img" src="<?= $imagePagin['src'] ?>">
                                                    </li>
                                                <? endforeach; ?>
                                                <div class="clearfix"></div>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                <h1 class="product_page--h1">
                                    <?= $arResult['NAME'] ?>
                                </h1>
                                <div class="product_page--description">
                                    <?= $arResult['~PREVIEW_TEXT'] ?>
                                </div>
                                <? if (($arResult['PROPERTIES']['OPTIMAL_PRICE']['VALUE'] > 0) || ($arResult['PROPERTIES']['MINIMUM_PRICE']['VALUE'] > 0)): ?>
                                    <span class="product_page--price">
                                        <span class="d-none d-sm-block d-md-block d-lg-block d-xl-block block_price">Цена:&nbsp;</span><span
                                                class="<?= (!empty($arResult['PROPERTIES']['OPTIMAL_PRICE']['VALUE']) && ($arResult['PROPERTIES']['MINIMUM_PRICE']['VALUE'] !== $arResult['PROPERTIES']['OPTIMAL_PRICE']['VALUE'])) ? 'old--price product_page--price--span' : 'product_page--price--span' ?>"><?= $arResult['PROPERTIES']['MINIMUM_PRICE']['VALUE'] ?>
                                            &#8399;&nbsp;</span>
                                        <? if (!empty($arResult['PROPERTIES']['OPTIMAL_PRICE']['VALUE']) && ($arResult['PROPERTIES']['MINIMUM_PRICE']['VALUE'] !== $arResult['PROPERTIES']['OPTIMAL_PRICE']['VALUE'])): ?>
                                            <span class="new_catalog--block--sum"><?= round($arResult['PROPERTIES']['OPTIMAL_PRICE']['VALUE']) ?>
                                                &#8399;</span>
                                            <div class="clearfix"></div>
                                        <? endif; ?>

                                </span>
                                <? endif; ?>
                                <? if (!empty($arResult['PROPERTIES']['ARTNUMBER']['VALUE'])): ?>
                                    <div class="product_page--code">Артикул:
                                        <span class="product_page--code--span"><?= $arResult['PROPERTIES']['ARTNUMBER']['VALUE'] ?></span>
                                        <? $isfavorite = $f->getIsFavorit($arResult['ID']); ?>
                                        <span class="product_page--code--favorite"> <i
                                                    class="fa <?= $isfavorite['status'] ? 'fa-heart' : 'fa-heart-o' ?>"
                                                    onclick="isFavorite(this)" data-product-id="<?= $arResult['ID'] ?>"
                                                    aria-hidden="true"></i></span>
                                    </div>

                                    <div class="clearfix"></div>
                                <? endif; ?>
                                <? if ($arResult['ONLINE'] > 0 || $arResult['OFFLINE'] > 0) {
                                    $nonEmpty = 'Y';
                                } else {
                                    $nonEmpty = 'N';
                                } ?>
<!--                                --><?//echo'<pre>'.print_r($arResult['OFFERS'], true).'</pre>';?>

                                    <? if ($arResult['OFFERS'][0]['PROPERTIES']['SIZES_SHOES']['VALUE_SIZE'] !== 'ONE_SIZE'): ?>
                                        <div class="parent-to-js">
                                            <div class="parent-for-sizes-online" <?= $nonEmpty == 'N' ? 'style="display:none"' : 'style="display:block"' ?>>
                                                <span class="product_page--size--title">Размеры</span>
                                                <!--размеры для мобильных-->
                                                <div class="b_mob--size">

                                                    <div class="dropdown  d-block d-sm-block d-md-none d-lg-none d-xl-none">
                                                        <a class="btn mob-size-parent-js btn-secondary dropdown-toggle"
                                                           href="#"
                                                           role="button"
                                                           id="dropdownMenuLink" data-toggle="dropdown"
                                                           aria-haspopup="true"
                                                           aria-expanded="false">
                                                            Выбрать размер
                                                        </a>
                                                        <div class="dropdown-menu mob-size-up-js"
                                                             aria-labelledby="dropdownMenuLink">
                                                            <div class="b_mob--size--block1">
                                                                <div class="b_mob--size--text">Размер производителя
                                                                </div>
                                                                <div class="product_page--size">
                                                                    <? $offline = 0;
                                                                    $online = 0;
                                                                    foreach ($arResult['OFFERS'] as $offer): ?>
                                                                        <? if (!empty($offer['PROPERTIES']['SIZES_SHOES']['VALUE_SIZE'])): ?>
                                                                            <? $stock = $s->getStock($offer['ID'], $_COOKIE['CityID']);
                                                                            $offline = $offline + $stock['result']['PRODUCT_AMOUNT']['OFFLINE'];
                                                                            $online = $online + $stock['result']['PRODUCT_AMOUNT']['ONLINE'];
                                                                            ?>
                                                                            <? if (($stock['result']['PRODUCT_AMOUNT']['OFFLINE'] > 0) || ($stock['result']['PRODUCT_AMOUNT']['ONLINE'] > 0)): ?>
                                                                                <span class="product_page--size--block">
                                                                    <span data-offline-amount="<?= $stock['result']['PRODUCT_AMOUNT']['OFFLINE'] ?>"
                                                                          data-online-amount="<?= $stock['result']['PRODUCT_AMOUNT']['ONLINE'] ?>"
                                                                          data-offer-id="<?= $offer['ID'] ?>"
                                                                          class="mob-size-def-js product_page--size--span"><?= $offer['PROPERTIES']['SIZES_SHOES']['VALUE_SIZE'] ?></span>
                                                            </span>
                                                                            <? else: ?>
                                                                                <span class="product_page--size--block">
                                                                <span class="product_page--size--span noclose product_page--size_none"><?= $offer['PROPERTIES']['SIZES_SHOES']['VALUE_SIZE'] ?></span>
                                                            </span>
                                                                            <? endif; ?>
                                                                        <? endif; ?>
                                                                    <? endforeach; ?>

                                                                </div>
                                                                <div class="clearfix"></div>
                                                            </div>
                                                            <? if ($arResult['OFFERS'][0]['PROPERTIES']['SIZES_SHOES']['VALUE_SIZE']<16): ?>
                                                                <div class="b_mob--size--block2">
                                                                    <div class=" b_mob--size--text">Российский размер
                                                                    </div>
                                                                    <div class="product_page--size">
                                                                        <? $offline = 0;
                                                                        $online = 0;
                                                                        foreach ($arResult['OFFERS'] as $offer): ?>
                                                                            <? if (!empty($offer['PROPERTIES']['SIZES_SHOES']['VALUE_SIZE'])): ?>
                                                                                <? $stock = $s->getStock($offer['ID'], $_COOKIE['CityID']);
                                                                                $offline = $offline + $stock['result']['PRODUCT_AMOUNT']['OFFLINE'];
                                                                                $online = $online + $stock['result']['PRODUCT_AMOUNT']['ONLINE'];
                                                                                ?>
                                                                                <? if (($stock['result']['PRODUCT_AMOUNT']['OFFLINE'] > 0) || ($stock['result']['PRODUCT_AMOUNT']['ONLINE'] > 0)): ?>
                                                                                    <span class="product_page--size--block">
                                                                                <span data-offline-amount="<?= $stock['result']['PRODUCT_AMOUNT']['OFFLINE'] ?>"
                                                                                      data-online-amount="<?= $stock['result']['PRODUCT_AMOUNT']['ONLINE'] ?>"
                                                                                      data-size="
                                                                                        <?$string = preg_replace('~[^0-9]+~','',$offer['PROPERTIES']['SIZES_SHOES']['VALUE_SIZE']);
                                                                                            echo $string ?>"
                                                                                      data-offer-id="<?= $offer['ID'] ?>"
                                                                                      class="product_page--size--span mob-size-ru-js"><?= $offer['PROPERTIES']['SIZES_SHOES']['RU_VALUE_SIZE'] ?></span>
                                                                                </span>
                                                                                                    <? else: ?>
                                                                                                        <span class="product_page--size--block">
                                                                                    <span class="product_page--size--span noclose product_page--size_none"><?= $offer['PROPERTIES']['SIZES_SHOES']['RU_VALUE_SIZE'] ?></span>
                                                                                </span>
                                                                                <? endif; ?>
                                                                            <? endif; ?>
                                                                        <? endforeach; ?>

                                                                    </div>
                                                                    <div class="clearfix"></div>
                                                                </div>
                                                            <? endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!---->


                                                <span data-toggle="modal" data-target="#modalSize"
                                                      class="product_page--size--link">Как определить размер</span>
                                                <div class="clearfix"></div>
                                                <!--размеры для десктопа-->

                                                <div class="product_page--size d-none d-sm-none d-md-none d-lg-block d-xl-block">
                                                    <?

                                                    $offline = 0;
                                                    $online = 0;
                                                    foreach ($arResult['OFFERS'] as $offer): ?>
                                                        <? if (!empty($offer['PROPERTIES']['SIZES_SHOES']['VALUE_SIZE'])): ?>
                                                            <? $stock = $s->getStock($offer['ID'], $_COOKIE['CityID']);
                                                            $offline = $offline + $stock['result']['PRODUCT_AMOUNT']['OFFLINE'];
                                                            $online = $online + $stock['result']['PRODUCT_AMOUNT']['ONLINE'];
                                                            ?>
                                                            <? if (($offer['PROPERTIES']['SIZES_SHOES']['OFFLINE'] > 0) || ($offer['PROPERTIES']['SIZES_SHOES']['ONLINE'] > 0)): ?>
                                                                <span class="product_page--size--block">
                                                                <span data-offline-amount="<?= $offer['PROPERTIES']['SIZES_SHOES']['OFFLINE'] ?>"
                                                                      data-online-amount="<?= $offer['PROPERTIES']['SIZES_SHOES']['ONLINE'] ?>"
                                                                      data-offer-id="<?= $offer['ID'] ?>"
                                                                      class="product_page--size--span product-size-hover-js product-size-click-js"><?= $offer['PROPERTIES']['SIZES_SHOES']['VALUE_SIZE'] ?></span>
                                                                    <? if ($offer['PROPERTIES']['SIZES_SHOES']['VALUE_SIZE']<16): ?>
                                                                        <div class="product_page--hint product-ru-size-js">Российский размер: <?= $offer['PROPERTIES']['SIZES_SHOES']['RU_VALUE_SIZE'] ?>
                                                                            <div class="hint--arrow"></div></div>
                                                                    <? endif ?>
                                                                </span>
                                                            <? else: ?>
                                                                <span class="product_page--size--block">
                                                                <span class="product_page--size--span product-size-hover-js product_page--size_none"><?= $offer['PROPERTIES']['SIZES_SHOES']['VALUE_SIZE'] ?></span>
                                                                                <? if ($offer['PROPERTIES']['SIZES_SHOES']['VALUE_SIZE']<16): ?>
                                                                                    <div class="product_page--hint product-ru-size-js">Российский размер: <?= $offer['PROPERTIES']['SIZES_SHOES']['RU_VALUE_SIZE'] ?>
                                                                                        <div class="hint--arrow"></div></div>
                                                                                <? endif ?>
                                                                </span>
                                                            <? endif; ?>
                                                        <? endif; ?>
                                                    <? endforeach; ?>

                                                </div>
                                                <!---->
                                                <div class="clearfix"></div>
                                                <span data-offline="<?=$offline?>"
                                                      data-online="<?=$online?>"
                                                      class="product_page--clear product-size-clear-js">Сбросить фильтр</span>
                                            </div>
                                        </div>
                                    <? endif; ?>

                                <div class="row parent-for-sizes-all" <?= $nonEmpty == 'N' ? 'style="display:none"' : 'style="display:block"' ?>>
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-9">
                                        <div class="common-for-sizes-online block_online" <?= $online > 0 ? 'style="display:block"' : 'style="display:none"' ?>>
                                            <div onclick="AddJs(this)" data-product-id="<?=$arResult['PROPERTIES']['SIZE_TYPE']['VALUE_XML_ID'] == 'none'?$arResult['OFFERS'][0]['ID']:'' ?>" class="product_page--buy product-click-js">
                                                Купить
                                            </div>
                                            <div style="display: none" class="product_page--buy--hint product-cart-hint-js">
                                                Выберите размер
                                                <div class="arrow-hint"></div>
                                            </div>
                                            <div onclick="yaCounter43537484.reachGoal('zakaz'); return true;" data-name="<?= $arResult['NAME'] ?>" data-product-id="<?=$arResult['PROPERTIES']['SIZE_TYPE']['VALUE_XML_ID'] == 'none'?$arResult['OFFERS'][0]['ID']:'' ?>" data-price="<?= $arResult['PROPERTIES']['OPTIMAL_PRICE']['VALUE'] ?>" data-image="<?=$oneImage['src']?>" class="product_page--click product-one-click-js">
                                                Купить в 1 клик
                                            </div>
                                            <div style="display: none" class="product-one_page--buy--hint product-one-cart-hint-js">
                                                Выберите размер
                                                <div class="arrow-hint"></div>
                                            </div>
                                            <?//@todo поставить оптимальную цену( обычную или скидучную ) взять с поля в админке OPIMAL_PRICE?>
                                            <div <?= $offline > 0 ? 'style="display:block"' : 'style="display:none"' ?>
                                                    class="show-size-online-js">
                                                <div data-product-id="<?= $arResult['ID'] ?>"
                                                     data-offer-id="<?= $_POST['size_id'] ?>"
                                                     onclick="showModalMagazin1(this, true)"
                                                     class="product_page--availability show-availability-js product_page-availability-js">
                                                    Посмотреть наличие<br> в розничных магазинах
                                                </div>
                                            </div>
                                            <div <?= $offline > 0 ? 'style="display:none"' : 'style="display:block"' ?>
                                                    class="note show-availability-online-js online">
                                                Этот товар доступен только для покупки
                                                в интернет-магазине
                                            </div>
                                        </div>


                                        <!--блок описания товара для мобильных-->

                                        <div class="d-block d-sm-block d-md-none d-lg-none d-xl-none">
                                            <div class="product_page--info_product">
                                                Информация о товаре
                                            </div>
                                            <div class="product_page--description">
                                                <?if(!empty($arResult['~DETAIL_TEXT'])):?>
                                                    <div class="inf_title">
                                                        <?= $arResult['~DETAIL_TEXT'] ?>
                                                    </div>
                                                <?endif;?>
                                                <div class="product_page--description--title">Описание</div>
                                                <ul class="product_page--description--ul product_page--description--ul1">
                                                    <?
                                                    $brand = CIBlockElement::GetByID($arResult['PROPERTIES']['BRAND_REF']['VALUE']);
                                                    if ($ar_brand = $brand->GetNext())
                                                        ?>
                                                    <? if ($ar_brand['NAME']): ?>
                                                        <li class="product_page--description--li">
                                                            <span class="product_page--description--span">Бренд:</span> <?= $ar_brand['NAME'] ?>
                                                        </li>
                                                    <? endif; ?>
                                                    <?
                                                    if(!empty($arResult['PROPERTIES']['MATERIAL_PODOSHVA']['VALUE'])):?>
                                                        <li class="product_page--description--li">
                                                            <span class="product_page--description--span">Материал подошвы:</span>

                                                            <?foreach ($arResult['PROPERTIES']['MATERIAL_PODOSHVA']['VALUE'] as $keySole => $itemSole):
                                                                $sole = CIBlockElement::GetByID($itemSole);
                                                                if ($ar_sole = $sole->GetNext()){
                                                                    echo $ar_sole['NAME'];
                                                                    if(count($arResult['PROPERTIES']['MATERIAL_PODOSHVA']['VALUE'])>1 && count($arResult['PROPERTIES']['MATERIAL_PODOSHVA']['VALUE'])-1 != $keySole){
                                                                        echo ', ';
                                                                    }
                                                                }?>
                                                            <?endforeach; ?>
                                                        </li>
                                                    <? endif; ?>

                                                    <?if(!empty($arResult['PROPERTIES']['COUNTRY']['VALUE'])):?>
                                                        <li class="product_page--description--li">
                                                            <span class="product_page--description--span">Страна производства:</span>

                                                            <?foreach ($arResult['PROPERTIES']['COUNTRY']['VALUE'] as $keyCountry => $itemCountry):
                                                                $country = CIBlockElement::GetByID($itemCountry);
                                                                if ($ar_country = $country->GetNext()){
                                                                    echo $ar_country['NAME'];
                                                                    if(count($arResult['PROPERTIES']['COUNTRY']['VALUE'])>1 && count($arResult['PROPERTIES']['COUNTRY']['VALUE'])-1 != $keyCountry){
                                                                        echo ', ';
                                                                    }
                                                                }?>
                                                            <?endforeach; ?>
                                                        </li>
                                                    <? endif; ?>

                                                    <?if(!empty($arResult['PROPERTIES']['LINING']['VALUE'])):?>
                                                        <li class="product_page--description--li">
                                                            <span class="product_page--description--span">Материал подкладки:</span>

                                                            <?foreach ($arResult['PROPERTIES']['LINING']['VALUE'] as $keyLining => $itemLining):
                                                                $lining = CIBlockElement::GetByID($itemLining);
                                                                if ($ar_lining = $lining->GetNext()){
                                                                    echo $ar_lining['NAME'];
                                                                    if(count($arResult['PROPERTIES']['LINING']['VALUE'])>1 && count($arResult['PROPERTIES']['LINING']['VALUE'])-1 != $keyLining){
                                                                        echo ', ';
                                                                    }
                                                                }?>
                                                            <?endforeach; ?>
                                                        </li>
                                                    <? endif; ?>

                                                    <? if ($arResult['PROPERTIES']['YEAR']['VALUE']): ?>
                                                        <li class="product_page--description--li">
                                                            <span class="product_page--description--span">Год:</span> <?= $arResult['PROPERTIES']['YEAR']['VALUE'] ?>
                                                        </li>
                                                    <? endif; ?>

                                                    <?if(!empty($arResult['PROPERTIES']['GENDER']['VALUE'])):?>
                                                        <li class="product_page--description--li">
                                                            <span class="product_page--description--span">Пол:</span>

                                                            <?foreach ($arResult['PROPERTIES']['GENDER']['VALUE'] as $keyGender => $itemGender):
                                                                $gender = CIBlockElement::GetByID($itemGender);
                                                                if ($ar_gender = $gender->GetNext()){
                                                                    echo $ar_gender['NAME'];
                                                                    if(count($arResult['PROPERTIES']['GENDER']['VALUE'])>1 && count($arResult['PROPERTIES']['GENDER']['VALUE'])-1 != $keyGender){
                                                                        echo ', ';
                                                                    }
                                                                }?>
                                                            <?endforeach; ?>
                                                        </li>
                                                    <? endif; ?>

                                                    <?if(!empty($arResult['PROPERTIES']['SEASON']['VALUE'])):?>
                                                        <li class="product_page--description--li">
                                                            <span class="product_page--description--span">Сезон:</span>

                                                            <?foreach ($arResult['PROPERTIES']['SEASON']['VALUE'] as $keySeason => $itemSeason):
                                                                $season = CIBlockElement::GetByID($itemSeason);
                                                                if ($ar_season = $season->GetNext()){
                                                                    echo $ar_season['NAME'];
                                                                    if(count($arResult['PROPERTIES']['SEASON']['VALUE'])>1 && count($arResult['PROPERTIES']['SEASON']['VALUE'])-1 != $keySeason){
                                                                        echo ', ';
                                                                    }
                                                                }?>
                                                            <?endforeach; ?>
                                                        </li>
                                                    <? endif; ?>

                                                    <?if(!empty($arResult['PROPERTIES']['SKIN_TYPE']['VALUE'])):?>
                                                        <li class="product_page--description--li">
                                                            <span class="product_page--description--span">Материал верха:</span>

                                                            <?foreach ($arResult['PROPERTIES']['SKIN_TYPE']['VALUE'] as $keySkin_type => $itemSkin_type):
                                                                $skin_type = CIBlockElement::GetByID($itemSkin_type);
                                                                if ($ar_skin_type = $skin_type->GetNext()){
                                                                    echo $ar_skin_type['NAME'];
                                                                    if(count($arResult['PROPERTIES']['SKIN_TYPE']['VALUE'])>1 && count($arResult['PROPERTIES']['SKIN_TYPE']['VALUE'])-1 != $keySkin_type){
                                                                        echo ', ';
                                                                    }
                                                                }?>
                                                            <?endforeach; ?>
                                                        </li>
                                                    <? endif; ?>

                                                    <?if(!empty($arResult['PROPERTIES']['COLOR']['VALUE'])):?>
                                                        <li class="product_page--description--li">
                                                            <span class="product_page--description--span">Цвет:</span>

                                                            <?foreach ($arResult['PROPERTIES']['COLOR']['VALUE'] as $keyColor => $itemColor):
                                                                $color = CIBlockElement::GetByID($itemColor);
                                                                if ($ar_color = $color->GetNext()){
                                                                    echo $ar_color['NAME'];
                                                                    if(count($arResult['PROPERTIES']['COLOR']['VALUE'])>1 && count($arResult['PROPERTIES']['COLOR']['VALUE'])-1 != $keyColor){
                                                                        echo ', ';
                                                                    }
                                                                }?>
                                                            <?endforeach; ?>
                                                        </li>
                                                    <? endif; ?>

                                                    <? if ($arResult['PROPERTIES']['ARTNUMBER']['VALUE']): ?>
                                                        <li class="product_page--description--li">
                                                            <span class="product_page--description--span">Артикул:</span> <?= $arResult['PROPERTIES']['ARTNUMBER']['VALUE'] ?>
                                                        </li>
                                                    <? endif; ?>

                                                    <?// каблук HEEL
                                                    if(!empty($arResult['PROPERTIES']['HEEL']['VALUE'])):?>
                                                        <li class="product_page--description--li">
                                                            <span class="product_page--description--span">Каблук:</span>

                                                            <?foreach ($arResult['PROPERTIES']['HEEL']['VALUE'] as $keyHeel => $itemHeel):
                                                                $heel = CIBlockElement::GetByID($itemHeel);
                                                                if ($ar_heel = $heel->GetNext()){
                                                                    echo $ar_heel['NAME'];
                                                                    if(count($arResult['PROPERTIES']['HEEL']['VALUE'])>1 && count($arResult['PROPERTIES']['HEEL']['VALUE'])-1 != $keyHeel){
                                                                        echo ', ';
                                                                    }
                                                                }?>
                                                            <?endforeach; ?>
                                                        </li>
                                                    <? endif; ?>

                                                    <?// Материал MATERIAL
                                                    if(!empty($arResult['PROPERTIES']['MATERIAL']['VALUE'])):?>
                                                        <li class="product_page--description--li">
                                                            <span class="product_page--description--span">Материал:</span>

                                                            <?foreach ($arResult['PROPERTIES']['MATERIAL']['VALUE'] as $keyMaterial => $itemMaterial):
                                                                $material = CIBlockElement::GetByID($itemMaterial);
                                                                if ($ar_material = $material->GetNext()){
                                                                    echo $ar_material['NAME'];
                                                                    if(count($arResult['PROPERTIES']['MATERIAL']['VALUE'])>1 && count($arResult['PROPERTIES']['MATERIAL']['VALUE'])-1 != $keyMaterial){
                                                                        echo ', ';
                                                                    }
                                                                }?>
                                                            <?endforeach; ?>
                                                        </li>
                                                    <? endif; ?>

                                                    <?// Застежка CLASP
                                                    if(!empty($arResult['PROPERTIES']['CLASP']['VALUE'])):?>
                                                        <li class="product_page--description--li">
                                                            <span class="product_page--description--span">Застежка:</span>

                                                            <?foreach ($arResult['PROPERTIES']['CLASP']['VALUE'] as $keyClasp => $itemClasp):
                                                                $clasp = CIBlockElement::GetByID($itemClasp);
                                                                if ($ar_clasp = $clasp->GetNext()){
                                                                    echo $ar_clasp['NAME'];
                                                                    if(count($arResult['PROPERTIES']['CLASP']['VALUE'])>1 && count($arResult['PROPERTIES']['CLASP']['VALUE'])-1 != $keyClasp){
                                                                        echo ', ';
                                                                    }
                                                                }?>
                                                            <?endforeach; ?>
                                                        </li>
                                                    <? endif; ?>

                                                    <?// Назначение APPOINTMENT
                                                    if(!empty($arResult['PROPERTIES']['APPOINTMENT']['VALUE'])):?>
                                                        <li class="product_page--description--li">
                                                            <span class="product_page--description--span">Назначение:</span>

                                                            <?foreach ($arResult['PROPERTIES']['APPOINTMENT']['VALUE'] as $keyAppointment => $itemAppointment):
                                                                $appointment = CIBlockElement::GetByID($itemAppointment);
                                                                if ($ar_appointment = $appointment->GetNext()){
                                                                    echo $ar_appointment['NAME'];
                                                                    if(count($arResult['PROPERTIES']['APPOINTMENT']['VALUE'])>1 && count($arResult['PROPERTIES']['APPOINTMENT']['VALUE'])-1 != $keyAppointment){
                                                                        echo ', ';
                                                                    }
                                                                }?>
                                                            <?endforeach; ?>
                                                        </li>
                                                    <? endif; ?>

                                                    <?// Поп-размеры POP_DIMENSIONS
                                                    if(!empty($arResult['PROPERTIES']['POP_DIMENSIONS']['VALUE'])):?>
                                                        <li class="product_page--description--li">
                                                            <span class="product_page--description--span">Поп-размеры:</span>

                                                            <?foreach ($arResult['PROPERTIES']['POP_DIMENSIONS']['VALUE'] as $keyDimensions => $itemDimensions):
                                                                $dimensions = CIBlockElement::GetByID($itemDimensions);
                                                                if ($ar_dimensions = $dimensions->GetNext()){
                                                                    echo $ar_dimensions['NAME'];
                                                                    if(count($arResult['PROPERTIES']['POP_DIMENSIONS']['VALUE'])>1 && count($arResult['PROPERTIES']['POP_DIMENSIONS']['VALUE'])-1 != $keyDimensions){
                                                                        echo ', ';
                                                                    }
                                                                }?>
                                                            <?endforeach; ?>
                                                        </li>
                                                    <? endif; ?>

                                                    <?// Супинатор SUPINATOR
                                                    if(!empty($arResult['PROPERTIES']['SUPINATOR']['VALUE'])):?>
                                                        <li class="product_page--description--li">
                                                            <span class="product_page--description--span">Супинатор:</span>

                                                            <?foreach ($arResult['PROPERTIES']['SUPINATOR']['VALUE'] as $keySupinator => $itemSupinator):
                                                                $supinator = CIBlockElement::GetByID($itemSupinator);
                                                                if ($ar_supinator = $supinator->GetNext()){
                                                                    echo $ar_supinator['NAME'];
                                                                    if(count($arResult['PROPERTIES']['SUPINATOR']['VALUE'])>1 && count($arResult['PROPERTIES']['SUPINATOR']['VALUE'])-1 != $keySupinator){
                                                                        echo ', ';
                                                                    }
                                                                }?>
                                                            <?endforeach; ?>
                                                        </li>
                                                    <? endif; ?>

                                                    <?// Характеристики OPTIONS
                                                    if(!empty($arResult['PROPERTIES']['OPTIONS']['VALUE'])):?>
                                                        <li class="product_page--description--li">
                                                            <span class="product_page--description--span">Характеристики:</span>

                                                            <?foreach ($arResult['PROPERTIES']['OPTIONS']['VALUE'] as $keyOptions => $itemOptions):
                                                                $options = CIBlockElement::GetByID($itemOptions);
                                                                if ($ar_options = $options->GetNext()){
                                                                    echo $ar_options['NAME'];
                                                                    if(count($arResult['PROPERTIES']['OPTIONS']['VALUE'])>1 && count($arResult['PROPERTIES']['OPTIONS']['VALUE'])-1 != $keyOptions){
                                                                        echo ', ';
                                                                    }
                                                                }?>
                                                            <?endforeach; ?>
                                                        </li>
                                                    <? endif; ?>

                                                    <?// Особенности FEATURES
                                                    if(!empty($arResult['PROPERTIES']['FEATURES']['VALUE'])):?>
                                                        <li class="product_page--description--li">
                                                            <span class="product_page--description--span">Особенности:</span>

                                                            <?foreach ($arResult['PROPERTIES']['FEATURES']['VALUE'] as $keyFeatures => $itemFeatures):
                                                                $features = CIBlockElement::GetByID($itemFeatures);
                                                                if ($ar_features = $features->GetNext()){
                                                                    echo $ar_features['NAME'];
                                                                    if(count($arResult['PROPERTIES']['FEATURES']['VALUE'])>1 && count($arResult['PROPERTIES']['FEATURES']['VALUE'])-1 != $keyFeatures){
                                                                        echo ', ';
                                                                    }
                                                                }?>
                                                            <?endforeach; ?>
                                                        </li>
                                                    <? endif; ?>

                                                    <?// Повод OCCASION
                                                    if(!empty($arResult['PROPERTIES']['OCCASION']['VALUE'])):?>
                                                        <li class="product_page--description--li">
                                                            <span class="product_page--description--span">Повод:</span>

                                                            <?foreach ($arResult['PROPERTIES']['OCCASION']['VALUE'] as $keyOccasion => $itemOccasion):
                                                                $occasion = CIBlockElement::GetByID($itemOccasion);
                                                                if ($ar_occasion = $occasion->GetNext()){
                                                                    echo $ar_occasion['NAME'];
                                                                    if(count($arResult['PROPERTIES']['OCCASION']['VALUE'])>1 && count($arResult['PROPERTIES']['OCCASION']['VALUE'])-1 != $keyOccasion){
                                                                        echo ', ';
                                                                    }
                                                                }?>
                                                            <?endforeach; ?>
                                                        </li>
                                                    <? endif; ?>
                                                </ul>
                                                <div class="clearfix"></div>
                                            </div>


                                            <!--Вызов модального окна для просмотра списка магазинов и карты-->

                                            <div class="parent-button-mag-js">

                                            </div>


                                            <!---->


                                        </div>

                                        <div <?= $offline > 0 || $online > 0 ? 'style="display:block"' : 'style="display:none"' ?>
                                                class="common-for-sizes-common product_page--info">
                                            Информацию о наличии размера можно получить, позвонив в магазин!<br>
                                            Пожалуйста, уточните цены в <a href="/shops/" class="product_page--info--link">магазине</a>!
                                            На сайте представлены НЕ ВСЕ модели!
                                        </div>
                                        <div <?= $offline > 0 ? 'style="display:block"' : 'style="display:none"' ?>
                                                class="common-for-sizes-offline">
                                            <div onclick="showModalMagazin1(this, true)"
                                                 data-product-id="<?= $arResult['ID'] ?>"
                                                <?= $offline > 0 && $online <= 0 ? 'style="display:block"' : 'style="display:none"' ?>
                                                 class="product_page--availability2 show-availability-js product_offline_score-js">
                                                Выбрать магазин
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row non-size-show-js" <?= $nonEmpty == 'N' ? 'style="display:block"' : 'style="display:none"' ?>>
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-9">
                                        <div class="purchase out-of-stock">
                                            <div class="out-of-stock-message">В розничных магазинах Вашего города товар
                                                не найден.
                                                Выберите другой город
                                            </div>
                                        </div>
                                        <div  class="common-for-sizes">
                                            <div onclick="changeCity()" class="change-city--empty">
                                                Выбрать город
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>


                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                <!--блок описания товара для десктопа-->

                                <div class="d-none d-sm-none d-md-block d-lg-block d-xl-block">
                                    <div class="product_info--block">

                                        <!-- Навигационные вкладки -->
                                        <ul class="nav nav-tabs tabs-fast-js" role="tablist">
                                            <li role="presentation">
                                                <a href="#tableOne" class="active info-li-js"
                                                    aria-controls="home"
                                                    role="tab" data-toggle="tab">Информация о товаре</a></li>
                                            <li role="presentation">
                                                <a href="#reviews" class="info-li-js"
                                                    aria-controls="home"
                                                    role="tab" data-toggle="tab">Отзывы</a></li>
                                        </ul>

                                        <div class="tab-content tab-fast-js">

                                            <div role="tabpanel" class="tab-pane active" id="tableOne">

                                                <div class="product_page--description">
                                                    <div class="row">
                                                        <div class="col-12 col-sm-12 col-md-12 col-lg-8 col-xl-8">
                                                            <?if(!empty($arResult['~DETAIL_TEXT'])):?>
                                                                <div class="inf_title">
                                                                    <?= $arResult['~DETAIL_TEXT'] ?>
                                                                </div>
                                                            <?endif;?>
                                                            <div class="product_page--description--title">Описание</div>
                                                            <ul class="product_page--description--ul product_page--description--ul1">
                                                                <?
                                                                $brand = CIBlockElement::GetByID($arResult['PROPERTIES']['BRAND_REF']['VALUE']);
                                                                if ($ar_brand = $brand->GetNext())
                                                                    ?>
                                                                <? if ($ar_brand['NAME']): ?>
                                                                    <li class="product_page--description--li">
                                                                        <span class="product_page--description--span">Бренд:</span> <?= $ar_brand['NAME'] ?>
                                                                    </li>
                                                                <? endif; ?>
                                                                <?
                                                                if(!empty($arResult['PROPERTIES']['MATERIAL_PODOSHVA']['VALUE'])):?>
                                                                <li class="product_page--description--li">
                                                                    <span class="product_page--description--span">Материал подошвы:</span>

                                                                    <?foreach ($arResult['PROPERTIES']['MATERIAL_PODOSHVA']['VALUE'] as $keySole => $itemSole):
                                                                    $sole = CIBlockElement::GetByID($itemSole);
                                                                    if ($ar_sole = $sole->GetNext()){
                                                                        echo $ar_sole['NAME'];
                                                                        if(count($arResult['PROPERTIES']['MATERIAL_PODOSHVA']['VALUE'])>1 && count($arResult['PROPERTIES']['MATERIAL_PODOSHVA']['VALUE'])-1 != $keySole){
                                                                            echo ', ';
                                                                        }
                                                                    }?>
                                                                    <?endforeach; ?>
                                                                </li>
                                                                <? endif; ?>

                                                                <?if(!empty($arResult['PROPERTIES']['COUNTRY']['VALUE'])):?>
                                                                <li class="product_page--description--li">
                                                                    <span class="product_page--description--span">Страна производства:</span>

                                                                    <?foreach ($arResult['PROPERTIES']['COUNTRY']['VALUE'] as $keyCountry => $itemCountry):
                                                                    $country = CIBlockElement::GetByID($itemCountry);
                                                                    if ($ar_country = $country->GetNext()){
                                                                        echo $ar_country['NAME'];
                                                                        if(count($arResult['PROPERTIES']['COUNTRY']['VALUE'])>1 && count($arResult['PROPERTIES']['COUNTRY']['VALUE'])-1 != $keyCountry){
                                                                            echo ', ';
                                                                        }
                                                                    }?>
                                                                    <?endforeach; ?>
                                                                </li>
                                                                <? endif; ?>

                                                                <?if(!empty($arResult['PROPERTIES']['LINING']['VALUE'])):?>
                                                                <li class="product_page--description--li">
                                                                    <span class="product_page--description--span">Материал подкладки:</span>

                                                                    <?foreach ($arResult['PROPERTIES']['LINING']['VALUE'] as $keyLining => $itemLining):
                                                                    $lining = CIBlockElement::GetByID($itemLining);
                                                                    if ($ar_lining = $lining->GetNext()){
                                                                        echo $ar_lining['NAME'];
                                                                        if(count($arResult['PROPERTIES']['LINING']['VALUE'])>1 && count($arResult['PROPERTIES']['LINING']['VALUE'])-1 != $keyLining){
                                                                            echo ', ';
                                                                        }
                                                                    }?>
                                                                    <?endforeach; ?>
                                                                </li>
                                                                <? endif; ?>

                                                                <? if ($arResult['PROPERTIES']['YEAR']['VALUE']): ?>
                                                                    <li class="product_page--description--li">
                                                                        <span class="product_page--description--span">Год:</span> <?= $arResult['PROPERTIES']['YEAR']['VALUE'] ?>
                                                                    </li>
                                                                <? endif; ?>

                                                                <?if(!empty($arResult['PROPERTIES']['GENDER']['VALUE'])):?>
                                                                    <li class="product_page--description--li">
                                                                        <span class="product_page--description--span">Пол:</span>

                                                                        <?foreach ($arResult['PROPERTIES']['GENDER']['VALUE'] as $keyGender => $itemGender):
                                                                            $gender = CIBlockElement::GetByID($itemGender);
                                                                            if ($ar_gender = $gender->GetNext()){
                                                                                echo $ar_gender['NAME'];
                                                                                if(count($arResult['PROPERTIES']['GENDER']['VALUE'])>1 && count($arResult['PROPERTIES']['GENDER']['VALUE'])-1 != $keyGender){
                                                                                    echo ', ';
                                                                                }
                                                                            }?>
                                                                        <?endforeach; ?>
                                                                    </li>
                                                                <? endif; ?>

                                                                <?if(!empty($arResult['PROPERTIES']['SEASON']['VALUE'])):?>
                                                                    <li class="product_page--description--li">
                                                                        <span class="product_page--description--span">Сезон:</span>

                                                                        <?foreach ($arResult['PROPERTIES']['SEASON']['VALUE'] as $keySeason => $itemSeason):
                                                                            $season = CIBlockElement::GetByID($itemSeason);
                                                                            if ($ar_season = $season->GetNext()){
                                                                                echo $ar_season['NAME'];
                                                                                if(count($arResult['PROPERTIES']['SEASON']['VALUE'])>1 && count($arResult['PROPERTIES']['SEASON']['VALUE'])-1 != $keySeason){
                                                                                    echo ', ';
                                                                                }
                                                                            }?>
                                                                        <?endforeach; ?>
                                                                    </li>
                                                                <? endif; ?>

                                                                <?if(!empty($arResult['PROPERTIES']['SKIN_TYPE']['VALUE'])):?>
                                                                    <li class="product_page--description--li">
                                                                        <span class="product_page--description--span">Материал верха:</span>

                                                                        <?foreach ($arResult['PROPERTIES']['SKIN_TYPE']['VALUE'] as $keySkin_type => $itemSkin_type):
                                                                            $skin_type = CIBlockElement::GetByID($itemSkin_type);
                                                                            if ($ar_skin_type = $skin_type->GetNext()){
                                                                                echo $ar_skin_type['NAME'];
                                                                                if(count($arResult['PROPERTIES']['SKIN_TYPE']['VALUE'])>1 && count($arResult['PROPERTIES']['SKIN_TYPE']['VALUE'])-1 != $keySkin_type){
                                                                                    echo ', ';
                                                                                }
                                                                            }?>
                                                                        <?endforeach; ?>
                                                                    </li>
                                                                <? endif; ?>

                                                                <?if(!empty($arResult['PROPERTIES']['COLOR']['VALUE'])):?>
                                                                    <li class="product_page--description--li">
                                                                        <span class="product_page--description--span">Цвет:</span>

                                                                        <?foreach ($arResult['PROPERTIES']['COLOR']['VALUE'] as $keyColor => $itemColor):
                                                                            $color = CIBlockElement::GetByID($itemColor);
                                                                            if ($ar_color = $color->GetNext()){
                                                                                echo $ar_color['NAME'];
                                                                                if(count($arResult['PROPERTIES']['COLOR']['VALUE'])>1 && count($arResult['PROPERTIES']['COLOR']['VALUE'])-1 != $keyColor){
                                                                                    echo ', ';
                                                                                }
                                                                            }?>
                                                                        <?endforeach; ?>
                                                                    </li>
                                                                <? endif; ?>

                                                                <? if ($arResult['PROPERTIES']['ARTNUMBER']['VALUE']): ?>
                                                                    <li class="product_page--description--li">
                                                                        <span class="product_page--description--span">Артикул:</span> <?= $arResult['PROPERTIES']['ARTNUMBER']['VALUE'] ?>
                                                                    </li>
                                                                <? endif; ?>

                                                                <?// каблук HEEL
                                                                if(!empty($arResult['PROPERTIES']['HEEL']['VALUE'])):?>
                                                                    <li class="product_page--description--li">
                                                                        <span class="product_page--description--span">Каблук:</span>

                                                                        <?foreach ($arResult['PROPERTIES']['HEEL']['VALUE'] as $keyHeel => $itemHeel):
                                                                            $heel = CIBlockElement::GetByID($itemHeel);
                                                                            if ($ar_heel = $heel->GetNext()){
                                                                                echo $ar_heel['NAME'];
                                                                                if(count($arResult['PROPERTIES']['HEEL']['VALUE'])>1 && count($arResult['PROPERTIES']['HEEL']['VALUE'])-1 != $keyHeel){
                                                                                    echo ', ';
                                                                                }
                                                                            }?>
                                                                        <?endforeach; ?>
                                                                    </li>
                                                                <? endif; ?>

                                                                <?// Материал MATERIAL
                                                                if(!empty($arResult['PROPERTIES']['MATERIAL']['VALUE'])):?>
                                                                    <li class="product_page--description--li">
                                                                        <span class="product_page--description--span">Материал:</span>

                                                                        <?foreach ($arResult['PROPERTIES']['MATERIAL']['VALUE'] as $keyMaterial => $itemMaterial):
                                                                            $material = CIBlockElement::GetByID($itemMaterial);
                                                                            if ($ar_material = $material->GetNext()){
                                                                                echo $ar_material['NAME'];
                                                                                if(count($arResult['PROPERTIES']['MATERIAL']['VALUE'])>1 && count($arResult['PROPERTIES']['MATERIAL']['VALUE'])-1 != $keyMaterial){
                                                                                    echo ', ';
                                                                                }
                                                                            }?>
                                                                        <?endforeach; ?>
                                                                    </li>
                                                                <? endif; ?>

                                                                <?// Застежка CLASP
                                                                if(!empty($arResult['PROPERTIES']['CLASP']['VALUE'])):?>
                                                                    <li class="product_page--description--li">
                                                                        <span class="product_page--description--span">Застежка:</span>

                                                                        <?foreach ($arResult['PROPERTIES']['CLASP']['VALUE'] as $keyClasp => $itemClasp):
                                                                            $clasp = CIBlockElement::GetByID($itemClasp);
                                                                            if ($ar_clasp = $clasp->GetNext()){
                                                                                echo $ar_clasp['NAME'];
                                                                                if(count($arResult['PROPERTIES']['CLASP']['VALUE'])>1 && count($arResult['PROPERTIES']['CLASP']['VALUE'])-1 != $keyClasp){
                                                                                    echo ', ';
                                                                                }
                                                                            }?>
                                                                        <?endforeach; ?>
                                                                    </li>
                                                                <? endif; ?>

                                                                <?// Назначение APPOINTMENT
                                                                if(!empty($arResult['PROPERTIES']['APPOINTMENT']['VALUE'])):?>
                                                                    <li class="product_page--description--li">
                                                                        <span class="product_page--description--span">Назначение:</span>

                                                                        <?foreach ($arResult['PROPERTIES']['APPOINTMENT']['VALUE'] as $keyAppointment => $itemAppointment):
                                                                            $appointment = CIBlockElement::GetByID($itemAppointment);
                                                                            if ($ar_appointment = $appointment->GetNext()){
                                                                                echo $ar_appointment['NAME'];
                                                                                if(count($arResult['PROPERTIES']['APPOINTMENT']['VALUE'])>1 && count($arResult['PROPERTIES']['APPOINTMENT']['VALUE'])-1 != $keyAppointment){
                                                                                    echo ', ';
                                                                                }
                                                                            }?>
                                                                        <?endforeach; ?>
                                                                    </li>
                                                                <? endif; ?>

                                                                <?// Поп-размеры POP_DIMENSIONS
                                                                if(!empty($arResult['PROPERTIES']['POP_DIMENSIONS']['VALUE'])):?>
                                                                    <li class="product_page--description--li">
                                                                        <span class="product_page--description--span">Поп-размеры:</span>

                                                                        <?foreach ($arResult['PROPERTIES']['POP_DIMENSIONS']['VALUE'] as $keyDimensions => $itemDimensions):
                                                                            $dimensions = CIBlockElement::GetByID($itemDimensions);
                                                                            if ($ar_dimensions = $dimensions->GetNext()){
                                                                                echo $ar_dimensions['NAME'];
                                                                                if(count($arResult['PROPERTIES']['POP_DIMENSIONS']['VALUE'])>1 && count($arResult['PROPERTIES']['POP_DIMENSIONS']['VALUE'])-1 != $keyDimensions){
                                                                                    echo ', ';
                                                                                }
                                                                            }?>
                                                                        <?endforeach; ?>
                                                                    </li>
                                                                <? endif; ?>

                                                                <?// Супинатор SUPINATOR
                                                                if(!empty($arResult['PROPERTIES']['SUPINATOR']['VALUE'])):?>
                                                                    <li class="product_page--description--li">
                                                                        <span class="product_page--description--span">Супинатор:</span>

                                                                        <?foreach ($arResult['PROPERTIES']['SUPINATOR']['VALUE'] as $keySupinator => $itemSupinator):
                                                                            $supinator = CIBlockElement::GetByID($itemSupinator);
                                                                            if ($ar_supinator = $supinator->GetNext()){
                                                                                echo $ar_supinator['NAME'];
                                                                                if(count($arResult['PROPERTIES']['SUPINATOR']['VALUE'])>1 && count($arResult['PROPERTIES']['SUPINATOR']['VALUE'])-1 != $keySupinator){
                                                                                    echo ', ';
                                                                                }
                                                                            }?>
                                                                        <?endforeach; ?>
                                                                    </li>
                                                                <? endif; ?>

                                                                <?// Характеристики OPTIONS
                                                                if(!empty($arResult['PROPERTIES']['OPTIONS']['VALUE'])):?>
                                                                    <li class="product_page--description--li">
                                                                        <span class="product_page--description--span">Характеристики:</span>

                                                                        <?foreach ($arResult['PROPERTIES']['OPTIONS']['VALUE'] as $keyOptions => $itemOptions):
                                                                            $options = CIBlockElement::GetByID($itemOptions);
                                                                            if ($ar_options = $options->GetNext()){
                                                                                echo $ar_options['NAME'];
                                                                                if(count($arResult['PROPERTIES']['OPTIONS']['VALUE'])>1 && count($arResult['PROPERTIES']['OPTIONS']['VALUE'])-1 != $keyOptions){
                                                                                    echo ', ';
                                                                                }
                                                                            }?>
                                                                        <?endforeach; ?>
                                                                    </li>
                                                                <? endif; ?>

                                                                <?// Особенности FEATURES
                                                                if(!empty($arResult['PROPERTIES']['FEATURES']['VALUE'])):?>
                                                                    <li class="product_page--description--li">
                                                                        <span class="product_page--description--span">Особенности:</span>

                                                                        <?foreach ($arResult['PROPERTIES']['FEATURES']['VALUE'] as $keyFeatures => $itemFeatures):
                                                                            $features = CIBlockElement::GetByID($itemFeatures);
                                                                            if ($ar_features = $features->GetNext()){
                                                                                echo $ar_features['NAME'];
                                                                                if(count($arResult['PROPERTIES']['FEATURES']['VALUE'])>1 && count($arResult['PROPERTIES']['FEATURES']['VALUE'])-1 != $keyFeatures){
                                                                                    echo ', ';
                                                                                }
                                                                            }?>
                                                                        <?endforeach; ?>
                                                                    </li>
                                                                <? endif; ?>

                                                                <?// Повод OCCASION
                                                                if(!empty($arResult['PROPERTIES']['OCCASION']['VALUE'])):?>
                                                                    <li class="product_page--description--li">
                                                                        <span class="product_page--description--span">Повод:</span>

                                                                        <?foreach ($arResult['PROPERTIES']['OCCASION']['VALUE'] as $keyOccasion => $itemOccasion):
                                                                            $occasion = CIBlockElement::GetByID($itemOccasion);
                                                                            if ($ar_occasion = $occasion->GetNext()){
                                                                                echo $ar_occasion['NAME'];
                                                                                if(count($arResult['PROPERTIES']['OCCASION']['VALUE'])>1 && count($arResult['PROPERTIES']['OCCASION']['VALUE'])-1 != $keyOccasion){
                                                                                    echo ', ';
                                                                                }
                                                                            }?>
                                                                        <?endforeach; ?>
                                                                    </li>
                                                                <? endif; ?>
                                                            </ul>
                                                            <div class="clearfix"></div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                            <div role="tabpanel" class="tab-pane" id="reviews">

                                                <div class="product_page--description">
                                                    <div class="row">
                                                        <script type="text/javascript" src="/bitrix/js/webdebug.reviews/jquery-raty-2.7.0.min.js"></script>
                                                        <div class="col-12 col-sm-12 col-md-12 col-lg-8 col-xl-8">
                                                            <?$APPLICATION->IncludeComponent(
                                                                "webdebug:reviews2.page",
                                                                "",
                                                                Array(
                                                                    "ALLOW_VOTE" => "Y",
                                                                    "AUTO_LOADING" => "N",
                                                                    "CACHE_TIME" => "3600",
                                                                    "CACHE_TYPE" => "A",
                                                                    "COUNT" => "10",
                                                                    "DATE_FORMAT" => "d.m.Y",
                                                                    "DISPLAY_BOTTOM_PAGER" => "Y",
                                                                    "DISPLAY_TOP_PAGER" => "N",
                                                                    "FILTER_NAME" => "",
                                                                    "INTERFACE_ID" => "1",
                                                                    "JS" => "all",
                                                                    "MANUAL_CSS_INCLUDE" => "N",
                                                                    "MINIMIZE_FORM" => "Y",
                                                                    "PAGER_DESC_NUMBERING" => "N",
                                                                    "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
                                                                    "PAGER_SHOW_ALL" => "N",
                                                                    "PAGER_SHOW_ALWAYS" => "N",
                                                                    "PAGER_TEMPLATE" => ".default",
                                                                    "PAGER_TITLE" => "Отзывы",
                                                                    "SHOW_ALL_IF_ADMIN" => "Y",
                                                                    "SHOW_ANSWERS" => "Y",
                                                                    "SHOW_ANSWER_AVATAR" => "Y",
                                                                    "SHOW_ANSWER_DATE" => "Y",
                                                                    "SHOW_AVATARS" => "Y",
                                                                    "SORT_BY_1" => "ID",
                                                                    "SORT_BY_2" => "ID",
                                                                    "SORT_ORDER_1" => "DESC",
                                                                    "SORT_ORDER_2" => "DESC",
                                                                    "TARGET" => $arResult["ID"],
                                                                    "TARGET_SUFFIX" => "E_",
                                                                    "USER_ANSWER_NAME" => "#NAME# #LAST_NAME#"
                                                                )
                                                            );?><br>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                        </div>
                                    </div>


                                </div>
                                <!---->


                                <!--блок доставки десктоп-->

                                <!--                        <div class="common-for-sizes-online" --><? //= $online > 0 ? 'style="display:block"' : 'style="display:none"' ?>
                                <!--                             class="product_page--info_block block_online-js d-none d-sm-none d-md-block d-lg-block d-xl-block">-->
                                <!--                            <div class="product_page--info_text--title">Способы доставки</div>-->
                                <!--                            <div class="product_page--info_text">-->
                                <!--                                <span class="product_page--info_text--span">Для данного товара есть несколько способов доставки. Узнайте</span>-->
                                <!--                                <a href="" class="product_page--info_block--link">стоимость доставки</a>-->
                                <!--                                <span class="product_page--info_text--span">в ваш город.</span>-->
                                <!--                                <div class="clearfix"></div>-->
                                <!--                            </div>-->
                                <!--                        </div>-->

                                <!--                        <div class="common-for-sizes-online" --><? //= $online > 0 ? 'style="display:block"' : 'style="display:none"' ?>
                                <!--                             class="product_page--info_block block_online-js">-->
                                <!--                            <div class="product_page--info_text--title">Доставка в Новосибирск</div>-->
                                <!--                            <div class="product_page--info_text2">-->
                                <!--                                Срок: 2 дня<br>-->
                                <!--                                Стоимость: 700 руб.-->
                                <!--                            </div>-->
                                <!--                            <a href="" class="product_page--info_block--link product_page--info_block--link2">Подробнее-->
                                <!--                                о доставке</a>-->
                                <!--                        </div>-->
                                <!---->


                                <!--блок доставка для мобильных-->

                                <!--                        <div --><? //= $online > 0 ? 'style="display:block"' : 'style="display:none"' ?>
                                <!--                                class="common-for-sizes-online d-block d-sm-block d-md-none d-lg-none d-xl-none"-->
                                <!--                                id="accordion_product"-->
                                <!--                                role="tablist">-->
                                <!--                            <div class="card">-->
                                <!--                                <div class="card-header" role="tab" id="headingOne">-->
                                <!--                                    <div class="mb-0">-->
                                <!--                                        <a class="mob--title" data-toggle="collapse" href="#collapseOne"-->
                                <!--                                           aria-expanded="true" aria-controls="collapseOne">-->
                                <!--                                            Доставка-->
                                <!--                                        </a>-->
                                <!--                                    </div>-->
                                <!--                                </div>-->
                                <!---->
                                <!--                                <div id="collapseOne" class="collapse" role="tabpanel" aria-labelledby="headingOne"-->
                                <!--                                     data-parent="#accordion">-->
                                <!--                                    <div class="product_page--info_block block_online-js">-->
                                <!--                                        <div class="product_page--info_text--title">Способы доставки</div>-->
                                <!--                                        <div class="product_page--info_text">-->
                                <!--                                            <span class="product_page--info_text--span">Для данного товара есть несколько способов доставки. Узнайте</span>-->
                                <!--                                            <a href="" class="product_page--info_block--link">стоимость доставки</a>-->
                                <!--                                            <span class="product_page--info_text--span">в ваш город.</span>-->
                                <!--                                            <div class="clearfix"></div>-->
                                <!--                                        </div>-->
                                <!--                                    </div>-->
                                <!---->
                                <!--                                    <div class="product_page--info_block block_online-js">-->
                                <!--                                        <div class="product_page--info_text--title">Доставка в Новосибирск</div>-->
                                <!--                                        <div class="product_page--info_text2">-->
                                <!--                                            Срок: 2 дня<br>-->
                                <!--                                            Стоимость: 700 руб.-->
                                <!--                                        </div>-->
                                <!--                                        <a href=""-->
                                <!--                                           class="product_page--info_block--link product_page--info_block--link2">Подробнее-->
                                <!--                                            о доставке</a>-->
                                <!--                                    </div>-->
                                <!--                                </div>-->
                                <!--                            </div>-->
                                <!--                        </div>-->

                                <!---->

                            </div>
                        </div>
                    </div>

                    <div class="product_page--slider">
                        <div class="container">
                            <div class="row">
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                    <? $APPLICATION->IncludeFile(
                                        $APPLICATION->GetTemplatePath("include/product/viewer_slider.php"),
                                        array('productId' => $arResult['ID']),
                                        array("MODE" => "html")
                                    ); ?>

                                </div>
                            </div>
                        </div>
                    </div>
                    <? if (!empty($arResult['PROPERTIES']['RECOMMEND']['VALUE'])): ?>
                        <div class="product_page--slider">
                            <div class="container">
                                <div class="row">
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                        <? $APPLICATION->IncludeFile(
                                            $APPLICATION->GetTemplatePath("include/product/related_products.php"),
                                            array('arProduct' => $arResult['PROPERTIES']['RECOMMEND']['VALUE']),
                                            array("MODE" => "html")
                                        ); ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                    <? endif; ?>

                </div>
            </div>
        </div>
    </div>
</section>

<script>
    //    window.onload = function() {
    //    };
    $(document).ready(function () {

        window.ajax = 'Y';
        window.cart = 'Y';

        $('.product_page').on('click', '.header_black--city_modal-js', function (e) {
            $('#modalCity').modal('show');
        });

        $('#modalListMap').on('click', '.header_black--city_modal-js', function (e) {
            $('#modalCity').modal('show');
        });
        var sizeReturn = showModalMagazin($('.show-availability-js:first'), true);

        var width = $(window).width();
        if (width <= 767) {
            var product = new Swiper('.swiper-container--product', {
                loop: true,
                pagination: {
                    el: '.swiper-pagination_product',
                    clickable: true,
                },
                slidesPerView: 1,
            });
        }
        else if (width > 767) {
            var product = new Swiper(".swiper-container--product", {
                pagination: {
                    el: '.swiper-pagination_product',
                    clickable: false,
                },
                navigation: {
                    nextEl: '.swiper-button-next_product',
                    prevEl: '.swiper-button-prev_product',
                },
                loop: true,
                slidesPerView: 1,
                on: {
                    init: function () {
                        $(".left_card_main .swiper-container .resize-image-js").each(function (e, t) {
                            var n = $(t), i = n.parent(), j = i.find('.origin-image-js');
                            i.on("mouseenter", function () {
                                n.css('display', 'none');
                                j.css('display', 'block');
                                i.css({height: i.outerHeight()}), j.addClass("zoome")
                            }).mousemove(function (e) {
                                var t = i.outerHeight(), r = j.innerHeight(), a = (r - t) / t, s = e.pageY - i.offset().top;
                                j.css({top: -(s * a)})
                            }).on("mouseleave", function () {
                                j.removeClass("zoome");
                                j.css('display', 'none');
                                n.css('display', 'block');
                            })
                        })
                    }
                }
            });
        }
        $(".nav-link").on("click", function () {
            return product.slideTo($(this).index() + 1), $(".nav-link").removeClass("active"), $(this).addClass("active"), !1
        });


        product.on('slideChange', function (e) {
            $(".nav-link").removeClass("active");
            var t = product.realIndex;
            $(".nav-link:nth-child(" + (t + 1) + ")").addClass("active")
        });

        $('.parent-to-js').on('click', '.product-size-click-js', function () {
            var ofline = $(this).attr('data-offline-amount');
            var online = $(this).attr('data-online-amount');
            var offer = $(this).attr('data-offer-id');
            $('.product-cart-hint-js').hide();
            $('.product-one-cart-hint-js').hide();

            if (ofline > 0) {
                if (online > 0) {
                    $('.common-for-sizes-offline').hide();
                    $('.product_offline_score-js').hide();
                } else {
                    $('.common-for-sizes-offline').show();
                    $('.product_offline_score-js').show();
                }
            } else {
                $('.common-for-sizes-offline').hide();
                $('.product_offline_score-js').hide();
            }
            if (online > 0) {
                $('.block_online').show();
            } else {
                $('.block_online').hide();
            }
            $(".product-size-click-js").removeClass('product_page--size_active');
            $(this).addClass('product_page--size_active');
            $('.product-click-js').attr('data-product-id', offer);
            $('.product-one-click-js').attr('data-product-id', offer);
        });

        $('.parent-to-js').on('click', '.product-size-clear-js', function () {
            var ofline = $(this).attr('data-offline');
            var online = $(this).attr('data-online');
            $(".product-size-click-js").removeClass('product_page--size_active');//Убираем активность с размера
            $('.product-click-js').attr('data-product-id', '');//Убираем размер с кнопки купить
            $('.product-one-click-js').attr('data-product-id', '');//Убираем размер с кнопки купить
            if(online>0){
                $('.block_online').show();
            }else{
                $('.block_online').hide();
            }
            if(ofline>0){
                if(online>0) {
                    $('.common-for-sizes-offline').hide();
                    $('.product_offline_score-js').hide();
                }else{
                    $('.common-for-sizes-offline').show();
                    $('.product_offline_score-js').show();
                }
            }
//            $('.product_page-availability-js').show();

            $(".mob-size-def-js").removeClass('product_page--size_active');
            $(".mob-size-ru-js").removeClass('product_page--size_active');
            $('.mob-size-parent-js').text('Выбрать размер');
        });
    });

    if (!jQuery.cookie('click_next')) {
        setTimeout(function () {
            jQuery(document).ready(function () {
                showWindow();
            })
        }, 5000);
    }

    //наведение на размер в карточке
    $('.parent-to-js').on('mouseenter', '.product-size-hover-js', function () {
        var elem = $(this).parent().find('.product-ru-size-js');
        $('.product-ru-size-js').hide();
        elem.fadeIn();
    }).on('mouseleave', '.product-size-hover-js', function () {
        var elem = $(this).parent().find('.product-ru-size-js');
        elem.fadeOut();
    });

    $('.parent-to-js').on('click', '.mob-size-def-js', function () {
        $('.mob-size-parent-js').dropdown('toggle');

        var size = $(this).text();
        var offerId = $(this).attr('data-offer-id');
        $('.mob-size-parent-js').text(size);
        var sizeRu = $('.mob-size-up-js').find('.mob-size-ru-js[data-offer-id=' + offerId + ']');

        var ofline = $(this).attr('data-offline-amount');
        var online = $(this).attr('data-online-amount');
        var offer = $(this).attr('data-offer-id');

        if (ofline > 0) {
            if (online > 0) {
                $('.common-for-sizes-offline').hide();
                $('.product_offline_score-js').hide();
            } else {
                $('.common-for-sizes-offline').show();
                $('.product_offline_score-js').show();
            }
        } else {
            $('.common-for-sizes-offline').hide();
            $('.product_offline_score-js').hide();
        }
        if (online > 0) {
            $('.block_online').show();
        } else {
            $('.block_online').hide();
        }
        $(".mob-size-def-js").removeClass('product_page--size_active');
        $(".mob-size-ru-js").removeClass('product_page--size_active');
        $(this).addClass('product_page--size_active');
        $('.product-click-js').attr('data-product-id', offer);
        $('.product-one-click-js').attr('data-product-id', offer);
        sizeRu.addClass('product_page--size_active');
    });

    $('.parent-to-js').on('click', '.mob-size-ru-js', function () {
        $('.mob-size-parent-js').dropdown('toggle');

        var size = $(this).attr('data-size');
        var offerId = $(this).attr('data-offer-id');
        $('.mob-size-parent-js').text(size);
        var sizeDef = $('.mob-size-up-js').find('.mob-size-def-js[data-offer-id=' + offerId + ']');
        var ofline = $(this).attr('data-offline-amount');
        var online = $(this).attr('data-online-amount');
        var offer = $(this).attr('data-offer-id');

        if (ofline > 0) {
            if (online > 0) {
                $('.common-for-sizes-offline').hide();
                $('.product_offline_score-js').hide();
            } else {
                $('.common-for-sizes-offline').show();
                $('.product_offline_score-js').show();
            }
        } else {
            $('.common-for-sizes-offline').hide();
            $('.product_offline_score-js').hide();
        }
        if (online > 0) {
            $('.block_online').show();
        } else {
            $('.block_online').hide();
        }
        $(".mob-size-def-js").removeClass('product_page--size_active');
        $(".mob-size-ru-js").removeClass('product_page--size_active');
        $(this).addClass('product_page--size_active');
        sizeDef.addClass('product_page--size_active');
        $('.product-click-js').attr('data-product-id', offer)
        $('.product-one-click-js').attr('data-product-id', offer);
    });

    $(document).on("click.bs.dropdown.data-api", ".noclose", function (e) {
        e.stopPropagation()
    });
    $(document).on("click.bs.dropdown.data-api", ".b_mob--size--block1", function (e) {
        e.stopPropagation()
    });
    $(document).on("click.bs.dropdown.data-api", ".b_mob--size--block2", function (e) {
        e.stopPropagation()
    });

    function showWindow() {
        jQuery(".product_page-but1-js").addClass('active');
        jQuery(".product_page-message-js").addClass('active');
        setTimeout(function () {
            jQuery(document).ready(function () {
                jQuery(".product_page-but1-js").removeClass('active');
                jQuery(".product_page-message-js").removeClass('active');
            })
        }, 2000);
    }
    $(".product_page-but1-js").on("click", function () {
        jQuery.cookie('click_next', true, {
            expires: 10,
            path: '/'
        });
    });


</script>

<!---->
<!--<script>-->
<!--    $(document).ready(function () {-->
<!--        $(window).resize(function () {-->
<!--            if ($(window).width() <= 499) {-->
<!--                (function ($) {-->
<!---->
<!--                        $(".modal_size--table--width table").mCustomScrollbar({axis: "x", theme: "dark"});-->
<!--                        $(".all--block--width .all--block").mCustomScrollbar({axis: "x", theme: "dark"});-->
<!---->
<!---->
<!--                })(jQuery);-->
<!--            }-->
<!--            else if ($(window).width() > 499) {-->
<!--                $(".modal_size--table--width table").mCustomScrollbar("disable",true);-->
<!--                $(".all--block--width .all--block").mCustomScrollbar("disable",true);-->
<!--                $(".all--block--width .all--block").css("width", "100%");-->
<!--            }-->
<!--        });-->
<!--    });-->
<!--    if ($(window).width() <= 499) {-->
<!--        (function ($) {-->
<!--            $(window).on("load", function () {-->
<!--                $(".modal_size--table--width table").mCustomScrollbar({axis: "x", theme: "dark"});-->
<!--                $(".all--block--width .all--block").mCustomScrollbar({axis: "x", theme: "dark"});-->
<!---->
<!--            });-->
<!--        })(jQuery);-->
<!--    }-->
<!---->
<!--</script>-->
