<?php

namespace App\Helpers;

use Cocur\Slugify\RuleProvider\DefaultRuleProvider;

class SlugifyRuleProvider extends DefaultRuleProvider
{

    public function __construct()
    {
        $this->rules['arabic'] = array(
            "ء" => "ء",
            "ا" => "ا",
            "أ" => "أ",
            "إ" => "إ",
            "آ" => "آ",
            "ؤ" => "ؤ",
            "ئ" => "ئ",
            "ب" => "ب",
            "ت" => "ت",
            "ث" => "ث",
            "ج" => "ج",
            "ح" => "ح",
            "خ" => "خ",
            "د" => "د",
            "ذ" => "ذ",
            "ر" => "ر",
            "ز" => "ز",
            "س" => "س",
            "ش" => "ش",
            "ص" => "ص",
            "ض" => "ض",
            "ط" => "ط",
            "ظ" => "ظ",
            "ع" => "ع",
            "غ" => "غ",
            "ف" => "ف",
            "ق" => "ق",
            "ك" => "ك",
            "ل" => "ل",
            "م" => "م",
            "ن" => "ن",
            "ه" => "ه",
            "و" => "و",
            "ي" => "ي",
            "ة" => "ة",
            "ى" => "ى",
        );
    }
}
