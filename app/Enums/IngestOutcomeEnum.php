<?php

namespace App\Enums;

enum IngestOutcomeEnum: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Unchanged = 'unchanged';
}
