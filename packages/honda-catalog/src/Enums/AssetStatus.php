<?php

namespace Honda\Catalog\Enums;

enum AssetStatus: string
{
    case Remote = 'remote';
    case Mirrored = 'mirrored';
    case Failed = 'failed';
}
