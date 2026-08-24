<?php
namespace App\Enums;

enum EntityStatus: string
{
    case Draft = 'draft';
    case Generated = 'generated';
    case Committed = 'committed';
    case Eaten = 'eaten';
    case Skipped = 'skipped';
    case Completed = 'completed';
}