<?php namespace App\Models;

class NewsletterModel extends BaseModel
{
    protected $builder;

    public function __construct()
    {
        parent::__construct();
        $this->builder = $this->db->table('subscribers');
    }

    //add to subscriber
    public function addSubscriber($email)
    {
        $data = [
            'email' => $email,
            'token' => generateToken(),
            'created_at' => date('Y-m-d H:i:s')
        ];
        return $this->builder->insert($data);
    }

    //update subscriber token
    public function updateSubscriberToken($email)
    {
        $this->builder->where('email', cleanStr($email))->update(['token' => generateToken()]);
    }

    //get subscribers
    public function getSubscribers()
    {
        return $this->builder->orderBy('id')->get()->getResult();
    }

    //get subscriber
    public function getSubscriber($email)
    {
        return $this->builder->where('email', cleanStr($email))->get()->getRow();
    }

    //get subscriber by id
    public function getSubscriberById($id)
    {
        return $this->builder->where('id', clrNum($id))->get()->getRow();
    }

    //get subscribers count
    public function getSubscribersCount()
    {
        return $this->builder->countAllResults();
    }

    //load more subscribers
    public function loadMoreSubscribers($q, $perPage, $offset)
    {
        $q = cleanStr($q);
        if (!empty($q)) {
            $this->builder->like('email', $q);
        }
        return $this->builder->orderBy('id')->limit($perPage, $offset)->get()->getResult();
    }

    //get subscriber emails by ids
    public function getSubscriberEmailsByIds($ids)
    {
        if (!is_array($ids)) {
            $ids = array_filter(array_map('intval', explode(',', $ids)));
        }

        if (empty($ids)) {
            return [];
        }

        $rows = $this->builder->select('email')->whereIn('id', $ids)->get()->getResult();
        if (empty($rows)) {
            return [];
        }

        return array_map(fn($item) => $item->email, $rows);
    }

    //delete from subscribers
    public function deleteFromSubscribers($id)
    {
        return $this->builder->where('id', clrNum($id))->delete();
    }

    //get subscriber by token
    public function getSubscriberByToken($token)
    {
        return $this->builder->where('token', cleanStr($token))->get()->getRow();
    }

    //unsubscribe email
    public function unSubscribeEmail($email)
    {
        $this->builder->where('email', cleanStr($email))->delete();
    }

    //update settings
    public function updateSettings()
    {
        $data = processFormData('newsletter');

        $uploadModel = new UploadModel();
        $file = $uploadModel->uploadTempFile('file');

        if (!empty($file) && !empty($file['path'])) {
            $data['image'] = $uploadModel->uploadNewsletterImage($file['path']);
            $data['storage'] = $this->activeStorage;

            $existing = getSettingsUnserialized('newsletter');
            if (!empty($existing->image)) {
                deleteStorageFile($existing->image, $existing->storage);
            }
            $uploadModel->deleteTempFile($file['path']);
        }

        $serialized = serialize($data);
        return $this->db->table('general_settings')->where('id', 1)->update(['newsletter_settings' => $serialized]);
    }

    //send email
    public function sendEmail()
    {
        $emailModel = new EmailModel();
        $email = inputPost('email');
        $subject = inputPost('subject');
        $body = inputPost('body');
        $submit = inputPost('submit');
        if ($submit == "subscribers") {
            $subscriber = $this->getSubscriber($email);
            if (!empty($subscriber)) {
                if ($emailModel->sendEmailNewsletter($subscriber, $subject, $body)) {
                    return true;
                }
            }
        } else {
            $data = [
                'subject' => $subject,
                'message' => $body,
                'to' => $email,
                'template_path' => "email/newsletter",
                'subscriber' => null,
            ];
            return $emailModel->sendEmail($data);
        }
        return false;
    }
}