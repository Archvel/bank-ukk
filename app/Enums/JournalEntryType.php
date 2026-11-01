<?php

namespace App\Enums;

enum JournalEntryType: string
{
    case Debit = 'debit';
    case Credit = 'credit';
}
