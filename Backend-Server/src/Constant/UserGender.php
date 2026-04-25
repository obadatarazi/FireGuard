<?php

namespace App\Constant;

use App\Trait\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum UserGender: string implements TranslatableInterface
{
    use EnumTrait;
    case Male = "MALE";
    case Female = "FEMALE";

    public function trans(TranslatorInterface $translator, string|null $locale = null): string
    {   
        return $translator->trans('Gender.'.$this->name, locale: $locale, domain: 'general');
    }

}