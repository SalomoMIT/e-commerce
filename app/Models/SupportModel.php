<?php namespace App\Models;

class SupportModel extends BaseModel
{
    protected $builder;
    protected $builderCategories;
    protected $builderTickets;
    protected $builderSubTickets;

    public function __construct()
    {
        parent::__construct();
        $this->builder = $this->db->table('knowledge_base');
        $this->builderCategories = $this->db->table('knowledge_base_categories');
        $this->builderTickets = $this->db->table('support_tickets');
        $this->builderSubTickets = $this->db->table('support_subtickets');

    }

    /*
     * --------------------------------------------------------------------
     * Content
     * --------------------------------------------------------------------
     */

    //input values
    public function inputValues()
    {
        return [
            'lang_id' => inputPost('lang_id'),
            'title' => inputPost('title'),
            'slug' => inputPost('slug'),
            'content' => inputPost('content'),
            'category_id' => inputPost('category_id'),
            'content_order' => inputPost('content_order')
        ];
    }

    //add content
    public function addContent()
    {
        $data = $this->inputValues();
        if (empty($data['slug'])) {
            $data['slug'] = strSlug($data['title']);
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->builder->insert($data);
    }

    //edit content
    public function editContent($id)
    {
        $content = $this->getContent($id);
        if (!empty($content)) {
            $data = $this->inputValues();
            $data['slug'] = removeSpecialCharacters($data['slug'], true);
            if (empty($data['slug'])) {
                $data['slug'] = strSlug($data['title']);
            }
            return $this->builder->where('id', $id)->update($data);
        }
        return false;
    }

    //get content
    public function getContent($id)
    {
        return $this->builder->where('id', clrNum($id))->get()->getRow();
    }

    //get content by slug
    public function getContentBySlug($slug)
    {
        return $this->builder->where('slug', cleanStr($slug))->get()->getRow();
    }

    //get contents by category
    public function getContentsByCategory($categoryId)
    {
        return $this->builder->where('category_id', clrNum($categoryId))->orderBy('knowledge_base.content_order')->get()->getResult();
    }

    //get first content by category
    public function getFirstContentByCategory($categoryId)
    {
        return $this->builder->where('category_id', clrNum($categoryId))->get(1)->getRow();
    }

    //get contents by langugae
    public function getContentsByLang($langId)
    {
        return $this->builder->select('knowledge_base.*, (SELECT name FROM knowledge_base_categories WHERE knowledge_base.category_id = knowledge_base_categories.id) AS category_name')
            ->where('lang_id', clrNum($langId))->orderBy('knowledge_base.content_order')->get()->getResult();
    }

    //get content search count
    public function getContentSearchCount($langId, $q)
    {
        $this->builder->select('knowledge_base.*, (SELECT name FROM knowledge_base_categories WHERE knowledge_base.category_id = knowledge_base_categories.id) AS category_name, 
        (SELECT slug FROM knowledge_base_categories WHERE knowledge_base.category_id = knowledge_base_categories.id) AS category_slug');
        if (!empty($q)) {
            $this->builder->like('knowledge_base.title', cleanStr($q))->orLike('knowledge_base.content', cleanStr($q));
        }
        return $this->builder->where('lang_id', clrNum($langId))->countAllResults();
    }

    //get content search results
    public function getContentSearchResults($langId, $q, $perPage, $offset)
    {
        $this->builder->select('knowledge_base.*, (SELECT name FROM knowledge_base_categories WHERE knowledge_base.category_id = knowledge_base_categories.id) AS category_name, 
        (SELECT slug FROM knowledge_base_categories WHERE knowledge_base.category_id = knowledge_base_categories.id) AS category_slug');
        if (!empty($q)) {
            $this->builder->like('knowledge_base.title', cleanStr($q))->orLike('knowledge_base.content', cleanStr($q));
        }
        return $this->builder->where('lang_id', clrNum($langId))->orderBy('knowledge_base.created_at DESC')->limit($perPage, $offset)->get()->getResult();
    }

    //delete content
    public function deleteContent($id)
    {
        $content = $this->getContent($id);
        if (!empty($content)) {
            return $this->builder->where('id', $content->id)->delete();
        }
        return false;
    }

    /*
     * --------------------------------------------------------------------
     * Category
     * --------------------------------------------------------------------
     */

    //add category
    public function addCategory()
    {
        $data = [
            'lang_id' => inputPost('lang_id'),
            'name' => inputPost('name'),
            'slug' => inputPost('slug'),
            'category_order' => inputPost('category_order')
        ];
        if (empty($data['slug'])) {
            $data['slug'] = strSlug($data['name']);
        }
        return $this->builderCategories->insert($data);
    }

    //edit category
    public function editCategory($id)
    {
        $category = $this->getCategory($id);
        if (!empty($category)) {
            $data = [
                'lang_id' => inputPost('lang_id'),
                'name' => inputPost('name'),
                'slug' => inputPost('slug'),
                'category_order' => inputPost('category_order')
            ];
            $data['slug'] = removeSpecialCharacters($data['slug'], true);
            return $this->builderCategories->where('id', $category->id)->update($data);
        }
        return false;
    }

    //get category
    public function getCategory($id)
    {
        return $this->builderCategories->where('id', clrNum($id))->get()->getRow();
    }

    //get category by slug
    public function getCategoryBySlug($slug)
    {
        return $this->builderCategories->where('slug', cleanStr($slug))->get()->getRow();
    }

    //get categories by langugae
    public function getCategoriesByLang($langId)
    {
        return $this->builderCategories->select('knowledge_base_categories.*, (SELECT COUNT(knowledge_base.id) FROM knowledge_base WHERE knowledge_base.category_id = knowledge_base_categories.id) AS num_content')
            ->where('lang_id', clrNum($langId))->orderBy('category_order')->get()->getResult();
    }

    //delete category
    public function deleteCategory($id)
    {
        $category = $this->getCategory($id);
        if (!empty($category)) {
            return $this->builderCategories->where('id', $category->id)->delete();
        }
        return false;
    }

    /*
     * --------------------------------------------------------------------
     * Support
     * --------------------------------------------------------------------
     */

    /*
     * Status
     * 1: Open
     * 2: Responded
     * 3: Closed
     */

    //add ticket
    public function addTicket($isSupportReply)
    {
        $userId = 0;
        $isGuest = 1;
        if (authCheck()) {
            $userId = user()->id;
            $isGuest = 0;
        }
        $data = [
            'user_id' => $userId,
            'name' => '',
            'email' => '',
            'subject' => inputPost('subject'),
            'is_guest' => $isGuest,
            'status' => 1,
            'updated_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        if ($isGuest == 1) {
            $data['name'] = inputPost('name');
            $data['email'] = inputPost('email');
        }
        if ($this->builderTickets->insert($data)) {
            $id = $this->db->insertID();
            return $this->addSubTicket($id, $isSupportReply, $userId);
        }
        return false;
    }

    //add ticket
    public function addSubTicket($ticketId, $isSupportReply, $userId = null)
    {
        $ticket = $this->getTicket($ticketId);
        if (empty($ticket)) {
            return false;
        }

        $userId = 0;
        if ($userId == null && authCheck()) {
            $userId = user()->id;
        }

        $data = [
            'ticket_id' => $ticketId,
            'user_id' => $userId,
            'message' => inputPost('message'),
            'attachments' => '',
            'storage' => $this->activeStorage,
            'is_support_reply' => $isSupportReply,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $arrayFiles = array();
        $attachments = helperGetSession('ticket_attachments');
        if (!empty($attachments)) {
            $uploadModel = new UploadModel();
            foreach ($attachments as $item) {
                $newName = $uploadModel->uploadAttachment($item->tempPath);
                if (!empty($newName)) {
                    $itemFile = new \stdClass();
                    $itemFile->id = $item->fileId;
                    $itemFile->orj_name = $item->name;
                    $itemFile->name = $newName;
                    array_push($arrayFiles, $itemFile);
                }
            }
        }

        if (!empty($arrayFiles)) {
            $data['attachments'] = serialize($arrayFiles);
        }

        if ($this->builderSubTickets->insert($data)) {
            $subTicketId = $this->db->insertID();
            if ($isSupportReply == 1) {
                $this->builderTickets->where('id', clrNum($ticketId))->update(['status' => 2]);
            } else {
                $this->builderTickets->where('id', clrNum($ticketId))->update(['status' => 1]);
            }
            $this->addSupportMessageEmail($isSupportReply, $ticket, $subTicketId);
            helperDeleteSession('ticket_attachments');
        }
        return true;
    }

    //get ticket
    public function getTicket($id)
    {
        return $this->builderTickets->where('id', clrNum($id))->get()->getRow();
    }

    //get tickets count
    public function getTicketsCount($status)
    {
        if ($status == 1 || $status == 2 || $status == 3) {
            $this->builderTickets->where('status', clrNum($status));
        }
        return $this->builderTickets->countAllResults();
    }

    //get tickets paginated
    public function getTicketsPaginated($status, $perPage, $offset)
    {
        if ($status == 1 || $status == 2 || $status == 3) {
            $this->builderTickets->where('status', clrNum($status));
        }
        return $this->builderTickets->orderBy('created_at DESC')->limit($perPage, $offset)->get()->getResult();
    }

    //get tickets by user
    public function getTicketsByUserId($userId)
    {
        return $this->builderTickets->where('user_id', clrNum($userId))->orderBy('status, id DESC')->get()->getResult();
    }

    //get tickets count
    public function getUserTicketsCount($userId)
    {
        return $this->builderTickets->where('user_id', clrNum($userId))->countAllResults();
    }

    //get tickets by user
    public function getUserTicketsPaginated($userId, $perPage, $offset)
    {
        return $this->builderTickets->where('user_id', clrNum($userId))->orderBy('status, id DESC')->limit($perPage, $offset)->get()->getResult();
    }

    //get subtickets
    public function getSubTickets($ticketId)
    {
        return $this->builderSubTickets->where('ticket_id', clrNum($ticketId))->orderBy('id DESC')->get()->getResult();
    }

    //get subticket
    public function getSubTicket($id)
    {
        return $this->builderSubTickets->where('id', clrNum($id))->get()->getRow();
    }

    //change ticket status
    public function changeTicketStatus($id, $status)
    {
        if (isAdmin() && ($status == 1 || $status == 2 || $status == 3)) {
            return $this->builderTickets->where('id', clrNum($id))->update(['status' => clrNum($status)]);
        }
        return false;
    }

    //close ticket
    public function closeTicket($id)
    {
        $ticket = $this->getTicket($id);
        if (!empty($ticket)) {
            if (user()->id == $ticket->user_id) {
                return $this->builderTickets->where('id', $ticket->id)->update(['status' => 3]);
            }
        }
        return false;
    }

    //delete ticket
    public function deleteTicket($id): bool
    {
        $ticket = $this->getTicket($id);
        if (empty($ticket)) {
            return false;
        }

        $subTickets = $this->getSubTickets($ticket->id);
        if (!empty($subTickets)) {
            foreach ($subTickets as $subTicket) {
                $attachments = unserializeData($subTicket->attachments);
                if (!empty($attachments)) {
                    foreach ($attachments as $item) {
                        if (!empty($item->name)) {
                            $filePath = 'uploads/support/' . $item->name;
                            deleteStorageFile($filePath, $subTicket->storage);
                        }
                    }
                }
            }
        }

        $this->builderSubTickets->where('ticket_id', $ticket->id)->delete();
        return $this->builderTickets->where('id', $ticket->id)->delete();
    }

    //prepare support message email
    public function addSupportMessageEmail($isSupportReply, $ticket, $subTicketId)
    {
        $subTicket = $this->builderSubTickets->where('id', clrNum($subTicketId))->get()->getRow();
        if (!empty($ticket) && !empty($subTicket) && getEmailOptionStatus($this->generalSettings, 'support_system') == 1) {
            $adminEmail = $this->generalSettings->mail_options_account;
            $userEmail = '';
            $username = '';
            if (!empty($ticket->user_id)) {
                $user = getUser($ticket->user_id);
                if (!empty($user)) {
                    $userEmail = $user->email;
                    $username = $user->username;
                }
            } else {
                if (!empty($ticket->email)) {
                    $userEmail = $ticket->email;
                    $username = $ticket->name;
                }
            }
            if ($isSupportReply) {
                //send to user
                if (!empty($userEmail)) {
                    $emailData = [
                        'email_type' => 'support',
                        'email_address' => $userEmail,
                        'email_subject' => trans("msg_support_message_replied") . ' - ' . trans("support_ticket") . ' #' . $ticket->id,
                        'template_path' => 'email/main',
                        'email_data' => serialize([
                            'mailTitle' => trans("msg_support_message_replied"),
                            'content' => trans("msg_support_message_replied_exp") . "<br>",
                            'url' => generateUrl('help_center', 'ticket') . '/' . $ticket->id,
                            'buttonText' => trans("view_details")
                        ])
                    ];
                    addToEmailQueue($emailData);
                }
            } else {
                //send to admin
                if (!empty($adminEmail)) {
                    $emailData = [
                        'email_type' => 'support',
                        'email_address' => $adminEmail,
                        'email_subject' => trans("msg_support_new_message") . ' - ' . trans("support_ticket") . ' #' . $ticket->id,
                        'template_path' => 'email/main',
                        'email_data' => serialize([
                            'mailTitle' => trans("msg_support_new_message"),
                            'content' => "<b>" . trans("user") . ":</b> " . $username .
                                "<br>" . "<b>" . trans("email") . ":</b> " . $userEmail .
                                "<br>" . "<b>" . trans("subject") . ":</b> " . $ticket->subject .
                                "<br>",
                            'url' => adminUrl('support-ticket/' . $ticket->id),
                            'buttonText' => trans("view_details")
                        ])
                    ];
                    addToEmailQueue($emailData);
                }
                //send to user
                if (!empty($userEmail)) {
                    $emailData = [
                        'email_type' => 'support',
                        'email_address' => $userEmail,
                        'email_subject' => trans("msg_support_message_received") . ' - ' . trans("support_ticket") . ' #' . $ticket->id,
                        'template_path' => 'email/main',
                        'email_data' => serialize([
                            'mailTitle' => trans("msg_support_message_received"),
                            'content' => trans("msg_support_message_received_exp") . "<br>",
                            'url' => generateUrl('help_center', 'ticket') . '/' . $ticket->id,
                            'buttonText' => trans("view_details")
                        ])
                    ];
                    addToEmailQueue($emailData);
                }
            }
        }
    }

}