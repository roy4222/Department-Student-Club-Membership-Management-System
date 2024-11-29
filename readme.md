# 社團會費管理系統開發指南

## 1. 系統概述
此系統用於管理社團成員資料及會費繳納狀況，預計使用人數約50人。系統提供會員管理、會費追蹤、通知發送及報表匯出等功能。

## 2. 核心功能

### 2.1 成員資料管理
- 基本資料管理（姓名、學號、聯繫方式、入學時間）
- 職位紀錄
- 權限分級：社長、幹部、一般成員

### 2.2 會費管理
- 每學期會費金額設定
- 繳費狀態追蹤
- 繳費紀錄管理

### 2.3 通知系統
- 繳費期限前提醒
- 逾期提醒
- 通過Email發送通知

### 2.4 報表功能
- Excel格式匯出
- 包含欄位：姓名、學號、繳費日期、金額
- 依照繳費狀態顯示會員清單

## 3. 技術規格

### 3.1 開發環境
- 後端：PHP
- 資料庫：MySQL
- 前端：HTML, CSS, JavaScript

### 3.2 資料庫結構
```sql
-- 成員資料表 (members)
CREATE TABLE members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id VARCHAR(10) UNIQUE,
    name VARCHAR(50),
    email VARCHAR(100),
    phone VARCHAR(20),
    entry_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 職位紀錄表 (positions)
CREATE TABLE positions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT,
    position_name VARCHAR(50),
    start_date DATE,
    end_date DATE,
    FOREIGN KEY (member_id) REFERENCES members(id)
);

-- 會費設定表 (fee_settings)
CREATE TABLE fee_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    semester VARCHAR(20),
    amount DECIMAL(10,2),
    due_date DATE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES members(id)
);

-- 繳費紀錄表 (payments)
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT,
    fee_setting_id INT,
    amount DECIMAL(10,2),
    payment_date DATE,
    payment_method VARCHAR(50),
    receipt_no VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id),
    FOREIGN KEY (fee_setting_id) REFERENCES fee_settings(id)
);
```

## 4. 專案結構
```
project/
├── config/                 # 配置文件
│   ├── database.php       # 數據庫配置
│   └── config.php         # 系統配置
│
├── public/                 # 公開訪問目錄
│   ├── index.php          # 入口文件
│   ├── assets/            # 靜態資源
│   │   ├── css/          # CSS文件
│   │   ├── js/           # JavaScript文件
│   │   └── images/       # 圖片資源
│   │
│   └── uploads/           # 上傳文件目錄
│
├── src/                    # 源代碼目錄
│   ├── Controllers/       # 控制器
│   │   ├── AuthController.php
│   │   ├── MemberController.php
│   │   ├── FeeController.php
│   │   └── ReportController.php
│   │
│   ├── Models/            # 模型
│   │   ├── Member.php
│   │   ├── Position.php
│   │   ├── FeeSetting.php
│   │   └── Payment.php
│   │
│   └── Utils/             # 工具類
│       ├── Database.php
│       ├── Auth.php
│       └── Mailer.php
│
├── views/                  # 視圖文件
│   ├── layouts/           # 布局模板
│   ├── auth/              # 認證相關
│   ├── members/           # 會員管理
│   ├── fees/              # 會費管理
│   └── reports/           # 報表相關
│
└── tests/                  # 測試文件
```

## 5. 開發階段規劃

### 5.1 第一階段：基礎功能
1. 專案環境搭建
   - 設置開發環境
   - 創建資料庫
   - 配置基本路由

2. 會員管理模塊
   ```php
   // MemberController.php 示例
   class MemberController {
       public function index() {
           // 顯示會員列表
       }
       
       public function create() {
           // 創建新會員
       }
       
       public function update($id) {
           // 更新會員資料
       }
   }
   ```

3. 權限系統
   ```php
   // Auth.php 示例
   class Auth {
       public static function checkRole($role) {
           // 檢查用戶權限
       }
       
       public static function login($username, $password) {
           // 用戶登入
       }
   }
   ```

### 5.2 第二階段：會費管理
1. 會費設定功能
2. 繳費紀錄管理
3. 報表匯出功能

### 5.3 第三階段：通知系統
1. Email通知功能
2. 提醒機制實作

### 5.4 第四階段：系統優化
1. 界面美化
2. 功能優化
3. 測試與除錯

## 6. 部署指南

### 6.1 環境要求
- PHP >= 7.4
- MySQL >= 5.7
- Web服務器（Apache/Nginx）
- Composer（PHP套件管理器）

### 6.2 安裝步驟
1. 克隆代碼庫
```bash
git clone [repository_url]
cd club-fee-system
```

2. 安裝依賴
```bash
composer install
```

3. 配置環境
```bash
cp .env.example .env
# 編輯 .env 文件設置數據庫連接等信息
```

4. 初始化數據庫
```bash
php scripts/init_database.php
```

## 7. 測試指南

### 7.1 單元測試
```bash
# 運行所有測試
./vendor/bin/phpunit tests/

# 運行特定測試
./vendor/bin/phpunit tests/MemberTest.php
```

### 7.2 功能測試清單
- [ ] 會員註冊/登入
- [ ] 會員資料管理
- [ ] 會費設定
- [ ] 繳費紀錄
- [ ] 報表匯出
- [ ] 郵件通知

## 8. 安全性考慮
1. SQL注入防護
2. XSS防護
3. CSRF防護
4. 密碼加密
5. 資料備份

## 9. 維護指南
1. 定期備份資料庫
2. 檢查錯誤日誌
3. 更新系統安全補丁
4. 清理暫存文件

## 10. 常見問題解答

### 10.1 部署相關
Q: 如何修改資料庫配置？
A: 編輯 config/database.php 文件

### 10.2 使用相關
Q: 如何重置管理員密碼？
A: 使用管理工具執行重置命令

## 11. 聯絡方式
如有任何問題，請聯繫系統管理員或在項目Issues中提出。

## 12. 版本歷史
- v0.1.0 - 基礎功能開發
- v0.2.0 - 會費管理模塊
- v0.3.0 - 通知系統
- v1.0.0 - 正式發布

## 13. 授權資訊
此系統為內部使用，未經許可不得對外分享或商業使用.

# 社團會員管理系統

一個用於管理學生社團會員、活動和行政功能的網頁應用系統。

## 最新更新（2024-01-09）

### 1. 會員管理功能增強
- 實作完整的會員 CRUD（新增、讀取、更新、刪除）功能
- 統一的 API 端點（member_api.php）處理所有會員操作
- 改進的錯誤處理和用戶反饋
- 新增會員活動參與記錄功能

### 2. 介面優化
- 實作表格欄位排序功能（學號、姓名、科系、活動次數）
- 改進的模態框處理機制
- 更好的表單驗證和錯誤提示
- 優化的資料顯示格式

### 3. 資料庫操作改進
- 使用交易確保資料一致性
- 優化的 SQL 查詢
- 改進的關聯資料處理
- 更安全的資料庫操作

### 4. 錯誤處理和除錯
- 添加詳細的錯誤日誌
- 改進的錯誤提示訊息
- 完整的 API 錯誤處理
- 前端除錯功能增強

## 主要功能

### 會員管理
- 新增、編輯、刪除會員資料
- 會員列表動態排序
- 會員職位管理
- 活動參與記錄追踪

### 活動管理
- 活動參與統計
- 出席狀態追踪
- 詳細的活動記錄

### 介面功能
- 響應式設計
- 直覺的操作介面
- 即時的用戶反饋
- 現代化的視覺設計

## 技術規格

### 前端技術
- Bootstrap 5.3.0
- jQuery 3.7.0
- FontAwesome 6.4.0
- 現代化的 JavaScript

### 後端技術
- PHP 7.x
- MySQL
- PDO 資料庫連接
- RESTful API 設計

## 安裝需求

### 系統需求
- PHP 7.x 或更高版本
- MySQL 資料庫
- Apache 網頁伺服器
- 現代網頁瀏覽器

### 相依套件
- Bootstrap 5.3.0
- jQuery 3.7.0
- FontAwesome 6.4.0

## 安裝步驟

1. 複製專案檔案到網頁伺服器
2. 匯入資料庫結構（sql/club_db.sql）
3. 設定資料庫連線（config/database.php）
4. 確保檔案權限正確
5. 透過瀏覽器訪問系統

## 開發者注意事項

### 程式碼組織
- 使用 MVC 架構概念
- 統一的 API 處理方式
- 模組化的功能實作
- 一致的程式碼風格

### 安全考量
- 資料驗證和清理
- SQL 注入防護
- XSS 防護
- CSRF 防護

### 未來規劃
1. 更完整的權限控制
2. 進階的報表功能
3. 國際化支援
4. 更多的客製化選項

## 作者
[您的名字]

## 授權
[授權資訊]