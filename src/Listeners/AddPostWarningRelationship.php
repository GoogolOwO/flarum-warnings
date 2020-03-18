<?php

namespace Askvortsov\FlarumWarnings\Listeners;

use Askvortsov\FlarumWarnings\Model\Warning;
use Askvortsov\FlarumWarnings\Api\Serializer\WarningSerializer;
use Flarum\Api\Controller;
use Flarum\Api\Event\WillGetData;
use Flarum\Api\Serializer\BasicPostSerializer;
use Flarum\Event\GetApiRelationship;
use Flarum\Event\GetModelRelationship;
use Flarum\Post\Post;
use Illuminate\Contracts\Events\Dispatcher;

class AddPostWarningRelationship
{
    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(GetModelRelationship::class, [$this, 'getModelRelationship']);
        $events->listen(GetApiRelationship::class, [$this, 'getApiRelationship']);
        $events->listen(WillGetData::class, [$this, 'includeRelationships']);
    }

    /**
     * @param GetModelRelationship $event
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany|null
     */
    public function getModelRelationship(GetModelRelationship $event)
    {
        if ($event->isRelationship(Post::class, 'warnings')) {
            return $event->model->hasMany(Warning::class, 'post_id');
        }
    }

    /**
     * @param GetApiRelationship $event
     * @return \Tobscure\JsonApi\Relationship|null
     */
    public function getApiRelationship(GetApiRelationship $event)
    {
        if ($event->isRelationship(BasicPostSerializer::class, 'warnings')) {
            $actor = $event->serializer->getActor();
            $author = $event->model->user;
            if ($actor->id == $author->id || $actor->can('users.viewWarnings', $event->model)) {
                return $event->serializer->hasMany($event->model, WarningSerializer::class, 'warnings');
            }
        }
    }

    /**
     * @param WillGetData $event
     */
    public function includeRelationships(WillGetData $event)
    {
        if ($event->isController(Controller\ShowDiscussionController::class)) {
            $event->addInclude([
                'posts.warnings',
                'posts.warnings.warnedUser',
                'posts.warnings.addedByUser'
            ]);
        }

        if ($event->isController(Controller\ListPostsController::class)
            || $event->isController(Controller\ShowPostController::class)) {
            $event->addInclude([
                'warnings',
                'warnings.warnedUser',
                'warnings.addedByUser'
            ]);
        }
    }
}