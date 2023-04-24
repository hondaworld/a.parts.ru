<?php

namespace App\Model\Order\Service;

use App\Model\Finance\Entity\FinanceType\FinanceType;
use App\Model\Firm\Entity\Schet\Schet;

class DocumentService
{
    /**
     * @param array $good
     * @param array $expenseDocuments
     * @param array $incomeDocuments
     * @return array
     */
    public function document(array $good, array $expenseDocuments, array $incomeDocuments): ?array
    {
        if ($good['expenseDocumentID'] && isset($expenseDocuments[$good['expenseDocumentID']])) {
            $document = $expenseDocuments[$good['expenseDocumentID']];
            return [
                'name' => $document['doc_name'],
                'url' => '/' . $document['doc_path'] . '?id=' . $good['expenseDocumentID'],
                'number' => $document['document_num'],
                'date' => $document['dateofadded'],
                'manager_name' => $document['manager_name']
            ];
        } elseif ($good['incomeDocumentID'] && isset($incomeDocuments[$good['incomeDocumentID']])) {
            $document = $incomeDocuments[$good['incomeDocumentID']];
            return [
                'name' => $document['doc_name'],
                'url' => '/' . $document['doc_path'] . '?id=' . $good['incomeDocumentID'],
                'number' => $document['document_num'],
                'date' => $document['dateofadded']
            ];
        }
        return null;
    }

    /**
     * @param array $good
     * @param array $schets
     * @return array
     */
    public function schet(array $good, array $schets): ?array
    {
        if ($good['schetID'] && isset($schets[$good['schetID']])) {
            $schet = $schets[$good['schetID']];
            return [
                'number' => $schet['schet_num'],
                'date' => $schet['dateofadded'],
                'url' => '/schet.php?id=' . $good['schetID'],
                'is_print' => $schet['finance_typeID'] != FinanceType::DEFAULT_BEZNAL_CARD_ID,
                'is_exist' => in_array($schet['status'], [Schet::NOT_PAID, Schet::PAID])
            ];
        }
        return null;
    }
}