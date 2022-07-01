<?php

namespace App\Model;

class Utils {

    /**
     * Function to parse given array to another array
     * @param int $id
     * @param array $relevantItems
     * @param string $dbNameOfId
     * @param string $dbNameOfRelItems
     * @return array
     */
    public static function getArrayForDynamicInsert(int $id, array $relevantItems, string $dbNameOfId, string $dbNameOfRelItems): array {
        if($id < 0) {
            throw new InvalidArgumentException("Id must be greater than zero.");
        }

        if(empty($dbNameOfId) || empty($dbNameOfRelItems)) {
            throw new InvalidArgumentException("dbNameOfId and dbnameOfRelIds must be filled.");
        }

        $values = array();
        foreach ($relevantItems as $relevantItem) {
            $values[] = [
                $dbNameOfId => $id,
                $dbNameOfRelItems => $relevantItem,
            ];
        }
        return $values;
    }

}