<?php

namespace App;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Delivering = 'delivering';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
