<?php

namespace App\Libraries;

use CodeIgniter\HTTP\IncomingRequest;
use InvalidArgumentException;

/**
 * YooMoney Integration Library for CodeIgniter 4
 *
 * Handles webhook verification and data parsing for secure payment processing.
 */
class YooMoney
{
    /**
     * The YooMoney Secret Key used for hash validation.
     *
     * @var string
     */
    private string $secretKey;

    /**
     * Constructor.
     *
     * @param object $config Configuration object containing 'secret_key'
     * @throws InvalidArgumentException If secret key is missing.
     */
    public function __construct(object $config)
    {
        if (empty($config->secret_key)) {
            throw new InvalidArgumentException('YooMoney Secret Key is required.');
        }

        $this->secretKey = $config->secret_key;
    }

    /**
     * Handles and verifies an incoming webhook request.
     *
     * @param IncomingRequest $request
     * @return object|null Returns parsed data as object if valid, otherwise null
     */
    public function handleWebhook(IncomingRequest $request): ?object
    {
        $data = [
            'notificationType' => $request->getPost('notification_type'),
            'operationId'      => $request->getPost('operation_id'),
            'amount'           => $request->getPost('amount'),
            'withdrawAmount'   => $request->getPost('withdraw_amount'),
            'currency'         => $request->getPost('currency'),
            'datetime'         => $request->getPost('datetime'),
            'sender'           => $request->getPost('sender'),
            'codepro'          => $request->getPost('codepro'),
            'label'            => $request->getPost('label'),
            'sha1Hash'         => $request->getPost('sha1_hash'),
        ];

        // Create string for hash verification
        $paramsForHash = [
            $data['notificationType'],
            $data['operationId'],
            $data['amount'],
            $data['currency'],
            $data['datetime'],
            $data['sender'],
            $data['codepro'],
            $this->secretKey,
            $data['label'],
        ];

        $stringToHash = implode('&', $paramsForHash);
        $generatedHash = sha1($stringToHash);

        // Does the incoming hash match the hash we created?
        if (strtolower($generatedHash) !== strtolower($data['sha1Hash'])) {
            log_message('critical', 'YooMoney Webhook: HASH MISMATCH. Notification rejected.');
            return null;
        }

        return (object) $data;
    }
}