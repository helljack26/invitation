wrk.method = "POST"
wrk.headers["Authorization"] = "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vIiwiYXVkIjoiaHR0cDovLyIsImlhdCI6MTczMjM3NjM4MiwiZXhwIjoxNzMyMzc5OTgyLCJkYXRhIjp7InVzZXJJZCI6IjIifX0.b4vA6l_qRbube2GPFTlUikantZ6IzgUFYHQwgaSjzVI"
wrk.headers["Content-Type"] = "application/json"

requestIndex = 0
requests = {
    {path = "/api/company/employees", method = "GET", body = nil},
    {path = "/api/role/list", method = "GET", body = nil},
    {path = "/api/role/getUserRole", method = "POST", body = '{"userId": 2}'}
}

function request()
    req = requests[requestIndex % #requests + 1]
    wrk.method = req.method
    wrk.body = req.body
    return wrk.format(nil, req.path)
end
