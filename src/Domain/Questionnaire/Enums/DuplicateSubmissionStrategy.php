<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Enums;

enum DuplicateSubmissionStrategy: string
{
    case ALLOW_MULTIPLE = 'allow_multiple';
    case PREVENT_BY_IP = 'prevent_by_ip';
    case PREVENT_BY_USER = 'prevent_by_user';
    case PREVENT_BY_COOKIE = 'prevent_by_cookie';
}
