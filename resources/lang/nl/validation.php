<?php

return [

    /*
    |---------------------------------------------------------------------------
    | Validation Language Lines
    |---------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'De :attribute moet worden geaccepteerd.',
    'active_url' => 'De :attribute is geen geldige URL.',
    'after' => 'De :attribute moet een datum na :date zijn.',
    'after_or_equal' => 'De :attribute moet een datum na of gelijk aan :date zijn.',
    'alpha' => 'De :attribute mag alleen letters bevatten.',
    'alpha_dash' => 'De :attribute mag alleen letters, cijfers, streepjes en underscores bevatten.',
    'alpha_num' => 'De :attribute mag alleen letters en cijfers bevatten.',
    'array' => 'De :attribute moet een array zijn.',
    'before' => 'De :attribute moet een datum voor :date zijn.',
    'before_or_equal' => 'De :attribute moet een datum voor of gelijk aan :date zijn.',
    'between' => [
        'numeric' => 'De :attribute moet tussen :min en :max liggen.',
        'file' => 'De :attribute moet tussen :min en :max kilobytes zijn.',
        'string' => 'De :attribute moet tussen :min en :max tekens zijn.',
        'array' => 'De :attribute moet tussen :min en :max items hebben.',
    ],
    'boolean' => 'Het veld :attribute moet waar of onwaar zijn.',
    'confirmed' => 'De bevestiging van de :attribute komt niet overeen.',
    'date' => 'De :attribute is geen geldige datum.',
    'date_equals' => 'De :attribute moet een datum zijn die gelijk is aan :date.',
    'date_format' => 'De :attribute komt niet overeen met het formaat :format.',
    'different' => 'De :attribute en :other moeten verschillend zijn.',
    'digits' => 'De :attribute moet :digits cijfers zijn.',
    'digits_between' => 'De :attribute moet tussen :min en :max cijfers zijn.',
    'dimensions' => 'De :attribute heeft ongeldige afbeeldingsdimensies.',
    'distinct' => 'Het veld :attribute heeft een duplicaatwaarde.',
    'email' => 'De :attribute moet een geldig e-mailadres zijn.',
    'ends_with' => 'De :attribute moet eindigen met een van de volgende: :values.',
    'exists' => 'De geselecteerde :attribute is ongeldig.',
    'file' => 'De :attribute moet een bestand zijn.',
    'filled' => 'Het veld :attribute moet een waarde hebben.',
    'gt' => [
        'numeric' => 'De :attribute moet groter zijn dan :value.',
        'file' => 'De :attribute moet groter zijn dan :value kilobytes.',
        'string' => 'De :attribute moet groter zijn dan :value tekens.',
        'array' => 'De :attribute moet meer dan :value items hebben.',
    ],
    'gte' => [
        'numeric' => 'De :attribute moet groter dan of gelijk zijn aan :value.',
        'file' => 'De :attribute moet groter dan of gelijk zijn aan :value kilobytes.',
        'string' => 'De :attribute moet groter dan of gelijk zijn aan :value tekens.',
        'array' => 'De :attribute moet :value items of meer hebben.',
    ],
    'image' => 'De :attribute moet een afbeelding zijn.',
    'in' => 'De geselecteerde :attribute is ongeldig.',
    'in_array' => 'Het veld :attribute bestaat niet in :other.',
    'integer' => 'De :attribute moet een geheel getal zijn.',
    'ip' => 'De :attribute moet een geldige IP-adres zijn.',
    'ipv4' => 'De :attribute moet een geldig IPv4-adres zijn.',
    'ipv6' => 'De :attribute moet een geldig IPv6-adres zijn.',
    'json' => 'De :attribute moet een geldige JSON-string zijn.',
    'lt' => [
        'numeric' => 'De :attribute moet kleiner zijn dan :value.',
        'file' => 'De :attribute moet kleiner zijn dan :value kilobytes.',
        'string' => 'De :attribute moet kleiner zijn dan :value tekens.',
        'array' => 'De :attribute moet minder dan :value items hebben.',
    ],
    'lte' => [
        'numeric' => 'De :attribute moet kleiner dan of gelijk zijn aan :value.',
        'file' => 'De :attribute moet kleiner dan of gelijk zijn aan :value kilobytes.',
        'string' => 'De :attribute moet kleiner dan of gelijk zijn aan :value tekens.',
        'array' => 'De :attribute mag niet meer dan :value items hebben.',
    ],
    'max' => [
        'numeric' => 'De :attribute mag niet groter zijn dan :max.',
        'file' => 'De :attribute mag niet groter zijn dan :max kilobytes.',
        'string' => 'De :attribute mag niet groter zijn dan :max tekens.',
        'array' => 'De :attribute mag niet meer dan :max items hebben.',
    ],
    'mimes' => 'De :attribute moet een bestand zijn van het type: :values.',
    'mimetypes' => 'De :attribute moet een bestand zijn van het type: :values.',
    'min' => [
        'numeric' => 'De :attribute moet minstens :min zijn.',
        'file' => 'De :attribute moet minstens :min kilobytes zijn.',
        'string' => 'De :attribute moet minstens :min tekens zijn.',
        'array' => 'De :attribute moet minstens :min items hebben.',
    ],
    'not_in' => 'De geselecteerde :attribute is ongeldig.',
    'not_regex' => 'Het formaat van de :attribute is ongeldig.',
    'numeric' => 'De :attribute moet een nummer zijn.',
    'password' => 'Het wachtwoord is onjuist.',
    'present' => 'Het veld :attribute moet aanwezig zijn.',
    'regex' => 'Het formaat van de :attribute is ongeldig.',
    'required' => 'Het veld :attribute is verplicht.',
    'required_if' => 'Het veld :attribute is verplicht wanneer :other :value is.',
    'required_unless' => 'Het veld :attribute is verplicht, tenzij :other in :values is.',
    'required_with' => 'Het veld :attribute is verplicht wanneer :values aanwezig is.',
    'required_with_all' => 'Het veld :attribute is verplicht wanneer :values aanwezig zijn.',
    'required_without' => 'Het veld :attribute is verplicht wanneer :values niet aanwezig is.',
    'required_without_all' => 'Het veld :attribute is verplicht wanneer geen van :values aanwezig is.',
    'same' => 'De :attribute en :other moeten overeenkomen.',
    'size' => [
        'numeric' => 'De :attribute moet :size zijn.',
        'file' => 'De :attribute moet :size kilobytes zijn.',
        'string' => 'De :attribute moet :size tekens zijn.',
        'array' => 'De :attribute moet :size items bevatten.',
    ],
    'starts_with' => 'De :attribute moet beginnen met een van de volgende: :values.',
    'string' => 'De :attribute moet een string zijn.',
    'timezone' => 'De :attribute moet een geldige tijdzone zijn.',
    'unique' => 'De :attribute is al in gebruik.',
    'uploaded' => 'De :attribute is niet geüpload.',
    'url' => 'Het formaat van de :attribute is ongeldig.',
    'uuid' => 'De :attribute moet een geldige UUID zijn.',

    /*
    |---------------------------------------------------------------------------
    | Custom Validation Language Lines
    |---------------------------------------------------------------------------
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
    |---------------------------------------------------------------------------
    | Custom Validation Attributes
    |---------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];