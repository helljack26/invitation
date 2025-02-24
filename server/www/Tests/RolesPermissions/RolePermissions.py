import unittest
import requests
import json

BASE_URL = "http://localhost/api"
EMAIL = "test@example.com"
PASSWORD = "yourpassword"

class TestRoleAPI(unittest.TestCase):
    token = None
    headers = None

    @classmethod
    def setUpClass(cls):
        """Получение токена перед запуском всех тестов"""
        cls.token = cls.get_token()
        if cls.token is None:
            raise Exception("Не удалось получить токен")
        cls.headers = {
            "Content-Type": "application/json",
            "Authorization": f"Bearer {cls.token}"
        }
        print(f"Получен токен: {cls.token}")

    @staticmethod
    def get_token():
        """Получение токена аутентификации"""
        url = f"{BASE_URL}/auth/login"
        data = {"email": EMAIL, "password": PASSWORD}
        response = requests.post(url, headers={"Content-Type": "application/json"}, json=data)
        if response.status_code == 200:
            return response.json().get("jwt")
        return None

    def setUp(self):
        """Инициализация перед каждым тестом"""
        self.created_roles = []

    def tearDown(self):
        """Очистка после каждого теста"""
        for role_id in self.created_roles:
            self.delete_role(role_id)

    def delete_role(self, role_id):
        """Удалить созданную роль"""
        url = f"{BASE_URL}/role/delete"
        response = requests.post(url, headers=self.headers, json={"role_id": role_id})
        print(f"Удаление роли (role_id={role_id}): {response.status_code}")

    def test_create_role(self):
        """Создать роль для компании"""
        url = f"{BASE_URL}/role/create"
        data = {
            "role_name": "Test Manager",
            "description": "Role for testing",
            "permissions": ["view_content", "edit_content"]
        }
        response = requests.post(url, headers=self.headers, json=data)
        print(f"Ответ сервера (test_create_role): {response.status_code}, {response.text}")
        self.assertEqual(response.status_code, 201)
        response_data = response.json()
        self.assertIn("role_id", response_data)
        self.created_roles.append(response_data["role_id"])

        def test_list_roles(self):
            """Получить список всех ролей компании"""
            url = f"{BASE_URL}/role/list"
            response = requests.get(url, headers=self.headers)
            print(f"Ответ сервера (test_list_roles): {response.status_code}, {response.text}")
            
            # Проверка, что статус код равен 200
            self.assertEqual(response.status_code, 200, f"Ошибка: {response.text}")
            
            # Проверка, что ответ является словарем с ключом 'roles'
            response_data = response.json()
            self.assertIn("roles", response_data)
            
            # Проверка, что 'roles' — это список
            self.assertIsInstance(response_data["roles"], list)

    def test_get_all_permissions(self):
        """Получить список всех пермишинов"""
        url = f"{BASE_URL}/role/getAllPermissions"
        response = requests.get(url, headers=self.headers)
        print(f"Ответ сервера (test_get_all_permissions): {response.status_code}, {response.text}")
        self.assertEqual(response.status_code, 200)
        self.assertIsInstance(response.json(), dict)

    def test_assign_role(self):
        """Назначить роль пользователю"""
        # Сначала создаём роль
        role_id = self.create_test_role()
        self.created_roles.append(role_id)

        url = f"{BASE_URL}/role/assign"
        data = {
            "target_user_id": 3,
            "role_id": role_id
        }
        response = requests.post(url, headers=self.headers, json=data)
        print(f"Ответ сервера (test_assign_role): {response.status_code}, {response.text}")
        self.assertEqual(response.status_code, 200)
        response_data = response.json()
        self.assertIn("message", response_data)

    def test_assign_permissions(self):
        """Назначить пермишин роли"""
        # Сначала создаём роль
        role_id = self.create_test_role()
        self.created_roles.append(role_id)

        url = f"{BASE_URL}/role/assignPermissions"
        data = {
            "role_id": role_id,
            "permissions": ["create_content", "delete_content"]
        }
        response = requests.post(url, headers=self.headers, json=data)
        print(f"Ответ сервера (test_assign_permissions): {response.status_code}, {response.text}")
        self.assertEqual(response.status_code, 200)
        response_data = response.json()
        self.assertIn("message", response_data)

    def test_get_permissions_by_role(self):
        """Получить пермишины конкретной роли"""
        # Сначала создаём роль
        role_id = self.create_test_role()
        self.created_roles.append(role_id)

        url = f"{BASE_URL}/role/getPermissionsByRole"
        data = {"role_id": role_id}
        response = requests.post(url, headers=self.headers, json=data)
        print(f"Ответ сервера (test_get_permissions_by_role): {response.status_code}, {response.text}")
        self.assertEqual(response.status_code, 200)
        self.assertIsInstance(response.json(), list)

    def create_test_role(self):
        """Вспомогательный метод для создания роли"""
        url = f"{BASE_URL}/role/create"
        data = {
            "role_name": "Temporary Role",
            "description": "Temporary role for testing",
            "permissions": ["view_content"]
        }
        response = requests.post(url, headers=self.headers, json=data)
        response_data = response.json()
        return response_data.get("role_id")


if __name__ == "__main__":
    unittest.main()
