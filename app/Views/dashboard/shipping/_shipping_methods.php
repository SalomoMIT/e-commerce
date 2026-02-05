<div class="row">
    <div class="col-sm-12">

        <?php if (!empty($methods)):
            foreach ($methods as $method):?>
                <div class="response-shipping-method">
                    <span class="title"><?= esc(@parseSerializedNameArray($method->name_array, $activelang->id)); ?></span>
                    <div class="btn-group btn-group-option">
                        <button type="button" class="btn btn-sm btn-default btn-edit" data-toggle="modal" data-target="#modalEditShippingMethod<?= esc($method->id); ?>"><i class="fa fa-edit"></i></span></button>
                        <button type="button" class="btn btn-sm btn-default" onclick='deleteShippingMethod("<?= esc($method->id); ?>","<?= esc(trans("confirm_delete")); ?>");'><i class="fa fa-trash-can"></i></span></button>
                    </div>
                </div>
            <?php endforeach;
        else: ?>
            <div class="alert alert-danger alert-large">
                <strong><?= trans("warning"); ?>!</strong>&nbsp;<?= trans("shipping_method_exp"); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalShippingMethod"><i class="fa fa-plus"></i>&nbsp;<?= trans("add_shipping_method"); ?></button>
    </div>
</div>