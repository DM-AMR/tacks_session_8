# أمثلة متقدمة لـ Task Management API

هذا الملف يحتوي على أمثلة متقدمة وحالات استخدام شاملة للـ API.

---

## 📚 جدول المحتويات

1. [أمثلة متقدمة مع cURL](#أمثلة-متقدمة-مع-curl)
2. [أمثلة مع JavaScript](#أمثلة-مع-javascript)
3. [أمثلة مع Python](#أمثلة-مع-python)
4. [أمثلة مع PHP](#أمثلة-مع-php)
5. [حالات استخدام واقعية](#حالات-استخدام-واقعية)

---

## أمثلة متقدمة مع cURL

### 1. عرض جميع المهام مع الترتيب والتصفية

```bash
# عرض المهام المعلقة مرتبة حسب تاريخ الاستحقاق
curl -X GET "http://localhost:8000/api/tasks?status=pending&sort_by=due_date&sort_order=asc"

# عرض المهام المكتملة مرتبة حسب تاريخ الإنشاء (الأحدث أولاً)
curl -X GET "http://localhost:8000/api/tasks?status=completed&sort_by=created_at&sort_order=desc"

# عرض جميع المهام مع 5 عناصر في الصفحة
curl -X GET "http://localhost:8000/api/tasks?per_page=5"

# الانتقال إلى الصفحة الثانية
curl -X GET "http://localhost:8000/api/tasks?page=2&per_page=10"
```

### 2. إنشاء مهام بحالات مختلفة

```bash
# إنشاء مهمة بسيطة (بدون وصف أو تاريخ استحقاق)
curl -X POST "http://localhost:8000/api/tasks" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "مهمة بسيطة"
  }'

# إنشاء مهمة مع جميع التفاصيل
curl -X POST "http://localhost:8000/api/tasks" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "تطوير الميزة الجديدة",
    "description": "تطوير نظام الدفع والتكامل مع بوابة الدفع",
    "status": "in_progress",
    "due_date": "2024-12-31 23:59:59"
  }'

# إنشاء مهمة عاجلة (بدون وصف)
curl -X POST "http://localhost:8000/api/tasks" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "إصلاح الخطأ الحرج",
    "status": "pending",
    "due_date": "2024-01-20 17:00:00"
  }'
```

### 3. تحديث المهام بطرق مختلفة

```bash
# تحديث الحالة فقط
curl -X PUT "http://localhost:8000/api/tasks/1" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "in_progress"
  }'

# تحديث العنوان والوصف
curl -X PUT "http://localhost:8000/api/tasks/1" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "عنوان محدث",
    "description": "وصف محدث"
  }'

# تحديث تاريخ الاستحقاق
curl -X PUT "http://localhost:8000/api/tasks/1" \
  -H "Content-Type: application/json" \
  -d '{
    "due_date": "2024-12-25 18:00:00"
  }'

# تحديث شامل
curl -X PUT "http://localhost:8000/api/tasks/1" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "عنوان جديد",
    "description": "وصف جديد",
    "status": "completed",
    "due_date": "2024-12-31 23:59:59"
  }'
```

### 4. حذف مهام

```bash
# حذف مهمة محددة
curl -X DELETE "http://localhost:8000/api/tasks/1"

# حذف مهام متعددة (يتطلب حلقة)
for id in 1 2 3 4 5; do
  curl -X DELETE "http://localhost:8000/api/tasks/$id"
done
```

---

## أمثلة مع JavaScript

### 1. فئة Helper للـ API

```javascript
class TaskAPI {
  constructor(baseUrl = 'http://localhost:8000/api') {
    this.baseUrl = baseUrl;
  }

  /**
   * عرض جميع المهام
   */
  async getTasks(filters = {}) {
    const params = new URLSearchParams();
    
    if (filters.status) params.append('status', filters.status);
    if (filters.sortBy) params.append('sort_by', filters.sortBy);
    if (filters.sortOrder) params.append('sort_order', filters.sortOrder);
    if (filters.perPage) params.append('per_page', filters.perPage);
    if (filters.page) params.append('page', filters.page);

    const url = params.toString() 
      ? `${this.baseUrl}/tasks?${params.toString()}`
      : `${this.baseUrl}/tasks`;

    const response = await fetch(url);
    return response.json();
  }

  /**
   * عرض مهمة محددة
   */
  async getTask(id) {
    const response = await fetch(`${this.baseUrl}/tasks/${id}`);
    return response.json();
  }

  /**
   * إنشاء مهمة جديدة
   */
  async createTask(taskData) {
    const response = await fetch(`${this.baseUrl}/tasks`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(taskData),
    });
    return response.json();
  }

  /**
   * تحديث مهمة
   */
  async updateTask(id, taskData) {
    const response = await fetch(`${this.baseUrl}/tasks/${id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(taskData),
    });
    return response.json();
  }

  /**
   * حذف مهمة
   */
  async deleteTask(id) {
    const response = await fetch(`${this.baseUrl}/tasks/${id}`, {
      method: 'DELETE',
    });
    return response.json();
  }
}

// الاستخدام
const api = new TaskAPI();

// عرض جميع المهام المعلقة
api.getTasks({ status: 'pending' })
  .then(data => console.log(data));

// إنشاء مهمة جديدة
api.createTask({
  title: 'مهمة جديدة',
  description: 'وصف المهمة',
  status: 'pending',
  due_date: '2024-12-31 23:59:59'
})
  .then(data => console.log(data));
```

### 2. مثال مع React

```jsx
import { useState, useEffect } from 'react';

function TaskManager() {
  const [tasks, setTasks] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [filter, setFilter] = useState('');

  const api = 'http://localhost:8000/api';

  // عرض المهام
  useEffect(() => {
    fetchTasks();
  }, [filter]);

  const fetchTasks = async () => {
    setLoading(true);
    try {
      const url = filter 
        ? `${api}/tasks?status=${filter}`
        : `${api}/tasks`;
      
      const response = await fetch(url);
      const data = await response.json();
      setTasks(data.data);
      setError(null);
    } catch (err) {
      setError('خطأ في تحميل المهام');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const createTask = async (title) => {
    try {
      const response = await fetch(`${api}/tasks`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ title }),
      });
      const data = await response.json();
      setTasks([...tasks, data.data]);
    } catch (err) {
      console.error('خطأ في إنشاء المهمة:', err);
    }
  };

  const updateTask = async (id, updates) => {
    try {
      const response = await fetch(`${api}/tasks/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(updates),
      });
      const data = await response.json();
      setTasks(tasks.map(t => t.id === id ? data.data : t));
    } catch (err) {
      console.error('خطأ في تحديث المهمة:', err);
    }
  };

  const deleteTask = async (id) => {
    try {
      await fetch(`${api}/tasks/${id}`, { method: 'DELETE' });
      setTasks(tasks.filter(t => t.id !== id));
    } catch (err) {
      console.error('خطأ في حذف المهمة:', err);
    }
  };

  return (
    <div className="container">
      <h1>إدارة المهام</h1>
      
      {error && <div className="error">{error}</div>}
      
      <div className="filters">
        <button onClick={() => setFilter('')}>الكل</button>
        <button onClick={() => setFilter('pending')}>معلقة</button>
        <button onClick={() => setFilter('in_progress')}>قيد التنفيذ</button>
        <button onClick={() => setFilter('completed')}>مكتملة</button>
      </div>

      {loading ? (
        <p>جاري التحميل...</p>
      ) : (
        <ul>
          {tasks.map(task => (
            <li key={task.id}>
              <h3>{task.title}</h3>
              <p>{task.description}</p>
              <span className={`status ${task.status}`}>{task.status}</span>
              <button onClick={() => updateTask(task.id, { status: 'completed' })}>
                اكتمل
              </button>
              <button onClick={() => deleteTask(task.id)}>حذف</button>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}

export default TaskManager;
```

### 3. مثال مع Async/Await

```javascript
async function manageTasksWithAsyncAwait() {
  const baseUrl = 'http://localhost:8000/api/tasks';

  try {
    // 1. إنشاء مهمة جديدة
    console.log('إنشاء مهمة جديدة...');
    const createResponse = await fetch(baseUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        title: 'مهمة جديدة',
        description: 'وصف المهمة',
        status: 'pending',
      }),
    });
    const createdTask = await createResponse.json();
    const taskId = createdTask.data.id;
    console.log('تم إنشاء المهمة:', createdTask.data);

    // 2. عرض المهمة
    console.log('\nعرض المهمة...');
    const getResponse = await fetch(`${baseUrl}/${taskId}`);
    const task = await getResponse.json();
    console.log('المهمة:', task.data);

    // 3. تحديث المهمة
    console.log('\nتحديث المهمة...');
    const updateResponse = await fetch(`${baseUrl}/${taskId}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        status: 'in_progress',
        description: 'وصف محدث',
      }),
    });
    const updatedTask = await updateResponse.json();
    console.log('تم تحديث المهمة:', updatedTask.data);

    // 4. عرض جميع المهام
    console.log('\nعرض جميع المهام...');
    const listResponse = await fetch(baseUrl);
    const allTasks = await listResponse.json();
    console.log(`إجمالي المهام: ${allTasks.meta.total}`);
    console.log('المهام:', allTasks.data);

    // 5. حذف المهمة
    console.log('\nحذف المهمة...');
    const deleteResponse = await fetch(`${baseUrl}/${taskId}`, {
      method: 'DELETE',
    });
    const deleteResult = await deleteResponse.json();
    console.log('الرسالة:', deleteResult.message);

  } catch (error) {
    console.error('خطأ:', error);
  }
}

// تشغيل الدالة
manageTasksWithAsyncAwait();
```

---

## أمثلة مع Python

### 1. فئة Helper للـ API

```python
import requests
from typing import Optional, Dict, List
from datetime import datetime

class TaskAPIClient:
    def __init__(self, base_url: str = 'http://localhost:8000/api'):
        self.base_url = base_url
        self.session = requests.Session()

    def get_tasks(self, status: Optional[str] = None, 
                  sort_by: str = 'created_at',
                  sort_order: str = 'desc',
                  per_page: int = 15,
                  page: int = 1) -> Dict:
        """عرض جميع المهام"""
        params = {
            'sort_by': sort_by,
            'sort_order': sort_order,
            'per_page': per_page,
            'page': page,
        }
        if status:
            params['status'] = status

        response = self.session.get(f'{self.base_url}/tasks', params=params)
        return response.json()

    def get_task(self, task_id: int) -> Dict:
        """عرض مهمة محددة"""
        response = self.session.get(f'{self.base_url}/tasks/{task_id}')
        return response.json()

    def create_task(self, title: str, description: str = None,
                   status: str = 'pending',
                   due_date: str = None) -> Dict:
        """إنشاء مهمة جديدة"""
        data = {
            'title': title,
            'description': description,
            'status': status,
        }
        if due_date:
            data['due_date'] = due_date

        response = self.session.post(f'{self.base_url}/tasks', json=data)
        return response.json()

    def update_task(self, task_id: int, **kwargs) -> Dict:
        """تحديث مهمة"""
        response = self.session.put(
            f'{self.base_url}/tasks/{task_id}',
            json=kwargs
        )
        return response.json()

    def delete_task(self, task_id: int) -> Dict:
        """حذف مهمة"""
        response = self.session.delete(f'{self.base_url}/tasks/{task_id}')
        return response.json()

    def get_pending_tasks(self) -> Dict:
        """عرض المهام المعلقة"""
        return self.get_tasks(status='pending')

    def get_in_progress_tasks(self) -> Dict:
        """عرض المهام قيد التنفيذ"""
        return self.get_tasks(status='in_progress')

    def get_completed_tasks(self) -> Dict:
        """عرض المهام المكتملة"""
        return self.get_tasks(status='completed')

# الاستخدام
client = TaskAPIClient()

# عرض جميع المهام
tasks = client.get_tasks()
print(f"إجمالي المهام: {tasks['meta']['total']}")

# إنشاء مهمة جديدة
new_task = client.create_task(
    title='مهمة جديدة',
    description='وصف المهمة',
    status='pending',
    due_date='2024-12-31 23:59:59'
)
print(f"تم إنشاء المهمة: {new_task['data']['id']}")

# تحديث المهمة
updated = client.update_task(
    new_task['data']['id'],
    status='in_progress'
)
print(f"تم تحديث المهمة: {updated['message']}")

# حذف المهمة
deleted = client.delete_task(new_task['data']['id'])
print(f"تم حذف المهمة: {deleted['message']}")
```

### 2. مثال مع معالجة الأخطاء

```python
import requests
from requests.exceptions import RequestException, Timeout

def safe_api_call(method, url, **kwargs):
    """استدعاء آمن للـ API مع معالجة الأخطاء"""
    try:
        response = requests.request(method, url, timeout=10, **kwargs)
        response.raise_for_status()
        return response.json(), None
    except Timeout:
        return None, 'انتهت مهلة الانتظار'
    except RequestException as e:
        return None, f'خطأ في الطلب: {str(e)}'
    except ValueError:
        return None, 'استجابة غير صحيحة'

# الاستخدام
base_url = 'http://localhost:8000/api'

# إنشاء مهمة
data, error = safe_api_call(
    'POST',
    f'{base_url}/tasks',
    json={
        'title': 'مهمة جديدة',
        'description': 'وصف المهمة',
    }
)

if error:
    print(f'خطأ: {error}')
else:
    print(f'نجح: {data}')
```

---

## أمثلة مع PHP

### 1. فئة Helper للـ API

```php
<?php

class TaskAPIClient
{
    private string $baseUrl;

    public function __construct(string $baseUrl = 'http://localhost:8000/api')
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * عرض جميع المهام
     */
    public function getTasks(
        ?string $status = null,
        string $sortBy = 'created_at',
        string $sortOrder = 'desc',
        int $perPage = 15,
        int $page = 1
    ): array {
        $query = [
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
            'per_page' => $perPage,
            'page' => $page,
        ];

        if ($status) {
            $query['status'] = $status;
        }

        $url = $this->baseUrl . '/tasks?' . http_build_query($query);
        return $this->makeRequest('GET', $url);
    }

    /**
     * عرض مهمة محددة
     */
    public function getTask(int $id): array
    {
        return $this->makeRequest('GET', "{$this->baseUrl}/tasks/{$id}");
    }

    /**
     * إنشاء مهمة جديدة
     */
    public function createTask(
        string $title,
        ?string $description = null,
        string $status = 'pending',
        ?string $dueDate = null
    ): array {
        $data = [
            'title' => $title,
            'description' => $description,
            'status' => $status,
        ];

        if ($dueDate) {
            $data['due_date'] = $dueDate;
        }

        return $this->makeRequest('POST', "{$this->baseUrl}/tasks", $data);
    }

    /**
     * تحديث مهمة
     */
    public function updateTask(int $id, array $data): array
    {
        return $this->makeRequest('PUT', "{$this->baseUrl}/tasks/{$id}", $data);
    }

    /**
     * حذف مهمة
     */
    public function deleteTask(int $id): array
    {
        return $this->makeRequest('DELETE', "{$this->baseUrl}/tasks/{$id}");
    }

    /**
     * إجراء طلب HTTP
     */
    private function makeRequest(
        string $method,
        string $url,
        ?array $data = null
    ): array {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $httpCode,
            'data' => json_decode($response, true),
        ];
    }
}

// الاستخدام
$client = new TaskAPIClient();

// عرض جميع المهام
$tasks = $client->getTasks();
echo "إجمالي المهام: " . $tasks['data']['meta']['total'] . "\n";

// إنشاء مهمة جديدة
$newTask = $client->createTask(
    'مهمة جديدة',
    'وصف المهمة',
    'pending',
    '2024-12-31 23:59:59'
);
echo "تم إنشاء المهمة: " . $newTask['data']['data']['id'] . "\n";

// تحديث المهمة
$updated = $client->updateTask(
    $newTask['data']['data']['id'],
    ['status' => 'in_progress']
);
echo "تم تحديث المهمة\n";

// حذف المهمة
$deleted = $client->deleteTask($newTask['data']['data']['id']);
echo "تم حذف المهمة\n";
```

---

## حالات استخدام واقعية

### 1. نظام إدارة المشاريع

```javascript
class ProjectTaskManager {
  constructor(apiUrl) {
    this.apiUrl = apiUrl;
  }

  /**
   * الحصول على ملخص المشروع
   */
  async getProjectSummary() {
    const pending = await this.getTasks('pending');
    const inProgress = await this.getTasks('in_progress');
    const completed = await this.getTasks('completed');

    return {
      total: pending.meta.total + inProgress.meta.total + completed.meta.total,
      pending: pending.meta.total,
      inProgress: inProgress.meta.total,
      completed: completed.meta.total,
      completionPercentage: Math.round(
        (completed.meta.total / (pending.meta.total + inProgress.meta.total + completed.meta.total)) * 100
      ),
    };
  }

  /**
   * الحصول على المهام العاجلة
   */
  async getUrgentTasks() {
    const tasks = await this.getTasks('pending');
    const now = new Date();
    
    return tasks.data.filter(task => {
      if (!task.due_date) return false;
      const dueDate = new Date(task.due_date);
      const daysUntilDue = Math.floor((dueDate - now) / (1000 * 60 * 60 * 24));
      return daysUntilDue <= 3 && daysUntilDue >= 0;
    });
  }

  /**
   * إنشاء مهام من قالب
   */
  async createTasksFromTemplate(template) {
    const createdTasks = [];
    
    for (const taskData of template.tasks) {
      const response = await fetch(`${this.apiUrl}/tasks`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(taskData),
      });
      const result = await response.json();
      createdTasks.push(result.data);
    }

    return createdTasks;
  }

  private async getTasks(status) {
    const response = await fetch(`${this.apiUrl}/tasks?status=${status}`);
    return response.json();
  }
}

// الاستخدام
const manager = new ProjectTaskManager('http://localhost:8000/api');

// الحصول على ملخص المشروع
manager.getProjectSummary().then(summary => {
  console.log(`إجمالي المهام: ${summary.total}`);
  console.log(`معلقة: ${summary.pending}`);
  console.log(`قيد التنفيذ: ${summary.inProgress}`);
  console.log(`مكتملة: ${summary.completed}`);
  console.log(`نسبة الإنجاز: ${summary.completionPercentage}%`);
});

// الحصول على المهام العاجلة
manager.getUrgentTasks().then(tasks => {
  console.log('المهام العاجلة:', tasks);
});
```

### 2. نظام الإشعارات

```python
import requests
from datetime import datetime, timedelta

class TaskNotificationSystem:
    def __init__(self, api_url):
        self.api_url = api_url

    def check_overdue_tasks(self):
        """التحقق من المهام المتأخرة"""
        response = requests.get(f'{self.api_url}/tasks')
        tasks = response.json()['data']
        
        now = datetime.now()
        overdue_tasks = []

        for task in tasks:
            if task['due_date'] and task['status'] != 'completed':
                due_date = datetime.fromisoformat(task['due_date'])
                if due_date < now:
                    overdue_tasks.append(task)

        return overdue_tasks

    def check_upcoming_deadlines(self, days=3):
        """التحقق من المهام القادمة"""
        response = requests.get(f'{self.api_url}/tasks')
        tasks = response.json()['data']
        
        now = datetime.now()
        upcoming_tasks = []

        for task in tasks:
            if task['due_date'] and task['status'] != 'completed':
                due_date = datetime.fromisoformat(task['due_date'])
                days_until = (due_date - now).days
                
                if 0 <= days_until <= days:
                    upcoming_tasks.append({
                        'task': task,
                        'days_until': days_until,
                    })

        return upcoming_tasks

    def send_notifications(self):
        """إرسال الإشعارات"""
        overdue = self.check_overdue_tasks()
        upcoming = self.check_upcoming_deadlines()

        if overdue:
            print(f'⚠️ لديك {len(overdue)} مهام متأخرة:')
            for task in overdue:
                print(f'  - {task["title"]} (استحقاق: {task["due_date"]})')

        if upcoming:
            print(f'📅 لديك {len(upcoming)} مهام قادمة:')
            for item in upcoming:
                task = item['task']
                days = item['days_until']
                print(f'  - {task["title"]} ({days} أيام)')

# الاستخدام
notifier = TaskNotificationSystem('http://localhost:8000/api')
notifier.send_notifications()
```

---

## نصائح وأفضل الممارسات

1. **استخدام الترقيم:** عند التعامل مع عدد كبير من المهام، استخدم `per_page` و `page`
2. **التصفية:** استخدم `status` للتصفية حسب حالة المهمة
3. **الترتيب:** استخدم `sort_by` و `sort_order` للحصول على النتائج بالترتيب المطلوب
4. **معالجة الأخطاء:** تحقق دائماً من رموز الحالة (status codes)
5. **التخزين المؤقت:** استخدم caching لتقليل عدد الطلبات

---

**تم الإنشاء:** يناير 2024
**الإصدار:** 1.0.0
