<?php

namespace Wsmallnews\Comment\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Wsmallnews\Support\Enums\Traits\EnumHelper;

enum CommentStatus: string implements HasColor, HasIcon, HasLabel
{
    use EnumHelper;

    case Normal = 'normal';

    case Unaudited = 'unaudited';

    case Hidden = 'hidden';

    public function getLabel(): string | Htmlable | null
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

    public function getIcon(): string | BackedEnum | null
    {
        return match ($this) {
            self::Normal => Heroicon::Eye,
            self::Unaudited => Heroicon::DocumentCheck,
            self::Hidden => Heroicon::EyeSlash,
        };
    }
}
