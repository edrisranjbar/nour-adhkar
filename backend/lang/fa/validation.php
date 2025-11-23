<?php

return [
    'required' => 'فیلد :attribute الزامی است.',
    'email' => 'فرمت :attribute معتبر نیست.',
    'unique' => ':attribute قبلاً استفاده شده است.',
    'min' => [
        'string' => ':attribute باید حداقل :min نویسه باشد.',
        'numeric' => ':attribute باید حداقل :min باشد.',
    ],
    'max' => [
        'string' => ':attribute نباید بیشتر از :max نویسه باشد.',
        'numeric' => ':attribute نباید بیشتر از :max باشد.',
    ],
    'confirmed' => 'تأییدیه :attribute مطابقت ندارد.',
    'string' => ':attribute باید یک رشته متنی باشد.',

    'attributes' => [
        'name' => 'نام',
        'email' => 'ایمیل',
        'password' => 'رمز عبور',
        'password_confirmation' => 'تکرار رمز عبور',
        'token' => 'توکن',
    ],
    
    'custom' => [
        'email' => [
            'unique' => 'این ایمیل قبلاً استفاده شده است.',
        ],
    ],
];


