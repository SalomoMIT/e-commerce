<label><?= esc($editorLabel); ?></label>
<?php if ($editorFileManager || $editorAiWriter): ?>
    <div class="row">
        <div class="col-sm-12 m-b-10">
            <?php if (!empty($editorFileManager)): ?>
                <button type="button" class="btn btn-default btn-file-manager" data-image-type="editor" data-toggle="modal" data-target="#imageFileManagerModal"><i class="fa fa-image"></i>&nbsp;&nbsp;<?= trans("add_image"); ?></button>
            <?php endif; ?>
            &nbsp;
            <?php if (aiWriter()->status && !empty($editorAiWriter) && hasPermission('ai_writer')): ?>
                <button type="button" class="btn btn-md btn-default btn-open-ai-writer" data-toggle="modal" data-target="#modalAiWriter"><i class="fa fa-pencil"></i>&nbsp;&nbsp;&nbsp;<?= trans("ai_writer"); ?></button>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
<textarea class="form-control <?= esc($editorClass); ?>" name="<?= $editorInputName; ?>" <?= !empty($editorAiWriter) ? 'id="editor_main"' : ''; ?>><?= $editorContent; ?></textarea>