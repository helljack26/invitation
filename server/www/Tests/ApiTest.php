<?php

use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase
{
    private $baseUrl = "http://localhost";

    // Ваш токен для авторизации
    private $token = "Barer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vIiwiYXVkIjoiaHR0cDovLyIsImlhdCI6MTcwNTQ3NzUwNywiZXhwIjoxNzA1NDgxMTA3LCJkYXRhIjp7InVzZXJJZCI6IjIifX0.SYZhcT38s34mIEe3iMkfg044nRe-R98HOgITNc9WlHA";

    public function testGetUsersList()
    {
        $url = $this->baseUrl . "/api/user/list?limit=20";
        $response = $this->makeRequest($url, 'GET');

        $this->assertEquals(200, $response['http_code']);
    }
    public function testRegisterUser()
    {
        $url = $this->baseUrl . "/api/auth/register";
        $data = [
            'username' => 'RegTestNameUsername',
            'email' => 'regtest@example.com',
            'password' => 'yourpassword',
            'first_name' => 'RegTestNameFirst',
            'second_name' => 'RegTestNameSecond',
            'last_name' => 'RegTestNameLast'
        ];
        $response = $this->makeRequest($url, 'POST', $data);
        $this->assertEquals(201, $response['http_code']);
    }
    public function testGetUserCompanyInfo()
    {
        $url = $this->baseUrl . "/api/user/company_info";
        $headers = [
            "Authorization: $this->token",
        ];

        $response = $this->makeRequest($url, 'GET', [], $headers);
        $this->assertEquals(200, $response['http_code']);
    }
    public function testGetUserSubscriptions()
    {
        $url = $this->baseUrl . "/api/subscription/available";
        $headers = [
            "Authorization: $this->token",
        ];

        $response = $this->makeRequest($url, 'GET', [], $headers);
        $this->assertEquals(200, $response['http_code']);
    }

    public function testGetAllCompanySubscriptions()
    {
        $url = $this->baseUrl . "/api/subscription/vievall?limit=10&page=1";
        $headers = [
            "Authorization: $this->token",
        ];

        $response = $this->makeRequest($url, 'GET', [], $headers);
        $this->assertEquals(200, $response['http_code']);
    }
    public function testSubscribeToSubscription()
    {
        $url = $this->baseUrl . "/api/subscription/subscribe";
        $headers = [
            "Authorization: $this->token",
            "Content-Type: application/json",
        ];
        $data = [
            'subscription_type_id' => '1',
        ];

        $response = $this->makeRequest($url, 'POST', $data, $headers);
        $this->assertEquals(200, $response['http_code']);
    }

    public function testUpdateSubscription()
    {
        $url = $this->baseUrl . "/api/subscription/update";
        $headers = [
            "Authorization: $this->token",
            "Content-Type: application/json",
        ];
        $data = [
            'new_subscription_type_id' => '2',
        ];

        $response = $this->makeRequest($url, 'POST', $data, $headers);
        $this->assertEquals(200, $response['http_code']);
    }
    public function testGetAllRoles()
    {
        $url = $this->baseUrl . "/api/role/getAllRole";
        $headers = [
            "Authorization: $this->token",
            "Content-Type: application/json",
        ];
        $response = $this->makeRequest($url, 'GET', [], $headers);
        $this->assertEquals(200, $response['http_code']);
    }

    public function testGetAllPermissions()
    {
        $url = $this->baseUrl . "/api/role/getAllPermissions";
        $headers = [
            "Authorization: Bearer $this->token",
        ];

        $response = $this->makeRequest($url, 'GET', [], $headers);
        $this->assertEquals(200, $response['http_code']);
    }


    public function testGetCurrentUserInfo()
    {
        $url = $this->baseUrl . "/api/user/current";
        $headers = [
            "Authorization: $this->token",
            "Content-Type: application/json",
        ];
        $response = $this->makeRequest($url, 'GET', [], $headers);
        $this->assertEquals(200, $response['http_code']);
    }

    public function testUpdateOrInsertF2Tax()
    {
        $url = $this->baseUrl . "/api/tax/insertF2";
        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer $this->token",

        ];
        $data = [
            "FOP2_Taxes_id" => 25,
            "FOP3_Taxes_id" => null,
            "Month" => 1,
            "Quarter" => 1,
            "Year" => 2024,
            "Default_ZP" => 3000.00,
            "enterpriseId" => 2,
            "EN_Tax_Rate" => 18.5,
            "ESV_Rate" => 20.0,
        ];

        $response = $this->makeRequest($url, 'POST', $data, $headers);
        $this->assertEquals(200, $response['http_code']);
    }
    public function testAssignRole()
    {
        $url = $this->baseUrl . "/api/role/assign";
        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer $this->token",
        ];
        $data = [
            'target_user_id' => '2',
            'role_id' => '3',
        ];

        $response = $this->makeRequest($url, 'POST', $data, $headers);
        $this->assertEquals(200, $response['http_code']);
    }
    public function testGetTotalTax()
    {
        $enterpriseId = 2;
        $url = $this->baseUrl . "/api/tax/calculate?enterpriseId=$enterpriseId";
        $headers = [
            "Authorization: Bearer $this->token",
        ];

        $response = $this->makeRequest($url, 'GET', [], $headers);
        $this->assertEquals(200, $response['http_code']);
    }

    public function testGetOwnEnterprises()
    {
        $url = $this->baseUrl . "/api/enterprise/all";
        $headers = [
            "Authorization: Bearer $this->token",
        ];

        $response = $this->makeRequest($url, 'GET', [], $headers);
        $this->assertEquals(200, $response['http_code']);
    }

    public function testCalculateTaxWithDates()
    {
        $enterpriseId = 1;
        $startDate = '2023-01-01';
        $endDate = '2024-01-31';
        $url = $this->baseUrl . "/api/tax/calculate?enterpriseId=$enterpriseId&startDate=$startDate&endDate=$endDate";
        $headers = [
            "Authorization: Bearer $this->token",
        ];

        $response = $this->makeRequest($url, 'GET', [], $headers);
        $this->assertEquals(200, $response['http_code']);
    }

    public function testGetIntegrationList()
    {
        $url = $this->baseUrl . "/api/integration/list";
        $headers = [
            "Authorization: Bearer $this->token",
        ];

        $response = $this->makeRequest($url, 'GET', [], $headers);
        $this->assertEquals(200, $response['http_code']);
    }

    public function testAddIntegration()
    {
        $url = $this->baseUrl . "/api/integration/add";
        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer $this->token",
        ];
        $data = [
            'integrationType' => 'Torgsoft',
            'settings' => [
                'host' => 'example.com',
                'port' => '1234',
            ],
        ];

        $response = $this->makeRequest($url, 'POST', $data, $headers);
        $this->assertEquals(200, $response['http_code']);
    }
    public function testUpdateIntegration()
    {
        $url = $this->baseUrl . "/api/integration/update";
        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer $this->token",
        ];
        $data = [
            'integrationId' => '1',
            'settings' => [
                'host' => 'newexample.com',
                'port' => '4321',
            ],
        ];

        $response = $this->makeRequest($url, 'PUT', $data, $headers);
        $this->assertEquals(200, $response['http_code']);
    }

    public function testDeleteIntegration()
    {
        $url = $this->baseUrl . "/api/integration/delete";
        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer $this->token",
        ];
        $data = [
            'integrationId' => '1',
        ];

        $response = $this->makeRequest($url, 'DELETE', $data, $headers);
        $this->assertEquals(200, $response['http_code']);
    }

    public function testSyncTorgsoftEnterprises()
    {
        $url = $this->baseUrl . "/api/torgsoft/syncEnterprises";
        $headers = [
            "Authorization: Bearer $this->token",
        ];

        $response = $this->makeRequest($url, 'POST', [], $headers);
        $this->assertEquals(200, $response['http_code']);
    }

    public function testSyncTorgsoftInvoices()
    {
        $url = $this->baseUrl . "/api/torgsoft/syncInvoices";
        $headers = [
            "Authorization: Bearer $this->token",
        ];

        $response = $this->makeRequest($url, 'POST', [], $headers);
        $this->assertEquals(200, $response['http_code']);
    }

    public function testSyncTorgsoftFinancialDocuments()
    {
        $url = $this->baseUrl . "/api/torgsoft/syncFinancialDocuments";
        $headers = [
            "Authorization: Bearer $this->token",
        ];

        $response = $this->makeRequest($url, 'POST', [], $headers);
        $this->assertEquals(200, $response['http_code']);
    }
    public function testSwitchToUser()
    {
        $url = $this->baseUrl . "/api/user/switchToUser";
        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer $this->token",
        ];
        $data = [
            'userId' => 6,
        ];

        $response = $this->makeRequest($url, 'POST', $data, $headers);
        $this->assertEquals(200, $response['http_code']);
    }

    private function makeRequest($url, $method, $data = [])
    {
        $ch = curl_init();

        if ($method === 'POST' || $method === 'PUT') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $headers = [
            "Authorization: Bearer $this->token",
            "Content-Type: application/json",
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return ['http_code' => $httpCode, 'result' => $result];
    }
}