<div id="wrapper">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav class="nav-breadcrumb" aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= langBaseUrl(); ?>"><?= trans("home"); ?></a></li>
                        <li class="breadcrumb-item"><a href="<?= generateUrl('settings', 'edit_profile'); ?>"><?= trans("profile_settings"); ?></a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?= $title; ?></li>
                    </ol>
                </nav>
                <h1 class="page-title"><?= trans("profile_settings"); ?></h1>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 col-md-3">
                <div class="row-custom">
                    <?= view("settings/_tabs"); ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-9">
                <div class="row-custom">
                    <div class="sidebar-tabs-content">
                        <?= view('partials/_messages'); ?>
                        <form action="<?= base_url('social-media-post'); ?>" method="post" id="form_validate">
                            <?= csrf_field(); ?>
                            <?php $socialArray = getSocialLinksArray(user(), true);
                            foreach ($socialArray as $item):?>
                                <div class="form-group">
                                    <label class="control-label"><?= trans($item['inputName']); ?></label>
                                    <input type="text" class="form-control form-input" name="<?= $item['inputName']; ?>" placeholder="<?= trans($item['inputName']); ?>" value="<?= esc($item['value']); ?>" maxlength="1000">
                                </div>
                            <?php endforeach; ?>
                            <button type="submit" class="btn btn-md btn-custom m-t-10">
                                <?= trans("save_changes") ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>