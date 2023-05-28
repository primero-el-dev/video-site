<?php

namespace App\Entity\Enum;

enum UserActionEnum: string
{
    case LIKE = 'like';
    case LIKE_DELETE = 'like_delete';
    case DISLIKE = 'dislike';
    case DISLIKE_DELETE = 'dislike_delete';
    case REPORT = 'report';
    case REPORT_DELETE = 'report_delete';
    case SUBSCRIBE = 'subscribe';
    case SUBSCRIBE_DELETE = 'subscribe_delete';
    case COMMENT_CREATE = 'comment_create';
    case COMMENT_DELETE = 'comment_delete';
    case VIDEO_CREATE = 'video_create';
    case VIDEO_DELETE = 'video_delete';
    case PLAYLIST_CREATE = 'playlist_create';
    case PLAYLIST_DELETE = 'playlist_delete';

    public function getBaseAction(): self
    {
        if (!$this->isBaseAction()) {
            $action = substr($this->value, 0, -7);
            
            return self::tryFrom($action) ?? self::tryFrom($action . '_create');
        }

        return $this;
    }

    public function isBaseAction(): bool
    {
        return !str_ends_with($this->value, '_delete');
    }

    public function getReverse(): self
    {
        if (str_ends_with($this->value, '_delete')) {
            return $this->getBaseAction();
        } elseif (str_ends_with($this->value, '_create')) {
            return self::from(substr($this->value, 0, -7) . '_delete');
        } else {
            return self::from($this->value . '_delete');
        }
    }
}