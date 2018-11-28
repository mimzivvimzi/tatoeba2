<?php
/**
 * Tatoeba Project, free collaborative creation of multilingual corpuses project
 * Copyright (C) 2009-2014  Allan SIMON <allan.simon@supinfo.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  Tatoeba
 * @author   Allan SIMON <allan.simon@supinfo.com>
 * @license  Affero General Public License
 * @link     http://tatoeba.org
 */
namespace App\Controller;

use App\Controller\AppController;
use App\Event\NotificationListener;
use Cake\Event\Event;

/**
 * Controller for the wall.
 *
 * @category Wall
 * @package  Controllers
 * @author   Allan SIMON <allan.simon@supinfo.com>
 * @license  Affero General Public License
 * @link     http://tatoeba.org
 */

class WallController extends AppController
{
    public $name = 'Wall' ;
    public $paginate = [
        'sortWhitelist' => ['WallThreads.last_message_date'],
        'order' => ['WallThreads.last_message_date' => 'DESC'],
        'limit' => 10,
        'fields' => ['lft', 'rght'],
        'conditions' => ['Wall.parent_id IS NULL'],
        'contain' => [
            'WallThreads' => ['fields' => ['last_message_date']]
        ]
    ];
    public $helpers = array(
        'Wall',
        'Date',
        'Pagination'
    );
    public $components = array ('Flash', 'Mailer');
    /**
     * to know who can do what
     *
     * @return void
     */

    public function beforeFilter(Event $event)
    {
        $this->Auth->allowedActions = array(
            'index',
            'show_message',
            'messages_of_user'
        );

        $eventManager = $this->Wall->getEventManager();
        $eventManager->attach(new NotificationListener());

        return parent::beforeFilter($event);
    }

    /**
     * display main wall page with all messages
     *
     * @return void
     */
    public function index()
    {
        $tenLastMessages = $this->Wall->getLastMessages(10);

        $userId = $this->Auth->user('id');
        $groupId = $this->Auth->user('group_id');

        $messageLftRght = $this->paginate();
        $messages = $this->Wall->getMessagesThreaded($messageLftRght);
        $messages = $this->Permissions->getWallMessagesOptions(
            $messages,
            $userId,
            $groupId
        );

        $isAuthenticated = !empty($userId);

        $this->set('isAuthenticated', $isAuthenticated);
        $this->set('allMessages', $messages);
        $this->set('tenLastMessages', $tenLastMessages);
    }


    /**
     * save a new first message
     *
     * @return void
     */
    public function save()
    {
        if ($this->Auth->user('id')) {
            $content = $this->request->getData('content');
            $session = $this->request->getSession();
            $lastMess = $session->read('hash_last_wall');
            $thisMess = md5($content);
            
            $session->write(
                'hash_last_wall',
                $thisMess
            );
            $this->Cookie->write(
                'hash_last_wall',
                $thisMess,
                false,
                "+1 month"
            );
            if ($lastMess != $thisMess) {
                $now = date("Y-m-d H:i:s");
                $newPost = $this->Wall->newEntity([
                    'owner'   => $this->Auth->user('id'),
                    'date'    => $now,
                    'content' => $content,
                ]);
                // now save to database
                $this->Wall->save($newPost);
            }
        }

        $this->redirect(
            array('action'=>'index')
        );
    }

    /**
     * save a new reply
     *
     * @return void
     */

    public function save_inside()
    {
        $data = $this->request->getData();
        $userId = $this->Auth->user('id');
        $content = $data['content'];
        $parentId = $data['replyTo'];

        // now save to database
        $message = $this->Wall->saveReply($parentId, $content, $userId);
        if ($message) {
            $this->set('user', $message->user);
            $this->set('message', $message);
        }
    }

    /**
     * Edit a wall post
     *
     * @param int $messageId Id of the message to edit
     *
     * @return void
     */
    public function edit($messageId)
    {
        $messageId = Sanitize::paranoid($messageId);
        $this->Wall->id = $messageId;

        if (empty($this->request->data)) {
            $message = $this->Wall->read();
            $this->request->data = $message;

            $messageOwnerId = $this->Wall->getOwnerIdOfMessage($messageId);
            $messagePermissions = $this->Permissions->getWallMessageOptions(
                null,
                $messageOwnerId,
                CurrentUser::get('id'),
                CurrentUser::get('group_id')
            );

            if ($messagePermissions['canEdit'] == false) {
                $this->_cannotEdit();
            } else {
                $this->set("message", $message);
            }
        } else {
            //$this->request->data is not empty, so go save
            $messageId = $this->request->data['Wall']['id'];
            $this->Wall->id = $messageId;

            $messageOwnerId = $this->Wall->getOwnerIdOfMessage($messageId);
            $messagePermissions = $this->Permissions->getWallMessageOptions(
                null,
                $messageOwnerId,
                CurrentUser::get('id'),
                CurrentUser::get('group_id')
            );
            if ($messagePermissions['canEdit'] == false) {
                $this->_cannotEdit();
            } else {
                $editedPost = array(
                    'id' => $messageId,
                    'content' => trim($this->request->data['Wall']['content']),
                );

                if ($this->Wall->save($editedPost)) {
                    $this->Flash->set(
                        __("Message saved.")
                    );
                    $this->redirect(
                        array(
                            "action"=>"index",
                            $messageId,
                            "#" => "message_$messageId"
                        )
                    );
                } else {
                    $firstValidationErrorMessage = reset($this->Wall->validationErrors)[0];
                    $this->Flash->set($firstValidationErrorMessage);
                    $this->redirect(
                        array(
                            "action"=>"edit",
                            $messageId
                        )
                    );
                }
            }

        }

    }

    private function _cannotEdit() {
        $noPermission = __(
            'You do not have permission to edit this message.', true
        );
        $contactAdmin = format(__(
            'If you have received this message in error, '.
            'please contact administrators at {email}.'
        ), array('email' => 'team@tatoeba.org'));

        $this->Flash->set(
            '<p>'.$noPermission.'</p>'.
            '<p>'.$contactAdmin.'</p>'
        );
        $this->redirect(array('action' => 'index'));
    }


    /**
     * use to delete a given message on the wall
     *
     * @param int $messageId Id of the message to delete
     *
     * @return void
     */

    public function delete_message($messageId)
    {
        $deleted = $this->Wall->deleteMessage($messageId);
        $this->redirect($this->referer());
    }

    /**
     * Use to display a single thread
     * usefull when we want a permalink to a message with its whole thread
     *
     * @param int $messageId the message to display with its thread
     *
     * @return void
     */
    public function show_message($messageId)
    {
        $userId = $this->Auth->user('id');
        $groupId = $this->Auth->user('group_id');

        $thread = $this->Wall->getWholeThreadContaining($messageId);

        /* NOTE : have a link to point the thread within the other thread
           is virtually impossible, as the ordering can change between the page
           generation and the user click on the link
        */
        $thread = $this->Permissions->getWallMessagesOptions(
            $thread,
            $userId,
            $groupId
        );

        if (!empty($thread)) {
            $this->set("message", $thread[0]);
        } else {
            $this->Flash->set(
                __('The message you are trying to view does not exist or has been deleted.')
            );
            $this->redirect(
                array('action' => 'index')
            );
        }


        $this->set("isAuthenticated", $this->Auth->user());
    }


    /**
     * Display messages of a user.
     *
     * @param string $username Username.
     *
     * @return void
     */
    public function messages_of_user($username)
    {
        $userId = $this->Wall->User->getIdFromUsername($username);

        $this->paginate = array(
            "order" => "date DESC",
            "limit" => 20,
            "fields" => array (
                "id", "date", "content", "hidden", "owner", "modified"
            ),
            "conditions" => array (
                "owner" => $userId,
            ),
            "contain" => array (
                "User" => array(
                    'fields' => array("username", "image")
                )
            )
        );

        $messages = $this->paginate();

        $this->set("messages", $messages);
        $this->set("username", $username);
    }


    /**
     * Hides a given message on the Wall. The message is still going to be there
     * but only visible to the admins and the author of the message.
     *
     * @param int $messageId Id of the message to hide
     *
     * @return void
     */
    public function hide_message($messageId)
    {
        if (CurrentUser::isAdmin()) {
            $messageId = Sanitize::paranoid($messageId);

            $this->Wall->id = $messageId;
            $this->Wall->saveField('hidden', true);

            // redirect to previous page
            $this->redirect($this->referer());
        }

    }


    /**
     * Display back a given message on the Wall that was hidden.
     *
     * @param int $messageId Id of the message to display again
     *
     * @return void
     */
    public function unhide_message($messageId)
    {
        if (CurrentUser::isAdmin()) {
            $messageId = Sanitize::paranoid($messageId);

            $this->Wall->id = $messageId;
            $this->Wall->saveField('hidden', false);

            // redirect to previous page
            $this->redirect($this->referer());
        }

    }

}
