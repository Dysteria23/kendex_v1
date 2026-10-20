<?php

return [
    'accepted' => 'Debes aceptar :attribute.',
    'array' => 'El campo :attribute debe ser una lista.',
    'before_or_equal' => 'El campo :attribute debe ser una fecha anterior o igual a :date.',
    'after_or_equal' => 'El campo :attribute debe ser una fecha posterior o igual a :date.',
    'between' => [
        'numeric' => 'El campo :attribute debe estar entre :min y :max.',
        'string' => 'El campo :attribute debe tener entre :min y :max caracteres.',
    ],
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'date' => 'El campo :attribute no es una fecha válida.',
    'email' => 'Escribe un correo electrónico válido.',
    'enum' => 'El valor elegido para :attribute no es válido.',
    'exists' => 'El valor elegido para :attribute no es válido.',
    'file' => 'El campo :attribute debe ser un archivo.',
    'gt' => [
        'numeric' => 'El campo :attribute debe ser mayor que :value.',
    ],
    'gte' => [
        'numeric' => 'El campo :attribute debe ser mayor o igual que :value.',
    ],
    'in' => 'El valor elegido para :attribute no es válido.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'lte' => [
        'numeric' => 'El campo :attribute debe ser menor o igual que :value.',
    ],
    'max' => [
        'numeric' => 'El campo :attribute no puede ser mayor que :max.',
        'file' => 'El archivo :attribute no puede pesar más de :max KB.',
        'string' => 'El campo :attribute no puede tener más de :max caracteres.',
    ],
    'mimes' => 'El archivo :attribute debe ser de tipo: :values.',
    'min' => [
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'not_in' => 'El valor elegido para :attribute no es válido.',
    'numeric' => 'El campo :attribute debe ser un número.',
    'regex' => 'El formato de :attribute no es válido.',
    'required' => 'El campo :attribute es obligatorio.',
    'string' => 'El campo :attribute debe ser texto.',
    'unique' => 'Ya existe un registro con ese :attribute.',
    'uploaded' => 'No se pudo subir :attribute. Revisa el tamaño del archivo.',

    'password' => [
        'letters' => 'La contraseña debe incluir al menos una letra.',
        'mixed' => 'La contraseña debe incluir mayúsculas y minúsculas.',
        'numbers' => 'La contraseña debe incluir al menos un número.',
        'symbols' => 'La contraseña debe incluir al menos un símbolo.',
        'uncompromised' => 'Esa contraseña apareció en una filtración de datos. Elige otra.',
    ],

    'custom' => [],

    'attributes' => [
        'name' => 'nombre',
        'email' => 'correo electrónico',
        'password' => 'contraseña',
        'phone' => 'teléfono',
        'position' => 'cargo',
        'role' => 'rol',
        'category_id' => 'categoría',
        'department_id' => 'departamento',
        'amount' => 'monto total',
        'itbis_amount' => 'ITBIS',
        'rnc_provider' => 'RNC o cédula',
        'ncf' => 'NCF',
        'description' => 'descripción',
        'expense_date' => 'fecha del gasto',
        'upload' => 'comprobante',
        'budget_monthly' => 'presupuesto mensual',
        'monthly_limit' => 'límite mensual',
        'supervisor_id' => 'supervisor',
        'period' => 'período',
    ],
];
