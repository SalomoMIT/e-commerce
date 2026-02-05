<script src="<?= base_url('assets/admin/vendor/chart/chart.min.js'); ?>"></script>
<script src="<?= base_url('assets/admin/vendor/chart/utils.js'); ?>"></script>
<script src="<?= base_url('assets/admin/vendor/chart/analyser.js'); ?>"></script>

<?php if ($generalSettings->membership_plans_system == 1 && user()->is_membership_plan_expired == 1): ?>
    <div class="alert alert-danger alert-large">
        <strong><i class="fa fa-info-circle"></i></strong>&nbsp;&nbsp;<?= trans("msg_vendor_membership_plan_expired"); ?>&nbsp;<a href="<?= generateUrl('select_membership_plan'); ?>" class="text-danger font-600"><?= trans("renew_your_plan") ?></a>
    </div>
<?php endif; ?>
<div class="row m-b-30">
    <div class="col-sm-12">
        <div class="small-boxes-dashboard">
            <?php if ($baseVars->isSaleActive): ?>
                <div class="col-lg-3 col-md-6 col-sm-12 p-0">
                    <div class="small-box-dashboard small-box-dashboard-first">
                        <h3 class="total"><?= !empty($totalSalesCount) ? $totalSalesCount : 0; ?></h3>
                        <span class="text-muted"><?= trans("number_of_total_sales"); ?></span>
                        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-cart-check" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm7 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                            <path fill-rule="evenodd" d="M11.354 5.646a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L8 8.293l2.646-2.647a.5.5 0 0 1 .708 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12 p-0">
                    <div class="small-box-dashboard">
                        <h3 class="total"><?= !empty($balance) ? $balance : 0; ?></h3>
                        <span class="text-muted"><?= trans("balance"); ?></span>
                        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-cash-stack" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path d="M14 3H1a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1h-1z"/>
                            <path fill-rule="evenodd" d="M15 5H1v8h14V5zM1 4a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1H1z"/>
                            <path d="M13 5a2 2 0 0 0 2 2V5h-2zM3 5a2 2 0 0 1-2 2V5h2zm10 8a2 2 0 0 1 2-2v2h-2zM3 13a2 2 0 0 0-2-2v2h2zm7-4a2 2 0 1 1-4 0 2 2 0 0 1 4 0z"/>
                        </svg>
                    </div>
                </div>
            <?php endif; ?>
            <div<?= !$baseVars->isSaleActive ? ' class="classified-small-boxes"' : ''; ?>>
                <div class="col-lg-3 col-md-6 col-sm-12 p-0">
                    <div class="small-box-dashboard" <?= !$baseVars->isSaleActive ? 'style="border-radius: 4px 0 0 4px;"' : ''; ?>>
                        <h3 class="total"><?= !empty($productsCount) ? $productsCount : 0; ?></h3>
                        <span class="text-muted"><?= trans("products"); ?></span>
                        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-basket" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M5.757 1.071a.5.5 0 0 1 .172.686L3.383 6h9.234L10.07 1.757a.5.5 0 1 1 .858-.514L13.783 6H15a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1v4.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 13.5V9a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h1.217L5.07 1.243a.5.5 0 0 1 .686-.172zM2 9v4.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V9H2zM1 7v1h14V7H1zm3 3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 4 10zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 6 10zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 8 10zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 1 .5-.5zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 1 .5-.5z"/>
                        </svg>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12 p-0">
                    <div class="small-box-dashboard small-box-dashboard-last">
                        <h3 class="total"><?= !empty($pendingProductsCount) ? $pendingProductsCount : 0; ?></h3>
                        <span class="text-muted"><?= trans("pending_products"); ?></span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-hourglass" viewBox="0 0 16 16">
                            <path d="M2 1.5a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-1v1a4.5 4.5 0 0 1-2.557 4.06c-.29.139-.443.377-.443.59v.7c0 .213.154.451.443.59A4.5 4.5 0 0 1 12.5 13v1h1a.5.5 0 0 1 0 1h-11a.5.5 0 1 1 0-1h1v-1a4.5 4.5 0 0 1 2.557-4.06c.29-.139.443-.377.443-.59v-.7c0-.213-.154-.451-.443-.59A4.5 4.5 0 0 1 3.5 3V2h-1a.5.5 0 0 1-.5-.5m2.5.5v1a3.5 3.5 0 0 0 1.989 3.158c.533.256 1.011.791 1.011 1.491v.702c0 .7-.478 1.235-1.011 1.491A3.5 3.5 0 0 0 4.5 13v1h7v-1a3.5 3.5 0 0 0-1.989-3.158C8.978 9.586 8.5 9.052 8.5 8.351v-.702c0-.7.478-1.235 1.011-1.491A3.5 3.5 0 0 0 11.5 3V2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($baseVars->isSaleActive): ?>
    <div class="row">
        <?php if (!empty($activeSalesCount) || !empty($completedSalesCount)): ?>
            <div class="col-lg-4 col-sm-12 col-xs-12">
                <div class="box box-primary box-sm index-box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?= trans("sales"); ?></h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="index-chart-container">
                            <canvas id="chart_sales"></canvas>
                        </div>
                    </div>
                    <div class="box-footer clearfix"></div>
                </div>
            </div>
            <div class="col-lg-8 col-sm-12 col-xs-12">
                <div class="box box-primary box-sm index-box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?= trans("monthly_sales"); ?></h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="index-chart-container">
                            <canvas id="chart_montly_sales"></canvas>
                        </div>
                    </div>
                    <div class="box-footer clearfix"></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="row">

    <?php if ($generalSettings->product_comments): ?>
        <div class="col-lg-6 col-sm-12 col-xs-12">
            <div class="box box-primary box-sm index-box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= trans("latest_comments"); ?></h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                    </div>
                </div>
                <div class="box-body index-table">
                    <div class="table-responsive">
                        <table class="table no-margin">
                            <thead>
                            <tr>
                                <th><?= trans("id"); ?></th>
                                <th><?= trans("comment"); ?></th>
                                <th><?= trans("product"); ?></th>
                                <th><?= trans("date"); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($latestComments)):
                                foreach ($latestComments as $item): ?>
                                    <tr>
                                        <td style="width: 10%"><?= esc($item->id); ?></td>
                                        <td style="width: 35%"><?= characterLimiter(esc($item->comment), 40, '...'); ?></td>
                                        <td style="width: 35%">
                                            <a href="<?= langBaseUrl($item->product_slug); ?>" class="link-black" target="_blank"><?= esc(characterLimiter($item->title, 40, '...')); ?></a>
                                        </td>
                                        <td class="white-space-nowrap" style="width: 20%"><?= formatDate($item->created_at); ?></td>
                                    </tr>
                                <?php endforeach;
                            endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="box-footer clearfix text-right">
                    <a href="<?= generateDashUrl("comments"); ?>" class="btn btn-sm btn-default"><?= trans("view_all"); ?></a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($generalSettings->reviews): ?>
    <div class="col-lg-6 col-sm-12 col-xs-12">
        <div class="box box-primary box-sm index-box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= trans("latest_reviews"); ?></h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                </div>
            </div>
            <div class="box-body index-table">
                <div class="table-responsive">
                    <table class="table no-margin">
                        <thead>
                        <tr>
                            <th><?= trans("id"); ?></th>
                            <th><?= trans("comment"); ?></th>
                            <th><?= trans("product"); ?></th>
                            <th><?= trans("date"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($latestReviews)):
                            foreach ($latestReviews as $item): ?>
                                <tr>
                                    <td style="width: 10%"><?= esc($item->id); ?></td>
                                    <td class="break-word">
                                        <div class="pull-left" style="width: 100%;">
                                            <?= view('admin/includes/_review_stars', ['review' => $item->rating]); ?>
                                        </div>
                                        <p class="pull-left">
                                            <?= esc($item->review); ?>
                                        </p>
                                    </td>
                                    <td style="width: 35%">
                                        <a href="<?= langBaseUrl($item->product_slug); ?>" class="link-black" target="_blank"><?= esc(characterLimiter($item->product_title, 40, '...')); ?></a>
                                    </td>
                                    <td class="white-space-nowrap" style="width: 20%"><?= formatDate($item->created_at); ?></td>
                                </tr>
                            <?php endforeach;
                        endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="box-footer clearfix text-right">
                <a href="<?= generateDashUrl('reviews'); ?>" class="btn btn-sm btn-default"><?= trans("view_all"); ?></a>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>
<div class="row">
    <?php if ($baseVars->isSaleActive): ?>
        <div class="col-lg-6 col-sm-12 col-xs-12">
            <div class="box box-primary box-sm index-box">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= trans("latest_sales"); ?></h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                    </div>
                </div>
                <div class="box-body index-table">
                    <div class="table-responsive">
                        <table class="table no-margin">
                            <thead>
                            <tr>
                                <th><?= trans("sale"); ?></th>
                                <th><?= trans("status"); ?></th>
                                <th><?= trans("payment"); ?></th>
                                <th><?= trans("date"); ?></th>
                                <th><?= trans("options"); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($latestSales)):
                                foreach ($latestSales as $item): ?>
                                    <tr>
                                        <td>#<?= $item->order_number; ?></td>
                                        <td>
                                            <?php if ($item->status == 1):
                                                echo trans("completed");
                                            elseif ($item->status == 2):
                                                echo trans("cancelled");
                                            else:
                                                echo trans("order_processing");
                                            endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($item->status == 2):
                                                echo trans("cancelled");
                                            else:
                                                if ($item->payment_status == 'payment_received'):
                                                    echo trans("payment_received");
                                                else:
                                                    echo trans("pending_payment");
                                                endif;
                                            endif; ?>
                                        </td>
                                        <td><?= formatDate($item->created_at); ?></td>
                                        <td style="width: 10%">
                                            <a href="<?= generateDashUrl('sale') . '/' . esc($item->order_number); ?>" class="btn btn-xs btn-info"><?= trans('details'); ?></a>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="box-footer clearfix text-right">
                    <a href="<?= generateDashUrl('sales'); ?>" class="btn btn-sm btn-default"><?= trans("view_all"); ?></a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($activeSalesCount) || !empty($completedSalesCount)): ?>
    <script>
        //total sales
        var ctx = document.getElementById('chart_sales').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: [
                    '<?= trans("active_sales"); ?> (<?= !empty($activeSalesCount) ? $activeSalesCount : 0; ?>)',
                    '<?= trans("completed_sales"); ?> (<?= !empty($completedSalesCount) ? $completedSalesCount : 0; ?>)'
                ],
                datasets: [{
                    data: [<?= !empty($activeSalesCount) ? $activeSalesCount : 0; ?>, <?= !empty($completedSalesCount) ? $completedSalesCount : 0; ?>],
                    backgroundColor: [
                        '#1BC5BD',
                        '#6993FF'
                    ],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutoutPercentage: 70,
                tooltips: {
                    callbacks: {
                        label: function (tooltipItem, data) {
                            return data['labels'][tooltipItem['index']];
                        }
                    }
                }
            }
        });
    </script>
<?php endif; ?>

<script>
    //monthly sales
    var months = ["<?= trans("january");?>", "<?= trans("february");?>", "<?= trans("march");?>", "<?= trans("april");?>", "<?= trans("may");?>", "<?= trans("june");?>", "<?= trans("july");?>", "<?= trans("august");?>", "<?= trans("september");?>", "<?= trans("october");?>", "<?= trans("november");?>", "<?= trans("december");?>"];
    var i;
    for (i = 0; i < months.length; i++) {
        months[i] = months[i].substr(0, 3);
    }
    var presets = window.chartColors;
    var utils = Samples.utils;
    var inputs = {
        min: 0,
        max: 100,
        count: 8,
        decimals: 2,
        continuity: 1
    };
    var options = {
        maintainAspectRatio: false,
        spanGaps: false,
        elements: {
            line: {
                tension: 0.000001
            }
        },
        plugins: {
            filler: {
                propagate: false
            }
        },
        scales: {
            x: {
                ticks: {
                    autoSkip: false,
                    maxRotation: 0
                }
            },
            yAxes: [
                {
                    ticks: {
                        beginAtZero: true,
                        callback: function (label, index, labels) {
                            return "<?= $defaultCurrency->symbol; ?>" + label;
                        }
                    }
                }
            ]
        },
        tooltips: {
            callbacks: {
                label: function (tooltipItem, data) {
                    return data['labels'][tooltipItem['index']] + ": <?= $defaultCurrency->symbol; ?>" + data['datasets'][0]['data'][tooltipItem['index']];
                }
            }
        }
    };
    [false, 'origin', 'start', 'end'].forEach(function () {
        utils.srand(8);
        new Chart('chart_montly_sales', {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    backgroundColor: utils.transparentize("#bfe8e6"),
                    borderColor: "#1BC5BD",
                    data: [<?php for ($i = 1; $i <= 12; $i++) {
                        echo $i > 1 ? ',' : '';
                        $total = 0;
                        if (!empty($salesSum)):
                            foreach ($salesSum as $sum):
                                if (isset($sum->month) && $sum->month == $i):
                                    $total = $sum->total_amount;
                                    break;
                                endif;
                            endforeach;
                        endif;
                        echo numToDecimal($total);
                    }?>],
                    label: "<?= trans("sales"); ?> (<?= date("Y") ?>)"
                }]
            },
            options: Chart.helpers.merge(options, {
                title: {
                    display: false
                },
                elements: {
                    line: {
                        tension: 0.4,
                        borderWidth: 2
                    }
                }
            })
        });
    });
</script>