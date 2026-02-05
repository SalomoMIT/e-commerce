<div class="container">
<div class="navbar navbar-light navbar-expand">
<button class="scroll-btn scroll-btn-left"><i class="icon-arrow-left"></i></button>
<ul class="nav navbar-nav mega-menu">
<?php $limit = $generalSettings->menu_limit;
$i = 0;
if (!empty($menuCategories)):
foreach ($menuCategories as $category):
if ($i < $limit):?>
<li class="nav-item dropdown" data-category-id="<?= $category->id; ?>">
<a id="nav_main_category_<?= $category->id; ?>" href="<?= generateCategoryUrl($category); ?>" class="nav-link dropdown-toggle nav-main-category" data-id="<?= $category->id; ?>" data-parent-id="<?= $category->parent_id; ?>" data-has-sb="<?= !empty($category->has_subcategory) ? '1' : '0'; ?>"><?= esc($category->cat_name); ?></a>
<?php if (!empty($category->children) && countItems($category->children) > 0):
$childs = $category->children; ?>
<div id="mega_menu_content_<?= $category->id; ?>" class="dropdown-menu mega-menu-content mds-scrollbar">
<div class="row">
<div class="<?= !empty($category->childrenWithImage) ? 'col-8 col-category-links' : 'col-12'; ?> menu-subcategories">
<div class="row" data-masonry='<?= $baseVars->rtl ? '{"percentPosition": true, "isOriginLeft": false}' : '{"percentPosition": true}' ?>'>
<?php foreach ($childs as $child): ?>
<div class="col-3 <?= empty($category->childrenWithImage) ? 'col-20-percent' : ''; ?> mb-3">
<a id="nav_main_category_<?= $child->id; ?>" href="<?= generateCategoryUrl($child); ?>" class="second-category nav-main-category" data-id="<?= $child->id; ?>" data-parent-id="<?= $child->parent_id; ?>" data-has-sb="<?= !empty($child->has_subcategory) ? '1' : '0'; ?>"><?= esc($child->cat_name); ?></a>
<?php if (!empty($child->children) && countItems($child->children) > 0):
$subChilds = $child->children; ?>
<ul>
<?php foreach ($subChilds as $subChild): ?>
<li><a id="nav_main_category_<?= $subChild->id; ?>" href="<?= generateCategoryUrl($subChild); ?>" class="nav-main-category" data-id="<?= $subChild->id; ?>" data-parent-id="<?= $subChild->parent_id; ?>" data-has-sb="0"><?= esc($subChild->cat_name); ?></a></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
</div>
<?php endforeach; ?>
</div>
</div>
<?php if (!empty($category->childrenWithImage)): ?>
<div class="col-4 col-category-images">
<?php foreach ($category->childrenWithImage as $imageCategory): ?>
<div class="nav-category-image">
<a href="<?= generateCategoryUrl($imageCategory); ?>">
<img data-src="<?= getStorageFileUrl($imageCategory->image, $imageCategory->storage); ?>" alt="<?= esc($imageCategory->cat_name); ?>" class="lazyload img-fluid" width="194" height="194">
<span><?= characterLimiter(esc($imageCategory->cat_name), 20, '..'); ?></span>
</a>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
</div>
<?php endif; ?>
</li>
<?php $i++;
endif;
endforeach;
if (countItems($menuCategories) > $limit):?>
<li class="nav-item dropdown" data-category-id="more">
<a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false"><?= trans("more"); ?>&nbsp;<i class="icon-arrow-down"></i></a>
<div id="mega_menu_content_more" class="dropdown-menu mega-menu-content mega-menu-content-more mds-scrollbar">
<div class="row">
<div class="col-12 menu-subcategories">
<div class="row" data-masonry='<?= $baseVars->rtl ? '{"percentPosition": true, "isOriginLeft": false}' : '{"percentPosition": true}' ?>'>
<?php $i = 0;
if (!empty($menuCategories)):
foreach ($menuCategories as $category):
if ($i >= $limit):?>
<div class="col-3 col-20-percent mb-3">
<a id="nav_main_category_<?= $category->id; ?>" href="<?= generateCategoryUrl($category); ?>" class="second-category nav-main-category" data-id="<?= $category->id; ?>" data-parent-id="<?= $category->parent_id; ?>" data-has-sb="<?= !empty($category->has_subcategory) ? '1' : '0'; ?>"><?= esc($category->cat_name); ?></a>
<?php if (!empty($category->children) && countItems($category->children) > 0):
$childs = $category->children; ?>
<ul>
<?php foreach ($childs as $child): ?>
<li><a id="nav_main_category_<?= $child->id; ?>" href="<?= generateCategoryUrl($child); ?>" class="nav-main-category" data-id="<?= $child->id; ?>" data-parent-id="<?= $child->parent_id; ?>" data-has-sb="<?= !empty($child->has_subcategory) ? '1' : '0'; ?>"><?= esc($child->cat_name); ?></a></li>
<?php if (!empty($child->children)):
foreach ($child->children as $subChild): ?>
<li class="hidden"><a id="nav_main_category_<?= $subChild->id; ?>" href="<?= generateCategoryUrl($subChild); ?>" class="nav-main-category" data-id="<?= $subChild->id; ?>" data-parent-id="<?= $subChild->parent_id; ?>" data-has-sb="0"><?= esc($subChild->cat_name); ?></a></li>
<?php endforeach;
endif;
endforeach; ?>
</ul>
<?php endif; ?>
</div>
<?php endif;
$i++;
endforeach;
endif; ?>
</div>
</div>
</div>
</div>
</li>
<?php endif;
endif; ?>
</ul>
<button class="scroll-btn scroll-btn-right"><i class="icon-arrow-right"></i></button>
</div>
</div>