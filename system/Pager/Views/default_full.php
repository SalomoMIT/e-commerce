<?php

use CodeIgniter\Pager\PagerRenderer;

/**
 * @var PagerRenderer $pager
 */
$pager->setSurroundCount(2);
if (countItems($pager->links()) > 1): ?>
    <nav aria-label="pagination">
        <ul class="pagination">

            <?php if ($pager->hasPrevious()) : ?>
                <li class="page-item">
                    <a href="<?= $pager->getFirst() ?>" class="page-link" aria-label="first">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            <?php endif ?>

            <?php if ($pager->getPreviousPage()) : ?>
                <li class="page-item">
                    <a href="<?= $pager->getPreviousPage() ?>" class="page-link" aria-label="previous">
                        <span aria-hidden="true">&lsaquo;</span>
                    </a>
                </li>
            <?php endif ?>

            <?php foreach ($pager->links() as $link) : ?>
                <li class="page-item<?= $link['active'] ? ' active' : '' ?>">
                    <a href="<?= $link['uri'] ?>" class="page-link">
                        <?= $link['title'] ?>
                    </a>
                </li>
            <?php endforeach ?>

            <?php if ($pager->getNextPage()) : ?>
                <li class="page-item">
                    <a href="<?= $pager->getNextPage() ?>" class="page-link" aria-label="next">
                        <span aria-hidden="true">&rsaquo;</span>
                    </a>
                </li>
            <?php endif ?>

            <?php if ($pager->hasNext()) : ?>
                <li class="page-item">
                    <a href="<?= $pager->getLast() ?>" class="page-link" aria-label="last">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            <?php endif ?>

        </ul>
    </nav>
<?php endif; ?>