<?php

namespace App\Controllers;

use App\Models\ChatModel;
use CodeIgniter\Controller;

/**
 * This controller bypasses the heavy load of BaseController to provide
 * various background services for the application. It is designed for
 * frequent, asynchronous, and lightweight operations (polling).
 */
class ServiceController extends Controller
{
    protected $helpers = ['text', 'app'];
    protected $session;
    protected $db;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->session = session();
        $this->db = db_connect();
    }

    /**
     * Synchronizes the chat state for the current user
     */
    public function syncChat()
    {
        $chatId = (int)$this->request->getPost('chatId');
        $lastChatMessageId = (int)$this->request->getPost('lastChatMessageId'); // The last message ID of the current chat
        $lastKnownStateId = (int)$this->request->getPost('lastKnownStateId'); // To check the final version of the front side
        $currentUserId = (int)$this->session->get('auth_user_id');

        if (empty($currentUserId)) {
            return $this->response->setJSON(['status' => 0, 'reason' => 'auth_required']);
        }

        $newMessages = $this->db->table('chat_messages')
            ->select('id, chat_id')
            ->where('receiver_id', $currentUserId)->where('is_read', 0)
            ->orderBy('id', 'ASC')
            ->get()->getResult();

        if (empty($newMessages)) {
            return $this->response->setJSON(['status' => 0, 'reason' => 'no_new_messages']);
        }

        $currentStateId = 0;
        if (!empty($newMessages)) {
            $lastMessage = end($newMessages);
            $currentStateId = $lastMessage->id;
        }

        if ($currentStateId <= $lastKnownStateId) {
            return $this->response->setJSON(['status' => 0, 'reason' => 'no_new_changes']);
        }

        $model = new ChatModel();

        if(!empty($chatId)){
            $model->setChatMessagesAsRead($chatId);
        }

        $chat = null;
        $chats = $model->getChats($currentUserId);
        $arrayMessages = [];

        if (!empty($chatId)) {
            $chat = $model->getChat($chatId);

            $hasNewMessages = false;
            foreach ($newMessages as $message) {
                if ($message->chat_id == $chatId) {
                    $hasNewMessages = true;
                    break;
                }
            }

            if ($hasNewMessages) {
                $arrayMessages = $model->getMessagesArray($currentUserId, $chat->id, $lastChatMessageId);
            }
        }

        return $this->response->setJSON([
            'status' => 1,
            'currentStateId' => $currentStateId,
            'chatId' => !empty($chat) ? $chat->id : null,
            'htmlContacts' => view('chat/_contacts', ['chat' => $chat, 'chats' => $chats]),
            'arrayMessages' => array_slice($arrayMessages, 0, 10)
        ]);

        return $this->response->setJSON(['status' => 0]);
    }
}