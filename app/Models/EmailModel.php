<?php namespace App\Models;

// Require third-party libraries for email sending
require APPPATH . "ThirdParty/phpmailer/vendor/autoload.php";
require APPPATH . "ThirdParty/mailgun/vendor/autoload.php";
require APPPATH . "ThirdParty/brevo/vendor/autoload.php";

// Use statements for different email libraries
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Api\TransactionalEmailsApi;
use SendinBlue\Client\Model\SendSmtpEmail;
use Mailgun\Mailgun;

class EmailModel extends BaseModel
{
    protected $builder;
    protected $emailSettings;

    public function __construct()
    {
        parent::__construct();
        // Initialize database builder for 'email_queue' table
        $this->builder = $this->db->table('email_queue');
        // Get unserialized email settings
        $this->emailSettings = getSettingsUnserialized('email');
    }

    /**
     * Add an email to the processing queue.
     * @param array $data Email data
     */
    public function addToEmailQueue($data)
    {
        if (empty($data['email_priority'])) {
            $data['email_priority'] = 2; // Default priority
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->builder->insert($data);
    }

    /**
     * Process and send emails from the queue.
     */
    public function runEmailQueue()
    {
        $this->db->transStart();
        // Lock rows while selecting
        $rows = $this->db->query("SELECT * FROM email_queue ORDER BY email_priority, id LIMIT 5 FOR UPDATE")->getResult();

        foreach ($rows as $row) {
            try {
                $data = [
                    'emailRow' => $row,
                    'to' => $row->email_address,
                    'subject' => $row->email_subject,
                    'template_path' => $row->template_path
                ];

                $this->sendEmail($data);
                $this->removeFromEmailQueue($row->id);
            } catch (\Exception $e) {
            }
        }

        $this->db->transComplete();
    }

    /**
     * Remove an email from the queue by its ID.
     * @param int $id Email queue item ID
     */
    public function removeFromEmailQueue($id)
    {
        $this->builder->where('id', clrNum($id))->delete();
    }

    /**
     * Send a test email.
     * @param string $email Recipient email address
     * @param string $subject Email subject
     * @param string $message Email message body
     * @return bool
     */
    public function sendTestEmail($email, $subject, $message)
    {
        if (!empty($email)) {
            $data = [
                'subject' => $subject,
                'message' => $message,
                'to' => $email,
                'template_path' => "email/newsletter", // A generic template for testing
                'subscriber' => "",
            ];
            return $this->sendEmail($data);
        }
        return false;
    }

    /**
     * Send a newsletter email to a subscriber.
     * @param object $subscriber Subscriber object
     * @param string $subject Email subject
     * @param string $body Email HTML body
     * @return bool
     */
    public function sendEmailNewsletter($subscriber, $subject, $body)
    {
        if (empty($subscriber) || empty($subscriber->email)) {
            return false;
        }

        // If subscriber token is missing, generate one
        if (empty($subscriber->token)) {
            $newsletterModel = new NewsletterModel();
            $newsletterModel->updateSubscriberToken($subscriber->email);
            $subscriber = $newsletterModel->getSubscriber($subscriber->email);

            if (empty($subscriber) || empty($subscriber->token)) {
                return false;
            }
        }

        $data = [
            'subject' => $subject,
            'message' => $body,
            'to' => $subscriber->email,
            'template_path' => "email/newsletter",
            'subscriber' => $subscriber,
        ];

        return $this->sendEmail($data);
    }

    /**
     * Main email sending dispatcher.
     * Selects the mail service based on settings.
     * @param array $data Email data
     * @return bool
     */
    public function sendEmail($data): bool
    {
        // Determine protocol and encryption, with safe fallbacks
        $protocol = in_array($this->emailSettings->mail_protocol, ['smtp', 'mail'], true)
            ? $this->emailSettings->mail_protocol
            : 'smtp';

        $encryption = in_array($this->emailSettings->mail_encryption, ['tls', 'ssl'], true)
            ? $this->emailSettings->mail_encryption
            : 'tls';

        $service = $this->emailSettings->mail_service;

        // Route to the appropriate sending method
        switch ($service) {
            case 'brevo':
                return $this->sendEmailBrevo($data);
            case 'mailgun':
                return $this->sendEmailMailgun($data);
            case 'codeigniter':
                return $this->sendEmailCodeigniter($protocol, $encryption, $data);
            case 'php-mailer':
            default:
                return $this->sendEmailPHPMailer($protocol, $encryption, $data);
        }
    }

    /**
     * Send email using CodeIgniter's built-in Email library.
     * @param string $protocol 'smtp' or 'mail'
     * @param string $encryption 'tls' or 'ssl'
     * @param array $data Email data
     * @return bool
     */
    public function sendEmailCodeigniter($protocol, $encryption, $data)
    {
        $email = \Config\Services::email();

        $commonConfig = [
            'mailType' => 'html',
            'charset' => 'UTF-8',
            'wordWrap' => true,
            'newline' => "\r\n",
        ];

        if ($protocol === 'smtp') {
            $email->initialize(array_merge($commonConfig, [
                'protocol' => 'smtp',
                'SMTPHost' => $this->emailSettings->mail_host,
                'SMTPUser' => $this->emailSettings->mail_username,
                'SMTPPass' => $this->emailSettings->mail_password,
                'SMTPPort' => (int)$this->emailSettings->mail_port,
                'SMTPCrypto' => $encryption,
            ]));
        } else {
            $email->initialize(array_merge($commonConfig, ['protocol' => 'mail']));
        }

        $html = view($data['template_path'], $data);
        $text = strip_tags($html);

        $email->setFrom($this->emailSettings->mail_reply_to, $this->emailSettings->mail_title);
        $email->setTo($data['to']);
        $email->setSubject($data['subject']);
        $email->setMessage($html);
        $email->setAltMessage($text);

        if ($email->send()) {
            return true;
        }
        log_message('error', 'CodeIgniter Mail Error: ' . $email->printDebugger(['headers']));
        return false;
    }

    /**
     * Send email using PHPMailer.
     * @param string $protocol 'smtp' or 'mail'
     * @param string $encryption 'tls' or 'ssl'
     * @param array $data Email data
     * @return bool
     */
    public function sendEmailPHPMailer($protocol, $encryption, $data)
    {
        $mail = new PHPMailer(true);
        try {
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->isHTML(true);
            $mail->setFrom($this->emailSettings->mail_reply_to, $this->emailSettings->mail_title);
            $mail->addAddress($data['to']);
            $mail->Subject = $data['subject'];
            $mail->Body = view($data['template_path'], $data);
            $mail->AltBody = strip_tags($mail->Body);

            if ($protocol === 'smtp') {
                $mail->isSMTP();
                $mail->Host = $this->emailSettings->mail_host;
                $mail->SMTPAuth = true;
                $mail->Username = $this->emailSettings->mail_username;
                $mail->Password = $this->emailSettings->mail_password;
                $mail->SMTPSecure = $encryption;
                $mail->Port = $this->emailSettings->mail_port;
            } else {
                $mail->isMail();
            }

            $mail->send();
            return true;
        } catch (Exception $e) {
            log_message('error', 'PHPMailer Error: ' . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Send email with Mailgun API.
     * @param array $data Email data
     * @return bool
     */

    public function sendEmailMailgun($data)
    {
        try {
            // Validate that required settings are available
            if (empty($this->emailSettings->mailgun_api_key) || empty($this->emailSettings->mailgun_domain)) {
                log_message('error', 'Mailgun Email Error: API Key or Domain is not configured.');
                return false;
            }

            // Validate required email data
            if (empty($data['to']) || empty($data['subject']) || empty($data['template_path'])) {
                log_message('error', 'Mailgun Email Error: Missing required email data (to, subject, or template).');
                return false;
            }

            // Handle Mailgun region (US or EU)
            if (!empty($this->emailSettings->mailgun_region) && $this->emailSettings->mailgun_region === 'eu') {
                $mg = Mailgun::create($this->emailSettings->mailgun_api_key, 'https://api.eu.mailgun.net');
            } else {
                $mg = Mailgun::create($this->emailSettings->mailgun_api_key);
            }

            $domain = $this->emailSettings->mailgun_domain;

            // Render the HTML and plain text content from the view file
            $htmlContent = view($data['template_path'], $data);
            $textContent = strip_tags($htmlContent);

            // Sender email: Prioritize the specific Mailgun sender, fall back to the general reply-to.
            // This is the address that MUST be authorized to send from your Mailgun domain.
            $senderEmail = !empty($this->emailSettings->mailgun_sender_email)
                ? $this->emailSettings->mailgun_sender_email
                : $this->emailSettings->mail_reply_to;

            // Reply-To email: This is where the user's replies will go.
            $replyToEmail = $this->emailSettings->mail_reply_to;

            // Format the 'From' header using the sender's name and the authorized sender email.
            $fromHeader = !empty($this->emailSettings->mail_title)
                ? sprintf('%s <%s>', $this->emailSettings->mail_title, $senderEmail)
                : $senderEmail;

            $params = [
                'from'    => $fromHeader,
                'to'      => $data['to'],
                'subject' => $data['subject'],
                'text'    => $textContent,
                'html'    => $htmlContent
            ];

            // Add a Reply-To header. This is the best practice.
            if (!empty($replyToEmail)) {
                $params['h:Reply-To'] = $replyToEmail;
            }

            // Send the email
            $mg->messages()->send($domain, $params);

            return true;
        } catch (\Exception $e) {
            log_message('error', 'Mailgun Email Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email with Brevo (formerly Sendinblue) API.
     * @param array $data Email data
     * @return bool
     */
    public function sendEmailBrevo($data)
    {
        try {
            if (empty($data['to']) || empty($data['template_path'])) {
                log_message('error', 'Brevo Email Error: Missing required email data.');
                return false;
            }

            // Configure API key authorization
            $config = \Brevo\Client\Configuration::getDefaultConfiguration()
                ->setApiKey('api-key', $this->emailSettings->brevo_api_key);

            // Create API instance
            $apiInstance = new \Brevo\Client\Api\TransactionalEmailsApi(
                new \GuzzleHttp\Client(),
                $config
            );

            // Render email template
            $html = view($data['template_path'], $data);

            // Create email object
            $email = new \Brevo\Client\Model\SendSmtpEmail([
                'subject' => $data['subject'] ?? '',
                'sender' => [
                    'name' => $this->emailSettings->mail_title,
                    'email' => $this->emailSettings->mail_reply_to
                ],
                'to' => [
                    ['email' => $data['to'], 'name' => $this->emailSettings->mail_title]
                ],
                'htmlContent' => $html,
                'textContent' => strip_tags($html),
            ]);

            // Send email
            $result = $apiInstance->sendTransacEmail($email);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

}