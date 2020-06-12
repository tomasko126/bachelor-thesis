@php
    if (!function_exists('getName')) {
        function getName($animal) {
            if (!isset($animal)) {
                return '???';
            }

            return $animal->name;
        }
    }

    if (!function_exists('getRegistrationNumber')) {
        function getRegistrationNumber($animal) {
            if (!isset($animal)) {
                return '???';
            }

            $animal = $animal->toArray();

            if (isset($animal['czkp_registration']['registration_number'])) {
                return $animal['czkp_registration']['type'] .
                ' ' .
                $animal['czkp_registration']['registration_number'] .
                '-' .
                $animal['czkp_registration']['year'];
            }

            return '???';
        }
    }

    if (!function_exists('getBirthdate')) {
        function getBirthDate($animal) {
            if (!isset($animal)) {
                return '??.??.????';
            }

            return isset($animal['birthdate']) ? $animal['birthdate'] : '??.??.????';
        }
    }

    if (!function_exists('getVariety')) {
        function getVariety($animal, $index) {
            if (!isset($animal)) {
                return '-';
            }

            $variety = '';

            if ($index === 0) {
                $variety .= ($animal->eyes_color ?? '-') . ' / ';
                $variety .= ($animal->ear_type ?? '-') . ' / ';
            } else if ($index === 1) {
                $variety .= ($animal->fur_color ?? '-') . ' / ';
                $variety .= ($animal->fur_type ?? '-') . ' / ';
                $variety .= ($animal->markings ?? '-');
            }

            return $variety;
        }
    }

    if (!function_exists('getOwner')) {
        function getOwner($animal) {
            if (!isset($animal) || !isset($animal->owner)) {
                return '???';
            }

            return $animal->owner->name;
        }
    }

    if (!function_exists('getBreeder')) {
        function getBreeder($animal) {
            if (!isset($animal) || !isset($animal->breeder)) {
                return '???';
            }

            return $animal->breeder->name;
        }
    }
@endphp
