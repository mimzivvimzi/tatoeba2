<?php
/**
 * Tatoeba Project, free collaborative creation of multilingual corpuses project
 * Copyright (C) 2018   HO Ngoc Phuong Trang
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
 */
namespace App\Model\Table;

use App\Model\Entity\User;
use App\Model\CurrentUser;
use Cake\ORM\Table;
use Cake\ORM\Entity;
use Cake\Database\Schema\TableSchema;
use Cake\Datasource\Exception\RecordNotFoundException;

class UsersLanguagesTable extends Table
{
    // TODO Reimplement the update of language stats

    protected function _initializeSchema(TableSchema $schema)
    {
        $schema->setColumnType('details', 'text');
        return $schema;
    }

    public function initialize(array $config)
    {
        $this->belongsTo('Users', ['foreignKey' => 'of_user_id']);
        $this->belongsTo('Languages', ['foreignKey' => 'language_code']);

        $this->addBehavior('Timestamp');
    }

    public function getLanguagesOfUser($userId)
    {
        $languages = $this->find(
            'all',
            array(
                'conditions' => array('of_user_id' => $userId),
                'order' => 'level DESC'
            )
        );

        return $languages;
    }


    public function getLanguagesByUser($userId)
    {
        $languages = $this->find(
            'all',
            array(
                'conditions' => array('by_user_id' => $userId),
                'order' => 'level DESC'
            )
        );

        return $languages;
    }


    public function getLanguageInfoOfUser($lang, $userId)
    {
        $languageInfo = $this->find()
            ->where([
                'of_user_id' => $userId,
                'by_user_id' => $userId,
                'language_code' => $lang
            ])
            ->first();

        return $languageInfo;
    }


    public function getLanguageInfo($id)
    {
        try  {
            $result = $this->get($id)
                ->extract(['language_code', 'by_user_id']);
        } catch (RecordNotFoundException $e) {
            $result = null;
        }

        return $result;
    }


    public function getUsersForLanguage($lang)
    {
        $result = array(
            'conditions' => array(
                'language_code' => $lang,
                'Users.role IN' => User::ROLE_CONTRIBUTOR_OR_HIGHER,
            ),
            'fields' => array(
                'of_user_id',
                'level',
            ),
            'contain' => array(
                'Users' => array(
                    'fields' => array(
                        'id',
                        'username',
                        'image'
                    )
                )
            ),
            'order' => ['UsersLanguages.level' => 'DESC'],
            'limit' => 30
        );

        return $result;
    }


    public function getNumberOfUsersForEachLanguage()
    {
        $result = $this->find()
            ->select([
                'language_code',
                'total' => 'COUNT(*)'
            ])
            ->where(['Users.role IN' => User::ROLE_CONTRIBUTOR_OR_HIGHER])
            ->order(['total' => 'DESC'])
            ->contain(['Users'])
            ->group(['language_code'])
            ->toList();

        return $result;
    }

    /**
     * Executed on Sentence's beforeFind
     */
    public function reportNativeness($query) {
        $query->join([
            'table' => 'users_languages',
            'alias' => 'UsersLanguages',
            'type' => 'LEFT',
            'conditions' => [
                'Sentences.user_id = UsersLanguages.of_user_id',
                'Sentences.lang = UsersLanguages.language_code',
                'UsersLanguages.level' => 5
            ]
        ]);
        $isNative = $query->newExpr()
                          ->isNotNull('UsersLanguages.id')
                          ->notEq('Users.role', 'spammer')
                          ->gt('Users.level', '-1');
        $query->select(['Users__is_native' => $isNative]);
        return $query;
    }

    /**
     * Save a language for the user
     *
     * @param array   $data          The request data
     * @param integer $currentUserId The user id
     *
     * @return Entity|false
     **/
    public function saveUserLanguage($data, $currentUserId) 
    {
        if (empty($data['id'])) {
            $canSave = !empty($data['language_code']) && $data['language_code'] != 'und';
            $langInfo = $this->newEntity();
            $langInfo->language_code = $data['language_code'];
        } else {
            $id = $data['id'];
            try {
                $langInfo = $this->get($id);
            } catch (RecordNotFoundException $e) {
                $langInfo = $this->newEntity();
            }            
            $canSave = $langInfo->by_user_id == $currentUserId;
        }

        if ($canSave) {
            $langInfo->of_user_id = $currentUserId;
            $langInfo->by_user_id = $currentUserId;
            $langInfo->level = isset($data['level']) && is_numeric($data['level']) ? $data['level'] : null;
            $langInfo->details = isset($data['details']) ? $data['details'] : null;
            
            return $this->save($langInfo);
        } else {
            return false;
        }
    }

    public function deleteUserLanguage($id, $currentUserId)
    {
        try {
            $langInfo = $this->get($id);
        } catch (RecordNotFoundException $e) {
            $langInfo = null;
        }
        
        $canDelete = $langInfo && $langInfo->by_user_id == $currentUserId;

        if ($canDelete) {
            return $this->delete($langInfo);
        } else {
            return false;
        }
    }
}
