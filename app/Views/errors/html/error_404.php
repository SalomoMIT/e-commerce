<style>
    .error-404{min-height:600px;width:100%;text-align:center;padding-top:130px}.error-404 h1{font-size:64px;margin-bottom:10px;font-weight:700}.error-404 h2{margin-top:10px;font-size:24px;font-weight:600}.error-404 p{color:#888}.error-404 .btn{margin-top:30px}
</style>
<section id="main">
    <div class="container">
        <div class="row">
            <div class="error-404">
                <h1>404</h1>
                <h2><?= trans("page_not_found"); ?></h2>
                <p><?= trans("page_not_found_sub"); ?></p>
                <a class="btn btn-lg btn-custom" href="<?= langBaseUrl(); ?>"><?= trans("goto_home"); ?></a>
            </div>
        </div>
    </div>
</section>