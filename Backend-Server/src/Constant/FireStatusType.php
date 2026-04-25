<?php

namespace App\Constant;

use App\Trait\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum FireStatusType: string implements TranslatableInterface
{
    use EnumTrait;

    public const VALUES = ['ONFIRE', 'COMPLETED', 'INPROGRESS', 'CANCELED'];
    
    case Completed = "COMPLETED";
    case OnFire = "ONFIRE";
    case InProgress = "INPROGRESS";
    case Canceled = "CANCELED";

    
    public function trans(TranslatorInterface $translator, string|null $locale = null): string
    {   
        return $translator->trans('FireStatus.'.$this->name, locale: $locale, domain: 'general');
    }

}