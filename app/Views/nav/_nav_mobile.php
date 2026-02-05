<div id="navMobile" class="nav-mobile">
<div class="nav-mobile-sc">
<div class="nav-mobile-inner">
<div class="row">
<div class="col-sm-12 mobile-nav-buttons">
<?php if ($generalSettings->multi_vendor_system == 1):
if (authCheck()): ?>
<a href="<?= generateDashUrl("add_product"); ?>" class="btn btn-md btn-custom btn-block"><?= trans("sell_now"); ?></a>
<?php else: ?>
<button type="button" class="btn btn-md btn-custom btn-block close-menu-click" data-toggle="modal" data-target="#loginModal" aria-label="mobile-sell-now"><?= trans("sell_now"); ?></button>
<?php endif;
endif; ?>
</div>
</div>
<div class="row">
<div class="col-sm-12">
<div class="nav nav-tabs nav-tabs-mobile-menu" id="nav-tab">
<button class="nav-link active" data-toggle="tab" data-target="#tabMobileMainMenu" type="button" aria-label="button-open-main-menu"><?= trans("main_menu"); ?></button>
<button class="nav-link" id="nav-profile-tab" data-toggle="tab" data-target="#tabMobileCategories" type="button" aria-label="button-open-categories"><?= trans("categories"); ?></button>
</div>
<div class="tab-content tab-content-mobile-menu nav-mobile-links">
<div class="tab-pane fade show active" id="tabMobileMainMenu" role="tabpanel">
<ul id="navbar_mobile_links" class="navbar-nav">
<?php if (authCheck()): ?>
<li class="dropdown profile-dropdown nav-item">
<a href="#" class="dropdown-toggle image-profile-drop nav-link" data-toggle="dropdown" aria-expanded="false">
<?php if ($baseVars->unreadMessageCount > 0): ?>
<span class="message-notification message-notification-mobile"><?= $baseVars->unreadMessageCount; ?></span>
<?php endif; ?>
<img src="<?= getUserAvatar(user()->avatar, user()->storage_avatar); ?>" alt="<?= esc(getUsername(user())); ?>" width="42" height="42">
<?= esc(getUsername(user())); ?> <span class="icon-arrow-down"></span>
</a>
<ul class="dropdown-menu">
<?= view("nav/_profile_dropdown.php", ['profileMenuAdmin' => true, 'profileMenuDash' => true]); ?>
</ul>
</li>
<?php endif; ?>
<li class="nav-item"><a href="<?= langBaseUrl(); ?>" class="nav-link"><?= trans("home"); ?></a></li>
<li class="nav-item"><a href="<?= generateUrl('wishlist'); ?>" class="nav-link"><?= trans("wishlist"); ?></a></li>
<?php if (!empty($menuLinks)):
foreach ($menuLinks as $menuLink):
if ($menuLink->page_default_name == 'blog' || $menuLink->page_default_name == 'contact' || $menuLink->location == 'top_menu'):
$itemLink = generateMenuItemUrl($menuLink);
if (!empty($menuLink->page_default_name)):
$itemLink = generateUrl($menuLink->page_default_name);
endif; ?>
<li class="nav-item"><a href="<?= $itemLink; ?>" class="nav-link"><?= esc($menuLink->title); ?></a></li>
<?php endif;
endforeach;
endif;
if (!authCheck()): ?>
<li class="nav-item">
<button type="button" data-toggle="modal" data-target="#loginModal" class="nav-link close-menu-click button-link" aria-label="nav-login-menu"><?= trans("login"); ?></button>
</li>
<li class="nav-item"><a href="<?= generateUrl('register'); ?>" class="nav-link"><?= trans("register"); ?></a></li>
<?php endif;
if ($generalSettings->location_search_header == 1 && countItems($activeCountries) > 0): ?>
<li class="nav-item nav-item-messages">
<button type="button" data-toggle="modal" data-target="#locationModal" class="nav-link btn-modal-location close-menu-click button-link" aria-label="nav-location-menu">
<i class="icon-map-marker float-left"></i>&nbsp;<?= !empty($baseVars->defaultLocationInput) ? $baseVars->defaultLocationInput : trans("location"); ?>
</button>
<?php if (!empty($baseVars->defaultLocationInput)): ?>
<form action="<?= base_url('Home/setDefaultLocationPost'); ?>" method="post" class="display-inline-block m-b-10">
<?= csrf_field(); ?>
<button type="submit" name="submit" value="reset" class="btn-reset-location"><?= trans("reset"); ?></button>
</form>
<?php endif; ?>
</li>
<?php endif; ?>
<li class="d-flex justify-content-center mobile-flex-dropdowns">
<?php if ($generalSettings->multilingual_system == 1 && countItems($activeLanguages) > 1): ?>
<div class="nav-item dropdown top-menu-dropdown">
<button type="button" class="nav-link dropdown-toggle button-link" data-toggle="dropdown" aria-label="nav-flag-menu">
<img src="<?= base_url($activeLang->flag_path); ?>" class="flag" alt="<?= esc($activeLang->name) . " " . trans("active"); ?>-mb" style="width: 18px; height: auto;"><?= esc($activeLang->name); ?>&nbsp;<i class="icon-arrow-down"></i>
</button>
<ul class="dropdown-menu dropdown-menu-lang">
<?php foreach ($activeLanguages as $language): ?>
<li>
<a href="<?= convertUrlByLanguage($language); ?>" class="dropdown-item <?= $language->id == $activeLang->id ? 'selected' : ''; ?>">
<img src="<?= base_url($language->flag_path); ?>" class="flag" alt="<?= esc($language->name); ?>-mb" style="width: 18px; height: auto;"><?= esc($language->name); ?>
</a>
</li>
<?php endforeach; ?>
</ul>
</div>
<?php endif;
if ($paymentSettings->currency_converter == 1 && countItems($currencies) > 1): ?>
<div class="nav-item dropdown top-menu-dropdown">
<button type="button" class="nav-link dropdown-toggle button-link" data-toggle="dropdown" aria-label="nav-currency-menu">
<?= getSelectedCurrency()->code; ?>&nbsp;(<?= getSelectedCurrency()->symbol; ?>)&nbsp;<i class="icon-arrow-down"></i>
</button>
<form action="<?= base_url('set-selected-currency-post'); ?>" method="post">
<?= csrf_field(); ?>
<ul class="dropdown-menu">
<?php foreach ($currencies as $currency):
if ($currency->status == 1):?>
<li>
<button type="submit" name="currency" value="<?= $currency->code; ?>"><?= $currency->code; ?>&nbsp;(<?= $currency->symbol; ?>)</button>
</li>
<?php endif;
endforeach; ?>
</ul>
</form>
</div>
<?php endif; ?>
</li>
</ul>
</div>
<div class="tab-pane fade" id="tabMobileCategories" role="tabpanel">
<div id="navbar_mobile_back_button"></div>
<ul id="navbar_mobile_categories" class="navbar-nav navbar-mobile-categories">
<?php if (!empty($parentCategories)):
foreach ($parentCategories as $category):
if ($category->has_subcategory > 0): ?>
<li class="nav-item">
<button type="button" class="nav-link button-link" data-id="<?= $category->id; ?>" data-parent-id="<?= $category->parent_id; ?>" aria-label="nav-category-<?= $category->id; ?>"><?= esc($category->cat_name); ?><i class="icon-arrow-right"></i></button>
</li>
<?php else: ?>
<li class="nav-item"><a href="<?= generateCategoryUrl($category); ?>" class="nav-link"><?= esc($category->cat_name); ?></a></li>
<?php endif; ?>
<?php endforeach;
endif; ?>
</ul>
</div>
</div>
</div>
</div>
</div>
</div>
</div>