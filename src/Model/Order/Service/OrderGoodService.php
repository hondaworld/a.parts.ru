<?php

namespace App\Model\Order\Service;

use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Order\Entity\Order\Order;
use App\ReadModel\Card\ZapCardReserveFetcher;
use App\ReadModel\Income\IncomeStatusFetcher;
use App\ReadModel\Shop\DeleteReasonFetcher;

class OrderGoodService
{
    private ZapCardReserveFetcher $zapCardReserveFetcher;
    private array $deleteReasons;
    private array $incomeStatuses;

    public function __construct(DeleteReasonFetcher $deleteReasonFetcher, ZapCardReserveFetcher $zapCardReserveFetcher, IncomeStatusFetcher $incomeStatusFetcher)
    {
        $this->zapCardReserveFetcher = $zapCardReserveFetcher;
        $this->deleteReasons = $deleteReasonFetcher->assoc();
        $this->incomeStatuses = $incomeStatusFetcher->assoc();
    }

    /**
     * Получение статусов детали
     *
     * @param array $good
     * @return string
     */
    public function getStatus(array $good): string
    {
        if ($good['order_status'] == Order::ORDER_STATUS_MOVED) return "Перенесенный";
        elseif ($good['isDeleted']) return $this->deletedStatus($good);
        elseif ($good['zapSkladID']) return $this->warehouseStatus($good);
        elseif (!$good['incomeID']) return "Не заказано";
        elseif ($good['expenseDocumentID']) return "Отгружено";
        else return $good['income'] ? ($this->incomeStatuses[$good['income']['status']] ?? '') : '';
    }

    public function isDisabled(array $good): bool
    {
        return $good['isExpenseGood'] || $this->isPerem($good) || $good['isDeleted'] || $good['expenseDocumentID'] || $good['incomeDocumentID'];
    }

    public function isPerem(array $good): bool
    {
        return !empty($good['expenseSklads']);
    }

    public function isExpense(array $good): bool
    {
        return $good['isExpenseGood'] && !$good['expenseDocumentID'];
    }

    private function deletedStatus(array $good): string
    {
        return $good['deleteReasonID'] ? $this->deleteReasons[$good['deleteReasonID']] : 'Отказ/возврат';
    }

    private function warehouseStatus(array $good): string
    {
        if ($good['expenseDocumentID']) {
            return "Отгружено";
        } else {
            if ($this->zapCardReserveFetcher->isOrderGoodReserveInPath($good['goodID'])) {
                return "В пути";
            } elseif ($good['reserve'] && $good['reserve']['quantity'] > 0) {
                return "На складе";
            }
        }
        return "Не обработано";
    }

    public function getStyleClasses(array $good, array $expenseDocuments): array
    {
        $arr = [];
        if ($good['isDisabled']) {
            if ($good['isDeleted']) {
                if ($good['order_status'] != 3) {
                    $arr[] = 'text-danger';
                } else {
                    $arr[] = "text-danger-light";
                }
            } else {
                if (isset($expenseDocuments[$good['expenseDocumentID']]["isService"]) && $expenseDocuments[$good['expenseDocumentID']]["isService"] == 1)
                    $arr[] = "text-info";
                else
                    $arr[] = 'text-secondary';
            }
        } else {
            if ($good['zapSkladID']) {
                $arr[] = 'text-success';
            } elseif (!$good['incomeID']) {
                $arr[] = 'font-weight-bold';
            } else {
                if (in_array($good["income"]["status"], [IncomeStatus::FAILURE_USER, IncomeStatus::FAILURE_PROVIDER, IncomeStatus::OUT_OF_STOCK])) {
                    $arr[] = 'text-danger';
                } else if ($good["income"]["status"] == IncomeStatus::IN_WAREHOUSE) {
                    $arr[] = 'text-success';
                } else {
                    $arr[] = 'text-warning';
                }
            }
        }
//        if (!$good['expenseDocumentID'] && ($good['zapSkladID'] || isset($good["income"]) && $good["income"]["status"] == IncomeStatus::IN_WAREHOUSE)) {
//            $arr[] = 'text-success';
//        }
        return $arr;
    }
}