<div id="wrapper">
    <div class="container">
        <div class="section-affiliate section-affiliate-main">
            <div class="row">
                <div class="col-md-12 col-lg-6">
                    <h1 class="main-title"><?= esc(!empty($affDesc['title']) ? $affDesc['title'] : ''); ?></h1>
                    <p class="affiliate-description">
                        <?= esc(!empty($affDesc['description']) ? $affDesc['description'] : ''); ?>
                    </p>
                    <?php if (authCheck()):
                        if (user()->is_affiliate == 1):?>
                            <div class="alert alert-success alert-message">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg><?= trans("joined_affiliate_program"); ?>
                            </div>
                        <?php elseif (user()->is_affiliate == 2): ?>
                            <div class="alert alert-danger alert-message">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-circle">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="8" x2="12" y2="12"></line>
                                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                </svg><?= trans("removed_from_affiliate_program"); ?>
                            </div>
                        <?php else: ?>
                            <button type="button" class="btn btn-custom btn-affiliate m-b-15" data-toggle="modal" data-target="#modalAffiliate"><?= trans("join_program"); ?></button>
                        <?php endif; ?>
                    <?php else: ?>
                        <button type="button" class="btn btn-custom btn-affiliate m-b-15" data-toggle="modal" data-target="#loginModal"><?= trans("join_program"); ?></button>
                    <?php endif; ?>
                </div>
                <div class="col-md-12 col-lg-6">
                    <div class="affiliate-image">
                        <img src="<?= getStorageFileUrl($affiliateSettings->image, $affiliateSettings->storage, 'affiliate_bg'); ?>" alt="affiliate" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12 section-affiliate section-affiliate-colored display-flex flex-column align-items-center">
                <div class="container">
                    <div class="row">
                        <div class="col-12">
                            <h2 class="title"><?= trans("how_it_works"); ?></h2>
                        </div>
                        <div class="col-12 col-lg-4">
                            <div class="feature-box">
                                <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#00ab88" class="bi bi-person-add" viewBox="0 0 16 16">
                                        <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.5-5v1h1a.5.5 0 0 1 0 1h-1v1a.5.5 0 0 1-1 0v-1h-1a.5.5 0 0 1 0-1h1v-1a.5.5 0 0 1 1 0m-2-6a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/>
                                        <path d="M8.256 14a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z"/>
                                    </svg>
                                </div>
                                <h3 class="title"><?= !empty($affWorks[0]) && !empty($affWorks[0]['title']) ? esc($affWorks[0]['title']) : ''; ?></h3>
                                <p><?= !empty($affWorks[0]) && !empty($affWorks[0]['description']) ? esc($affWorks[0]['description']) : ''; ?></p>
                            </div>
                        </div>
                        <div class="col-12 col-lg-4">
                            <div class="feature-box">
                                <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" color="#00ab88" fill="none">
                                        <path d="M10 13.229C10.1416 13.4609 10.3097 13.6804 10.5042 13.8828C11.7117 15.1395 13.5522 15.336 14.9576 14.4722C15.218 14.3121 15.4634 14.1157 15.6872 13.8828L18.9266 10.5114C20.3578 9.02184 20.3578 6.60676 18.9266 5.11718C17.4953 3.6276 15.1748 3.62761 13.7435 5.11718L13.03 5.85978" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        <path d="M10.9703 18.14L10.2565 18.8828C8.82526 20.3724 6.50471 20.3724 5.07345 18.8828C3.64218 17.3932 3.64218 14.9782 5.07345 13.4886L8.31287 10.1172C9.74413 8.62761 12.0647 8.6276 13.4959 10.1172C13.6904 10.3195 13.8584 10.539 14 10.7708" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        <path d="M21 16H18.9211M16 21L16 18.9211" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M3 8H5.07889M8 3L8 5.07889" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <h3 class="title"><?= !empty($affWorks[1]) && !empty($affWorks[1]['title']) ? esc($affWorks[1]['title']) : ''; ?></h3>
                                <p><?= !empty($affWorks[1]) && !empty($affWorks[1]['description']) ? esc($affWorks[1]['description']) : ''; ?></p>
                            </div>
                        </div>
                        <div class="col-12 col-lg-4">
                            <div class="feature-box">
                                <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#00ab88" class="bi bi-wallet" viewBox="0 0 16 16">
                                        <path d="M0 3a2 2 0 0 1 2-2h13.5a.5.5 0 0 1 0 1H15v2a1 1 0 0 1 1 1v8.5a1.5 1.5 0 0 1-1.5 1.5h-12A2.5 2.5 0 0 1 0 12.5zm1 1.732V12.5A1.5 1.5 0 0 0 2.5 14h12a.5.5 0 0 0 .5-.5V5H2a2 2 0 0 1-1-.268M1 3a1 1 0 0 0 1 1h12V2H2a1 1 0 0 0-1 1"/>
                                    </svg>
                                </div>
                                <h3 class="title"><?= !empty($affWorks[2]) && !empty($affWorks[2]['title']) ? esc($affWorks[2]['title']) : ''; ?></h3>
                                <p><?= !empty($affWorks[2]) && !empty($affWorks[2]['description']) ? esc($affWorks[2]['description']) : ''; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="section-affiliate">
            <div class="row">
                <div class="col-12">
                    <h2 class="title"><?= esc(!empty($affContent['title']) ? $affContent['title'] : ''); ?></h2>
                    <?= !empty($affContent['content']) ? $affContent['content'] : ''; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-12 section-affiliate">
                <h2 class="title"><?= trans("frequently_asked_questions"); ?></h2>
                <div class="row justify-content-center">
                    <div class="col-12">
                        <?php if (!empty($affFaq)):
                            usort($affFaq, function ($a, $b) {
                                return $a['o'] <=> $b['o'];
                            });
                            foreach ($affFaq as $item):
                                $uniqId = uniqid(); ?>
                                <div class="accordion-box">
                                    <button class="btn collapsed" type="button" data-toggle="collapse" data-target="#collapse<?= $uniqId; ?>" aria-expanded="false" aria-controls="collapseFAQ">
                                        <?= !empty($item['q']) ? esc($item['q']) : ''; ?>
                                    </button>
                                    <div class="collapse" id="collapse<?= $uniqId; ?>">
                                        <div class="card card-body">
                                            <?= !empty($item['a']) ? esc($item['a']) : ''; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach;
                        endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (authCheck() && user()->is_affiliate != 1): ?>
    <div class="modal fade" id="modalAffiliate" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-custom modal-report-abuse">
                <form action="<?= base_url('Auth/joinAffiliateProgramPost'); ?>" method="post" class="validate_terms validate-form">
                    <?= csrf_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title"><?= trans("affiliate_program"); ?></h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true"><i class="icon-close"></i> </span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label><?= trans("first_name"); ?></label>
                            <input type="text" name="first_name" class="form-control auth-form-input" placeholder="<?= trans("first_name"); ?>" value="<?= esc(user()->first_name); ?>" maxlength="255" required>
                        </div>
                        <div class="form-group">
                            <label><?= trans("last_name"); ?></label>
                            <input type="text" name="last_name" class="form-control auth-form-input" placeholder="<?= trans("last_name"); ?>" value="<?= esc(user()->last_name); ?>" maxlength="255" required>
                        </div>
                        <div class="form-group">
                            <label><?= trans("email_address"); ?></label>
                            <input type="email" name="email" class="form-control auth-form-input" placeholder="<?= trans("email_address"); ?>" value="<?= esc(user()->email); ?>" maxlength="255" readonly>
                        </div>
                        <div class="form-group">
                            <label><?= trans("phone_number"); ?></label>
                            <input type="text" name="phone_number" class="form-control form-input" value="<?= esc(user()->phone_number); ?>" placeholder="<?= trans("phone_number"); ?>" maxlength="100" required>
                        </div>
                        <div class="form-group m-b-0">
                            <label><?= trans("location"); ?></label>
                            <?= view('partials/_location', ['countries' => getCountries(), 'countryId' => user()->country_id, 'stateId' => user()->state_id, 'cityId' => user()->city_id, 'isLocationOptional' => false, 'isFullWidth' => true]); ?>
                        </div>
                        <div class="form-group m-b-0">
                            <div class="row">
                                <div class="col-sm-12 col-lg-9 m-b-15">
                                    <input type="text" name="address" id="address_input" class="form-control form-input" value="<?= esc(user()->address); ?>" placeholder="<?= trans("address") ?>" maxlength="490">
                                </div>
                                <div class="col-sm-12 col-lg-3 m-b-15">
                                    <input type="text" name="zip_code" id="zip_code_input" class="form-control form-input" value="<?= esc(user()->zip_code); ?>" placeholder="<?= trans("zip_code") ?>" maxlength="90">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox custom-control-validate-input">
                                <input type="checkbox" class="custom-control-input" name="terms" id="checkbox_terms" required>
                                <label for="checkbox_terms" class="custom-control-label"><?= trans("terms_conditions_exp"); ?>&nbsp;
                                    <?php $pageTerms = getPageByDefaultName('terms_conditions', selectedLangId());
                                    if (!empty($pageTerms)): ?>
                                        <a href="<?= generateUrl($pageTerms->page_default_name); ?>" class="link-terms" target="_blank"><strong><?= esc($pageTerms->title); ?></strong></a>
                                    <?php endif; ?>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer text-right">
                        <button type="submit" class="btn btn-custom btn-block"><?= trans("join_program"); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>