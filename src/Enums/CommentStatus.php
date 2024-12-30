<?php

namespace Wsmallnews\Comment\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;
use Wsmallnews\Support\Enums\Traits\EnumHelper;

Enum CommentStatus :string implements HasColor, HasLabel
{

    use EnumHelper;

    case Normal = 'normal';

    case Unaudited = 'unaudited';

    case Hidden = 'hidden';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Normal => '正常',
            self::Unaudited => '未审核',
            self::Hidden => '已隐藏',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Normal => 'success',
            self::Unaudited => 'warning',
            self::Hidden => 'gray',
        };
    }

}