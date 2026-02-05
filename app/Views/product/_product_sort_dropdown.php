<?php $filterSort = strSlug(inputGet('sort'));
if (!in_array($filterSort, ["most_recent", "lowest_price", "highest_price", "highest_rating"])) {
    $filterSort = 'most_recent';
} ?>
<div class="dropdown">
    <button class="btn btn-md dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
            <path fill="#666666" d="M13.47 7.53a.75.75 0 0 0 1.06 0l.72-.72V17a.75.75 0 0 0 1.5 0V6.81l.72.72a.75.75 0 1 0 1.06-1.06l-2-2a.75.75 0 0 0-1.06 0l-2 2a.75.75 0 0 0 0 1.06m-4.72 9.66l.72-.72a.75.75 0 1 1 1.06 1.06l-2 2a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l.72.72V7a.75.75 0 0 1 1.5 0z"/>
        </svg>
        &nbsp;&nbsp;<?= trans($filterSort); ?>&nbsp;&nbsp;<i class="icon-arrow-down"></i>
    </button>
    <div class="dropdown-menu dropdownSortOptions">
        <button type="button" class="dropdown-item" data-action="most_recent"><?= trans("most_recent"); ?></button>
        <button type="button" class="dropdown-item" data-action="lowest_price"><?= trans("lowest_price"); ?></button>
        <button type="button" class="dropdown-item" data-action="highest_price"><?= trans("highest_price"); ?></button>
        <button type="button" class="dropdown-item" data-action="highest_rating"><?= trans("highest_rating"); ?></button>
    </div>
</div>