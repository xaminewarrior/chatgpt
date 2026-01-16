<?php

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    public function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleSet) {
            $value = trim((string) ($data[$field] ?? ''));
            $constraints = explode('|', $ruleSet);

            foreach ($constraints as $constraint) {
                [$name, $parameter] = array_pad(explode(':', $constraint, 2), 2, null);

                if ($name === 'required' && $value === '') {
                    $errors[$field][] = 'This field is required.';
                }

                if ($name === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = 'Please enter a valid email.';
                }

                if ($name === 'min' && $value !== '') {
                    $min = (int) $parameter;
                    if (mb_strlen($value) < $min) {
                        $errors[$field][] = "Must be at least {$min} characters.";
                    }
                }
            }
        }

        return $errors;
    }

    public function firstError(array $errors): ?string
    {
        foreach ($errors as $fieldErrors) {
            if (!empty($fieldErrors)) {
                return $fieldErrors[0];
            }
        }

        return null;
    }
}
