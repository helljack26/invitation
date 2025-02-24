<?php
namespace Service;

use Model\WarehouseDocumentModel;
use Model\FinancialDocumentModel;
use Model\EnterpriseModel;
use Model\TaxModel;

class TaxCalculator
{
    protected $warehouseModel;
    protected $financialModel;
    protected $enterpriseModel; // Добавлено для доступа к данным предприятий
    protected $taxModel; // Добавлено для доступа к данным предприятий

    public function __construct(
        WarehouseDocumentModel $warehouseDocumentModel,
        FinancialDocumentModel $financialDocumentModel,
        EnterpriseModel $enterpriseModel, // Новый параметр
        TaxModel $taxModel // Новый параметр

    ) {
        $this->warehouseModel = $warehouseDocumentModel;
        $this->financialModel = $financialDocumentModel;
        $this->enterpriseModel = $enterpriseModel; // Инициализация нового поля
        $this->taxModel = $taxModel; // Инициализация нового поля
    }


    // Метод для расчета ПДВ (НДС)
    public function calculateVAT($eGRPOUId, $startDate = null, $endDate = null)
    {
        $documents = $this->warehouseModel->getWarehouseDocumentsByEnterprise($eGRPOUId, $startDate, $endDate);
        $vatSumIncome = 0;     // ПДВ по приходам
        $vatSumExpense = 0;   // ПДВ по расходам


        foreach ($documents as $document) {
            if ($document['theForm'] === 'приход') {
                $vatSumIncome += $document['vatTotal'];
            } else if ($document['theForm'] === 'расход') {
                $vatSumExpense += $document['vatTotal'];
            }
        }

        $vatToPay = $vatSumIncome - $vatSumExpense;
        return $vatToPay;
    }


    // Метод для расчета акциза
    public function calculateExcise($eGRPOUId, $startDate = null, $endDate = null)
    {
        $documents = $this->warehouseModel->getWarehouseDocumentsByEnterprise($eGRPOUId, $startDate, $endDate);
        $exciseSumIncome = 0;    // Акциз по приходам
        $exciseSumExpense = 0;     // Акциз по расходам


        foreach ($documents as $document) {
            if ($document['theForm'] === 'приход') {
                $exciseSumIncome += $document['exciseTaxTotal'];
            } else if ($document['theForm'] === 'расход') {
                $exciseSumExpense += $document['exciseTaxTotal'];
            }
        }

        $exciseToPay = $exciseSumIncome - $exciseSumExpense;
        return $exciseToPay;
    }

    // Метод для расчета общей суммы по финансовым документам
    // тут добавить мин сумму зп 6700 ее надо будет множит на плавающий % и умножить на кол месяцев
    public function fob_2_en_esv($eGRPOUId, $startDate = null, $endDate = null)
    {
        // Отримання фінансових документів для конкретного підприємства
        $financialDocuments = $this->financialModel->getFinancialDocumentsByEnterprise($eGRPOUId, $startDate, $endDate);

        $totalIncome = 0.0; // Загальна сума доходів
        $combackProduct = 0.0; // Повернення товару

        // Добавим массив для суммирования доходов по кварталам
        $quarterlyIncome = [
            'Q1' => 0.0,
            'Q2' => 0.0,
            'Q3' => 0.0,
            'Q4' => 0.0,
        ];

        // Добавим массив для суммирования доходов по месяцам
        $monthlyIncome = [
            '1' => 0.0,
            '2' => 0.0,
            '3' => 0.0,
            '4' => 0.0,
            '5' => 0.0,
            '6' => 0.0,
            '7' => 0.0,
            '8' => 0.0,
            '9' => 0.0,
            '10' => 0.0,
            '11' => 0.0,
            '12' => 0.0,
        ];

        foreach ($financialDocuments as $document) {
            $documentDate = new DateTime($document['theDate']);


            // Определение квартала
            $quarter = ceil(date('n', $documentDate->getTimestamp()) / 3);

            // Определение месяца
            $month = date('n', $documentDate->getTimestamp());

            if ($document['moveCategory'] === 'Приходный') {
                $totalIncome += (float) $document['sumMoney'];
                $quarterlyIncome["Q$quarter"] += (float) $document['sumMoney'];
                $monthlyIncome["$month"] += (float) $document['sumMoney'];
            } elseif ($document['moveCategory'] === 'Расходный') {
                if ($document['analysisChipherName'] === '361 Розрахунки з покупцями (Торгова виручка)') {
                    // Товари, які було повернуто
                    $combackProduct += (float) $document['sumMoney'];
                    $quarterlyIncome["Q$quarter"] -= (float) $document['sumMoney'];
                    $monthlyIncome["$month"] -= (float) $document['sumMoney'];
                }
            }
        }

        // Общий финансовый результат (доходы минус расходы)
        $totalIncome = $totalIncome - $combackProduct;
        // Загальний дохід компанії
        return [
            'totalIncome' => $totalIncome,
            'quarterlyIncome' => $quarterlyIncome,
            'monthlyIncome' => $monthlyIncome,
            'combackProduct' => $combackProduct,
        ];
    }


    public function getFop2Tax($eGRPOUId, $startDate = null, $endDate = null)
    {
        $taxDocuments = $this->taxModel->getFOP2_TaxesByEnterprise($eGRPOUId, $startDate, $endDate);

        $sumEsvRates = 0;
        $monthlySumEsvRates = []; // Массив для хранения SumEsvRate по месяцам
        $QuarterSum1 = $QuarterSum2 = $QuarterSum3 = $QuarterSum4 = $SumEsvRate = 0;

        foreach ($taxDocuments as &$taxDocument) {
            //ЕСВ
            if ($taxDocument['ESV_Rate'] === '22') {
                $SumEsvRate = (float) $taxDocument['Default_ZP'] * $taxDocument['ESV_Rate'] / 100;
                $month = (int) $taxDocument['Month']; // Получение месяца из налогового документа
                // Добавление SumEsvRate для каждого месяца
                if (!isset ($monthlySumEsvRates[$month])) {
                    $monthlySumEsvRates[$month] = 0;
                }
                $sumEsvRates += $SumEsvRate;
                $monthlySumEsvRates[$month] = $SumEsvRate;
                // Create a new array with the additional field
                $newTaxDocument = $taxDocument;
                $newTaxDocument['sumEsvRates'] = $monthlySumEsvRates[$month];

                // Replace the original array with the new one
                $taxDocument = $newTaxDocument;
            }
            //EН
            switch ($taxDocument['Quarter']) {
                case '1':
                    $QuarterSum1 += (float) $taxDocument['Default_ZP'] * $taxDocument['EN_Tax_Rate'] / 100;
                    break;
                case '2':
                    $QuarterSum2 += (float) $taxDocument['Default_ZP'] * $taxDocument['EN_Tax_Rate'] / 100;
                    break;
                case '3':
                    $QuarterSum3 += (float) $taxDocument['Default_ZP'] * $taxDocument['EN_Tax_Rate'] / 100;
                    break;
                case '4':
                    $QuarterSum4 += (float) $taxDocument['Default_ZP'] * $taxDocument['EN_Tax_Rate'] / 100;
                    break;
            }
        }

        return [
            'QuarterSum1' => $QuarterSum1,
            'QuarterSum2' => $QuarterSum2,
            'QuarterSum3' => $QuarterSum3,
            'QuarterSum4' => $QuarterSum4,
            'SumEsvRate' => $sumEsvRates, // Вывод SumEsvRate по месяцам
            'taxDocuments' => $taxDocuments, // Если требуется вернуть исходные документы налогов
        ];
    }
    public function getFop3Tax($eGRPOUId, $startDate = null, $endDate = null)
    {
        $taxDocuments = $this->taxModel->getFOP3_TaxesByEnterprise($eGRPOUId, $startDate, $endDate);
        $sumEsvRates = 0;
        $monthlySumEsvRates = [];
        // Общий доход и месячные доходы с использованием функции fob_2_en_esv
        $totalIncome = $this->fob_2_en_esv($eGRPOUId, $startDate);
        $monthlyIncomes = $totalIncome['monthlyIncome'];

        foreach ($taxDocuments as &$taxDocument) {
            if ($taxDocument['ESV_Rate'] === '22') {
                $SumEsvRate = (float) $taxDocument['Default_ZP'] * $taxDocument['ESV_Rate'] / 100;
                $month = (int) $taxDocument['Month'];

                if (!isset ($monthlySumEsvRates[$month])) {
                    $monthlySumEsvRates[$month] = 0;
                }

                $sumEsvRates += $SumEsvRate;
                $monthlySumEsvRates[$month] = $SumEsvRate;

                $newTaxDocument = $taxDocument;
                $newTaxDocument['sumEsvRates'] = $monthlySumEsvRates[$month];
                $taxDocument = $newTaxDocument;
            }

            $month = $taxDocument['Month'];

            // Найти соответствующий доход для текущего месяца
            if (isset ($monthlyIncomes[$month])) {
                $monthlyIncome = $monthlyIncomes[$month];

                $monthTaxRate = (float) $taxDocument['EN_Tax_Rate'];

                // Рассчитываем суммы налогов для каждого месяца
                ${"MonthSum$month"} = (float) $monthlyIncome * $monthTaxRate / 100;
            }
        }

        // var_dump($taxDocuments);
        //в чат гпт посмотреть и доделать кварталы эту сумы по 3м месяцам
        $QuarterSum1F3 = $MonthSum1 + $MonthSum2 + $MonthSum3;
        $QuarterSum2F3 = $MonthSum4 + $MonthSum5 + $MonthSum6;
        $QuarterSum3F3 = $MonthSum7 + $MonthSum8 + $MonthSum9;
        $QuarterSum4F3 = $MonthSum10 + $MonthSum11 + $MonthSum12;

        return [
            'QuarterSum1F3' => $QuarterSum1F3,
            'QuarterSum2F3' => $QuarterSum2F3,
            'QuarterSum3F3' => $QuarterSum3F3,
            'QuarterSum4F3' => $QuarterSum4F3,
            'SumEsvRate' => $sumEsvRates, // Вывод SumEsvRate по месяцам
            'taxDocuments' => $taxDocuments, // Если требуется вернуть ис
        ];
    }
    // бекап калькуляцый
    // foreach ($financialDocuments as $document) {
    //     if ($document['moveCategory'] === 'Приходный') {
    //         $totalIncome += (float) $document['sumMoney'];
    //     } elseif ($document['moveCategory'] === 'Расходный') {
    //         if ($document['analysisChipherName'] === '361 Розрахунки з покупцями (Торгова виручка)') {
    //             // Товари, які було повернуто
    //             $combackProduct += (float) $document['sumMoney'];
    //         } elseif ($document['analysisChipherName'] === '6412 ЄП') {
    //             // Щомісячні авансові внески
    //             $esv += (float) $document['sumMoney'];
    //         } elseif ($document['analysisChipherName'] === '651 ЄСВ (ЗП)') {
    //             // Щомісячні внески ЄСВ від зарплати
    //             $esvZP += (float) $document['sumMoney'];
    //             $esvZPNames[] = array('theDate' => $document['theDate'], 'esv_zp_single' => $document['sumMoney']);
    //         } elseif ($document['analysisChipherName'] === 'ЗП збут') {
    //             // Додати дані, коли відбувається відбір за альтернативною умовою
    //             $esvZPNames[] = array('esv_zp_single' => $document['sumMoney']);
    //         }
    //     }
    // }

    // foreach ($esvZPNames as &$item) {
    //     // Перевірка, чи існує ключ 'esv_zp_single' у поточному елементі масиву
    //     if (isset($item['esv_zp_single'])) {
    //         $esv_zp_single = (float) $item['esv_zp_single'];
    //         $twentyTwoPercent = $esv_zp_single * 0.22; // Обчислення 22% від 'esv_zp_single'

    //         // Додавання ключа 'twenty_two_percent' до поточного елемента масиву з обчисленим значенням
    //         $item['twenty_two_percent'] = $twentyTwoPercent;
    //     }
    // }




    // public function calculateFinancialSum($eGRPOUId, $startDate = null, $endDate = null) {
    //     // Получение финансовых документов для конкретного предприятия
    //     $financialDocuments = $this->financialModel->getFinancialDocumentsByEnterprise($eGRPOUId, $startDate, $endDate);
    //     $totalIncome = 0.0; // Общая сумма доходов
    //     $totalExpense = 0.0; // Общая сумма расходов

    //     foreach ($financialDocuments as $document) {
    //         if ($document['moveCategory'] === 'Приходный') {
    //             $totalIncome += (float) $document['sumMoney'];
    //         } else if ($document['moveCategory'] === 'Расходный') {
    //             $totalExpense += (float) $document['sumMoney'];
    //         }
    //     }
    //     // Общий финансовый результат (доходы минус расходы)
    //     $totalIncome = $totalIncome - $totalExpense;
    //     return $totalIncome;
    // }


    public function calculateTotalTax($eGRPOUId, $startDate = null, $endDate = null)
    {
        $vatToPay = $this->calculateVAT($eGRPOUId, $startDate, $endDate);
        $exciseToPay = $this->calculateExcise($eGRPOUId, $startDate, $endDate);
        $totalIncome = $this->fob_2_en_esv($eGRPOUId, $startDate, $endDate);
        $getFop2Tax = $this->getFop2Tax($eGRPOUId, $startDate, $endDate);
        $getFop3Tax = $this->getFop3Tax($eGRPOUId, $startDate, $endDate);

        $totalTax = 1;
        $message = "";

        // Получение информации о предприятии
        $enterpriseInfo = $this->enterpriseModel->getEnterpriseById($eGRPOUId);

        // Расчет налога на основе налоговой ставки предприятия
        $taxRate = $enterpriseInfo['VatTaxes'] / 100;
        $realTaxRate = $enterpriseInfo['VatTaxes'];
        $totalTax = ($vatToPay + $exciseToPay + $totalIncome['totalIncome']) * $taxRate;

        // Добавление дополнительной отладочной информации

        return ['getFop2Tax' => $getFop2Tax, 'getFop3Tax' => $getFop3Tax, 'totalTax' => $totalTax, 'message' => $message, 'taxRate' => $realTaxRate, 'vatToPay' => $vatToPay, "exciseToPay" => $exciseToPay, 'totalIncome' => $totalIncome];
    }
}