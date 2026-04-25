<?php

namespace App\Constant;

use App\Trait\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum EmergencyRequestStatus: string implements TranslatableInterface
{
    use EnumTrait;

    public const VALUES = ['DANGEROUS', 'DONE'];
    
    case Dangerous = "DANGEROUS";
    case Done = "DONE";

    
    public function trans(TranslatorInterface $translator, string|null $locale = null): string
    {   
        return $translator->trans('EmergencyRequestStatus.'.$this->name, locale: $locale, domain: 'general');
    }

}