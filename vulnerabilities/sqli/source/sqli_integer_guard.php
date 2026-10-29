<?php

function validateUserId($input): ?int
{
    if (!is_string($input) && !is_int($input)) {
        return null;
    }

    $input = (string) $input;

    if (!preg_match('/\A[1-9][0-9]*\z/D', $input)) {
        return null;
    }

    $id = filter_var(
        $input,
        FILTER_VALIDATE_INT,
        [
            'options' => [
                'min_range' => 1,
                'max_range' => 2147483647
            ]
        ]
    );

    return $id === false ? null : $id;
}


