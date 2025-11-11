# Test SSE với Postman

## ✅ Có thể test SSE bằng Postman!

Postman hỗ trợ Server-Sent Events (SSE) từ phiên bản 10.x trở lên. Đây là hướng dẫn chi tiết:

## 🚀 Cách test SSE trong Postman

### Bước 1: Tạo Request mới

1. Mở Postman
2. Tạo request mới (New → HTTP Request)
3. Method: **GET**
4. URL: `http://localhost:8000/api/v1/notification/{eventId}`
    - Thay `{eventId}` bằng ID thực tế của event

### Bước 2: Cấu hình Headers (nếu cần authentication)

```
Accept: text/event-stream
Authorization: Bearer YOUR_JWT_TOKEN
```

### Bước 3: Send Request

1. Click **Send**
2. Response sẽ hiển thị ở dạng stream
3. Bạn sẽ thấy data được gửi liên tục từ server

## 📸 Ví dụ Response trong Postman

### Response ban đầu (event: initial)

```
event: initial
data: {"count":3,"notifications":[{"id":"673185f5b38fb8a8e306b3a8","event_id":"673185f5b38fb8a8e306b3a7","organizer_id":"673185f5b38fb8a8e306b3a6","message":"Sự kiện sắp bắt đầu","created_at":"2025-11-11T10:00:00.000000Z","updated_at":"2025-11-11T10:00:00.000000Z"}]}

: heartbeat

: heartbeat
```

### Khi có notification mới (event: notification)

```
event: notification
data: {"id":"673185f5b38fb8a8e306b3a9","event_id":"673185f5b38fb8a8e306b3a7","organizer_id":"673185f5b38fb8a8e306b3a6","message":"Thông báo mới","created_at":"2025-11-11T10:05:00.000000Z","updated_at":"2025-11-11T10:05:00.000000Z"}

: heartbeat
```

## 🎯 Postman Collection Export

### File: `SSE_Notifications.postman_collection.json`

```json
{
    "info": {
        "name": "SSE Notifications API",
        "description": "Test Server-Sent Events cho Notification System",
        "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
    },
    "item": [
        {
            "name": "SSE - Get Notifications by Event (Stream)",
            "request": {
                "method": "GET",
                "header": [
                    {
                        "key": "Accept",
                        "value": "text/event-stream",
                        "type": "text"
                    },
                    {
                        "key": "Authorization",
                        "value": "Bearer {{jwt_token}}",
                        "type": "text",
                        "description": "JWT token nếu API yêu cầu authentication"
                    }
                ],
                "url": {
                    "raw": "{{base_url}}/api/v1/notification/:eventId",
                    "host": ["{{base_url}}"],
                    "path": ["api", "v1", "notification", ":eventId"],
                    "variable": [
                        {
                            "key": "eventId",
                            "value": "673185f5b38fb8a8e306b3a7",
                            "description": "ID của event cần lấy notifications"
                        }
                    ]
                },
                "description": "SSE endpoint để nhận real-time notifications cho một event"
            },
            "response": []
        },
        {
            "name": "Create Notification (để test SSE)",
            "request": {
                "method": "POST",
                "header": [
                    {
                        "key": "Content-Type",
                        "value": "application/json",
                        "type": "text"
                    },
                    {
                        "key": "Authorization",
                        "value": "Bearer {{jwt_token}}",
                        "type": "text"
                    }
                ],
                "body": {
                    "mode": "raw",
                    "raw": "{\n    \"event_id\": \"673185f5b38fb8a8e306b3a7\",\n    \"organizer_id\": \"673185f5b38fb8a8e306b3a6\",\n    \"message\": \"Test notification từ Postman\"\n}"
                },
                "url": {
                    "raw": "{{base_url}}/api/v1/notification",
                    "host": ["{{base_url}}"],
                    "path": ["api", "v1", "notification"]
                },
                "description": "Tạo notification mới để test xem SSE có nhận được không"
            },
            "response": []
        },
        {
            "name": "Get All Notifications (Normal REST)",
            "request": {
                "method": "GET",
                "header": [
                    {
                        "key": "Authorization",
                        "value": "Bearer {{jwt_token}}",
                        "type": "text"
                    }
                ],
                "url": {
                    "raw": "{{base_url}}/api/v1/notification",
                    "host": ["{{base_url}}"],
                    "path": ["api", "v1", "notification"]
                },
                "description": "Lấy tất cả notifications (không dùng SSE)"
            },
            "response": []
        }
    ],
    "variable": [
        {
            "key": "base_url",
            "value": "http://localhost:8000",
            "type": "string"
        },
        {
            "key": "jwt_token",
            "value": "your_jwt_token_here",
            "type": "string"
        }
    ]
}
```

## 📝 Hướng dẫn sử dụng Postman Collection

### 1. Import Collection

-   Copy nội dung JSON trên
-   Postman → Import → Raw text → Paste → Import

### 2. Setup Environment Variables

-   Click vào nút "No Environment" → "Globals"
-   Thêm variables:
    -   `base_url`: `http://localhost:8000`
    -   `jwt_token`: JWT token của bạn (nếu cần authentication)

### 3. Test SSE Stream

**Scenario 1: Test SSE connection**

1. Mở request "SSE - Get Notifications by Event (Stream)"
2. Thay `eventId` bằng ID thực tế
3. Click **Send**
4. Postman sẽ giữ connection và hiển thị stream data
5. Bạn sẽ thấy:
    - Event `initial` với tất cả notifications hiện có
    - Heartbeat comments (`: heartbeat`)
    - Event `notification` khi có thông báo mới

**Scenario 2: Test real-time notification**

1. Giữ request SSE đang chạy (tab 1)
2. Mở tab mới trong Postman
3. Gọi request "Create Notification" với cùng `event_id`
4. Quay lại tab SSE → Bạn sẽ thấy notification mới xuất hiện ngay lập tức!

## 🔍 Lưu ý khi test với Postman

### ✅ Ưu điểm

-   Postman tự động parse SSE events
-   Hiển thị data dễ đọc
-   Có thể xem raw response
-   Giữ connection ổn định

### ⚠️ Hạn chế

-   Không có giao diện đẹp như demo HTML
-   Khó test concurrent connections
-   Không có visual notifications/toast

### 💡 Tips

1. **Xem Raw Response**: Click vào "Raw" tab để thấy format SSE thực tế
2. **Stop Stream**: Click "Cancel" để dừng stream
3. **Multiple Tabs**: Mở nhiều tab để test concurrent connections
4. **Console Log**: Mở Postman Console (View → Show Postman Console) để xem detailed logs

## 🧪 Test Scenarios

### Test 1: Connection & Initial Data

```
1. Send SSE request
2. Verify nhận được event "initial"
3. Verify count và danh sách notifications đúng
4. Verify heartbeat đều đặn
```

### Test 2: Real-time Notification

```
1. Giữ SSE connection (Tab 1)
2. Tạo notification mới (Tab 2)
3. Verify notification xuất hiện ở Tab 1 trong vòng 2 giây
```

### Test 3: Timeout

```
1. Send SSE request
2. Đợi 5 phút (timeout default)
3. Verify nhận được event "timeout"
4. Verify connection tự động đóng
```

### Test 4: Multiple Events

```
1. Giữ SSE connection
2. Tạo 3-5 notifications liên tiếp
3. Verify tất cả notifications đều được nhận
```

## 🛠️ Alternatives nếu Postman không hoạt động

### 1. CURL Command

```bash
curl -N -H "Accept: text/event-stream" \
     -H "Authorization: Bearer YOUR_JWT_TOKEN" \
     http://localhost:8000/api/v1/notification/673185f5b38fb8a8e306b3a7
```

### 2. Browser DevTools

```javascript
// Mở Console trong browser
const es = new EventSource(
    "http://localhost:8000/api/v1/notification/673185f5b38fb8a8e306b3a7"
);
es.addEventListener("initial", (e) =>
    console.log("Initial:", JSON.parse(e.data))
);
es.addEventListener("notification", (e) =>
    console.log("New:", JSON.parse(e.data))
);
```

### 3. Visual Studio Code REST Client Extension

```http
### SSE - Get Notifications
GET http://localhost:8000/api/v1/notification/673185f5b38fb8a8e306b3a7
Accept: text/event-stream
Authorization: Bearer {{jwt_token}}
```

### 4. Sử dụng demo HTML

```
Mở: http://localhost:8000/sse-demo.html
(Đây là cách tốt nhất để test và visualize)
```

## 📊 Expected Output trong Postman

```
event: initial
data: {"count":2,"notifications":[...]}

: heartbeat

: heartbeat

event: notification
data: {"id":"...","event_id":"...","message":"New notification"}

: heartbeat

: heartbeat

event: timeout
data: {"message":"Connection timeout"}
```

## 🎬 Video Tutorial (Postman SSE)

### Các bước trong video:

1. Import collection
2. Setup environment variables
3. Send SSE request
4. Observe stream data
5. Create notification in another tab
6. Verify real-time update
7. Check connection timeout

## ✨ Kết luận

**CÓ**, bạn hoàn toàn có thể test SSE bằng Postman!

**Khuyến nghị:**

-   ✅ Dùng Postman cho: Quick testing, API debugging, automation tests
-   ✅ Dùng demo HTML (`sse-demo.html`) cho: Visual testing, UX preview, demo cho stakeholders
-   ✅ Dùng curl cho: Server-side testing, CI/CD, scripting

**Best Practice:**

1. Test với Postman trước để verify API hoạt động
2. Sau đó test với HTML demo để verify client integration
3. Cuối cùng test với real frontend application

Done! 🎉
