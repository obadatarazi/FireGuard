<?php 
namespace App\Trait;


trait EnumTrait {

    public static function getByName(string $name){
        
        return constant("self::$name");
    }

    public static function getOptions( $translator)
    { 
        return array_map(function($item) use ($translator) {
            return [
                "label"=>static::class::getByName($item->name)
                ->trans($translator),"value"=>$item->value
            ];
        }, static::class::cases());       
    }

    public static function getByValue(?string $value): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->value == $value) {
                return $case;
            }
        }
        return null;
    }
}