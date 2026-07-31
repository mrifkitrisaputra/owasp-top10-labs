<?php
/**
 * NAC Cafe - Internal Notes API
 * Hidden endpoint (Flag 5 - discovered via XSS/JS analysis)
 * Returns supplier information
 */

header('Content-Type: application/json');
header('X-Internal-Service: NAC-Cafe-API');

// No authentication required (misconfiguration)
$response = [
    'status' => 'success',
    'service' => 'NAC Cafe Internal Notes',
    'timestamp' => date('Y-m-d H:i:s'),
    'data' => [
        'suppliers' => [
            [
                'id' => 'SUP-001',
                'name' => 'PT_Bumi_Arabica_Sejahtera',
                'type' => 'Primary Coffee Supplier',
                'region' => 'Aceh, Indonesia',
                'contract_start' => '2019-01-01',
                'contract_end' => '2026-12-31',
                'monthly_supply' => '500kg',
                'grade' => 'Premium Arabica Grade 1',
                'payment_terms' => 'Net 30',
                'contact' => 'procurement@bumiarabica.co.id',
                'notes' => 'Supplier utama biji kopi arabika grade premium. Hubungan bisnis sejak awal berdirinya cafe.'
            ],
            [
                'id' => 'SUP-002',
                'name' => 'CV Robusta Nusantara',
                'type' => 'Secondary Coffee Supplier',
                'region' => 'Lampung, Indonesia',
                'contract_start' => '2020-06-15',
                'contract_end' => '2025-06-15',
                'monthly_supply' => '200kg',
                'grade' => 'Robusta Grade A'
            ],
            [
                'id' => 'SUP-003',
                'name' => 'PT Susu Segar Indonesia',
                'type' => 'Dairy Supplier',
                'region' => 'Bandung, Indonesia',
                'monthly_supply' => '300 liter',
                'grade' => 'Fresh Whole Milk'
            ]
        ],
        'internal_notes' => [
            'Total supplier aktif: 3',
            'Review supplier berikutnya: Q1 2025',
            'Budget procurement bulanan: Rp 45.000.000'
        ]
    ]
];

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
