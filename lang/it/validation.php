<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'Il campo :attribute deve essere accettato.',
    'accepted_if' => 'Il campo :attribute deve essere accettato quando :other è :value.',
    'active_url' => 'Il campo :attribute deve essere un URL valido.',
    'after' => 'Il campo :attribute deve essere una data dopo :date.',
    'after_or_equal' => 'Il campo :attribute deve essere una data successiva o uguale a :date.',
    'alpha' => 'Il campo :attribute deve contenere solo lettere.',
    'alpha_dash' => 'Il campo :attribute deve contenere solo lettere, numeri, spazi e underscore.',
    'alpha_num' => 'Il campo :attribute deve contenere solo lettere e numeri.',
    'array' => 'Il campo :attribute deve essere un array.',
    'ascii' => 'Il campo :attribute può contenere solo caratteri e simboli single-byte.',
    'before' => 'Il campo :attribute deve essere una data precedente :date.',
    'before_or_equal' => 'Il campo :attribute deve essere una data precedente o uguale a :date.',
    'between' => [
        'array' => 'Il campo :attribute deve avere tra :min e :max elementi.',
        'file' => 'Il campo :attribute deve essere tra :min e :max kilobytes.',
        'numeric' => 'Il campo :attribute deve essere tra :min e :max.',
        'string' => 'Il campo :attribute deve essere tra :min e :max caratteri.',
    ],
    'boolean' => 'Il campo :attribute deve essere vero o falso.',
    'can' => 'Il campo :attribute contiene un valore non autorizzato.',
    'confirmed' => 'Il campo :attribute di conferma non coincide.',
    'current_password' => 'La password non è corretta.',
    'date' => 'Il campo :attribute deve essere una data valida.',
    'date_equals' => 'Il campo :attribute deve essere una data uguale a :date.',
    'date_format' => 'Il campo :attribute deve corrispondere al formato :format.',
    'decimal' => 'Il campo :attribute deve avere :decimal decimali.',
    'declined' => 'Il campo :attribute must be declined.',
    'declined_if' => 'Il campo :attribute must be declined when :other is :value.',
    'different' => 'Il campo :attribute e :other devono essere differenti.',
    'digits' => 'Il campo :attribute deve avere :digits cifre.',
    'digits_between' => 'Il campo :attribute deve avere tra :min e :max cifre.',
    'dimensions' => 'Il campo :attribute ha delle dimensioni invalide.',
    'distinct' => 'Il campo :attribute ha un valore duplicato.',
    'doesnt_end_with' => 'Il campo :attribute non può terminare con i seguenti valori: :values.',
    'doesnt_start_with' => 'Il campo :attribute non può iniziare con i seguenti valori: :values.',
    'email' => 'Il campo :attribute deve essere un indirizzo emai valido.',
    'ends_with' => 'Il campo :attribute must end with one of the following: :values.',
    'enum' => 'The selected :attribute is invalid.',
    'exists' => 'The selected :attribute is invalid.',
    'extensions' => 'Il campo :attribute must have one of the following extensions: :values.',
    'file' => 'Il campo :attribute must be a file.',
    'filled' => 'Il campo :attribute must have a value.',
    'gt' => [
        'array' => 'Il campo :attribute must have more than :value items.',
        'file' => 'Il campo :attribute must be greater than :value kilobytes.',
        'numeric' => 'Il campo :attribute must be greater than :value.',
        'string' => 'Il campo :attribute must be greater than :value characters.',
    ],
    'gte' => [
        'array' => 'Il campo :attribute must have :value items or more.',
        'file' => 'Il campo :attribute must be greater than or equal to :value kilobytes.',
        'numeric' => 'Il campo :attribute must be greater than or equal to :value.',
        'string' => 'Il campo :attribute must be greater than or equal to :value characters.',
    ],
    'hex_color' => 'Il campo :attribute must be a valid hexadecimal color.',
    'image' => 'Il campo :attribute must be an image.',
    'in' => 'The selected :attribute is invalid.',
    'in_array' => 'Il campo :attribute must exist in :other.',
    'integer' => 'Il campo :attribute must be an integer.',
    'ip' => 'Il campo :attribute must be a valid IP address.',
    'ipv4' => 'Il campo :attribute must be a valid IPv4 address.',
    'ipv6' => 'Il campo :attribute must be a valid IPv6 address.',
    'json' => 'Il campo :attribute must be a valid JSON string.',
    'list' => 'Il campo :attribute must be a list.',
    'lowercase' => 'Il campo :attribute must be lowercase.',
    'lt' => [
        'array' => 'Il campo :attribute must have less than :value items.',
        'file' => 'Il campo :attribute must be less than :value kilobytes.',
        'numeric' => 'Il campo :attribute must be less than :value.',
        'string' => 'Il campo :attribute must be less than :value characters.',
    ],
    'lte' => [
        'array' => 'Il campo :attribute must not have more than :value items.',
        'file' => 'Il campo :attribute must be less than or equal to :value kilobytes.',
        'numeric' => 'Il campo :attribute must be less than or equal to :value.',
        'string' => 'Il campo :attribute must be less than or equal to :value characters.',
    ],
    'mac_address' => 'Il campo :attribute must be a valid MAC address.',
    'max' => [
        'array' => 'Il campo :attribute must not have more than :max items.',
        'file' => 'Il campo :attribute must not be greater than :max kilobytes.',
        'numeric' => 'Il campo :attribute must not be greater than :max.',
        'string' => 'Il campo :attribute must not be greater than :max characters.',
    ],
    'max_digits' => 'Il campo :attribute must not have more than :max digits.',
    'mimes' => 'Il campo :attribute must be a file of type: :values.',
    'mimetypes' => 'Il campo :attribute must be a file of type: :values.',
    'min' => [
        'array' => 'Il campo :attribute must have at least :min items.',
        'file' => 'Il campo :attribute must be at least :min kilobytes.',
        'numeric' => 'Il campo :attribute must be at least :min.',
        'string' => 'Il campo :attribute must be at least :min characters.',
    ],
    'min_digits' => 'Il campo :attribute must have at least :min digits.',
    'missing' => 'Il campo :attribute must be missing.',
    'missing_if' => 'Il campo :attribute must be missing when :other is :value.',
    'missing_unless' => 'Il campo :attribute must be missing unless :other is :value.',
    'missing_with' => 'Il campo :attribute must be missing when :values is present.',
    'missing_with_all' => 'Il campo :attribute must be missing when :values are present.',
    'multiple_of' => 'Il campo :attribute must be a multiple of :value.',
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'Il campo :attribute format is invalid.',
    'numeric' => 'Il campo :attribute must be a number.',
    'password' => [
        'letters' => 'Il campo :attribute must contain at least one letter.',
        'mixed' => 'Il campo :attribute must contain at least one uppercase and one lowercase letter.',
        'numbers' => 'Il campo :attribute must contain at least one number.',
        'symbols' => 'Il campo :attribute must contain at least one symbol.',
        'uncompromised' => 'The given :attribute has appeared in a data leak. Please choose a different :attribute.',
    ],
    'present' => 'Il campo :attribute must be present.',
    'present_if' => 'Il campo :attribute must be present when :other is :value.',
    'present_unless' => 'Il campo :attribute must be present unless :other is :value.',
    'present_with' => 'Il campo :attribute must be present when :values is present.',
    'present_with_all' => 'Il campo :attribute must be present when :values are present.',
    'prohibited' => 'Il campo :attribute is prohibited.',
    'prohibited_if' => 'Il campo :attribute is prohibited when :other is :value.',
    'prohibited_unless' => 'Il campo :attribute is prohibited unless :other is in :values.',
    'prohibits' => 'Il campo :attribute prohibits :other from being present.',
    'regex' => 'Il campo :attribute format is invalid.',
    'required' => 'Il campo :attribute is required.',
    'required_array_keys' => 'Il campo :attribute must contain entries for: :values.',
    'required_if' => 'Il campo :attribute is required when :other is :value.',
    'required_if_accepted' => 'Il campo :attribute is required when :other is accepted.',
    'required_if_declined' => 'Il campo :attribute is required when :other is declined.',
    'required_unless' => 'Il campo :attribute is required unless :other is in :values.',
    'required_with' => 'Il campo :attribute is required when :values is present.',
    'required_with_all' => 'Il campo :attribute is required when :values are present.',
    'required_without' => 'Il campo :attribute is required when :values is not present.',
    'required_without_all' => 'Il campo :attribute is required when none of :values are present.',
    'same' => 'Il campo :attribute must match :other.',
    'size' => [
        'array' => 'Il campo :attribute must contain :size items.',
        'file' => 'Il campo :attribute must be :size kilobytes.',
        'numeric' => 'Il campo :attribute must be :size.',
        'string' => 'Il campo :attribute must be :size characters.',
    ],
    'starts_with' => 'Il campo :attribute must start with one of the following: :values.',
    'string' => 'Il campo :attribute must be a string.',
    'timezone' => 'Il campo :attribute must be a valid timezone.',
    'unique' => 'The :attribute has already been taken.',
    'uploaded' => 'The :attribute failed to upload.',
    'uppercase' => 'Il campo :attribute must be uppercase.',
    'url' => 'Il campo :attribute must be a valid URL.',
    'ulid' => 'Il campo :attribute must be a valid ULID.',
    'uuid' => 'Il campo :attribute must be a valid UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
