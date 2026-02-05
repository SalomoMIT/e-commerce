<div class="row">
    <div class="col-sm-12 title-section">
        <h3><?= trans('newsletter'); ?></h3>
    </div>
</div>
<div class="row" style="margin-bottom: 15px;">
    <div class="col-sm-12">
        <div class="alert alert-success alert-large m-t-10">
            <strong><?= trans("warning"); ?>!</strong>&nbsp;&nbsp;<?= trans("newsletter_send_many_exp"); ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><?= trans('send_email'); ?></h3>
            </div>
            <form id="formSendEmail">
                <div class="box-body">
                    <div class="form-group" style="margin-bottom: 10px;">
                        <label><?= trans('to'); ?></label>
                        <?php if (!empty($emails)): ?>
                            <p style="max-height: 150px; overflow-y: auto">
                                <?php foreach ($emails as $email): ?>
                                    <label class="label-newsletter-email"><?= $email; ?></label>
                                <?php endforeach; ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label><?= trans('subject'); ?></label>
                        <input type="text" name="subject" id="newsletter_subject" class="form-control" placeholder="<?= trans('subject'); ?>" maxlength="255" required>
                    </div>

                    <div class="form-group">
                        <?= renderTextEditorAdmin('content', trans("body")); ?>
                    </div>

                </div>
                <div class="box-footer">
                    <a href="<?= adminUrl('newsletter'); ?>" id="btn_newsletter_back" class="btn btn-danger"><?= trans("back"); ?></a>
                    <button type="submit" id="btn_send_newsletter" class="btn btn-primary pull-right"><?= trans('send_email'); ?>&nbsp;&nbsp;<i class="fa fa-send"></i></button>

                    <div class="col-sm-12 m-t-30">
                        <div class="row">
                            <div id="newsletter_spinner" class="newsletter-spinner">
                                <strong class="newsletter-sending"><?= trans("mail_is_being_sent"); ?></strong>
                                <strong class="text-newsletter-completed"><?= trans("completed"); ?>!</strong>
                                <div class="m-t-15">
                                    <div class="spinner">
                                        <div class="bounce1"></div>
                                        <div class="bounce2"></div>
                                        <div class="bounce3"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <div class="row">
                            <div class="newsletter-email-container">
                                <ul id="newsletter_sent_emails" class="list-group csv-uploaded-files"></ul>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>

<?= view('admin/includes/_image_file_manager'); ?>

<script>
    let arrayEmails = <?= !empty($emails) ? json_encode($emails) : '[]'; ?>;
    let arraySent = [];
    let subject = '';
    let body = '';
    let sentCount = 0;

    $(document).ready(function () {
        $("#formSendEmail").on("submit", function (e) {
            e.preventDefault();
            $("#newsletter_spinner").show();
            $("#btn_newsletter_back, #btn_send_newsletter").prop("disabled", true);

            subject = $("#newsletter_subject").val();
            body = tinyMCE.activeEditor.getContent();

            sendNewsletterEmail();
        });
    });

    function sendNewsletterEmail() {
        const email = getNextEmail();
        if (!email) {
            showCompletionMessage();
            return;
        }

        const data = {
            email,
            subject,
            body,
            submit: "<?= esc($subscriberType); ?>"
        };

        $.ajax({
            type: "POST",
            url: generateUrl('Admin//newsletterSendEmailPost'),
            data,
            success: function (response) {
                if (response.result === 1) {
                    markEmailAsSent(email);

                    setTimeout(() => {
                        sendNewsletterEmail();
                    }, 200);
                } else {
                    handleError(email);
                }
            },
            error: function () {
                handleError(email);
            }
        });
    }

    function getNextEmail() {
        return arrayEmails.find(email => !arraySent.includes(email)) || "";
    }

    function markEmailAsSent(email) {
        arraySent.push(email);
        sentCount++;
        $("#newsletter_sent_emails").prepend(
            `<li class="list-group-item list-group-item-success">
                <i class="fa fa-check"></i>&nbsp;${sentCount}. ${email}
            </li>`
        );
    }

    function handleError(email) {
        $("#newsletter_sent_emails").prepend(
            `<li class="list-group-item list-group-item-danger">
                <i class="fa fa-times"></i>&nbsp;${email}
            </li>`
        );
    }

    function showCompletionMessage() {
        $("#newsletter_spinner .newsletter-sending").hide();
        $("#newsletter_spinner .spinner").hide();
        $("#newsletter_spinner .text-newsletter-completed").show();
    }
</script>


<style>
    .label-newsletter-email {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 300 !important;
        color: #41464b !important;
        background-color: #e2e3e5 !important;
        border-color: #d3d6d8 !important;
    }

    .newsletter-email-container {
        max-height: 300px;
        overflow-y: auto;
        margin-top: 15px;
    }

    .newsletter-spinner {
        display: none;
        text-align: center;
        font-size: 16px;
    }

    .text-newsletter-completed {
        display: none;
    }
</style>
