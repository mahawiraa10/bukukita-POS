<?php

namespace App\Enums;

enum POStatus: string
{
    case DRAFT = 'draft';
    case APPROVED = 'approved';
    case RECEIVED = 'received';
    case CANCELLED = 'cancelled';
}
