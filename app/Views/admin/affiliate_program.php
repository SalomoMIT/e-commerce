<div class="row">
    <div class="col-sm-12 title-section">
        <h3><?= trans('affiliate_program'); ?></h3>
    </div>
</div>
<div class="row">
    <div class="col-sm-12 m-b-30">
        <label><?= trans("language"); ?></label>
        <select name="lang_id" class="form-control" onchange="window.location.href = '<?= adminUrl('affiliate-program'); ?>?lang='+this.value;" style="max-width: 600px;">
            <?php foreach ($activeLanguages as $language): ?>
                <option value="<?= $language->id; ?>" <?= $language->id == $settingsLang ? 'selected' : ''; ?>><?= esc($language->name); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
<div class="row">
    <div class="col-lg-6 col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><?= trans('settings'); ?></h3>
            </div>
            <form action="<?= base_url('Admin/affiliateProgramPost'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="box-body">
                    <div class="form-group">
                        <?= formSwitch('status', trans('status'), $affiliateSettings->status); ?>
                    </div>

                    <div class="form-group radio-affiliate-type">
                        <label><?= trans("program_type"); ?></label>
                        <?= formRadio('type', 'site_based', 'seller_based', trans("affiliate_site_based"), trans("affiliate_seller_based"), $affiliateSettings->type, 'col-md-12'); ?>
                    </div>

                    <div id="commissionRateContainer" <?= $affiliateSettings->type == 'seller_based' ? 'style="display:none;"' : ''; ?>>
                        <div class="form-group">
                            <label><?= trans('referrer_commission_rate'); ?></label>
                            <div class="input-group">
                                <span class="input-group-addon">%</span>
                                <input type="number" name="commission_rate" class="form-control" min="0" max="99" step="0.01" value="<?= $affiliateSettings->commission_rate; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label><?= trans('buyer_discount_rate'); ?></label>
                            <div class="input-group">
                                <span class="input-group-addon">%</span>
                                <input type="number" name="discount_rate" class="form-control" min="0" max="99" step="0.01" value="<?= $affiliateSettings->discount_rate; ?>">
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label class="control-label"><?= trans('image'); ?>&nbsp;(1200x980px)</label>
                        <div class="m-b-10">
                            <img src="<?= getStorageFileUrl($affiliateSettings->image, $affiliateSettings->storage, 'affiliate_bg'); ?>" alt="" style="max-width: 160px; max-height: 160px;">
                        </div>
                        <div class="display-block">
                            <a class='btn btn-success btn-sm btn-file-upload'>
                                <?= trans('select_image'); ?>
                                <input type="file" name="file" size="40" accept=".png, .jpg, .jpeg, .gif, .webp" onchange="$('#upload-file-info1').html($(this).val().replace(/.*[\/\\]/, ''));">
                            </a>
                            (.png, .jpg, .jpeg, .gif, .webp)
                        </div>
                        <span class='label label-info' id="upload-file-info1"></span>
                    </div>
                    <div class="box-footer" style="padding-left: 0; padding-right: 0;">
                        <button type="submit" name="submit" value="settings" class="btn btn-primary pull-right" data-toggle="modal" data-target="#loginModal"><?= trans('save_changes'); ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="col-lg-6 col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><?= trans('description'); ?></h3>
            </div>
            <form action="<?= base_url('Admin/affiliateProgramPost'); ?>" method="post">
                <?= csrf_field(); ?>
                <input type="hidden" name="lang_id" value="<?= clrNum(inputGet('lang')); ?>">
                <div class="box-body">
                    <div class="form-group">
                        <label class="control-label"><?= trans('title'); ?></label>
                        <input type="text" class="form-control" name="title" placeholder="<?= trans('title'); ?>" value="<?= esc(!empty($affDesc['title']) ? $affDesc['title'] : ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="control-label"><?= trans('description'); ?></label>
                        <textarea class="form-control text-area" name="description" placeholder="<?= trans('description'); ?>" style="min-height: 100px;"><?= esc(!empty($affDesc['description']) ? $affDesc['description'] : ''); ?></textarea>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" name="submit" value="description" class="btn btn-primary pull-right"><?= trans('save_changes'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><?= trans('content'); ?></h3>
            </div>
            <form action="<?= base_url('Admin/affiliateProgramPost'); ?>" method="post">
                <?= csrf_field(); ?>
                <input type="hidden" name="lang_id" value="<?= clrNum(inputGet('lang')); ?>">
                <div class="box-body">
                    <div class="form-group">
                        <label class="control-label"><?= trans('title'); ?></label>
                        <input type="text" class="form-control" name="title" placeholder="<?= trans('title'); ?>" value="<?= esc(!empty($affContent['title']) ? $affContent['title'] : ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <?= renderTextEditorAdmin('content', trans("content"), !empty($affContent['content']) ? $affContent['content'] : ''); ?>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" name="submit" value="content" class="btn btn-primary pull-right"><?= trans('save_changes'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><?= trans('how_it_works'); ?></h3>
            </div>
            <form action="<?= base_url('Admin/affiliateProgramPost'); ?>" method="post">
                <?= csrf_field(); ?>
                <input type="hidden" name="lang_id" value="<?= clrNum(inputGet('lang')); ?>">
                <div class="box-body">
                    <div class="panel-group">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle collapsed" data-toggle="collapse" href="#collapseWr1"><?= esc(!empty($affWorks[0]) && !empty($affWorks[0]['title']) ? $affWorks[0]['title'] : '#'); ?></a>
                                </h4>
                            </div>
                            <div id="collapseWr1" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label class="control-label"><?= trans('title'); ?></label>
                                        <input type="text" name="title1" value="<?= esc(!empty($affWorks[0]) && !empty($affWorks[0]['title']) ? $affWorks[0]['title'] : ''); ?>" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label"><?= trans('answer'); ?></label>
                                        <textarea name="description1" class="form-control form-textarea"><?= esc(!empty($affWorks[0]) && !empty($affWorks[0]['description']) ? $affWorks[0]['description'] : ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle collapsed" data-toggle="collapse" href="#collapseWr2"><?= esc(!empty($affWorks[1]) && !empty($affWorks[1]['title']) ? $affWorks[1]['title'] : '#'); ?></a>
                                </h4>
                            </div>
                            <div id="collapseWr2" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label class="control-label"><?= trans('title'); ?></label>
                                        <input type="text" name="title2" value="<?= esc(!empty($affWorks[1]) && !empty($affWorks[1]['title']) ? $affWorks[1]['title'] : ''); ?>" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label"><?= trans('answer'); ?></label>
                                        <textarea name="description2" class="form-control form-textarea"><?= esc(!empty($affWorks[1]) && !empty($affWorks[1]['description']) ? $affWorks[1]['description'] : ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle collapsed" data-toggle="collapse" href="#collapseWr3"><?= esc(!empty($affWorks[2]) && !empty($affWorks[2]['title']) ? $affWorks[2]['title'] : '#'); ?></a>
                                </h4>
                            </div>
                            <div id="collapseWr3" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label class="control-label"><?= trans('title'); ?></label>
                                        <input type="text" name="title3" value="<?= esc(!empty($affWorks[2]) && !empty($affWorks[2]['title']) ? $affWorks[2]['title'] : ''); ?>" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label"><?= trans('answer'); ?></label>
                                        <textarea name="description3" class="form-control form-textarea"><?= esc(!empty($affWorks[2]) && !empty($affWorks[2]['description']) ? $affWorks[2]['description'] : ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" name="submit" value="how_it_works" class="btn btn-primary pull-right"><?= trans('save_changes'); ?></button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-lg-6 col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><?= trans('frequently_asked_questions'); ?></h3>
            </div>
            <form action="<?= base_url('Admin/affiliateProgramPost'); ?>" method="post">
                <?= csrf_field(); ?>
                <input type="hidden" name="lang_id" value="<?= clrNum(inputGet('lang')); ?>">
                <div class="box-body">
                    <div id="panel_questions" class="panel-group">
                        <?php if (!empty($affFaq)):
                            usort($affFaq, function ($a, $b) {
                                return $a['o'] <=> $b['o'];
                            });
                            foreach ($affFaq as $item):
                                $uniqId = uniqid(); ?>
                                <div class="panel panel-default" id="panel<?= $uniqId; ?>">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a class="accordion-toggle collapsed" data-toggle="collapse" href="#collapse<?= $uniqId; ?>"><?= !empty($item['q']) ? esc($item['q']) : '#'; ?></a>
                                        </h4>
                                    </div>
                                    <div id="collapse<?= $uniqId; ?>" class="panel-collapse collapse">
                                        <div class="panel-body">
                                            <input type="hidden" name="question_id[]" value="<?= $uniqId; ?>">
                                            <div class="form-group">
                                                <label class="control-label"><?= trans('order'); ?></label>
                                                <input type="number" name="order_<?= $uniqId; ?>" value="<?= !empty($item['o']) ? esc($item['o']) : ''; ?>" class="form-control" style="max-width: 100px;">
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label"><?= trans('question'); ?></label>
                                                <input type="text" name="question_<?= $uniqId; ?>" value="<?= !empty($item['q']) ? esc($item['q']) : ''; ?>" class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label"><?= trans('answer'); ?></label>
                                                <textarea name="answer_<?= $uniqId; ?>" class="form-control form-textarea"><?= !empty($item['a']) ? esc($item['a']) : ''; ?></textarea>
                                            </div>
                                            <div class="form-group text-right">
                                                <button type="button" class="btn btn-danger" onclick="$('#panel<?= $uniqId; ?>').remove();"><?= trans("delete"); ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach;
                        endif; ?>
                    </div>
                    <div class="form-group m-t-5">
                        <button type="button" id="btnAddQuestion" class="btn btn-success"><?= trans("add_question"); ?></button>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" name="submit" value="questions" class="btn btn-primary pull-right"><?= trans('save_changes'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .panel-heading .accordion-toggle:after {
        font-family: 'FontAwesome';
        content: "\f105";
        float: right;
        color: grey;
    }

    .panel-heading .accordion-toggle.collapsed:after {
        content: "\f107";
    }

    .panel-heading .panel-title a {
        font-weight: 600;
        color: #55606e !important;
        font-size: 14px;
    }
</style>

<script>
    $(document).on('click', '#btnAddQuestion', function () {
        var uniqueId = Date.now() + '_' + Math.floor(Math.random() * 1000);
        $('#panel_questions').append('<div class="panel panel-default" id="panel' + uniqueId + '">' +
            '<div class="panel-heading"><h4 class="panel-title">' +
            '<a class="accordion-toggle" data-toggle="collapse" href="#collapse' + uniqueId + '"><?= clrQuotes(trans('question')); ?></a></h4></div>' +
            '<div id="collapse' + uniqueId + '" class="panel-collapse collapse in">' +
            '<div class="panel-body">' +
            '<input type="hidden" name="question_id[]" value="' + uniqueId + '">' +
            '<div class="form-group">' +
            '<label class="control-label"><?= clrQuotes(trans('question')); ?></label>' +
            '<input type="text" name="question_' + uniqueId + '" class="form-control">' +
            '</div>' +
            '<div class="form-group">' +
            '<label class="control-label"><?= clrQuotes(trans('answer')); ?></label>' +
            '<textarea name="answer_' + uniqueId + '" class="form-control form-textarea"></textarea>' +
            '</div>' +
            '<div class="form-group text-right">' +
            '<button type="button" class="btn btn-danger" onclick="$(\'#panel' + uniqueId + '\').remove();"><?= trans("delete"); ?></button>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>');
    });

    $(document).on("change", ".radio-affiliate-type input", function () {
        var val = $(this).val();
        if (val == 'site_based') {
            $('#commissionRateContainer').show();
        } else {
            $('#commissionRateContainer').hide();
        }
    });
</script>

<?= view('admin/includes/_image_file_manager'); ?>