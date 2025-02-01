<?php

namespace App\Enums;

enum InputTypeEnum: string
{
    case TEXT = 'text';
    case DATE = 'date';
    case SELECT = 'select';
    case CHECKBOX = 'checkbox';
    case RADIO = 'radio';

    public function getElement(string $name, ?string $value = null, string $style = null, string $classes = null, array $options = []): string
    {
        return match ($this){
            self::TEXT => '<input type="text" name="'.$name.'" id="'.$name.'" value="'.$value.'" style="'.$style.'" class="'.$classes.'" />',
            self::DATE => '<input type="date" name="'.$name.'" id="'.$name.'" value="'.$value.'" style="'.$style.'" class="'.$classes.'"  />',
            self::SELECT => (function() use($name, $style, $value, $classes, $options){
                $return[] ='<select type="date" name="'.$name.'"  id="'.$name.'" value="'.$value.'" style="'.$style.'" class="'.$classes.'">';
                foreach ($options as $key => $option){
                    $return[] = '<option value="'.$key.'"  '.($value == $key ? 'selected' : '').'>'.$option.'</option>';
                }
                $return[] = '</select>';

                return implode('', $return);
            })(),
            default => '',
        };
    }
}
