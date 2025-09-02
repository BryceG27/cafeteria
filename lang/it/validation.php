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
    'declined' => 'Il campo :attribute deve essere rifiutato.',
    'declined_if' => 'Il campo :attribute deve essere rifiutato quando :other è :value.',
    'different' => 'Il campo :attribute e :other devono essere differenti.',
    'digits' => 'Il campo :attribute deve avere :digits cifre.',
    'digits_between' => 'Il campo :attribute deve avere tra :min e :max cifre.',
    'dimensions' => 'Il campo :attribute ha delle dimensioni invalide.',
    'distinct' => 'Il campo :attribute ha un valore duplicato.',
    'doesnt_end_with' => 'Il campo :attribute non può terminare con i seguenti valori: :values.',
    'doesnt_start_with' => 'Il campo :attribute non può iniziare con i seguenti valori: :values.',
    'email' => 'Il campo :attribute deve essere un indirizzo emai valido.',
    'ends_with' => 'Il campo :attribute deve terminare con uno dei seguenti: :values.',
    'enum' => 'Il campo :attribute non è valido.',
    'exists' => 'Il campo :attribute non esiste.',
    'extensions' => 'Il campo :attribute deve avere una delle seguenti estensioni: :values.',
    'file' => 'Il campo :attribute deve essere un file.',
    'filled' => 'Il campo :attribute deve essere popolato.',
    'gt' => [
        'array' => 'Il campo :attribute deve avere più di :value elementi.',
        'file' => 'Il campo :attribute deve essere più grande di :value kilobytes.',
        'numeric' => 'Il campo :attribute deve essere più grande di :value.',
        'string' => 'Il campo :attribute deve essere più grande di :value caratteri.',
    ],
    'gte' => [
        'array' => 'Il campo :attribute deve avere :value elementi o più.',
        'file' => 'Il campo :attribute deve essere più grande o uguale a :value kilobytes.',
        'numeric' => 'Il campo :attribute deve essere più grande o uguale a :value.',
        'string' => 'Il campo :attribute deve essere più grande o uguale a :value caratteri.',
    ],
    'hex_color' => 'Il campo :attribute deve essere un colore esadecimale valido.',
    'image' => 'Il campo :attribute deve essere un\'immagine.',
    'in' => 'The selected :attribute è invalido.',
    'in_array' => 'Il campo :attribute deve esistere in :other.',
    'integer' => 'Il campo :attribute deve essere un intero.',
    'ip' => 'Il campo :attribute deve essere un indirizzo IP valido.',
    'ipv4' => 'Il campo :attribute deve essere un indirizzo IPv4 valido.',
    'ipv6' => 'Il campo :attribute deve essere un indirizzo IPv6 valido.',
    'json' => 'Il campo :attribute deve essere una string JSON valida.',
    'list' => 'Il campo :attribute deve essere una lista.',
    'lowercase' => 'Il campo :attribute deve essere minuscolo.',
    'lt' => [
        'array' => 'Il campo :attribute deve avere meno di :value elementi.',
        'file' => 'Il campo :attribute deve essere meno di :value kilobytes.',
        'numeric' => 'Il campo :attribute deve essere meno di :value.',
        'string' => 'Il campo :attribute deve essere meno di :value caratteri.',
    ],
    'lte' => [
        'array' => 'Il campo :attribute non può avere più di :value elementi.',
        'file' => 'Il campo :attribute deve essere meno o uguale a :value kilobytes.',
        'numeric' => 'Il campo :attribute deve essere meno o uguale a :value.',
        'string' => 'Il campo :attribute deve essere meno o uguale a :value caratteri.',
    ],
    'mac_address' => 'Il campo :attribute deve essere un indirizzo MAC valido.',
    'max' => [
        'array' => 'Il campo :attribute non può avere più di :max elementi.',
        'file' => 'Il campo :attribute non può essere più grande di :max kilobytes.',
        'numeric' => 'Il campo :attribute non può essere più grande di :max.',
        'string' => 'Il campo :attribute non può essere più grande di :max caratteri.',
    ],
    'max_digits' => 'Il campo :attribute non può avere più di :max cifre.',
    'mimes' => 'Il campo :attribute deve essere un file di tipo: :values.',
    'mimetypes' => 'Il campo :attribute deve essere un file di tipo: :values.',
    'min' => [
        'array' => 'Il campo :attribute deve avere almeno :min elementi.',
        'file' => 'Il campo :attribute deve essere almeno :min kilobytes.',
        'numeric' => 'Il campo :attribute deve essere almeno :min.',
        'string' => 'Il campo :attribute deve essere almeno :min caratteri.',
    ],
    'min_digits' => 'Il campo :attribute deve avere almeno :min cifre.',
    'missing' => 'Il campo :attribute deve essere assente.',
    'missing_if' => 'Il campo :attribute deve essere assente quando :other è :value.',
    'missing_unless' => 'Il campo :attribute deve essere assente a meno che :other è :value.',
    'missing_with' => 'Il campo :attribute deve essere assente quando :values è present.',
    'missing_with_all' => 'Il campo :attribute deve essere assente quando :values sono presenti.',
    'multiple_of' => 'Il campo :attribute deve essere un multiplo di :value.',
    'not_in' => 'The selected :attribute è invalido.',
    'not_regex' => 'Il campo :attribute formato è invalido.',
    'numeric' => 'Il campo :attribute deve essere un numero.',
    'password' => [
        'letters' => 'Il campo :attribute deve contenere almeno una lettera.',
        'mixed' => 'Il campo :attribute deve contenere almeno una lettera in maiuscolo e una in minuscolo.',
        'numbers' => 'Il campo :attribute deve contenere almeno un numbero.',
        'symbols' => 'Il campo :attribute deve contenere almeno un simbolo.',
        'uncompromised' => 'Il campo :attribute è apparso in un data leak. Si consiglia di scegliere un differente :attribute.',
    ],
    'present' => 'Il campo :attribute deve essere presente.',
    'present_if' => 'Il campo :attribute deve essere presente quando :other è :value.',
    'present_unless' => 'Il campo :attribute deve essere presente unless :other è :value.',
    'present_with' => 'Il campo :attribute deve essere presente quando :values è presente.',
    'present_with_all' => 'Il campo :attribute deve essere presente quando :values sono presenti.',
    'prohibited' => 'Il campo :attribute è proibito.',
    'prohibited_if' => 'Il campo :attribute è proibito quando :other è :value.',
    'prohibited_unless' => 'Il campo :attribute è proibito unless :other è in :values.',
    'prohibits' => 'Il campo :attribute proibisce :other dall\'essere presente.',
    'regex' => 'Il formato del campo :attribute è invalido.',
    'required' => 'Il campo :attribute è richiesto.',
    'required_array_keys' => 'The field :attribute must contain entries for: :values.',
    'required_if' => 'Il campo :attribute è richiesto quando :other è :value.',
    'required_if_accepted' => 'Il campo :attribute è richiesto quando :other è accettato.',
    'required_if_declined' => 'Il campo :attribute è richiesto quando :other è rifiutato.',
    'required_unless' => 'Il campo :attribute è richiesto a meno che :other è in :values.',
    'required_with' => 'Il campo :attribute è richiesto quando :values is presente.',
    'required_with_all' => 'Il campo :attribute è richiesto quando :values sono presenti.',
    'required_without' => 'Il campo :attribute è richiesto quando :values non è presente.',
    'required_without_all' => 'Il campo :attribute è richiesto quando nessun dei :values sono presenti.',
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
