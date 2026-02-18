<?php

namespace App\Controllers;

use App\Models\BiddingModel;
use App\Models\EarningsModel;
use App\Models\OrderAdminModel;
use App\Models\OrderModel;

class OrderController extends BaseController
{
    protected $orderModel;
    protected $biddingModel;
    protected $userId;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        if (!authCheck()) {
            redirectToUrl(langBaseUrl());
        }
        if (!isSaleActive()) {
            redirectToUrl(langBaseUrl());
        }
        $this->orderModel = new OrderModel();
        $this->biddingModel = new BiddingModel();
        $this->userId = user()->id;
    }

    /**
     * Orders
     */
    public function orders()
    {
        $data = setPageMeta(trans("orders"));

        $numRows = $this->orderModel->getOrdersCount($this->userId);
        $data['pager'] = paginate($this->baseVars->perPage, $numRows);
        $data['orders'] = $this->orderModel->getOrdersPaginated($this->userId, $this->baseVars->perPage, $data['pager']->offset);
        $data['activeTab'] = 'orders';
        $data['panelSettings'] = getPanelSettings();

        echo view('partials/_header', $data);
        echo view('order/orders', $data);
        echo view('partials/_footer');
    }

    /**
     * Order
     */
    public function order($orderNumber)
    {
        $data = setPageMeta(trans("order"));

        $data['order'] = $this->orderModel->getOrderByOrderNumber($orderNumber);
        if (empty($data['order'])) {
            return redirect()->to(langBaseUrl());
        }
        if ($data['order']->buyer_id != $this->userId) {
            return redirect()->to(langBaseUrl());
        }
        $data['orderProducts'] = $this->orderModel->getOrderItems($data['order']->id);

        $data['lastBankTransferStatus'] = '';
        if ($data['order']->payment_method == 'bank_transfer' && $data['order']->payment_status == 'pending_payment') {
            $lastBankTransfer = getLastBankTransfer('order', $data['order']->order_number);
            if (!empty($lastBankTransfer)) {
                $data['lastBankTransferStatus']  = $lastBankTransfer->status;
            }
        }

        $data['panelSettings'] = getPanelSettings();

        echo view('partials/_header', $data);
        echo view('order/order', $data);
        echo view('partials/_footer');
    }

    /**
     * Approve Order Product
     */
    public function approveOrderProductPost()
    {
        $orderProductId = inputPost('order_product_id');
        if ($this->orderModel->approveOrderProduct($orderProductId)) {
            //order product
            $orderProduct = getOrderProduct($orderProductId);
            //add seller earnings
            $earningsModel = new EarningsModel();
            $earningsModel->addSellerEarnings($orderProduct);
            //update order status
            $orderAdminModel = new OrderAdminModel();
            $orderAdminModel->updateOrderStatusIfCompleted($orderProduct->order_id);
        }
        return jsonResponse();
    }

    /**
     * Downloads
     */
    public function downloads()
    {
        if (!authCheck()) {
            return redirect()->to(langBaseUrl());
        }
        if (!isSaleActive()) {
            return redirect()->to(langBaseUrl());
        }
        if ($this->generalSettings->digital_products_system != 1) {
            return redirect()->to(langBaseUrl());
        }

        $data = setPageMeta(trans("downloads"));
        $data['user'] = user();
        $data['activeTab'] = 'downloads';
        $data['userRating'] = calculateUserRating($data['user']->id);
        $data['userSession'] = getUserSession();
        $data['numRows'] = $this->productModel->getUserDownloadsCount($data['user']->id);
        $data['pager'] = paginate($this->baseVars->perPage, $data['numRows']);
        $data['items'] = $this->productModel->getUserDownloadsPaginated($data['user']->id, $this->baseVars->perPage, $data['pager']->offset);

        echo view('partials/_header', $data);
        echo view('order/downloads', $data);
        echo view('partials/_footer');
    }

    /**
     * Refund Requests
     */
    public function refundRequests()
    {
        if ($this->generalSettings->refund_system != 1) {
            redirectToUrl(langBaseUrl());
            exit();
        }
        $data = setPageMeta(trans("refund_requests"));

        $data['panelSettings'] = getPanelSettings();
        $numRows = $this->orderModel->getRefundRequestCount($this->userId, 'buyer');
        $data['pager'] = paginate($this->baseVars->perPage, $numRows);
        $data['refundRequests'] = $this->orderModel->getRefundRequestsPaginated($this->userId, 'buyer', $this->baseVars->perPage, $data['pager']->offset);
        $data['userOrders'] = $this->orderModel->getOrdersByBuyerId($this->userId);
        $data['activeRefundRequestIds'] = $this->orderModel->getBuyerActiveRefundRequestIds($this->userId);
        $data['activeTab'] = 'refund_requests';

        echo view('partials/_header', $data);
        echo view('order/refund_requests', $data);
        echo view('partials/_footer');
    }

    /**
     * Refund
     */
    public function refund($id)
    {
        if ($this->generalSettings->refund_system != 1) {
            redirectToUrl(langBaseUrl());
            exit();
        }
        $data = setPageMeta(trans("refund"));

        $data['refundRequest'] = $this->orderModel->getRefundRequest($id);
        if (empty($data['refundRequest'])) {
            return redirect()->to(generateUrl('refund_requests'));
        }
        if (!isAdmin() && $data['refundRequest']->buyer_id != user()->id) {
            return redirect()->to(generateUrl('refund_requests'));
        }
        $data['product'] = getOrderProduct($data['refundRequest']->order_product_id);
        if (empty($data['product'])) {
            return redirect()->to(generateUrl('refund_requests'));
        }
        $data['messages'] = $this->orderModel->getRefundMessages($id);
        $data['activeTab'] = 'refund_requests';

        echo view('partials/_header', $data);
        echo view('order/refund', $data);
        echo view('partials/_footer');
    }

    /**
     * Submit Refund Request
     */
    public function submitRefundRequest()
    {
        if ($this->generalSettings->refund_system != 1) {
            redirectToUrl(langBaseUrl());
            exit();
        }
        $orderProductId = inputPost('order_product_id');
        $orderProduct = $this->orderModel->getOrderProduct($orderProductId);
        if (!empty($orderProduct)) {
            $user = getUser($orderProduct->seller_id);
            $refundId = $this->orderModel->addRefundRequest($orderProduct);
            if (!empty($this->generalSettings->mail_username) && !empty($user) && !empty($refundId)) {
                $emailData = [
                    'email_type' => 'refund',
                    'email_address' => $user->email,
                    'email_subject' => trans("refund_request"),
                    'template_path' => 'email/main',
                    'email_data' => serialize([
                        'content' => trans("msg_refund_request_email"),
                        'url' => generateDashUrl("refund_requests") . '/' . $refundId,
                        'buttonText' => trans("see_details")
                    ])
                ];
                addToEmailQueue($emailData);
            }
        }
        return redirect()->to(generateUrl('refund_requests'));
    }

    /**
     * Add Refund Message
     */
    public function addRefundMessage()
    {
        $id = inputPost('id');
        $request = $this->orderModel->getRefundRequest($id);
        if (!empty($request)) {
            $mailUserId = null;
            $refundUrl = generateUrl('refund_requests') . '/' . $request->id;
            if ($request->buyer_id == user()->id) {
                $this->orderModel->addRefundMessage($request->id, 1);
                $mailUserId = $request->seller_id;
                $refundUrl = generateDashUrl('refund_requests') . '/' . $request->id;
            } elseif ($request->seller_id == user()->id) {
                $this->orderModel->addRefundMessage($request->id, 0);
                $mailUserId = $request->buyer_id;
            }
            //send email
            if (!empty($mailUserId)) {
                $user = getUser($mailUserId);
                if (!empty($this->generalSettings->mail_username) && !empty($user)) {
                    $emailData = [
                        'email_type' => 'refund_message',
                        'email_address' => $user->email,
                        'email_subject' => trans("refund_request"),
                        'template_path' => 'email/main',
                        'email_data' => serialize([
                            'content' => trans("msg_refund_request_update_email"),
                            'url' => $refundUrl,
                            'buttonText' => trans("see_details")
                        ])
                    ];
                    addToEmailQueue($emailData);
                }
            }
        }
        redirectToBackUrl();
    }

    /**
     * Cancel Order
     */
    public function cancelOrderPost()
    {
        $orderId = inputPost('order_id');
        $this->orderModel->cancelOrder($orderId);
        return jsonResponse();
    }

    /*
     * --------------------------------------------------------------------
     * Bidding System
     * --------------------------------------------------------------------
     */

    /**
     * Quote Requests
     */
    public function quoteRequests()
    {
        $data = setPageMeta(trans("quote_requests"));
        $data['panelSettings'] = getPanelSettings();
        $data['numRows'] = $this->biddingModel->getQuoteRequestsCount($this->userId);
        $data['pager'] = paginate($this->baseVars->perPage, $data['numRows']);
        $data['quoteRequests'] = $this->biddingModel->getQuoteRequestsPaginated($this->userId, $this->baseVars->perPage, $data['pager']->offset);
        $data['activeTab'] = 'quote_requests';

        echo view('partials/_header', $data);
        echo view('order/quote_requests', $data);
        echo view('partials/_footer');
    }

    /**
     * Request Quote
     */
    public function requestQuotePost()
    {
        $productId = inputPost('product_id');
        $product = $this->productModel->getActiveProduct($productId);
        $pageLink = '&nbsp;<a href="' . generateUrl("quote_requests") . '" class="link-blue link-underlined">' . clrQuotes(trans("quote_requests")) . '</a>';

        if (!empty($product)) {
            if ($product->user_id == user()->id) {
                $this->session->setFlashdata('product_details_error', trans("msg_quote_request_error") . $pageLink);
                return redirect()->back();
            }

            $biddingModel = new BiddingModel();
            if (!$biddingModel->checkActiveQuoteRequest($product->id, user()->id)) {
                $this->session->setFlashdata('product_details_error', trans("already_have_active_request") . $pageLink);
                return redirect()->back();
            }

            $quantity = inputPost('product_quantity');
            $variantId = inputPost('variant_id');
            $extraOptions = inputPost('extra_options');

            $quoteId = $biddingModel->requestQuote($product, $quantity, $variantId, $extraOptions);
            if ($quoteId) {
                //send email
                $seller = getUser($product->user_id);
                if (!empty($seller) && getEmailOptionStatus($this->generalSettings, 'bidding_system') == 1) {
                    $emailData = [
                        'email_type' => 'quote',
                        'email_address' => $seller->email,
                        'email_subject' => trans("quote_request"),
                        'template_path' => 'email/main',
                        'email_data' => serialize([
                            'content' => trans("you_have_new_quote_request") . "<br>" . trans("quote") . ": " . "<strong>#" . $quoteId . "</strong>",
                            'url' => generateDashUrl('quote_requests'),
                            'buttonText' => trans("view_details")
                        ])
                    ];
                    addToEmailQueue($emailData);
                }
            }
            $this->session->setFlashdata('product_details_success', trans("msg_quote_request_sent") . $pageLink);
        }
        return redirect()->back();
    }

    /**
     * Accept Quote
     */
    public function acceptQuote()
    {
        $id = inputPost('id');
        $quoteRequest = $this->biddingModel->getQuoteRequest($id);
        if ($this->biddingModel->acceptQuote($quoteRequest)) {
            //send email
            $seller = getUser($quoteRequest->seller_id);
            if (!empty($seller) && getEmailOptionStatus($this->generalSettings, 'bidding_system') == 1) {
                $emailData = [
                    'email_type' => 'quote',
                    'email_address' => $seller->email,
                    'email_subject' => trans("quote_request"),
                    'template_path' => 'email/main',
                    'email_data' => serialize([
                        'content' => trans("your_quote_accepted") . '<br>' . trans("quote") . ': ' . '<strong>#' . $quoteRequest->id . '</strong>',
                        'url' => generateDashUrl('quote_requests'),
                        'buttonText' => trans("view_details")
                    ])
                ];
                addToEmailQueue($emailData);
            }
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Reject Quote
     */
    public function rejectQuote()
    {
        $id = inputPost('id');
        $quoteRequest = $this->biddingModel->getQuoteRequest($id);
        if ($this->biddingModel->rejectQuote($quoteRequest)) {
            //send email
            $seller = getUser($quoteRequest->seller_id);
            if (!empty($seller) && getEmailOptionStatus($this->generalSettings, 'bidding_system') == 1) {
                $emailData = [
                    'email_type' => 'quote',
                    'email_address' => $seller->email,
                    'email_subject' => trans("quote_request"),
                    'template_path' => 'email/main',
                    'email_data' => serialize([
                        'content' => trans("your_quote_rejected") . '<br>' . trans("quote") . ': ' . '<strong>#' . $quoteRequest->id . '</strong>',
                        'url' => generateDashUrl('quote_requests'),
                        'buttonText' => trans("view_details")
                    ])
                ];
                addToEmailQueue($emailData);
            }
        } else {
            setErrorMessage(trans("msg_error"));
        }
        redirectToBackUrl();
    }

    /**
     * Delete Quote Request
     */
    public function deleteQuoteRequest()
    {
        $id = inputPost('id');
        $this->biddingModel->deleteQuoteRequest($id);
        $this->biddingModel->deleteQuoteRequestIfBothDeleted($id);
        return jsonResponse();
    }
}
