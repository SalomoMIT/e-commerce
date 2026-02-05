<div class="row">
    <div class="col-sm-12 title-section">
        <h3><?= trans('newsletter'); ?></h3>
    </div>
</div>

<div class="row">
    <div class="col-md-6 col-sm-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <div class="left">
                    <h3 class="box-title"><?= trans('users'); ?>&nbsp;(<?= $usersCount; ?>)</h3>
                </div>
                <div class="right">
                    <input type="text" id="searchUsers" class="form-control" placeholder="<?= trans("search"); ?>" style="width: 180px;">
                </div>
            </div>
            <div class="box-body">
                <div id="userTableContainer" class="tableFixHead">
                    <table class="table table-users">
                        <thead>
                        <tr>
                            <th width="20"><input type="checkbox" id="checkboxAllUsers"></th>
                            <th><?= trans("id"); ?></th>
                            <th><?= trans("username"); ?></th>
                            <th><?= trans("email"); ?></th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <?php if ($usersCount > 0): ?>
                        <div id="spinnerUsers">
                            <div class="spinner" style="margin-top: 15px;">
                                <div class="bounce1"></div>
                                <div class="bounce2"></div>
                                <div class="bounce3"></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="box-footer">
                <form action="<?= base_url('Admin/newsletterSelectEmailsPost'); ?>" method="post">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="user_ids" id="selectedUserIds">
                    <button type="submit" name="submit" value="users" class="btn btn-lg btn-block btn-info"><?= trans("send_email"); ?>&nbsp;&nbsp;<i class="fa fa-send"></i></button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-sm-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <div class="left">
                    <h3 class="box-title"><?= trans('subscribers'); ?>&nbsp;(<?= $subscribersCount; ?>)</h3>
                </div>
                <div class="right">
                    <input type="text" id="searchSubscribers" class="form-control" placeholder="<?= trans("search"); ?>" style="width: 180px;">
                </div>
            </div>
            <div class="box-body">
                <div id="subscriberTableContainer" class="tableFixHead">
                    <table class="table table-subscribers">
                        <thead>
                        <tr>
                            <th width="20"><input type="checkbox" id="checkboxAllSubscribers"></th>
                            <th><?= trans("id"); ?></th>
                            <th><?= trans("email"); ?></th>
                            <th><?= trans("options"); ?></th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <?php if ($subscribersCount > 0): ?>
                        <div id="spinnerSubscribers">
                            <div class="spinner" style="margin-top: 15px;">
                                <div class="bounce1"></div>
                                <div class="bounce2"></div>
                                <div class="bounce3"></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="box-footer">
                <form action="<?= base_url('Admin/newsletterSelectEmailsPost'); ?>" method="post">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="subscriber_ids" id="selectedSubscriberIds">
                    <button type="submit" name="submit" value="subscribers" class="btn btn-lg btn-block btn-info"><?= trans("send_email"); ?>&nbsp;&nbsp;<i class="fa fa-send"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 col-sm-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><?= trans('settings'); ?></h3>
            </div>
            <form action="<?= base_url('Admin/newsletterSettingsPost'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="box-body">
                    <div class="form-group">
                        <?= formSwitch('status', trans('status'), $newsletterSettings->status); ?>
                    </div>

                    <div class="form-group">
                        <?= formSwitch('is_popup_active', trans('newsletter_popup'), $newsletterSettings->is_popup_active); ?>
                    </div>

                    <div class="form-group">
                        <label class="control-label"><?= trans("image"); ?></label>
                        <div style="margin-bottom: 10px;">
                            <img src="<?= getStorageFileUrl($newsletterSettings->image, $newsletterSettings->storage, 'newsletter_bg'); ?>" alt="" style="max-width: 300px; max-height: 300px;">
                        </div>
                        <div class="display-block">
                            <a class='btn btn-success btn-sm btn-file-upload'>
                                <?= trans('select_image'); ?>
                                <input type="file" name="file" size="40" accept=".jpg, .jpeg, .webp, .png" onchange="$('#upload-file-info').html($(this).val().replace(/.*[\/\\]/, ''));">
                            </a>
                            (.jpg, .jpeg, .webp, .png)
                        </div>
                        <span class='label label-info' id="upload-file-info"></span>
                    </div>
                </div>
                <div class="box-footer text-right">
                    <button type="submit" name="submit" value="general" class="btn btn-primary"><?= trans('save_changes'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $("#checkboxAllUsers").click(function () {
        $('.table-users input:checkbox').not(this).prop('checked', this.checked);
        updateSelectedUserIds();
    });
    $("#checkboxAllSubscribers").click(function () {
        $('.table-subscribers input:checkbox').not(this).prop('checked', this.checked);
        updateSelectedSubscriberIds();
    });

    $(document).on('change', 'input[name="user_id[]"]', function () {
        updateSelectedUserIds();
    });
    $(document).on('change', 'input[name="subscriber_id[]"]', function () {
        updateSelectedSubscriberIds();
    });

    function updateSelectedUserIds() {
        var selectedValues = $('input[name="user_id[]"]:checked').map(function () {
            return $(this).val();
        }).get();
        $('#selectedUserIds').val(selectedValues.join(','));
    }

    function updateSelectedSubscriberIds() {
        var selectedValues = $('input[name="subscriber_id[]"]:checked').map(function () {
            return $(this).val();
        }).get();
        $('#selectedSubscriberIds').val(selectedValues.join(','));
    }
</script>
<style>
    .tableFixHead {
        overflow: auto;
        height: 500px !important;
    }

    .tableFixHead thead th {
        position: sticky;
        top: 0;
        z-index: 1;
    }

    table {
        border-collapse: collapse;
        width: 100%;
    }

    th, td {
        padding: 8px 16px;
    }

    th {
        background: #fff !important;
    }

    .spinner > div {
        background-color: #BDBDBD;
    }
</style>

<script>
    const dataTables = {
        users: {
            currentPage: 0,
            searchQuery: '',
            isLoading: false,
            hasMore: true,
            debounceTimer: null,
            containerId: '#userTableContainer',
            inputId: '#searchUsers',
            spinnerId: '#spinnerUsers',
            ajaxUrl: generateUrl('Ajax/loadMoreUsers'),
            tableSelector: '#userTableContainer tbody',
        },
        subscribers: {
            currentPage: 0,
            searchQuery: '',
            isLoading: false,
            hasMore: true,
            debounceTimer: null,
            containerId: '#subscriberTableContainer',
            inputId: '#searchSubscribers',
            spinnerId: '#spinnerSubscribers',
            ajaxUrl: generateUrl('Ajax/loadMoreSubscribers'),
            tableSelector: '#subscriberTableContainer tbody',
        }
    };

    function loadMore(type) {
        const config = dataTables[type];
        if (config.isLoading) return;

        if (!config.hasMore) return;

        $(config.spinnerId).show();
        config.isLoading = true;
        config.currentPage++;

        $.ajax({
            type: 'POST',
            url: config.ajaxUrl,
            data: {
                page: config.currentPage,
                q: config.searchQuery
            },
            success: function (response) {
                config.hasMore = response.hasMore;

                setTimeout(() => {
                    if (response.result == 1) {
                        $(config.tableSelector).append(response.htmlContent);
                    }
                    config.isLoading = false;
                    $(config.spinnerId).hide();
                }, 300);
            },
            error: function () {
                config.isLoading = false;
                $(config.spinnerId).hide();
            }
        });
    }

    function setupScrollLoading(type) {
        const config = dataTables[type];
        const container = document.querySelector(config.containerId);
        container.addEventListener('scroll', function () {
            if (
                container.scrollTop + container.clientHeight >= container.scrollHeight - 10 &&
                !config.isLoading
            ) {
                loadMore(type);
            }
        });
    }

    function setupSearchInput(type) {
        const config = dataTables[type];
        $(document).on('input', config.inputId, function () {
            clearTimeout(config.debounceTimer);
            const q = $(this).val().trim();
            config.debounceTimer = setTimeout(() => {
                config.searchQuery = q.length > 1 ? q : '';
                config.currentPage = 0;
                $(config.tableSelector).empty();
                loadMore(type);
            }, 300);
        });
    }

    $(document).ready(function () {
        ['users', 'subscribers'].forEach(type => {
            setupScrollLoading(type);
            setupSearchInput(type);
            loadMore(type);
        });
    });
</script>