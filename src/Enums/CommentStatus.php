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

    case Rejected = 'rejected';

    public function getLabel(): string | Htmlable | null
    {
        return match ($this) {
            self::Normal => __('sn-comment::comment.comment_status.normal'),
            self::Unaudited => __('sn-comment::comment.comment_status.unaudited'),
            self::Hidden => __('sn-comment::comment.comment_status.hidden'),
            self::Rejected => __('sn-comment::comment.comment_status.rejected'),
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Normal => 'success',
            self::Unaudited => 'warning',
            self::Hidden => 'gray',
            self::Rejected => 'danger',
        };
    }

    public function getIcon(): string | BackedEnum | Htmlable | null
    {
        return match ($this) {
            self::Normal => Heroicon::Eye,
            self::Unaudited => Heroicon::DocumentCheck,
            self::Hidden => Heroicon::EyeSlash,
            self::Rejected => Heroicon::ShieldExclamation,
        };
    }
}
