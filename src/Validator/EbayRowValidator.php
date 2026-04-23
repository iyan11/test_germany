<?php

declare(strict_types=1);

namespace App\Validator;

use App\Domain\Ebay\EbayRow;

final class EbayRowValidator
{
    /** @return array<int, string> */
    public function validate(EbayRow $row): array
    {
        $required = [
            '*Action(SiteID=Germany|Country=DE|Currency=EUR|Version=941)',
            '*Category',
            '*Title',
            '*Description',
            '*ConditionID',
            '*Quantity',
            '*Format',
            '*StartPrice',
            '*Duration',
            '*Location',
        ];

        $errors = [];
        $values = $row->values();
        foreach ($required as $field) {
            if (!isset($values[$field]) || trim($values[$field]) === '') {
                $errors[] = sprintf('Required field "%s" is empty.', $field);
            }
        }

        return $errors;
    }
}
