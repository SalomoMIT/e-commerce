<?php namespace App\Models;

class ChatModel extends BaseModel
{
    protected $builder;
    protected $builderMessages;

    const CHATS_LIMIT = 200;
    const MESSAGES_LIMIT = 2000;

    public function __construct()
    {
        parent::__construct();
        $this->builder = $this->db->table('chat');
        $this->builderMessages = $this->db->table('chat_messages');
    }

    //add chat
    public function addChat()
    {
        $data = [
            'sender_id' => user()->id,
            'receiver_id' => inputPost('receiver_id'),
            'subject' => inputPost('subject'),
            'product_id' => inputPost('product_id'),
            'updated_at' => date("Y-m-d H:i:s"),
            'created_at' => date("Y-m-d H:i:s")
        ];
        if (empty($data['product_id'])) {
            $data['product_id'] = 0;
        }
        if ($this->builder->insert($data)) {
            return $this->db->insertID();
        }
        return false;
    }

    //add message
    public function addMessage($chatId)
    {
        $data = [
            'chat_id' => $chatId,
            'sender_id' => user()->id,
            'receiver_id' => inputPost('receiver_id'),
            'message' => inputPost('message'),
            'is_read' => 0,
            'deleted_user_id' => 0,
            'created_at' => date("Y-m-d H:i:s")
        ];
        if (!empty($data['message'])) {
            if ($messageId = $this->builderMessages->insert($data)) {
                $this->builder->where('id', clrNum($chatId))->update(['updated_at' => date("Y-m-d H:i:s")]);
                //send email
                $this->addMessageEmail($messageId);
                return $messageId;
            }
        }
        return false;
    }

    //add message
    public function addMessageEmail($messageId)
    {
        $message = $this->getMessage($messageId);
        if (!empty($message)) {
            $chat = $this->getChat($message->chat_id);
            $receiver = getUser($message->receiver_id);
            if (!empty($chat) && !empty($receiver) && $receiver->send_email_new_message == 1 && !empty($message->message)) {
                $emailData = [
                    'email_type' => 'new_message',
                    'email_address' => $receiver->email,
                    'email_subject' => trans("you_have_new_message"),
                    'email_data' => serialize(['messageSender' => getUsername(user()), 'messageSubject' => $chat->subject, 'messageText' => $message->message]),
                    'template_path' => 'email/new_message'
                ];
                addToEmailQueue($emailData);
            }
        }
    }

    //get chats by user id
    public function getChats($userId)
    {
        $escapedUserId = $this->db->escape($userId);
        $otherUserCase = "CASE WHEN chat.sender_id = {$escapedUserId} THEN chat.receiver_id ELSE chat.sender_id END";

        $subquery = "EXISTS (
        SELECT 1
        FROM chat_messages AS msg
        WHERE msg.chat_id = chat.id
        AND (msg.deleted_user_id IS NULL OR msg.deleted_user_id != {$escapedUserId}))";

        $chats = $this->builder->select("chat.*, c_user.username AS user_username, c_user.first_name AS user_first_name, c_user.last_name AS user_last_name, 
        c_user.avatar AS user_avatar,c_user.storage_avatar AS user_storage_avatar, c_user.role_id AS user_role_id")
            ->join("users AS c_user", "c_user.id = ({$otherUserCase})", "left", false)
            ->groupStart()->where("chat.sender_id", $userId)->orWhere("chat.receiver_id", $userId)->groupEnd()
            ->where($subquery)
            ->orderBy('chat.updated_at', 'DESC')
            ->limit(self::CHATS_LIMIT)
            ->get()->getResult();

        if (empty($chats)) {
            return [];
        }

        $chatIds = array_column($chats, 'id');

        $unreadCountsQuery = $this->builderMessages->select('chat_id, COUNT(id) AS unread_count')
            ->where('receiver_id', $userId)->where('is_read', 0)->whereIn('chat_id', $chatIds)
            ->groupBy('chat_id')
            ->get()->getResultArray();

        $unreadCounts = array_column($unreadCountsQuery, 'unread_count', 'chat_id');

        foreach ($chats as $chat) {
            $chat->num_unread_messages = $unreadCounts[$chat->id] ?? 0;
        }

        usort($chats, function ($a, $b) {
            if ($a->num_unread_messages != $b->num_unread_messages) {
                return $b->num_unread_messages <=> $a->num_unread_messages;
            }
            return strtotime($b->updated_at) <=> strtotime($a->updated_at);
        });

        return $chats;
    }

    //get user unread chats
    public function getUnreadChatsCount($userId)
    {
        return $this->builderMessages->select('chat_id')
            ->where('receiver_id', clrNum($userId))
            ->where('is_read', 0)
            ->where('deleted_user_id', 0)
            ->distinct()->countAllResults();
    }

    //get chat
    public function getChat($id)
    {
        return $this->builder->where('id', clrNum($id))->get()->getRow();
    }

    //get message
    public function getMessage($id)
    {
        return $this->builderMessages->where('id', clrNum($id))->get()->getRow();
    }

    //get messages
    public function getMessages($chatId, $lastMessageId = null)
    {
        $query = $this->builderMessages->select('chat_messages.*, users.avatar AS user_avatar, users.storage_avatar AS user_storage_avatar')
            ->join('users', 'users.id = chat_messages.sender_id', 'left')
            ->where('chat_id', clrNum($chatId));

        if (!empty($lastMessageId) && is_numeric($lastMessageId)) {
            $query->where('chat_messages.id >', (int)$lastMessageId)->orderBy('chat_messages.id', 'ASC');
        } else {
            $query->orderBy('chat_messages.id', 'DESC')->limit(self::MESSAGES_LIMIT);
        }

        $results = $query->get()->getResult();

        if (empty($lastMessageId)) {
            return array_reverse($results);
        }

        return $results;
    }

    //set chat messages as read
    public function setChatMessagesAsRead($chatId)
    {
        $currentUserId = user()->id;
        if (empty($currentUserId)) {
            return;
        }

        $this->builderMessages->where('chat_id', clrNum($chatId))->where('receiver_id', $currentUserId)->where('is_read', 0)
            ->update(['is_read' => 1]);
    }

    //build messages array
    public function getMessagesArray($userId, $chatId, $lastMessageId = null)
    {
        $array = [];
        $messages = $this->getMessages($chatId, $lastMessageId);
        if (!empty($messages)) {
            foreach ($messages as $message) {
                if ($message->deleted_user_id != $userId) {
                    $isRight = true;
                    if ($userId == $message->receiver_id) {
                        $isRight = false;
                    }
                    $item = [
                        'id' => $message->id,
                        'message' => esc($message->message),
                        'avatar' => getUserAvatar($message->user_avatar, $message->user_storage_avatar),
                        'time' => timeAgo($message->created_at),
                        'isRight' => $isRight
                    ];
                    array_push($array, $item);
                }
            }
        }
        return $array;
    }

    //get all chats count
    public function getChatsAllCount()
    {
        $this->filterChats();
        return $this->builder->countAllResults();
    }

    //get chats all
    public function getChatsAllPaginated($perPage, $offset)
    {
        $this->filterChats();
        return $this->builder->orderBy('updated_at DESC')->limit($perPage, $offset)->get()->getResult();
    }

    //get messages admin
    public function getMessagesAdmin($chatId)
    {
        return $this->builderMessages->select('chat_messages.*, 
        (SELECT username FROM users WHERE chat_messages.sender_id = users.id LIMIT 1) AS sender_username')
            ->where('chat_messages.chat_id', clrNum($chatId))->orderBy('created_at')->get()->getResult();
    }

    //filter chats
    public function filterChats()
    {
        $q = inputGet('q');
        if (!empty($q)) {
            $this->builder->like('subject', $q);
        }
    }

    //delete chat
    public function deleteChat($id)
    {
        $currentUserId = user()->id;
        if (empty($currentUserId)) {
            return;
        }

        $chat = $this->getChat($id);
        if (empty($chat) || ($chat->sender_id != $currentUserId && $chat->receiver_id != $currentUserId)) {
            return;
        }

        // Mark all messages in this chat that have NOT been deleted by anyone yet
        $this->builderMessages->where('chat_id', $id)->where('deleted_user_id', 0)->update(['deleted_user_id' => $currentUserId]);

        // Delete all messages in this chat that were ALREADY marked as deleted
        $this->builderMessages->where('chat_id', $id)->where('deleted_user_id !=', 0)->where('deleted_user_id !=', $currentUserId)->delete();

        // Count if there are any messages left in this chat
        $remainingMessages = $this->builderMessages->where('chat_id', $id)->countAllResults();

        // If no messages are left, delete the parent chat row
        if ($remainingMessages === 0) {
            $this->builder->where('id', $id)->delete();
        }
    }

    //delete chat permanently
    public function deleteChatPermanently($id)
    {
        $chat = $this->getChat($id);
        if (!empty($chat)) {
            $this->builder->where('id', $chat->id)->delete();
            $this->builderMessages->where('chat_id', $chat->id)->delete();
        }
        return true;
    }

    //delete chat message permanently
    public function deleteChatMessagePermanently($id)
    {
        $message = $this->getMessage($id);
        if (!empty($message)) {
            return $this->builderMessages->where('id', $message->id)->delete();
        }
    }
}