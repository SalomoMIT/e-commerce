<div class="container">
<div class="navbar navbar-light navbar-expand">
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
<div id="mega_menu_content_<?= $category->id; ?>" class="dropdown-menu dropdown-menu-large mds-scrollbar">
<div class="row">
<div class="col-4 left">
<?php $count = 0;
foreach ($childs as $child): ?>
<div class="large-menu-item <?= $count == 0 ? 'large-menu-item-first active' : ''; ?>" data-subcategory-id="<?= $child->id; ?>">
<a id="nav_main_category_<?= $child->id; ?>" href="<?= generateCategoryUrl($child); ?>" class="second-category nav-main-category" data-id="<?= $child->id; ?>" data-parent-id="<?= $child->parent_id; ?>" data-has-sb="<?= !empty($child->has_subcategory) ? '1' : '0'; ?>"><?= esc($child->cat_name); ?>&nbsp;<i class="icon-arrow-right"></i></a>
</div>
<?php $count++;
endforeach; ?>
</div>
<div class="col-8 right">
<?php $count = 0;
foreach ($childs as $child): ?>
<div id="large_menu_content_<?= $child->id; ?>" class="large-menu-content <?= ($count == 0) ? 'large-menu-content-first active' : ''; ?>">
<?php if (!empty($child->children) && countItems($child->children) > 0):
$subChilds = $child->children; ?>
<div class="row">
<div class="card-columns">
<?php foreach ($subChilds as $subChild): ?>
<div class="card item-large-menu-content">
<a id="nav_main_category_<?= $subChild->id; ?>" href="<?= generateCategoryUrl($subChild); ?>" class="second-category nav-main-category" data-id="<?= $subChild->id; ?>" data-parent-id="<?= $subChild->parent_id; ?>" data-has-sb="0"><?= esc($subChild->cat_name); ?></a>
</div>
<?php endforeach; ?>
</div>
</div>
<?php endif; ?>
</div>
<?php
$count++;
endforeach; ?>
</div>
</div>
</div>
<?php endif; ?>
</li>
<?php $i++;
endif;
endforeach;
if (countItems($menuCategories) > $limit): ?>
<li class="nav-item dropdown" data-category-id="more">
<a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false"><?= trans("more"); ?>&nbsp;<i class="icon-arrow-down"></i></a>
<div id="mega_menu_content_more" class="dropdown-menu dropdown-menu-large mds-scrollbar">
<div class="row">
<div class="col-4 left">
<?php $i = 0;
$count = 0;
foreach ($menuCategories as $category):
if ($i >= $limit): ?>
<div class="large-menu-item <?= $count == 0 ? 'large-menu-item-first active' : ''; ?>" data-subcategory-id="<?= $category->id; ?>">
<a id="nav_main_category_<?= $category->id; ?>" href="<?= generateCategoryUrl($category); ?>" class="second-category nav-main-category" data-id="<?= $category->id; ?>" data-parent-id="<?= $category->parent_id; ?>" data-has-sb="<?= !empty($category->has_subcategory) ? '1' : '0'; ?>"><?= esc($category->cat_name); ?>&nbsp;<i class="icon-arrow-right"></i></a>
</div>
<?php $count++;
endif;
$i++;
endforeach; ?>
</div>
<div class="col-8 right">
<?php $i = 0;
$count = 0;
foreach ($menuCategories as $category):
if ($i >= $limit): ?>
<div id="large_menu_content_<?= $category->id; ?>" class="large-menu-content <?= $count == 0 ? 'large-menu-content-first active' : ''; ?>">
<?php if (!empty($category->children) && countItems($category->children) > 0):
$childs = $category->children; ?>
<div class="row">
<div class="card-columns">
<?php foreach ($childs as $child): ?>
<div class="card item-large-menu-content">
<a id="nav_main_category_<?= $child->id; ?>" href="<?= generateCategoryUrl($child); ?>" class="second-category nav-main-category" data-id="<?= $child->id; ?>" data-parent-id="<?= $child->parent_id; ?>" data-has-sb="<?= !empty($child->has_subcategory) ? '1' : '0'; ?>"><?= esc($child->cat_name); ?></a>
</div>
<?php if (!empty($child->children)):
foreach ($child->children as $subChild): ?>
<div class="hidden"><a id="nav_main_category_<?= $subChild->id; ?>" href="<?= generateCategoryUrl($subChild); ?>" class="nav-main-category" data-id="<?= $subChild->id; ?>" data-parent-id="<?= $subChild->parent_id; ?>" data-has-sb="0"><?= esc($subChild->cat_name); ?></a></div>
<?php endforeach;
endif; ?>
<?php endforeach; ?>
</div>
</div>
<?php endif; ?>
</div>
<?php $count++;
endif;
$i++;
endforeach; ?>
</div>
</div>
</div>
</li>
<?php endif;
endif; ?>
</ul>
</div>
</div>