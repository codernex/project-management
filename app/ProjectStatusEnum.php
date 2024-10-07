<?php

namespace App;

enum ProjectStatusEnum: string
{
    case PENDING = 'pending';
    case PROGRESSING = 'progressing';
    case COMPLETED = 'completed';
}
