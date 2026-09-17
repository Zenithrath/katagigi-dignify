<?php

return [
    'transaction' => [
        'index' => [
            '_title' => 'Transaction',
            '_subtitle' => 'These are the transactions has been done by the patient after dental service.',
            'actions' => [
                'add' => 'Add Transaction',
                'delete' => 'Delete',
                'print' => 'Print',
                'find' => 'Find Transactions',
            ],
            'labels' => [
                'patient_keyword' => 'Keyword',
                'date_start' => 'Since',
                'date_end' => 'Until Date',
            ],
            'placeholders' => [
                'type_here' => 'Type keyword here...',
            ],
            'helpers' => [
                'patient_keyword' => 'Type the Patient ID, Patient Name or Patient Phone',
            ],
            'table' => [
                'patient' => 'Patient',
                'doctor' => 'Doctor',
                'service' => 'Service(s)',
                'pricing' => 'Pricing',
                'payment-method' => 'Payment Method',
                'date' => 'Transaction Date',
                'patient_id' => 'Patient ID: :id',
                'patient_phone' => 'Phone: :phone',
                'doctor_nipp' => 'NIPP. :nipp',
            ],
        ],
        'detail' => [
            'data' => [
                'patient' => 'Patient',
                'doctor' => 'Doctor',
                'service' => [
                    'title' => 'Service(s)',
                    'discount' => 'Discount',
                ],
                'schedule' => [
                    'recomendation' => 'Next Schedule Recommendation',
                    'reschedule' => 'Reschedule',
                ],
                'pricing' => [
                    'current_payment' => 'Current Payment',
                    'installments' => 'Installments',
                    'title' => 'Pricing',
                    'total' => 'Total',
                    'discount' => 'Discount',
                    'grand_total' => 'Grand Total',
                ],
                'voucher' => [
                    'title' => 'Voucher Code',
                    'code' => 'Voucher Code',
                    'discount' => 'Discount',
                ],
            ],
            'button' => [
                'cancel' => 'Cancel this Transaction',
                'submit_cancel' => 'Submit Cancelation',
            ],
            'helper' => [
                'canceled' => 'This transaction has been canceled at.',
                'reason' => 'due to reason: ',
                'cancel' => 'Just did a writing mistake? You can cancel it!',
            ],
        ],
    ],
    'income' => [
        'index' => [
            '_title' => 'Income Reports',
            '_subtitle' => 'Recapitulation of the incomes monthly. Based on doctor performance, service etc.',
            'lookup' => [
                '_helper' => "If time range isn't set, this page will display this month transactions.",
                'since' => 'Lookup Since',
                'until' => 'Lookup Until',
                'doctor' => [
                    'title' => 'Doctor',
                    'helper' => 'Leave blank to display all doctors.',
                ],
            ],
            'helper' => [
                'empty' => 'There is no report data!',
            ],
        ],
        'table' => [
            'service' => 'Service',
            'price' => 'Price',
            'discount' => 'Discount',
            'income' => 'Income',
        ],
    ],
];
