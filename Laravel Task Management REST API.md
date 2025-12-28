# Laravel Task Management REST API

دليل شامل لبناء REST API لإدارة المهام باستخدام Laravel مع أفضل الممارسات.

---

## 📋 جدول المحتويات

1. [المتطلبات](#المتطلبات)
2. [خطوات الإعداد](#خطوات-الإعداد)
3. [هيكل المشروع](#هيكل-المشروع)
4. [الملفات المطلوبة](#الملفات-المطلوبة)
5. [الـ Endpoints](#الـ-endpoints)
6. [أمثلة الاستخدام](#أمثلة-الاستخدام)
7. [معالجة الأخطاء](#معالجة-الأخطاء)

---

## المتطلبات

- PHP 8.1 أو أحدث
- Composer
- Laravel 10 أو أحدث
- MySQL أو قاعدة بيانات أخرى مدعومة

---

## خطوات الإعداد

### 1. إنشاء مشروع Laravel جديد

```bash
composer create-project laravel/laravel task-api
cd task-api
```

### 2. إنشاء ملف .env وتكوين قاعدة البيانات

```bash
cp .env.example .env
php artisan key:generate
```

عدّل ملف `.env` وأضف بيانات قاعدة البيانات:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_api
DB_USERNAME=root
DB_PASSWORD=
```

### 3. إنشاء قاعدة البيانات

```bash
# إنشاء قاعدة البيانات (إذا لم تكن موجودة)
mysql -u root -p -e "CREATE DATABASE task_api;"
```

### 4. نسخ الملفات المطلوبة

انسخ الملفات التالية إلى مشروعك:

#### أ. Model (app/Models/Task.php)
انسخ محتوى `laravel_task_api_model.php` إلى `app/Models/Task.php`

#### ب. Migration (database/migrations/YYYY_MM_DD_HHMMSS_create_tasks_table.php)
انسخ محتوى `laravel_task_api_migration.php` إلى ملف migration جديد

#### ج. Resource (app/Http/Resources/TaskResource.php)
انسخ محتوى `laravel_task_api_resource.php` إلى `app/Http/Resources/TaskResource.php`

#### د. Controller (app/Http/Controllers/Api/TaskController.php)
أنشئ المجلد `app/Http/Controllers/Api/` إذا لم يكن موجوداً
انسخ محتوى `laravel_task_api_controller.php` إلى `app/Http/Controllers/Api/TaskController.php`

#### هـ. Routes (routes/api.php)
استبدل محتوى `routes/api.php` بمحتوى `laravel_task_api_routes.php`

### 5. تشغيل الـ Migrations

```bash
php artisan migrate
```

### 6. بدء خادم التطوير

```bash
php artisan serve
```

سيبدأ الخادم على `http://localhost:8000`

---

## هيكل المشروع

```
task-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       └── TaskController.php
│   │   └── Resources/
│   │       └── TaskResource.php
│   └── Models/
│       └── Task.php
├── database/
│   └── migrations/
│       └── YYYY_MM_DD_HHMMSS_create_tasks_table.php
├── routes/
│   └── api.php
├── .env
└── composer.json
```

---

## الملفات المطلوبة

### 1. Task Model (`app/Models/Task.php`)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static function createRules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'nullable|in:pending,in_progress,completed',
            'due_date' => 'nullable|date_format:Y-m-d H:i:s',
        ];
    }

    public static function updateRules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'nullable|in:pending,in_progress,completed',
            'due_date' => 'nullable|date_format:Y-m-d H:i:s',
        ];
    }
}
```

### 2. Migration (`database/migrations/YYYY_MM_DD_HHMMSS_create_tasks_table.php`)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title')->notNull();
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->dateTime('due_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
```

### 3. TaskResource (`app/Http/Resources/TaskResource.php`)

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'due_date' => $this->due_date?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
```

### 4. TaskController (`app/Http/Controllers/Api/TaskController.php`)

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $status = $request->query('status');
        $sortBy = $request->query('sort_by', 'created_at');
        $sortOrder = $request->query('sort_order', 'desc');
        $perPage = $request->query('per_page', 15);

        $query = Task::query();

        if ($status) {
            $query->where('status', $status);
        }

        $query->orderBy($sortBy, $sortOrder);
        $tasks = $query->paginate($perPage);

        return TaskResource::collection($tasks);
    }

    public function show(Task $task): TaskResource
    {
        return new TaskResource($task);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(Task::createRules());
        $task = Task::create($validated);

        return response()->json([
            'message' => 'Task created successfully',
            'data' => new TaskResource($task),
        ], 201);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate(Task::updateRules());
        $task->update($validated);

        return response()->json([
            'message' => 'Task updated successfully',
            'data' => new TaskResource($task),
        ], 200);
    }

    public function destroy(Task $task): JsonResponse
    {
        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully',
        ], 200);
    }
}
```

### 5. Routes (`routes/api.php`)

```php
<?php

use App\Http\Controllers\Api\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('tasks')->group(function () {
    Route::get('/', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::post('/', [TaskController::class, 'store'])->name('tasks.store');
    Route::put('/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
});
```

---

## الـ Endpoints

### 1. عرض جميع المهام (GET)

**URL:** `GET /api/tasks`

**Query Parameters:**
- `status` (optional): تصفية حسب الحالة (pending, in_progress, completed)
- `sort_by` (optional): حقل الترتيب (default: created_at)
- `sort_order` (optional): ترتيب تصاعدي أو تنازلي (asc/desc, default: desc)
- `per_page` (optional): عدد العناصر في الصفحة (default: 15)

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "title": "إكمال المشروع",
      "description": "إنهاء جميع المتطلبات",
      "status": "in_progress",
      "due_date": "2024-12-31 23:59:59",
      "created_at": "2024-01-15 10:30:00",
      "updated_at": "2024-01-15 10:30:00"
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/tasks?page=1",
    "last": "http://localhost:8000/api/tasks?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://localhost:8000/api/tasks",
    "per_page": 15,
    "to": 1,
    "total": 1
  }
}
```

---

### 2. عرض مهمة محددة (GET)

**URL:** `GET /api/tasks/{id}`

**Parameters:**
- `id` (required): معرف المهمة

**Response (200 OK):**
```json
{
  "data": {
    "id": 1,
    "title": "إكمال المشروع",
    "description": "إنهاء جميع المتطلبات",
    "status": "in_progress",
    "due_date": "2024-12-31 23:59:59",
    "created_at": "2024-01-15 10:30:00",
    "updated_at": "2024-01-15 10:30:00"
  }
}
```

**Response (404 Not Found):**
```json
{
  "message": "No query results found for model [App\\Models\\Task] ..."
}
```

---

### 3. إنشاء مهمة جديدة (POST)

**URL:** `POST /api/tasks`

**Request Body:**
```json
{
  "title": "إكمال المشروع",
  "description": "إنهاء جميع المتطلبات",
  "status": "pending",
  "due_date": "2024-12-31 23:59:59"
}
```

**Response (201 Created):**
```json
{
  "message": "Task created successfully",
  "data": {
    "id": 1,
    "title": "إكمال المشروع",
    "description": "إنهاء جميع المتطلبات",
    "status": "pending",
    "due_date": "2024-12-31 23:59:59",
    "created_at": "2024-01-15 10:30:00",
    "updated_at": "2024-01-15 10:30:00"
  }
}
```

**Response (422 Unprocessable Entity):**
```json
{
  "message": "The title field is required.",
  "errors": {
    "title": ["The title field is required."]
  }
}
```

---

### 4. تحديث مهمة (PUT)

**URL:** `PUT /api/tasks/{id}`

**Parameters:**
- `id` (required): معرف المهمة

**Request Body:**
```json
{
  "title": "إكمال المشروع - محدث",
  "status": "in_progress"
}
```

**Response (200 OK):**
```json
{
  "message": "Task updated successfully",
  "data": {
    "id": 1,
    "title": "إكمال المشروع - محدث",
    "description": "إنهاء جميع المتطلبات",
    "status": "in_progress",
    "due_date": "2024-12-31 23:59:59",
    "created_at": "2024-01-15 10:30:00",
    "updated_at": "2024-01-15 11:00:00"
  }
}
```

---

### 5. حذف مهمة (DELETE)

**URL:** `DELETE /api/tasks/{id}`

**Parameters:**
- `id` (required): معرف المهمة

**Response (200 OK):**
```json
{
  "message": "Task deleted successfully"
}
```

---

## أمثلة الاستخدام

### استخدام cURL

#### 1. عرض جميع المهام
```bash
curl -X GET "http://localhost:8000/api/tasks"
```

#### 2. عرض مهمة محددة
```bash
curl -X GET "http://localhost:8000/api/tasks/1"
```

#### 3. إنشاء مهمة جديدة
```bash
curl -X POST "http://localhost:8000/api/tasks" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "إكمال المشروع",
    "description": "إنهاء جميع المتطلبات",
    "status": "pending",
    "due_date": "2024-12-31 23:59:59"
  }'
```

#### 4. تحديث مهمة
```bash
curl -X PUT "http://localhost:8000/api/tasks/1" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "in_progress"
  }'
```

#### 5. حذف مهمة
```bash
curl -X DELETE "http://localhost:8000/api/tasks/1"
```

### استخدام JavaScript/Fetch

```javascript
// عرض جميع المهام
fetch('http://localhost:8000/api/tasks')
  .then(response => response.json())
  .then(data => console.log(data));

// إنشاء مهمة جديدة
fetch('http://localhost:8000/api/tasks', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    title: 'إكمال المشروع',
    description: 'إنهاء جميع المتطلبات',
    status: 'pending',
    due_date: '2024-12-31 23:59:59'
  })
})
  .then(response => response.json())
  .then(data => console.log(data));

// عرض مهمة محددة
fetch('http://localhost:8000/api/tasks/1')
  .then(response => response.json())
  .then(data => console.log(data));

// تحديث مهمة
fetch('http://localhost:8000/api/tasks/1', {
  method: 'PUT',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    status: 'in_progress'
  })
})
  .then(response => response.json())
  .then(data => console.log(data));

// حذف مهمة
fetch('http://localhost:8000/api/tasks/1', {
  method: 'DELETE'
})
  .then(response => response.json())
  .then(data => console.log(data));
```

### استخدام Python

```python
import requests
import json

BASE_URL = 'http://localhost:8000/api'

# عرض جميع المهام
response = requests.get(f'{BASE_URL}/tasks')
print(response.json())

# إنشاء مهمة جديدة
task_data = {
    'title': 'إكمال المشروع',
    'description': 'إنهاء جميع المتطلبات',
    'status': 'pending',
    'due_date': '2024-12-31 23:59:59'
}
response = requests.post(f'{BASE_URL}/tasks', json=task_data)
print(response.json())

# عرض مهمة محددة
response = requests.get(f'{BASE_URL}/tasks/1')
print(response.json())

# تحديث مهمة
update_data = {'status': 'in_progress'}
response = requests.put(f'{BASE_URL}/tasks/1', json=update_data)
print(response.json())

# حذف مهمة
response = requests.delete(f'{BASE_URL}/tasks/1')
print(response.json())
```

---

## معالجة الأخطاء

### 1. خطأ التحقق من البيانات (422)

**Request:**
```bash
curl -X POST "http://localhost:8000/api/tasks" \
  -H "Content-Type: application/json" \
  -d '{"description": "بدون عنوان"}'
```

**Response:**
```json
{
  "message": "The title field is required.",
  "errors": {
    "title": ["The title field is required."]
  }
}
```

### 2. خطأ عدم وجود المورد (404)

**Request:**
```bash
curl -X GET "http://localhost:8000/api/tasks/999"
```

**Response:**
```json
{
  "message": "No query results found for model [App\\Models\\Task]."
}
```

### 3. خطأ الخادم (500)

في حالة حدوث خطأ في الخادم، ستتلقى استجابة 500 مع رسالة الخطأ.

---

## ملاحظات مهمة

### 1. التحقق من البيانات

تم تطبيق التحقق من البيانات في `Task::createRules()` و `Task::updateRules()`:

- **title**: مطلوب، نص، بحد أقصى 255 حرف
- **description**: اختياري، نص، بحد أقصى 1000 حرف
- **status**: اختياري، يجب أن يكون أحد: pending, in_progress, completed
- **due_date**: اختياري، تنسيق التاريخ Y-m-d H:i:s

### 2. الترتيب والتصفية

يمكن استخدام query parameters للترتيب والتصفية:

```bash
# تصفية حسب الحالة
curl -X GET "http://localhost:8000/api/tasks?status=pending"

# ترتيب حسب تاريخ الاستحقاق
curl -X GET "http://localhost:8000/api/tasks?sort_by=due_date&sort_order=asc"

# تحديد عدد العناصر في الصفحة
curl -X GET "http://localhost:8000/api/tasks?per_page=10"
```

### 3. Pagination

جميع نتائج `index()` مُرقّمة تلقائياً. استخدم `page` parameter للتنقل:

```bash
curl -X GET "http://localhost:8000/api/tasks?page=2"
```

### 4. الصيغ الزمنية

جميع التواريخ بصيغة ISO 8601 مع الوقت:
- **Input:** `Y-m-d H:i:s` (مثال: `2024-12-31 23:59:59`)
- **Output:** `Y-m-d H:i:s` (مثال: `2024-12-31 23:59:59`)

---

## الخطوات التالية

بعد إعداد الـ API الأساسي، يمكنك إضافة:

1. **Authentication:** إضافة Sanctum للمصادقة
2. **Authorization:** إضافة سياسات للتحقق من الصلاحيات
3. **Logging:** تسجيل جميع العمليات
4. **Rate Limiting:** تحديد معدل الطلبات
5. **Testing:** كتابة اختبارات PHPUnit
6. **Documentation:** توثيق الـ API باستخدام Swagger/OpenAPI

---

## المراجع

- [Laravel Documentation](https://laravel.com/docs)
- [Laravel API Resources](https://laravel.com/docs/eloquent-resources)
- [Laravel Validation](https://laravel.com/docs/validation)
- [Laravel Routing](https://laravel.com/docs/routing)

---

**تم الإنشاء:** يناير 2024
**الإصدار:** 1.0.0
