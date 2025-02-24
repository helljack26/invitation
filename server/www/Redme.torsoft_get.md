
# Отримати інформацію про складські документи
curl -X POST http://10.3.0.36:5000/api/Invoce/GetInvoices \
     -H "Content-Type: application/json" \
     -d '{
           "date1": "2023-01-01",
           "date2": "2024-12-31",
           "enterpriseId": 2,
           "isPayVAT": true
         }'


curl -X POST http://10.3.0.36:5000/api/Fin/GetFiscalFinDocs \
     -H "Content-Type: application/json" \
     -d '{
           "date1": "2023-01-01",
           "date2": "2024-12-31"
         }'