<?php

namespace App\graphql;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;
use App\graphql\Resolvers;

class Types
{
    private static $userType;
    private static $noteType;
    private static $noteRecipientType;
    private static $authPayloadType;
    private static $queryType;
    private static $mutationType;

    public static function user()
    {
        return self::$userType ?: (self::$userType = new ObjectType([
            'name' => 'User',
            'fields' => [
                'id' => Type::int(),
                'login' => Type::string(),
                'created_at' => Type::string(),
            ]
        ]));
    }

    public static function note()
    {
        return self::$noteType ?: (self::$noteType = new ObjectType([
            'name' => 'Note',
            'fields' => [
                'id' => Type::int(),
                'author_id' => Type::int(),
                'author' => [
                    'type' => self::user(),
                    'resolve' => function ($note) {
                        return Resolvers::getUserById($note['author_id']);
                    }
                ],
                'content' => Type::string(),
                'created_at' => Type::string(),
                'updated_at' => Type::string(),
                'recipients' => [
                    'type' => Type::listOf(self::user()),
                    'resolve' => function ($note) {
                        return Resolvers::getNoteRecipients($note['id']);
                    }
                ]
            ]
        ]));
    }

    public static function authPayload()
    {
        return self::$authPayloadType ?: (self::$authPayloadType = new ObjectType([
            'name' => 'AuthPayload',
            'fields' => [
                'token' => Type::string(),
                'user' => self::user()
            ]
        ]));
    }

    public static function query()
    {
        return self::$queryType ?: (self::$queryType = new ObjectType([
            'name' => 'Query',
            'fields' => [
                'me' => [
                    'type' => self::user(),
                    'resolve' => function () {
                        return Resolvers::getCurrentUser();
                    }
                ],
                'users' => [
                    'type' => Type::listOf(self::user()),
                    'resolve' => function () {
                        return Resolvers::getAllUsers();
                    }
                ],
                'myNotes' => [
                    'type' => Type::listOf(self::note()),
                    'description' => 'Нотатки, створені поточним користувачем',
                    'resolve' => function () {
                        return Resolvers::getMyNotes();
                    }
                ],
                'receivedNotes' => [
                    'type' => Type::listOf(self::note()),
                    'description' => 'Нотатки, отримані поточним користувачем',
                    'resolve' => function () {
                        return Resolvers::getReceivedNotes();
                    }
                ],
                'note' => [
                    'type' => self::note(),
                    'args' => [
                        'id' => Type::int()
                    ],
                    'resolve' => function ($root, $args) {
                        return Resolvers::getNoteById($args['id']);
                    }
                ],
                'pinnedUsers' => [
                    'type' => Type::listOf(self::user()),
                    'description' => 'Список запінених користувачів',
                    'resolve' => function () {
                        return Resolvers::getPinnedUsers();
                    }
                ],
                'blockedUsers' => [
                    'type' => Type::listOf(self::user()),
                    'description' => 'Список заблокованих користувачів',
                    'resolve' => function () {
                        return Resolvers::getBlockedUsers();
                    }
                ]
            ]
        ]));
    }

    public static function mutation()
    {
        return self::$mutationType ?: (self::$mutationType = new ObjectType([
            'name' => 'Mutation',
            'fields' => [
                'register' => [
                    'type' => self::authPayload(),
                    'args' => [
                        'login' => Type::string(),
                        'password' => Type::string()
                    ],
                    'resolve' => function ($root, $args) {
                        return Resolvers::register($args['login'], $args['password']);
                    }
                ],
                'login' => [
                    'type' => self::authPayload(),
                    'args' => [
                        'login' => Type::string(),
                        'password' => Type::string()
                    ],
                    'resolve' => function ($root, $args) {
                        return Resolvers::login($args['login'], $args['password']);
                    }
                ],
                'createNote' => [
                    'type' => self::note(),
                    'args' => [
                        'content' => Type::string(),
                        'recipient_ids' => Type::listOf(Type::int())
                    ],
                    'resolve' => function ($root, $args) {
                        return Resolvers::createNote($args['content'], $args['recipient_ids'] ?? []);
                    }
                ],
                'updateNote' => [
                    'type' => self::note(),
                    'args' => [
                        'id' => Type::int(),
                        'content' => Type::string()
                    ],
                    'resolve' => function ($root, $args) {
                        return Resolvers::updateNote($args['id'], $args['content']);
                    }
                ],
                'deleteNote' => [
                    'type' => Type::boolean(),
                    'args' => [
                        'id' => Type::int()
                    ],
                    'resolve' => function ($root, $args) {
                        return Resolvers::deleteNote($args['id']);
                    }
                ],
                'pinUser' => [
                    'type' => Type::boolean(),
                    'args' => [
                        'user_id' => Type::int()
                    ],
                    'resolve' => function ($root, $args) {
                        return Resolvers::pinUser($args['user_id']);
                    }
                ],
                'unpinUser' => [
                    'type' => Type::boolean(),
                    'args' => [
                        'user_id' => Type::int()
                    ],
                    'resolve' => function ($root, $args) {
                        return Resolvers::unpinUser($args['user_id']);
                    }
                ],
                'blockUser' => [
                    'type' => Type::boolean(),
                    'args' => [
                        'user_id' => Type::int()
                    ],
                    'resolve' => function ($root, $args) {
                        return Resolvers::blockUser($args['user_id']);
                    }
                ],
                'unblockUser' => [
                    'type' => Type::boolean(),
                    'args' => [
                        'user_id' => Type::int()
                    ],
                    'resolve' => function ($root, $args) {
                        return Resolvers::unblockUser($args['user_id']);
                    }
                ]
            ]
        ]));
    }
}
